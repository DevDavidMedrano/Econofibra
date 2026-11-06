<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Repositories;

interface ZoneFileReader
{
    /**
     * Uses only the contents it receives, which are not trusted.
     *
     * @throws ZoneFileRejected When the file cannot be used.
     */
    public function read(string $contents): ZoneFileContent;
}
