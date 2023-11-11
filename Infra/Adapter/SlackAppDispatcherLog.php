<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use MoisesK\SlackDispatcherPHP\Attachment;
use MoisesK\SlackDispatcherPHP\SlackAppMessage;
use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
use MoisesK\SlackDispatcherPHP\Dto\AttachmentAuthor;
use MoisesK\SlackDispatcherPHP\Dto\AttachmentFooter;

final class SlackAppDispatcherLog implements LogSystem
{
    public function debug(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? LOGS_PATH .  '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] DEBUG: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function trace(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? LOGS_PATH .  '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] TRACE: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function info(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? LOGS_PATH .  '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] INFO: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function warning(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? LOGS_PATH .  '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] WARN: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }

    public function error(string $message, array $options = []): void
    {
        $errorData = json_decode($message, true);

        $slackMessage = new SlackAppMessage(env('SLACK_APP_URL'));

        if ($errorData === false) {
            $attachment = new Attachment([
                'color' => '#FF0000',
                'text' => "*Message*: ```\n{$message}\n```",
                'footer' => new AttachmentFooter(
                    footer: 'SkyEx',
                    footerIcon: "https://beta.skyex.app/assets/logo-white-simbol.3c46f4aa.png"
                ),
                'ts' => time()
            ]);

            $slackMessage->setHeaderText("[:rotating_light: ERROR] " . strtoupper(env('APP_NAME')));
            $slackMessage->addAttachment($attachment);
            $slackMessage->dispatch();
            return;
        }

        $slackMessage->setHeaderText("[:rotating_light: ERROR] " . strtoupper(env('APP_NAME')));
        $slackMessage->addAttachment(new Attachment([
            'color' => '#FF0000',
            'pretext' => "_Type:_ *{$errorData['type']}*",
            'text' => "*Error Message*: {$errorData['message']}\n\n_File_: {$errorData['file']}\n\n" .
                "Queue Message: \n\n```{$errorData['queueMessage']}\n```\n\n",
                "StackTrace: \n\n```" . json_encode($errorData['stackTrace']) . "\n```",
            'author' => new AttachmentAuthor(
                authorName: 'Handler: ' . $errorData['handler'],
                authorIcon: 'https://w7.pngwing.com/pngs/564/932/' .
                'png-transparent-robot-logo-shape-encapsulated-postscript-robot-electronics-logo-head-thumbnail.png'
            ),
            'footer' => new AttachmentFooter(
                footer: strtoupper(env('APP_NAME')),
                footerIcon: "https://beta.skyex.app/assets/logo-white-simbol.3c46f4aa.png"
            ),
            'ts' => time()
        ]));
        $slackMessage->dispatch();
    }

    public function fatal(string $message, array $options = []): void
    {
        $fileName = $options['filename'] ?? LOGS_PATH .  '/fileLog.log';
        $output = '[' . date('Y-m-d H:i:s') . '] FATAL: ' . PHP_EOL . $message . PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $output, FILE_APPEND);
    }
}
