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

        $section1Content = '<!DOCTYPE html>
         <html xmlns="http://www.w3.org/1999/xhtml">
         <head>
         <title>Chapter 1</title>
         </head>
         <body>
         <h1>Chapter 1</h1>
         <p>This is the content of Chapter 1.</p>
         </body>
         </html>';
        $epubDocument->addSection('section1', 'Section 1', $section1Content);
        $epubDocument->addSection('section2', 'Section 2', $section1Content);
        $epubDocument->addSection('section3', 'Section 3', $section1Content);
        $epubFile = $epubDocument->generateEpub();

        exec("java -jar resources/EPUBCheck/epubcheck.jar $epubFile 2>&1", $output, $returnCode);

        $this->assertEquals(0, $returnCode, "EPUB validation failed:\n" . implode("\n", $output));
    }
}
