<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();

arch('classes')
    ->expect('HosmelQ\\Anydoc')
    ->classes()
    ->toBeFinal();

arch('strict types')
    ->expect('HosmelQ\Anydoc')
    ->toUseStrictTypes();
