<?php

declare(strict_types=1);

use Anydoc\Document;
use Anydoc\Exception\IoException;
use Anydoc\Exception\NeedsOcrException;
use HosmelQ\Anydoc\Anydoc;
use HosmelQ\Anydoc\Enums\Ocr;

it('reports unreadable local files during document conversion', function (): void {
    expect(fn (): Document => Anydoc::file(__DIR__.'/Fixtures/missing.csv')->document())
        ->toThrow(ErrorException::class);
});

it('preserves native file conversion exceptions', function (): void {
    expect(fn (): string => Anydoc::file(__DIR__.'/Fixtures/missing.csv')->markdown())
        ->toThrow(IoException::class);
});

it('rejects files with pages that need OCR by default', function (): void {
    $path = anydocMixedPdfPath();

    try {
        expect(fn (): string => Anydoc::file($path)->markdown())
            ->toThrow(NeedsOcrException::class);
    } finally {
        unlink($path);
    }
});

it('converts local files with extension fallback', function (): void {
    $conversion = Anydoc::file(__DIR__.'/Fixtures/people.csv');

    expect($conversion->markdown(ocr: Ocr::Hosted, apiUrl: 'http://127.0.0.1:1'))
        ->toContain('| name | role |')
        ->and($conversion->document())
        ->toBeInstanceOf(Document::class);
});
