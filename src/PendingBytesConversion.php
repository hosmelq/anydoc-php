<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc;

use Anydoc\Document;
use Anydoc\Exception\NeedsOcrException;
use HosmelQ\Anydoc\Enums\Format;
use HosmelQ\Anydoc\Enums\Ocr;
use HosmelQ\Anydoc\Internal\HostedParser;

final readonly class PendingBytesConversion
{
    public function __construct(
        private string $bytes,
        private null|Format $format,
    ) {
    }

    public function document(): Document
    {
        return anydoc_to_document($this->bytes, $this->format?->value);
    }

    public function markdown(
        Ocr $ocr = Ocr::Reject,
        null|string $apiKey = null,
        null|string $apiUrl = null,
    ): string {
        try {
            return anydoc_to_markdown_bytes($this->bytes, $this->format?->value);
        } catch (NeedsOcrException $needsOcrException) {
            if ($ocr === Ocr::Reject) {
                throw $needsOcrException;
            }
        }

        return new HostedParser()->markdown(
            $this->bytes,
            'document.pdf',
            $apiKey,
            $apiUrl,
        );
    }
}
