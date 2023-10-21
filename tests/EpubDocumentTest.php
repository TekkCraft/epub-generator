<?php

namespace Tekkcraft\EpubGenerator\Test;

use Tekkcraft\EpubGenerator\EpubDocument;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Tekkcraft\EpubGenerator\EpubDocument
 */
class EpubDocumentTest extends TestCase
{
    /**
     * Test generation of an EPUB document.
     *
     * @return void
     * @covers ::generateEpub
     * @covers ::addSection
     */
    public function testGenerateEpub()
    {
        $epubDocument = new EpubDocument('test', 'phpunit', 'unique-identifier', sys_get_temp_dir());

        $section1Content = '<h1>Chapter 1</h1><p>This is the content of Chapter 1.</p>';
        $epubDocument->addSection('section1', 'Section 1', $section1Content);
        $epubDocument->addSection('section2', 'Section 2', $section1Content);
        $epubDocument->addSection('section3', 'Section 3', $section1Content);
        $epubFile = $epubDocument->generateEpub();

        $checkJar = __DIR__ . '/resources/EPUBCheck/epubcheck.jar';

        exec("java -jar $checkJar $epubFile 2>&1", $output, $returnCode);

        $this->assertEquals(0, $returnCode, "EPUB validation failed:\n" . implode("\n", $output));
    }
}
