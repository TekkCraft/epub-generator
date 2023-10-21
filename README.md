# EPUB generator

A simple library to generate EPUB files.

## Usage

```php
$coverImage = new \Tekkcraft\EpubGenerator\EpubAsset('path/to/image-folder', 'cover.png', 'image/png');

$generator = new \Tekkcraft\EpubGenerator\EpubDocument('book', 'TekkCraft', 'unique-book-name', '/path/to/storage-directory', $coverImage);

$css = new EpubAsset(
    'path/to/css-folder',
    'epub.css',
    'text/css',
);
$generator->addCss($css);

$sectionOne = new \Tekkcraft\EpubGenerator\EpubSection(
    'section1',
    'Section 1',
    '<h1>Chapter 1</h1><p class="some-css-class">This is the content of Chapter 1.</p>',
);
$generator->addSection($sectionOne);

$image = new \Tekkcraft\EpubGenerator\EpubAsset('path/to/image-folder', 'image.png', 'image/png');
$generator->addImage($image);

$sectionTwo = new \Tekkcraft\EpubGenerator\EpubSection(
    'section2',
    'Section 2',
    '<h1>Chapter 2</h1><p>This is the content of Chapter 2.</p><img src="img/image.png" />',
);
$generator->addSection($sectionTwo);

$epubFile = $generator->generateEpub();
```

This would create a new EPUB file named ``book.epub`` in the directory ``/path/to/storage-directory``.
The ``$epubFile`` contains the file name of the generated EPUB.

All images are saved in the ``img`` folder and can be accessed using ``img/image-name.png``.\
All CSS assets are saved in the ``css`` folder and can be accessed using ``css/file-name.css``.

## Required headers for download

If you want to provide your EPUB file as a download, add these headers:

```php
header('Content-Type: application/epub+zip');
header(sprintf('Content-Disposition: attachment; filename="%s.epub"', 'my-ebook'));
header(sprintf('Content-Length: %s', strlen($epubFile)));
```

## Testing

In order to run the PHPUnit test suite you will need to have java installed on your system in order to use the ``epubcheck.jar``.
The ``epubcheck.jar`` will be downloaded from [https://github.com/w3c/epubcheck](https://github.com/w3c/epubcheck) and saved into the ``sys_get_temp_dir()`` on first test execution.
