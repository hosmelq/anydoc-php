<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Enums;

enum Ocr: string
{
    case Hosted = 'hosted';
    case Reject = 'reject';
}
