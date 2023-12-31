<?php

namespace Tekkcraft\EpubGenerator;

use DateTime;
use DOMDocument;
use DOMImplementation;
use RuntimeException;
use ZipArchive;

class EpubDocument
{
    /** @var EpubSection[] Sections to be added to the EPUB */
    private array $sections = [];

    /** @var EpubAsset[] Asset files (CSS & Images) to be added to the EPUB */
    private array $assets = [];

    /** @var string The EPUB content directory */
    private string $contentDir;


    /**
     * @param string $name The EPUB file name
     * @param string $author The author of the EPUB
     * @param string $identifier The identifier of the EPUB
     * @param string $path The path where the EPUB file should be saved to
     * @param EpubAsset|null $coverImage The image to be used as the cover
     */
    public function __construct(
        private string     $name,
        private string     $author,
        private string     $identifier,
        private string     $path,
        private ?EpubAsset $coverImage = null,
    )
    {
        $this->contentDir = 'EPUB';

        if ($this->coverImage) {
            $this->coverImage->setPathPrefix('img');
            $this->assets[] = $this->coverImage;
        }
    }

    /**
     * Add a new section to the EPUB file.
     *
     * @param EpubSection $section EPUB section
     */
    public function addSection(EpubSection $section): void
    {
        $this->sections[] = $section;
    }

    /**
     * Add a new image to be used in the EPUB file.
     *
     * @param EpubAsset $image The image
     * @return void
     */
    public function addImage(EpubAsset $image): void
    {
        $image->setPathPrefix('img');
        $this->assets[] = $image;
    }

    /**
     * Add a new CSS file to be used in the EPUB file.
     *
     * @param EpubAsset $cssFile The CSS file
     * @return void
     */
    public function addCss(EpubAsset $cssFile): void
    {
        $cssFile->setPathPrefix('css');
        $this->assets[] = $cssFile;
    }

