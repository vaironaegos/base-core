<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use Astrotech\Core\Base\Adapter\Contracts\LogSystem;

final class FileLog implements LogSystem
{
    public function debug(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? $options['logs_path'] . '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] DEBUG: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function trace(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? $options['logs_path'] . '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] TRACE: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function info(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? $options['logs_path'] . '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] INFO: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function warning(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? $options['logs_path'] . '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] WARN: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function error(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? $options['logs_path'] . '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] ERROR: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function fatal(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? $options['logs_path'] . '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] FATAL: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }
}
