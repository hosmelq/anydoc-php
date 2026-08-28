<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc;

use function Safe\file_get_contents;

use Anydoc\Document;
use Anydoc\Exception\NeedsOcrException;
use ErrorException;
use HosmelQ\Anydoc\Enums\Ocr;
use HosmelQ\Anydoc\Internal\HostedParser;

final readonly class PendingFileConversion
{
    public function __construct(private string $path)
    {
    }

    public function document(): Document
    {
        if (! is_file($this->path) || ! is_readable($this->path)) {
            throw new ErrorException("File does not exist or is not readable at path {$this->path}.");
        }

        $bytes = file_get_contents($this->path);
        $format = anydoc_format_from_bytes($bytes) ?? anydoc_format_from_path($this->path);

        return anydoc_to_document($bytes, $format);
    }

    public function markdown(
        Ocr $ocr = Ocr::Reject,
        null|string $apiKey = null,
        null|string $apiUrl = null,
    ): string {
        try {
            return anydoc_to_markdown($this->path);
        } catch (NeedsOcrException $needsOcrException) {
            if ($ocr === Ocr::Reject) {
                throw $needsOcrException;
            }
        }

        return new HostedParser()->markdown(
            file_get_contents($this->path),
            basename($this->path),
            $apiKey,
            $apiUrl,
        );
    }
}