    /**
     * Generate the EPUB file.
     *
     * @return string The epub file path
     */
    public function generateEpub(): string
    {
        $zip = new ZipArchive();
        $epubFile = $this->path . DIRECTORY_SEPARATOR . $this->name . '.epub';
        if ($zip->open($epubFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $this->addMimetype($zip);
            $this->addAssets($zip);
            $this->addContainer($zip);
            $this->createTocBody();
            if ($this->coverImage) {
                $this->createCoverBody();
            }
            $this->addSections($zip);
            $this->addPackageOpf($zip);

            $zip->close();

            return $epubFile;
        }

        throw new RuntimeException('Could not initiate epub (ZIP) file in memory');
    }

    /**
     * Add assets to the ZIP archive.
     *
     * @param ZipArchive $zip The ZIP archive
     * @return void
     */
    private function addAssets(ZipArchive $zip): void
    {
        if (empty($this->assets)) {
            return;
        }

        foreach ($this->assets as $asset) {
            if (!$this->zipDirectoryExists($zip, $this->contentDir . '/' . $asset->getPathPrefix())) {
                $zip->addEmptyDir($this->contentDir . '/' . $asset->getPathPrefix());
            }

            $zip->addFile($asset->getAssetPath() . '/' . $asset->getAssetName(), $this->contentDir . '/' . $asset->getHref());
        }
    }

    /**
     * Check if a directory exists within the ZIP archive.
     *
     * @param ZipArchive $zip The ZIP archive
     * @param string $directory The directory to check
     * @return bool
     */
    private function zipDirectoryExists(ZipArchive $zip, string $directory): bool
    {
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entry = $zip->getNameIndex($index);

            if (strpos($entry, $directory)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add content sections to the archive.
     *
     * Format:
     * <h1>Chapter 1</h1>
     * <p>This is the content of Chapter 1.</p>
     *
     * @param ZipArchive $zip The ZIP archive
     * @return void
     */
    private function addSections(ZipArchive $zip): void
    {
        foreach ($this->sections as $section) {
            $doc = new DOMDocument('1.0', 'UTF-8');

            $implementation = new DOMImplementation();
            $doctype = $implementation->createDocumentType('html');
            $doc->appendChild($doctype);

            $html = $doc->createElement('html');
            $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
            $html->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');
            $doc->appendChild($html);

            $head = $doc->createElement('head');
            $html->appendChild($head);

            foreach ($this->assets as $asset) {
                if ($asset->getMediaType() !== 'text/css') {
                    continue;
                }
                $css = $doc->createElement('link');
                $css->setAttribute('rel', 'stylesheet');
                $css->setAttribute('type', $asset->getMediaType());
                $css->setAttribute('href', '../' . $asset->getHref());
                $head->appendChild($css);
            }

            $title = $doc->createElement('title', $section->getSectionTitle());
            $head->appendChild($title);

            $body = $doc->createElement('body');
            $html->appendChild($body);

            $fragment = $doc->createDocumentFragment();
            $fragment->appendXML($section->getContent());

            $body->appendChild($fragment);

            $zip->addFromString(sprintf('%s/%s', $this->contentDir, $section->getHref()), $doc->saveXML());
        }
    }

    /**
     * Create TOC section.
     *
     * @return void
     */
    private function createTocBody(): void
    {
        $doc = new DOMDocument();
        $div = $doc->createElement('div');
        $div->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');
        $doc->appendChild($div);

        $h1 = $doc->createElement('h1', 'Table of Contents');
        $div->appendChild($h1);

        // Build navigation part
        $nav = $doc->createElement('nav');
        $nav->setAttribute('id', 'toc');
        $nav->setAttribute('epub:type', 'toc');
        $div->appendChild($nav);

        // Create ordered list
        $ol = $doc->createElement('ol');
        $nav->appendChild($ol);

        // Add list items to ordered list
        foreach ($this->sections as $section) {
            $li = $doc->createElement('li');
            $ol->appendChild($li);
            $a = $doc->createElement('a', $section->getSectionTitle());
            $li->appendChild($a);
            $a->setAttribute('href', $section->getSectionName() . '.xhtml');
        }

        $result = $doc->saveHTML();

        array_unshift($this->sections, new EpubSection('toc', 'Table of Contents', $result));
    }

    /**
     * Create TOC section.
     *
     * @return void
     */
    private function createCoverBody(): void
    {
        $doc = new DOMDocument();

        $img = $doc->createElement('img');
        $img->setAttribute('src', '../' . $this->coverImage->getHref());
        $doc->appendChild($img);

        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = true;

        $html = $doc->saveHTML();

        // Enforce closing / required for XHTML format and remove unnecessary "\n"
        $html = substr_replace($html, ' />', -2, 3);

        array_unshift($this->sections, new EpubSection('cover-page', 'Cover Page', $html));
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
        $dom = new DOMDocument('1.0', 'UTF-8');

        $containerElement = $dom->createElement('container');
        $containerElement->setAttribute('version', '1.0');
        $containerElement->setAttribute('xmlns', 'urn:oasis:names:tc:opendocument:xmlns:container');

        $rootfiles = $dom->createElement('rootfiles');

        $rootfile = $dom->createElement('rootfile');
        $rootfile->setAttribute('full-path', $this->contentDir . '/package.opf');
        $rootfile->setAttribute('media-type', 'application/oebps-package+xml');

        $rootfiles->appendChild($rootfile);

        $containerElement->appendChild($rootfiles);

        $dom->appendChild($containerElement);

        $containerXml = $dom->saveXML();

        $zip->addFromString('META-INF/container.xml', $containerXml);
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

        $doc = new DOMDocument('1.0', 'UTF-8');

        $packageElement = $doc->createElement('package');
        $packageElement->setAttribute('xmlns', 'http://www.idpf.org/2007/opf');
        $packageElement->setAttribute('version', '3.0');
        $packageElement->setAttribute('unique-identifier', 'pub-identifier');

        $metadataElement = $doc->createElement('metadata');
        $metadataElement->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');

        $packageElement->appendChild($metadataElement);

        $identifierElement = $doc->createElement('dc:identifier', $this->identifier);
        $identifierElement->setAttribute('id', 'pub-identifier');
        $metadataElement->appendChild($identifierElement);

        $metadataElement->appendChild($doc->createElement('dc:title', $this->name));
        $metadataElement->appendChild($doc->createElement('dc:creator', $this->author));
        $metadataElement->appendChild($doc->createElement('dc:language', 'en'));
        $metadataElement->appendChild($doc->createElement('meta', $currentTime->format('Y-m-d\TH:i:s\Z')))
            ->setAttribute('property', 'dcterms:modified');

        if ($this->coverImage) {
            $coverElement = $doc->createElement('meta');
            $coverElement->setAttribute('name', 'cover');
            $coverElement->setAttribute(
                'content',
                $this->coverImage->getHref(),
            );
            $metadataElement->appendChild($coverElement);
        }
        $manifestElement = $doc->createElement('manifest');
        $packageElement->appendChild($manifestElement);

        foreach ($this->sections as $section) {
            $itemSection = $doc->createElement('item');
            $itemSection->setAttribute('id', $section->getSectionName());
            $itemSection->setAttribute('href', $section->getHref());
            $itemSection->setAttribute('media-type', 'application/xhtml+xml');

            if ($section->getSectionName() === 'toc') {
                $itemSection->setAttribute('properties', 'nav');
            }

            $manifestElement->appendChild($itemSection);
        }

        foreach ($this->assets as $asset) {
            $assetSection = $doc->createElement('item');
            $assetSection->setAttribute('id', $asset->getAssetName());
            $assetSection->setAttribute('href', $asset->getHref());
            $assetSection->setAttribute('media-type', $asset->getMediaType());
            $manifestElement->appendChild($assetSection);
        }

        $spineElement = $doc->createElement('spine');
        $packageElement->appendChild($spineElement);

        $doc->appendChild($packageElement);

        foreach ($this->sections as $section) {
            $itemRefTOC = $doc->createElement('itemref');
            $itemRefTOC->setAttribute('idref', $section->getSectionName());
            $spineElement->appendChild($itemRefTOC);
        }

        $contentOpf = $doc->saveXML();

        $zip->addFromString($this->contentDir . '/package.opf', $contentOpf);
    }
}