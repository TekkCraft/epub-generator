<?php

namespace Tekkcraft\EpubGenerator;

final class EpubSection
{
    /**
     * Create a new EPUB section.
     *
     * Section content ($content) must be in HTML format.
     * e.g.:
     * <h1>Chapter 1</h1><p>This is the content of Chapter 1.</p>
     *
     * @param string $sectionName Section name
     * @param string $sectionTitle Section title
     * @param string $content Section content
     */
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