<?php

declare(strict_types=1);

namespace NotACms\Tests;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
trait TmpDirTrait
{
    private function removeDir(string $path): void
    {
        if (is_dir($path)) {
            (new Filesystem())->remove($path);
        }
    }
}
