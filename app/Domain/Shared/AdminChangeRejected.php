<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use RuntimeException;

/**
 * A change of the panel that was not applied. Its message is shown to the administrator.
 */
class AdminChangeRejected extends RuntimeException {}
