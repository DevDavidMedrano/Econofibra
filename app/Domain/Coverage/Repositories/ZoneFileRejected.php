<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

use App\Domain\Shared\AdminChangeRejected;

/**
 * A zone file that is refused as a whole, with the reason and, if there is one, the element.
 */
final class ZoneFileRejected extends AdminChangeRejected
{
    /** A name comes from the file, so its length is limited before it reaches the session. */
    private const MAX_ELEMENT_LENGTH = 100;

    public function __construct(string $reason, string $element = '')
    {
        $element = trim($element);

        if ($element !== '') {
            $shown = mb_strlen($element, 'UTF-8') > self::MAX_ELEMENT_LENGTH
                ? mb_substr($element, 0, self::MAX_ELEMENT_LENGTH, 'UTF-8').'…'
                : $element;
            $reason .= " Elemento: {$shown}";
        }

        parent::__construct($reason);
    }
}
