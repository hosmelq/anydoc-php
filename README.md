# anydoc for PHP

Convert documents to GitHub-Flavored Markdown in PHP, with access to
[anydoc's](https://github.com/firecrawl/anydoc) structured document model when
available.

## Requirements

- ext-anydoc ^0.2.4
- PHP 8.4+

## Installation

Install the native [extension](https://github.com/hosmelq/ext-anydoc) with
[PIE](https://php.github.io/pie/):

```bash
pie install hosmelq/ext-anydoc:^0.2.4
```

Install anydoc for PHP with Composer:

```bash
composer require hosmelq/anydoc
```

## Convert documents

Convert a local file to Markdown:

```php
use HosmelQ\Anydoc\Anydoc;

$markdown = Anydoc::file('report.docx')->markdown();
```

Convert document bytes directly:

```php
$bytes = file_get_contents('report.docx');

$markdown = Anydoc::bytes($bytes)->markdown();
```

CSV has no content signature, so CSV bytes require an explicit format:

```php
use HosmelQ\Anydoc\Anydoc;
use HosmelQ\Anydoc\Enums\Format;

$bytes = file_get_contents('data.csv');

$markdown = Anydoc::bytes($bytes, Format::Csv)->markdown();
```

All conversions run synchronously.

## Use hosted OCR

PDFs with scanned pages throw an `Anydoc\Exception\NeedsOcrException` by
default. Use hosted OCR to fall back to Firecrawl Parse:

```php
use HosmelQ\Anydoc\Anydoc;
use HosmelQ\Anydoc\Enums\Ocr;

$markdown = Anydoc::file('scan.pdf')->markdown(ocr: Ocr::Hosted);
```

anydoc always attempts the conversion locally first. Only a PDF that requires
OCR is uploaded, and Firecrawl Parse receives the whole document.

Hosted OCR is keyless by default. Set `FIRECRAWL_API_KEY` for higher limits or
pass `apiKey` directly. Set `FIRECRAWL_API_URL` or pass `apiUrl` to use another
endpoint.

## Read structured documents

Call `document()` to access anydoc's readonly document model:

```php
use HosmelQ\Anydoc\Anydoc;

$document = Anydoc::file('presentation.pptx')->document();

$assets = $document->assets;
$blocks = $document->blocks;
$notes = $document->notes;
```

The document model includes blocks, checkboxes, embedded assets, inline content,
lists, math, notes, and tables.

PDF supports Markdown conversion only. Calling `document()` for a PDF throws an
`Anydoc\Exception\UnsupportedException`.

## Supported formats

| Format | Extensions |
| --- | --- |
| CSV | `.csv` |
| EPUB | `.epub` |
| Excel | `.xls`, `.xlsb`, `.xlsm`, `.xlsx` |
| OpenDocument | `.odp`, `.ods`, `.odt` |
| PDF | `.pdf` |
| PowerPoint | `.pot`, `.pps`, `.ppsm`, `.ppsx`, `.ppt`, `.pptm`, `.pptx` |
| Rich Text Format | `.rtf` |
| Word | `.doc`, `.docm`, `.docx` |

Pass a `Format` enum case when the format cannot be detected from the content:

```php
use HosmelQ\Anydoc\Anydoc;
use HosmelQ\Anydoc\Enums\Format;

$markdown = Anydoc::bytes($bytes, Format::Docx)->markdown();
```

## Detect formats

Detect a format from bytes, an extension, or a path:

```php
use HosmelQ\Anydoc\Anydoc;

$fromBytes = Anydoc::formatFromBytes($bytes);
$fromExtension = Anydoc::formatFromExtension('.DOCX');
$fromPath = Anydoc::formatFromPath('documents/report.docx');
```

Each method returns a `Format` enum case or `null`. Extension detection is
case-insensitive and accepts an optional leading dot.

Local files use their contents first and their path as a fallback.

## Handle errors

Native conversion errors extend `Anydoc\Exception\ConvertException`:

```php
use Anydoc\Exception\ConvertException;
use Anydoc\Exception\PanicException;
use HosmelQ\Anydoc\Anydoc;
use HosmelQ\Anydoc\Enums\Ocr;
use HosmelQ\Anydoc\Exceptions\HostedException;

try {
    $markdown = Anydoc::file('scan.pdf')->markdown(ocr: Ocr::Hosted);
} catch (HostedException $exception) {
    // Handle a hosted OCR error.
} catch (ConvertException $exception) {
    // Handle a document conversion error.
} catch (PanicException $exception) {
    // Handle a panic from the native library.
}
```

Conversion exceptions include `EncryptedException`, `IoException`,
`MalformedException`, `MissingPartException`, `NeedsOcrException`,
`ResourceLimitException`, and `UnsupportedException`. Local file reads may
also throw an `ErrorException` when the file cannot be read.

`HostedException` represents a Firecrawl Parse failure and does not extend
`ConvertException`.

`PanicException` represents a panic from the native Rust library and does not
extend `ConvertException`.

## Development

Run the test suite with:

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## Contributing

Pull requests are welcome. Please run the test suite before submitting changes.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
