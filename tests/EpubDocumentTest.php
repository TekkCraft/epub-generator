<?php

namespace Tekkcraft\EpubGenerator\Test;

use Tekkcraft\EpubGenerator\EpubDocument;
use PHPUnit\Framework\TestCase;
use Tekkcraft\EpubGenerator\EpubAsset;
use Tekkcraft\EpubGenerator\EpubSection;
use Tekkcraft\EpubGenerator\Test\traits\EpubTestTrait;

/**
 * @coversDefaultClass \Tekkcraft\EpubGenerator\EpubDocument
 */
class EpubDocumentTest extends TestCase
{
    use EpubTestTrait;

    /**
     * Test generation of an EPUB document.
     *
     * @return void
     * @covers ::generateEpub
     * @covers ::addSection
     */
    public function testGenerateEpub(): void
    {
        $this->ensureEpubChecker();

        $coverImage = new EpubAsset(
            __DIR__ . DIRECTORY_SEPARATOR . 'resources',
            'epub-cover.png',
            'image/png',
        );

        $epubDocument = new EpubDocument('test', 'phpunit', 'unique-identifier', sys_get_temp_dir(), $coverImage);

        $css = new EpubAsset(
            __DIR__ . DIRECTORY_SEPARATOR . 'resources',
            'epub.css',
            'text/css',
        );
        $epubDocument->addCss($css);

        $image = new EpubAsset(
            __DIR__ . DIRECTORY_SEPARATOR . 'resources',
            'epub-image.png',
            'image/png',
        );
        $epubDocument->addImage($image);

        $sectionOne = new EpubSection(
            'section1',
            'Section 1',
            '<h1>Chapter 1</h1><p class="example">This is the content of Chapter 1.</p>',
        );
        $epubDocument->addSection($sectionOne);


        $sectionTwo = new EpubSection(
            'section2',
            'Section 2',
            '<h1>Chapter 2</h1><p>This is the content of Chapter 2.</p><img src="img/epub-image.png" />',
        );
        $epubDocument->addSection($sectionTwo);

        $epubFile = $epubDocument->generateEpub();

        $checkJar = $this->getEpubCheckJar();

        exec("java -jar $checkJar $epubFile 2>&1", $output, $returnCode);

        $this->assertEquals(0, $returnCode, "EPUB validation failed:\n" . implode("\n", $output));
    }

    /**
     * Test generation of an EPUB document.
     *
     * @return void
     * @covers ::generateEpub
     * @covers ::addSection
     */
    public function testGenerateEpubWithoutCover(): void
    {
        $this->ensureEpubChecker();

        $epubDocument = new EpubDocument('test', 'phpunit', 'unique-identifier', sys_get_temp_dir());

        $css = new EpubAsset(
            __DIR__ . DIRECTORY_SEPARATOR . 'resources',
            'epub.css',
            'text/css',
        );
        $epubDocument->addCss($css);

        $image = new EpubAsset(
            __DIR__ . DIRECTORY_SEPARATOR . 'resources',
            'epub-image.png',
            'image/png',
        );
        $epubDocument->addImage($image);

        $sectionOne = new EpubSection(
            'section1',
            'Section 1',
            '<h1>Chapter 1</h1><p class="example">This is the content of Chapter 1.</p>',
        );
        $epubDocument->addSection($sectionOne);


        $sectionTwo = new EpubSection(
            'section2',
            'Section 2',
            '<h1>Chapter 2</h1><p>This is the content of Chapter 2.</p><img src="img/epub-image.png" />',
        );
        $epubDocument->addSection($sectionTwo);

        $epubFile = $epubDocument->generateEpub();

        $checkJar = $this->getEpubCheckJar();

        exec("java -jar $checkJar $epubFile 2>&1", $output, $returnCode);

        $this->assertEquals(0, $returnCode, "EPUB validation failed:\n" . implode("\n", $output));
    }
}
