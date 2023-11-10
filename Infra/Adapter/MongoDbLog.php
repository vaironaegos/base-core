<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
use Astrotech\ApiBase\Domain\Contracts\LogRepository;
use Astrotech\ApiBase\Infra\Enum\LogLevelEnum;
use DateTimeImmutable;
use MoisesK\SlackDispatcherPHP\MessageDispatcher;

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

        $slackLogDispatcher = new SlackAppDispatcherLog(new MessageDispatcher(config('slack.appHook')));
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
