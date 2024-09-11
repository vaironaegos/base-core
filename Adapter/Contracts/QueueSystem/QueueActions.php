<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts\QueueSystem;

enum QueueActions: string
{
    case SIGN_IN = 'signIn';
    case CUSTOMER_ACTIVATION = 'customerActivation';
    case ADMIN_SIGN_IN = 'adminSignIn';
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
    case ARBITRATION_PROCESS = 'arbitrationProcess';
    case GENERATE_VIEW = 'generateView';
    case REMOVE_VIEW = 'removeView';
    case UPDATE_VIEW = 'updateView';
    case DEPOSIT_CREATED = 'depositCreated';
    case DEPOSIT_APPROVED = 'depositApproved';
    case P2P_TRANSFER_ORDER_CREATED = 'p2pTransferOrderCreated';
    case P2P_TRANSFER_HASH_FOUND = 'p2pTransferHashFound';
    case P2P_TRANSFER_ORDER_APPROVED = 'p2pTransferOrderApproved';
    case P2P_TRANSFER_RECEIVED = 'p2pTransferOrderReceived';
    case P2P_TRANSFER_SEND = 'p2pTransferOrderSended';
    case DEPOSIT_CANCELLED = 'depositCancelled';
    case VOUCHER_CREATED = 'voucherCreated';
    case VOUCHER_CAN_PAID_COMMISSION_CREATED = 'voucherCanPaidCommissionCreated';
    case VOUCHER_DELETED = 'voucherDeleted';
    case NETWORK_COMMISSIONS_PAID = 'networkCommissionsPaid';
    case BONUS_MATRIX_PAID = 'bonusMatrixPaid';
    case WITHDRAW_CREATED = 'withdrawCreated';
    case WITHDRAW_APPROVED = 'withdrawApproved';
    case WITHDRAW_REFUSED = 'withdrawRefused';
    case CUSTOMER_BALANCE_CHANGED = 'customerBalanceChanged';
    case ADMIN_GENERATE_CUSTOMER_FORGOT_PASSWORD_LINK = 'adminGenerateCustomerForgotPasswordLink';
}
