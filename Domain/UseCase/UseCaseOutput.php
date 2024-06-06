<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase;

use InvalidArgumentException;
use Astrotech\Core\Base\Domain\UseCase\Error\UseCaseError;
use Astrotech\Core\Base\Domain\UseCase\Enum\UseCaseOutputCode;

final class UseCaseOutput
{
    private bool $success;
    private ?string $errorKey = null;

    public function __construct(
        public UseCaseOutputCode $resultCode = UseCaseOutputCode::OK,
        public ?UseCaseError $error = null,
        public array $data = [],
    ) {
        $this->success = is_null($error);

        if (!$this->success) {
            $this->errorKey = UseCaseOutputCode::tryFrom($this->resultCode->value)->value;
        }
    }

    public function values(): array
    {
        if ($this->success) {
            return $this->data;
        }

        return [
            ...$this->error->output(),
            'data' => $this->data,
            'errorKey' => $this->errorKey
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
