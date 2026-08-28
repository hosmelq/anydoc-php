<?php

declare(strict_types=1);

use HosmelQ\Anydoc\Anydoc;
use HosmelQ\Anydoc\Enums\Format;

it('returns null when a format cannot be detected', function (): void {
    expect(Anydoc::formatFromPath('documents/report.txt'))
        ->toBeNull();
});

it('exposes all native format detection operations', function (): void {
    expect(Anydoc::formatFromBytes('{\\rtf1 anydoc}'))
        ->toBe(Format::Rtf)
        ->and(Anydoc::formatFromExtension('.PPTM'))
        ->toBe(Format::Pptx)
        ->and(Anydoc::formatFromPath('documents/report.xls'))
        ->toBe(Format::Xlsx);
});

it('maps every native format to the PHP enum', function (string $extension, Format $format): void {
    expect(Anydoc::formatFromExtension($extension))
        ->toBe($format);
})->with([
    'CSV' => ['csv', Format::Csv],
    'EPUB' => ['epub', Format::Epub],
    'Excel' => ['xlsx', Format::Xlsx],
    'OpenDocument presentation' => ['odp', Format::Odp],
    'OpenDocument spreadsheet' => ['ods', Format::Ods],
    'OpenDocument text' => ['odt', Format::Odt],
    'PDF' => ['pdf', Format::Pdf],
    'PowerPoint binary' => ['ppt', Format::Ppt],
    'PowerPoint Open XML' => ['pptx', Format::Pptx],
    'Rich Text Format' => ['rtf', Format::Rtf],
    'Word binary' => ['doc', Format::Doc],
    'Word Open XML' => ['docx', Format::Docx],
]);
