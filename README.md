# EPUB generator

A simple library to generate EPUB files.

## Usage

```php
$generator = new \Tekkcraft\EpubGenerator\EpubDocument('book', 'TekkCraft', 'unique-book-name', '/path/to/storage-directory');
$generator->addSection('section1', 'Section 1', '<h1>Section 1</h1><p>Some</p><p>content</p>');
$generator->addSection('section2', 'Section 2', '<h1>Section 2</h1><p>Some other</p><p>content</p>');
$epubFile = $generator->generateEpub();
```

This would create a new EPUB file named ``book.epub`` in the directory ``/path/to/storage-directory``.
The ``$epubFile`` contains the file name of the generated EPUB.

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
