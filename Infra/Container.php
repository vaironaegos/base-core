<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

final class Container
{
    private static ContainerInterface $instance;

    private function __construct(
        private readonly ContainerBuilder $containerBuilder
    ) {
        self::$instance = $this->containerBuilder->build();
    }

    public static function build(array $definitionDeps): self
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($definitionDeps);
        return new static($containerBuilder);
    }

    public static function instance(): ContainerInterface
    {
        return self::$instance;
    }

    public function set(string $name, string $value): void
    {
        self::$instance->set($name, $value);
    }

    public function get(string $name): mixed
    {
        return self::$instance->get($name);
    }
}
