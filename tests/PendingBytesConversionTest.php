<?php

declare(strict_types=1);

use Anydoc\Document;
use Anydoc\Exception\NeedsOcrException;
use Anydoc\Exception\UnsupportedException;
use HosmelQ\Anydoc\Anydoc;
use HosmelQ\Anydoc\Enums\Format;
use HosmelQ\Anydoc\Enums\Ocr;

it('preserves native conversion exceptions', function (): void {
    expect(fn (): string => Anydoc::bytes('not a document')->markdown())
        ->toThrow(UnsupportedException::class);
});

it('rejects pages that need OCR by default', function (): void {
    expect(fn (): string => Anydoc::bytes(anydocMixedPdf())->markdown())
        ->toThrow(NeedsOcrException::class);
});

it('converts bytes to markdown and the document model', function (): void {
    $bytes = "name,role\nAda,Engineer\n";
    $conversion = Anydoc::bytes($bytes, Format::Csv);

    expect($conversion->markdown(ocr: Ocr::Hosted, apiUrl: 'http://127.0.0.1:1'))
        ->toContain('| name | role |')
        ->and($conversion->document())
        ->toBeInstanceOf(Document::class);
});
