<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase;

use InvalidArgumentException;
use Astrotech\Core\Base\Domain\UseCase\Error\UseCaseError;

final class UseCaseOutput
{
    private bool $success;

    public function __construct(
        public ?UseCaseOutputCode $resultCode = null,
        public ?UseCaseError $error = null,
        public array $data = [],
    ) {
        if (is_null($this->resultCode)) {
            $this->resultCode = UseCaseOutputCode::ok();
        }

        $this->success = is_null($error);
    }

    public function values(): array
    {
        if ($this->success) {
            return $this->data;
        }

        return [
            ...$this->error->output(),
            'data' => $this->data,
            'errorKey' => $this->resultCode->getValue()
        ];
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (!array_key_exists($name, $this->data)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Data attribute '%s' in '%s' doesn't exists",
                    $name,
                    get_class($this)
                )
            );
        }

        return $this->data[$name];
    }
}
