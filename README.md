# lara-dana
Unofficial Dana Payment API package for Laravel. Visit https://dashboard.dana.id/api-docs for more detailed documentations.

## Requirements
  - Laravel >= 10.0

## Installation

#### 1. Install this package via composer:
```sh
composer require esyede/lara-dana
```

#### 2. Publish the config file:
```sh
php artisan vendor:publish --provider="Esyede\Dana\DanaCoreServiceProvider"
```

## Usage
Configs are stored in `config/dana.php`. Customize those with your own credentials.

## Functions

### 1. Create order
```php
$orderData = [
    [
    'order' => [
        'orderTitle' => 'Dummy product',
        'orderAmount' => [
            'currency' => 'IDR',
            'value' => 100
        ],
        'merchantTransId' => '201505080001',
        'merchantTransType' => 'dummy transaction type',
        'orderMemo' => 'Memo',
        'goods' => [
            [
                'merchantGoodsId' => '24525635625623',
                'description' => 'dummy description',
                'category' => 'dummy category',
                'price' => [
                    'currency' => 'IDR',
                    'value' => 100,
                ],
                'unit' => 'Kg',
                'quantity' => '3.2',
                'merchantShippingId' => '564314314574327545',
                'snapshotUrl' => '[http://snap.url.com]',
                'extendInfo' => [
                    'myInvoiceId' => 'T12345678ASDFG', // optional
                    'remark' => 'DEBIT', // optional
                ]
            ]
        ]
    ],
    'merchantId' => '216820000000006553000',
    'subMerchantId' => '12345678',
    'productCode' => '51051000100000000001',
];

DanaPayment::createOrder($orderData);
```

For more detailed documentation, visit https://dashboard.dana.id/api-docs/read/33


### 2. Get oAuth URL
```php
$terminalType = 'WEB';
$redirectUrl  = 'https://your-app-url.com/oauth/callback';
DanaPayment::generateOauthUrl($terminalType, $redirectUrl);
```
For more detailed documentation, visit https://dashboard.dana.id/api-docs/read/47


### 3. Get Request & Refresh Token
```php
$authToken = 'your-auth-token';
DanaPayment::getToken($authToken);
```

You can get value of `$authToken` from oAuth callback process. <br>
From this function you will receive `token` and `refresh_token`. <br>
Ref: https://dashboard.dana.id/api-docs/read/32


### 4. Get User Profile
```php
$accessToken = 'your_user_profile_access_token';
DanaPayment::profile($accessToken);
```
Fill the `$accessToken` with the response of `DanaPayment::getToken()`, ref: https://dashboard.dana.id/api-docs/read/38


### 5. Unbinding Access Token
```php
DanaPayment::unbindAllAccount();
```
This method is used to revoke or unbind all access token registered from the merchant. ref: https://dashboard.dana.id/api-docs/read/46


### 6. Hnadling callback response
```php
$status = true;
DanaPayment::handleFinishNotifyCallback($status);
```
This function will generate valid response for Dana API. `$status` is boolean.


### 6. Function for calculation MDR
```php
$payAmount = 100000;
$payMethod = 'BALANCE';
DanaCalculation::calculateMDR($payAmount, $payMethod);
```
This function will calculate MDR fee for dana. Fill the `$payMethod` and `$payAmount` from dana callback data.


# Contribution

This project is far from perfect. many of dna APIs isn't implemented yet.
I would be really happy if any of you could contribute to implement it.
