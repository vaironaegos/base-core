<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase\Enum;

enum UseCaseOutputCode: string
{
    case OK = 'ok';
    case APPLICATION_ERROR = 'applicationError';
    case RECORD_ALREADY_EXISTS = 'recordAlreadyExists';
    case RECORD_NOT_FOUND = 'recordNotFound';
    case FIELD_ALREADY_IN_USE = 'fieldAlreadyInUse';
    case FIELD_IS_INVALID = 'fieldIsInvalid';
    case INVALID_SIGNATURE = 'invalidSignature';
    case TOKEN_IS_EXPIRED = 'tokenIsExpired';
    case TOKEN_IS_INVALID = 'tokenIsInvalid';
}
