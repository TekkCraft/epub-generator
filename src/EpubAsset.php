<?php

namespace Tekkcraft\EpubGenerator;

class EpubAsset
{
    /** @var string Asset path prefix (css/, img/, etc.) */
    private string $pathPrefix;

    /**
     * @param string $assetPath The path to your asset
     * @param string $assetName The name of your asset
     * @param string $mediaType The media type of the asset (e.g. image/jpeg, text/css)
     */
    public function __construct(private string $assetPath, private string $assetName, private string $mediaType)
    {
    }

    /**
     * Get the asset path.
     *
     * @return string
     */
    public function getAssetPath(): string
    {
        return $this->assetPath;
    }

    /**
     * Get the asset name.
     *
     * @return string
     */
    public function getAssetName(): string
    {
        return $this->assetName;
    }

    /**
     * Get the asset media type.
     *
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    /**
     * Get the asset path.
     *
     * @return string
     */
    public function getHref(): string
    {
        return $this->pathPrefix . '/' . $this->assetName;
    }

    /**
     * Get the asset path prefix.
     *
     * @return string
     */
    public function getPathPrefix(): string
    {
        return $this->pathPrefix;
    }

    /**
     * Set the asset path prefix.
     * This is automatically set when adding an asset to the EpubDocument.
     *
     * @param string $pathPrefix The asset path prefix
     * @return void
     */
    public function setPathPrefix(string $pathPrefix): void
    {
        $this->pathPrefix = $pathPrefix;
    }
}
