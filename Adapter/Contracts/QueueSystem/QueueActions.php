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
}
