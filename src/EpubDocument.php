<?php

namespace Tekkcraft\EpubGenerator;

use DateTime;
use DOMDocument;
use DOMImplementation;
use RuntimeException;
use SimpleXMLElement;
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
    public function __construct(
        private string $name,
        private string $author,
        private string $identifier,
        private string $path,
    ) {
    }

    /**
     * Add a new section to the EPUB file.
     *
     *  Format:
     *  <h1>Chapter 1</h1>
     *  <p>This is the content of Chapter 1.</p>
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
            $doc->appendChild($html);

            $head = $doc->createElement('head');
            $html->appendChild($head);

            $title = $doc->createElement('title', $section->getSectionTitle());
            $head->appendChild($title);

            $body = $doc->createElement('body');
            $html->appendChild($body);

            $fragment = $doc->createDocumentFragment();
            $fragment->appendXML($section->getContent());

            $body->appendChild($fragment);

            $zip->addFromString(sprintf('EPUB/%s.xhtml', $section->getSectionName()), $doc->saveXML());
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
        $containerElement = new SimpleXMLElement('<container/>');
        $containerElement->addAttribute('version', '1.0', 'urn:oasis:names:tc:opendocument:xmlns:container');

        $containerElement->addAttribute('version', '1.0');
        $containerElement->addAttribute('xmlns', 'urn:oasis:names:tc:opendocument:xmlns:container');

        $rootfiles = $containerElement->addChild('rootfiles');
        $rootfile = $rootfiles->addChild('rootfile');
        $rootfile->addAttribute('full-path', 'EPUB/package.opf');
        $rootfile->addAttribute('media-type', 'application/oebps-package+xml');

        $containerXml = $containerElement->asXML();

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
        $doc = new DOMDocument('1.0', 'UTF-8');

        // Add <!DOCTYPE html> part
        $implementation = new DOMImplementation();
        $doctype = $implementation->createDocumentType('html');
        $doc->appendChild($doctype);

        $html = $doc->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');

        $doc->appendChild($html);

        // Build header part
        $header = $doc->createElement('head');
        $html->appendChild($header);
        $title = $doc->createElement('title', 'Table of Contents');
        $header->appendChild($title);

        // Build body part
        $body = $doc->createElement('body');
        $html->appendChild($body);
        $h1 = $doc->createElement('h1', 'Table of Contents');
        $body->appendChild($h1);

        // Build navigation part
        $nav = $doc->createElement('nav');
        $nav->setAttribute('id', 'toc');
        $nav->setAttribute('epub:type', 'toc');
        $body->appendChild($nav);

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

        $navContent = $doc->saveXML();

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

        $manifestElement = $doc->createElement('manifest');
        $packageElement->appendChild($manifestElement);

        $itemTOC = $doc->createElement('item');
        $itemTOC->setAttribute('id', 'toc');
        $itemTOC->setAttribute('href', 'toc.xhtml');
        $itemTOC->setAttribute('media-type', 'application/xhtml+xml');
        $itemTOC->setAttribute('properties', 'nav');
        $manifestElement->appendChild($itemTOC);

        foreach ($this->sections as $section) {
            $itemSection = $doc->createElement('item');
            $itemSection->setAttribute('id', $section->getSectionName());
            $itemSection->setAttribute('href', $section->getSectionName() . '.xhtml');
            $itemSection->setAttribute('media-type', 'application/xhtml+xml');
            $manifestElement->appendChild($itemSection);
        }

        $spineElement = $doc->createElement('spine');
        $packageElement->appendChild($spineElement);

        $itemRefTOC = $doc->createElement('itemref');
        $itemRefTOC->setAttribute('idref', 'toc');
        $spineElement->appendChild($itemRefTOC);

        $doc->appendChild($packageElement);

        foreach ($this->sections as $section) {
            $itemRefTOC = $doc->createElement('itemref');
            $itemRefTOC->setAttribute('idref', $section->getSectionName());
            $spineElement->appendChild($itemRefTOC);
        }

        $contentOpf = $doc->saveXML();

        $zip->addFromString('EPUB/package.opf', $contentOpf);
    }
}