<?php

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
use Astrotech\ApiBase\Infra\QueueConsumer\ConsumerBase;

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

//            if (!APP_IS_PRODUCTION && isset($messageBody['data']['automatedTests'])) {
//                $data = json_encode($messageBody, JSON_PRETTY_PRINT);
//                file_put_contents(RUNTIME_PATH . "/automatedTests/{$messageBody['eventId']}.log", $data);
//                continue;
//            }

            $logSystem->info(
                json_encode(
                    ['message' => "'{$actionName}' action matched with '{$routingKeyAction}' Routing Key!"],
                    JSON_PRETTY_PRINT
                ),
                ['category' => $traceId]
            );

            foreach ($handlers as $handlerClassName) {
                /** @var ConsumerBase $handler */
                $handler = new $handlerClassName($container, $message, $traceId);
                $handler->execute();
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
