## Required headers for download

```
header('Content-Type: application/epub+zip');
header(sprintf('Content-Disposition: attachment; filename="%s.epub"', 'my-ebook'));
header(sprintf('Content-Length: %s', strlen($epubFile)));
```
