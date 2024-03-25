<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase\Enum;

enum UseCaseOutputCode: string
{
    // Generic errors
    case OK = 'ok';
    case APPLICATION_ERROR = 'applicationError';
    case RECORD_ALREADY_EXISTS = 'recordAlreadyExists';
    case RECORD_NOT_FOUND = 'recordNotFound';
    case INVALID_SIGNATURE = 'invalidSignature';
    case TOKEN_IS_EXPIRED = 'tokenIsExpired';
    case TOKEN_IS_INVALID = 'tokenIsInvalid';

    case COMPANY_PAYMENT_GATEWAY_NOT_REGISTERED = 'companyNotHasValidPaymentGatewayRegistered';
    case CPF_CNPJ_IS_INVALID = 'cpfCnpjIsInvalid';
    case EMAIL_IS_INVALID = 'emailIsInvalid';
    case CPF_CNPJ_ALREADY_IN_USE = 'cpfCnpjAlreadyInUse';
    case EMAIL_ALREADY_IN_USE = 'emailAlreadyInUse';
    case PHONE_ALREADY_IN_USE = 'phoneAlreadyInUse';
    case USER_ALREADY_ACTIVE = 'userAlreadyActive';
    case PHONE_IS_INVALID = 'phoneIsInvalid';
    case AMOUNT_NOT_CAN_NEGATIVE = 'amountNotCanNegative';
}
