<?php

namespace Tekkcraft\EpubGenerator;

use DateTime;
use RuntimeException;
use ZipArchive;

class EpubDocument
{
    /** @var EpubSection[] Sections to be added to the EPUB */
    private array $sections = [];

    /**
     * @param string $name The EPUB file name
     * @param string $author The author of the EPUB
     * @param string $identifier The identifier of the EPUB
     * @param string $path The path where the EPUB file should be saved to
     */
    public function __construct(private string $name, private string $author, private string $identifier, private string $path)
    {
    }

    /**
     * Add a new section to the EPUB file.
     *
     *  Format:
     *  <!DOCTYPE html>
     *  <html xmlns="http://www.w3.org/1999/xhtml">
     *  <head>
     *  <title>Chapter 1</title>
     *  </head>
     *  <body>
     *  <h1>Chapter 1</h1>
     *  <p>This is the content of Chapter 1.</p>
     *  </body>
     *  </html>
     *
     * @param string $sectionName Section name
     * @param string $sectionTitle Section title
     * @param string $content Content in HTML format
     * @return void
     */
    public function addSection(string $sectionName, string $sectionTitle, string $content): void
    {
        $this->sections[] = new EpubSection($sectionName, $sectionTitle, $content);
    }

    public function generateEpub()
    {
        $zip = new ZipArchive();
        $epubFile = $this->path . DIRECTORY_SEPARATOR . $this->name . '.epub';
        if ($zip->open($epubFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $this->addMimetype($zip);
            $this->addContainer($zip);
            $this->addTocPage($zip);
            $this->addPackageOpf($zip);
            $this->addSections($zip);

            $zip->close();

            return $epubFile;
        }

        throw new RuntimeException('Could not initiate epub (ZIP) file in memory');
    }

    /**
     * Add content sections to the archive.
     *
     * Format:
     * <!DOCTYPE html>
     * <html xmlns="http://www.w3.org/1999/xhtml">
     * <head>
     * <title>Chapter 1</title>
     * </head>
     * <body>
     * <h1>Chapter 1</h1>
     * <p>This is the content of Chapter 1.</p>
     * </body>
     * </html>
     *
     * @param ZipArchive $zip The ZIP archive
     * @return void
     */
    private function addSections(ZipArchive $zip): void
    {
        foreach ($this->sections as $section) {
            $sectionContent = '<?xml version="1.0" encoding="UTF-8"?>' . $section->getContent();
            $zip->addFromString(sprintf('EPUB/%s.xhtml', $section->getSectionName()), $sectionContent);
        }
    }

    /**
     * Add mimetype file to the archive.
     *
     * @param ZipArchive $zip The ZIP archive
     * @return void
     */
    private function addMimetype(ZipArchive $zip): void
    {
        $zip->addFromString('mimetype', 'application/epub+zip');
    }

    /**
     * Add the container.xml to the archive.
     *
     * @param ZipArchive $zip The ZIP archive
     * @return void
     */
    private function addContainer(ZipArchive $zip): void
    {
        $containerXml = '<?xml version="1.0" encoding="UTF-8"?>
    <container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
        <rootfiles>
            <rootfile full-path="EPUB/package.opf" media-type="application/oebps-package+xml"/>
        </rootfiles>
    </container>';
        $zip->addFromString('META-INF/container.xml', $containerXml);
    }

    /**
     * Add required table of contents page.
     *
     * @param ZipArchive $zip The ZIP archive
     * @return void
     */
    private function addTocPage(ZipArchive $zip): void
    {
        $navContent = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
    <title>Table of Contents</title>
</head>
<body>
    <h1>Table of Contents</h1>
    <nav id="toc" epub:type="toc">
        <ol>';

            foreach ($this->sections as $section) {
                $navContent .= '<li><a href="' . $section->getSectionName() . '.xhtml">' . $section->getSectionTitle() . '</a></li>';
            }

        $navContent .= '</ol>
    </nav>
</body>
</html>';
        $zip->addFromString('EPUB/toc.xhtml', $navContent);
    }

    /**
     * Add required package.opf file
     *
     * @param ZipArchive $zip The ZIP archive
     * @return void
     */
    private function addPackageOpf(ZipArchive $zip): void
    {
        $currentTime = new DateTime('now');
        $contentOpf = '<?xml version="1.0" encoding="UTF-8"?>
    <package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="pub-identifier">
        <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
            <dc:identifier id="pub-identifier">' . $this->identifier . '</dc:identifier>
            <dc:title>' . $this->name . '</dc:title>
            <dc:creator>' . $this->author . '</dc:creator>
            <dc:language>en</dc:language>
            <meta property="dcterms:modified">' . $currentTime->format('Y-m-d\TH:i:s\Z') . '</meta>
        </metadata>
        <manifest>
            <item id="toc" href="toc.xhtml" media-type="application/xhtml+xml" properties="nav" />';

        foreach ($this->sections as $section) {
            $contentOpf .= '<item id="' . $section->getSectionName() . '" href="' . $section->getSectionName() . '.xhtml" media-type="application/xhtml+xml"/>';
        }

        $contentOpf .= '</manifest>
        <spine>
            <itemref idref="toc" />';

        foreach ($this->sections as $section) {
            $contentOpf .= '<itemref idref="' . $section->getSectionName() . '"/>';
        }

        $contentOpf .= '</spine>
    </package>';

        $zip->addFromString('EPUB/package.opf', $contentOpf);
    }
}