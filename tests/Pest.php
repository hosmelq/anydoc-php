<?php

declare(strict_types=1);

pest()->in(__DIR__);

function anydocMixedPdf(): string
{
    $encoded = file_get_contents(__DIR__.'/Fixtures/handmade-mixed.pdf.base64');
    $bytes = is_string($encoded) ? base64_decode($encoded, true) : false;

    if (! is_string($bytes)) {
        throw new RuntimeException('Unable to read the mixed PDF fixture.');
    }

    return $bytes;
}

function anydocMixedPdfPath(): string
{
    $path = sys_get_temp_dir().'/anydoc-'.bin2hex(random_bytes(8)).'.pdf';

    if (file_put_contents($path, anydocMixedPdf()) === false) {
        throw new RuntimeException('Unable to write the mixed PDF fixture.');
    }

    return $path;
}
