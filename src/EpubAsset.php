<?php

namespace Tekkcraft\EpubGenerator;

class EpubAsset
{
    /**
     * @param string $assetPath The path to your asset
     * @param string $assetName The name of your asset
     * @param string $mediaType The media type of the asset (e.g. image/jpeg, text/css)
     */
    public function __construct(private string $assetPath, private string $assetName, private string $mediaType)
    {
    }

    /**
     * Get the image path.
     *
     * @return string
     */
    public function getAssetPath(): string
    {
        return $this->assetPath;
    }

    /**
     * Get the image name.
     *
     * @return string
     */
    public function getAssetName(): string
    {
        return $this->assetName;
    }

    /**
     * Get the image media type.
     *
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
