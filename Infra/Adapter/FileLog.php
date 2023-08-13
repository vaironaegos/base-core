<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Astrotech\ApiBase\Adapter\Contracts\LogSystem;

final class FileLog implements LogSystem
{
    public function debug(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? 'fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }
}
