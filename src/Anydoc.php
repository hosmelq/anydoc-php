<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc;

use HosmelQ\Anydoc\Enums\Format;

final class Anydoc
{
    private function __construct()
    {
    }

    public static function bytes(string $bytes, null|Format $format = null): PendingBytesConversion
    {
        return new PendingBytesConversion($bytes, $format);
    }

    public static function file(string $file): PendingFileConversion
    {
        return new PendingFileConversion($file);
    }

    public static function formatFromBytes(string $bytes): null|Format
    {
        $format = anydoc_format_from_bytes($bytes);

        return $format === null ? null : Format::from($format);
    }

    public static function formatFromExtension(string $extension): null|Format
    {
        $format = anydoc_format_from_extension($extension);

        return $format === null ? null : Format::from($format);
    }

    public static function formatFromPath(string $path): null|Format
    {
        $format = anydoc_format_from_path($path);

        return $format === null ? null : Format::from($format);
    }
}
