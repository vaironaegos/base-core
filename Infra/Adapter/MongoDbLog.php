<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use DateTimeImmutable;
use Astrotech\Core\Base\Infra\Enum\LogLevelEnum;
use Astrotech\Core\Base\Adapter\Contracts\LogSystem;
use Astrotech\Core\Base\Domain\Contracts\LogRepository;

final class MongoDbLog implements LogSystem
{
    private string $defaultCategory = 'default';

    public function __construct(
        private readonly LogRepository $logRepository
    ) {
    }

    public function debug(string $message, array $options = []): void
    {
        $category = $options['category'] ?? $this->defaultCategory;
        $this->persistLog($category, LogLevelEnum::DEBUG, $message);
    }

    public function trace(string $message, array $options = []): void
    {
        $category = $options['category'] ?? $this->defaultCategory;
        $this->persistLog($category, LogLevelEnum::TRACE, $message);
    }

    public function info(string $message, array $options = []): void
    {
        $category = $options['category'] ?? $this->defaultCategory;
        $this->persistLog($category, LogLevelEnum::INFO, $message);
    }

    public function warning(string $message, array $options = []): void
    {
        $category = $options['category'] ?? $this->defaultCategory;
        $this->persistLog($category, LogLevelEnum::WARNING, $message);
    }

    public function error(string $message, array $options = []): void
    {
        $category = $options['category'] ?? $this->defaultCategory;
        $this->persistLog($category, LogLevelEnum::ERROR, $message);

        $slackLogDispatcher = new SlackAppDispatcherLog();
        $slackLogDispatcher->error($message, $options);
    }

    public function fatal(string $message, array $options = []): void
    {
        $category = $options['category'] ?? $this->defaultCategory;
        $this->persistLog($category, LogLevelEnum::FATAL, $message);
    }

    private function persistLog(string $category, LogLevelEnum $level, string $message): void
    {
        $this->logRepository->insert([
            'ip' => getRealIp(),
            'category' => $category,
            'level' => $level->value,
            'createdAt' => (new DateTimeImmutable())->format(DATE_ATOM),
            'message' => $message,
        ]);
    }
}
