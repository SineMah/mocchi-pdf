# mocchi-Pdf

Pdf to image converter

## Installation

```bash
git clone git@github.com:SineMah/mocchi-pdf.git
```

or

```bash
composer require mocchi/pdf
```

```php
$file = new \Mocchi\Pdf\File('./file.pdf');

$file->convert(['width' => 800, 'quality' => 100, 'type' => 'png']);
``` 

## Options array
* width (int)
* height (int)
* resolution (int)
* type (string) jpg or png
* quality (int) 1 ... 100
* path (string)
* file_name (string)
* page (int)

mocchi-Pdf overload values with default params from your source file.

## License
MIT