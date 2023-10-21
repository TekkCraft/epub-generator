<?php

namespace Tekkcraft\EpubGenerator;

class EpubImage
{
    /**
     * @param string $imagePath The path to your image
     * @param string $imageName The name of your image
     * @param string $mediaType The media type of the image (e.g. image/jpeg)
     */
    public function __construct(private string $imagePath, private string $imageName, private string $mediaType)
    {
    }

    /**
     * Get the image path.
     *
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * Get the image name.
     *
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
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
