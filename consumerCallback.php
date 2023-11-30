<?php

declare(strict_types=1);

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Doctrine\DBAL\Exception\DriverException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPProtocolException;
use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
use Astrotech\ApiBase\Exception\ValidationException;
use Astrotech\ApiBase\Infra\QueueConsumer\ConsumerBase;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;

function processMessage(AMQPMessage $message, ContainerInterface $container): void
{
    /** @var LogSystem $logSystem */
    $logSystem = $container->get(LogSystem::class);
    $consumerRoutes = require ROUTES_PATH . '/consumers.php';
    $messageBody = json_decode($message->getBody(), true);
    $traceId = uniqid();

    foreach ($consumerRoutes as $routingKey => $handlersList) {
        if ($message->getRoutingKey() !== $routingKey) {
            continue;
        }

        foreach ($handlersList as $routingKeyAction => $handlers) {
            $actionName = trim($messageBody['action']);

            if ($routingKeyAction !== $actionName) {
                continue;
            }

            if (!APP_IS_PRODUCTION && isset($messageBody['data']['automatedTests'])) {
                $data = json_encode($messageBody, JSON_PRETTY_PRINT);
                file_put_contents(RUNTIME_PATH . "/automatedTests/{$messageBody['eventId']}.log", $data);
                continue;
            }

            $logSystem->info(
                json_encode(
                    ['message' => "'{$actionName}' action matched with '{$routingKeyAction}' Routing Key!"],
                    JSON_PRETTY_PRINT
                ),
                ['category' => $traceId]
            );

            try {
                foreach ($handlers as $handlerClassName) {
                    $errorHandler = function (
                        Throwable $e,
                        array $details = []
                    ) use (
                        $logSystem,
                        $handlerClassName,
                        $messageBody,
                        $traceId
                    ): void {
                        $data = [
                            'date' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                            'handler' => $handlerClassName,
                            'type' => get_class($e),
                            'message' => "{$e->getMessage()}",
                            'file' => "{$e->getFile()}:{$e->getLine()}",
                            'stackTrace' => $e->getTrace(),
                            'queueMessage' => json_encode($messageBody),
                            ...$details
                        ];

                        $jsonPayload = json_encode($data, JSON_PRETTY_PRINT);

                        if ($jsonPayload !== false) {
                            $logSystem->error($jsonPayload, ['category' => $traceId]);
                            return;
                        }

                        $output = sprintf(
                            "[%s] %s (%s:%s)" . PHP_EOL . "%s",
                            $handlerClassName,
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine(),
                            $e->getTraceAsString()
                        );

                        $logSystem->error($output, ['category' => $traceId]);
                    };

                    /** @var ConsumerBase $handler */
                    $handler = new $handlerClassName($container, $message, $traceId);
                    $handler->execute();
                }

                $message->ack();
            } catch (ValidationException $e) {
                $errorHandler($e);
                $message->ack();
            } catch (
                RequestException
                | ConnectException
                | AMQPRuntimeException
                | AMQPProtocolException
                | AMQPConnectionClosedException
                $e
            ) {
                $errorHandler($e);
                $message->nack(true);
            } catch (DriverException $e) {
                $errorHandler($e, ['query' => $e->getQuery()->getSQL(), 'values' => $e->getQuery()->getParams()]);
                $message->nack(true);
            } catch (Throwable $e) {
                $errorHandler($e);
                $message->nack(true);
            } finally {
                $message->getChannel()->close();
            }

            $logSystem->info(
                json_encode(['message' => "The '{$actionName}' handlers finished."], JSON_PRETTY_PRINT),
                ['category' => $traceId]
            );
        }
    }

    $logSystem->info(json_encode(
        ['message' => "All handlers executed!"],
        JSON_PRETTY_PRINT
    ), ['category' => $traceId]);
}
