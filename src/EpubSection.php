<?php

namespace Tekkcraft\EpubGenerator;

final class EpubSection
{
    public function __construct(private string $sectionName, private string $sectionTitle, private string $content)
    {
    }

    /**
     * Get the section name.
     *
     * @return string
     */
    public function getSectionName(): string
    {
        return $this->sectionName;
    }

    /**
     * Get the section title.
     *
     * @return string
     */
    public function getSectionTitle(): string
    {
        return $this->sectionTitle;
    }

    /**
     * Get the section content (should be in XHTML format)
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}