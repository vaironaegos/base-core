<?php

namespace Astrotech\ApiBase\Adapter\Contracts\QueueSystem;

enum QueueActions: string
{
    case SIGN_IN = 'signIn';
    case SIGN_UP = 'signUp';
    case FORGOT_PASSWORD = 'forgotPassword';
    case RESET_PASSWORD = 'resetPassword';
}
