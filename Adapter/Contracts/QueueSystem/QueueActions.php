<?php

namespace Astrotech\ApiBase\Adapter\Contracts\QueueSystem;

enum QueueActions: string
{
    case SIGN_IN = 'signIn';
    case SIGN_UP = 'signUp';
    case FORGOT_PASSWORD = 'forgotPassword';
    case RESET_PASSWORD = 'resetPassword';
    case EMAIL_VERIFICATION_REQUEST = 'emailVerificationRequest';
    case EMAIL_VERIFICATION = 'emailVerification';
    case PHONE_VERIFICATION_REQUEST = 'phoneVerificationRequest';
    case PHONE_VERIFICATION = 'phoneVerification';
    case UPDATE_AVATAR = 'avatarUpdate';
    case CHANGE_AUTOMATIC_OPERATIONS = 'automaticOperationsUpdate';
    case UPDATE_PROFILE = 'updateProfile';
    case ARBITRATION_REQUEST = 'arbitrationRequest';
    case ARBITRATION_ORDER_CREATED = 'arbitrationOrderCreated';
    case ARBITRATION_ORDER_APPROVED = 'arbitrationOrderApproved';
    case ARBITRATION_PROCESS = 'arbitrationProccess';
    case GENERATE_VIEW = 'generateView';
    case UPDATE_VIEW = 'updateView';
}
