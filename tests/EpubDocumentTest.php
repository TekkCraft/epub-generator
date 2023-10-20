<?php

namespace Tekkcraft\EpubGenerator\Test;

use Tekkcraft\EpubGenerator\EpubDocument;
use PHPUnit\Framework\TestCase;

class EpubDocumentTest extends TestCase
{

    public function testGenerateEpub()
    {
        $epubDocument = new EpubDocument('test', 'phpunit', 'unique-identifier', __DIR__);

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
        $result = $epubDocument->generateEpub();

        $this->assertNotEmpty($result);
    }
}
