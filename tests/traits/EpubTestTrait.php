<?php

namespace Tekkcraft\EpubGenerator\Test\traits;

use RuntimeException;
use ZipArchive;

trait EpubTestTrait
{
    /**
     * Get the EPUB checker jar location.
     *
     * @return string
     */
    public function getEpubCheckJar(): string
    {
        return $this->getEpubCheckDir() . DIRECTORY_SEPARATOR . 'epubcheck.jar';
    }

    /**
     * Get EPUB check directory.
     *
     * @return string
     */
    private function getEpubCheckDir(): string
    {
        $parentDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'epubcheck';
        $installDir = scandir($parentDir)[2];

        return $parentDir . DIRECTORY_SEPARATOR . $installDir;
    }

    /**
     * Ensure EPUB checker is installed.
     *
     * @return void
     */
    public function ensureEpubChecker(): void
    {
        if (!is_dir($this->getEpubCheckDir())) {
            $this->downloadEpubChecker();
        }
    }

    /**
     * Download EPUB checker.
     *
     * @return void
     */
    private function downloadEpubChecker(): void
    {
        $epubCheckerArchive = 'https://github.com/w3c/epubcheck/releases/download/v5.1.0/epubcheck-5.1.0.zip';
        $epubChecker = file_get_contents($epubCheckerArchive);
        if (!$epubChecker) {
            throw new RuntimeException('Could not download epubchecker');
        }

        $localArchive = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'epubcheck.zip';
        file_put_contents($localArchive, $epubChecker);

        $zip = new ZipArchive();
        if ($zip->open($localArchive) === true) {
            $zip->extractTo($this->getEpubCheckDir());
            $zip->close();
        }
    }
}