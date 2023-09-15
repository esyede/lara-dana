<?php

return [

    'version' => '2.0',
    'env' => 'development', // development / production
    'active' => false,

    // Dana API base url (Ex: https://api-sandbox.saas.dana.id)
    'api_url' => 'https://api-sandbox.saas.dana.id',

    // Dana Web URL (Ex: https://m.sandbox.dana.id)
    'web_url' => 'https://m.sandbox.dana.id',

    // Dana merchant id (Ex: 212640060018011593493)
    'merchant_id' => '',

    // Dana client id (Ex: 2018122812174155520063)
    'client_id' => '',

    // Dana client secret (Ex: 3f5798274c9b427e9e0aa2c5db0a6454)
    'client_secret' => '',

    /**
     * for oauthRedirectUrl value
     * Put your redirect url for OAuth flow/account binding, to redirect the authCode
     * example = https://api.merchant.com/oauth-callback
     *
     */
    'oauth_redirect_url' => 'https://api.merchant.com/oauth-callback',

    /**
     * for oauthScopes value
     * Account binding
     *
     */
    'oauth_scopes' => 'CASHIER,QUERY_BALANCE,DEFAULT_BASIC_PROFILE,MINI_DANA',

    /**
     * for get user profile
     * user resources
     *
     */
    'user_resources' => [
        'BALANCE',
        'TRANSACTION_URL',
        'MASK_DANA_ID',
        'TOPUP_URL',
        'OTT',
    ],

    /**
     * for refundDestination value
     * Api configuration
     *
     */
    'refund_destination' => 'TO_BALANCE',

    /**
     * For date format
     */
    'date_format' => 'Y-m-d\TH:i:sP',

    /**
     * For expired date after. Unit is minutes
     */
    'expired_after' => 60,
    // Equivalent to 1 hours

    /**
     * For get notif every status order is changed
     */
    'order_notify_url' => '',

    /**
     * For get redirect user to merchant website
     */
    'pay_return_url' => '',

    /**
     * Get Dana public key
     */
    'rsa_public_key' => '',

    /**
     * Get local private key
     */
    'rsa_private_key' => '',

    /**
     * mdr percent update on 2023
     */
    'mdr_percent' => [
        /**
         * mdr persent for credit card
         * 0.0018 is equal to 1.8%
         */
        'credit_card' => 0.018,

        /**
         * mdr persent for credit card
         * 0.0018 is equal to 1.8%
         */
        'debit_card' => 0.018,

        /**
         * mdr persent for balance
         * 0.012 is equal to 1.2%
         */
        'balance' => 0.012,

        /**
         * mdr persent for credit card
         * 0.0012 is equal to 1.2%
         */
        'direct_debit_credit_card' => 0.012,

        /**
         * mdr persent for credit card
         * 0.0012 is equal to 1.2%
         */
        'direct_debit_debit_card' => 0.012,

        /**
         * mdr persent for credit card
         * 0.0012 is equal to 1.2%
         */
        'online_credit' => 0.012,

    ],
    'mdr_before_tax' => [
        /**
         * mdr before tax for virtual account
         * 2000 is equal to 2000 Rupiah
         */
        'online_credit' => 2000
    ],

    /**
     * fee tax
     *
     * 0.11 is equal to 11%
     */
    'fee_tax' => 0.11,
];
