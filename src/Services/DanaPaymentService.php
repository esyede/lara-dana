<?php

namespace Esyede\Dana\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Esyede\Dana\Facades\DanaCore;
use Esyede\Dana\Helpers\CreateOrder;
use Esyede\Dana\Validation\Validation;
use Esyede\Dana\Exception\DanaException;
use Esyede\Dana\Exception\DanaCreateOrderException;
use Esyede\Dana\Exception\DanaPaymentGetTokenException;
use Esyede\Dana\Exception\DanaPaymentUnBindingAllException;

class DanaPaymentService
{

    /**
     * Binding account to dana
     *
     * @param string $authCode
     *
     * @return Collection
     */
    public static function getToken(string $authCode): Collection
    {
        $path  = '/dana/oauth/auth/applyToken.htm';
        $heads = ['function' => 'dana.oauth.auth.applyToken'];
        $bodies = ['grantType' => 'AUTHORIZATION_CODE', 'authCode' => $authCode];

        $data = DanaCore::api($path, $heads, $bodies);

        if ($data->message()->status !== 'SUCCESS') {
            throw new DanaPaymentGetTokenException($data->message()->msg, $data->message()->code);
        }

        return collect([
            'token' => $data->body()->get('accessTokenInfo')->accessToken,
            'refresh_token' => $data->body()->get('accessTokenInfo')->accessToken,
            'expires_in' => $data->body()->get('accessTokenInfo')->expiresIn,
            'status' => $data->body()->get('accessTokenInfo')->tokenStatus,
        ]);
    }

    /**
     * Unbind access token use for merchant to revoke all tokens registered for its user
     *
     * @return Collection
     */
    public function unbindAllAccount(): Collection
    {
        $path  = '/dana/oauth/unbind/revokeAllTokens.htm';
        $heads = ['function' => 'dana.oauth.unbind.revokeAllTokens'];
        $bodies = ['merchantId' => config('dana.merchant_id')];

        $data = DanaCore::api($path, $heads, $bodies);

        if ($data->message()->status !== 'SUCCESS') {
            throw new DanaPaymentUnBindingAllException($data->message()->msg, $data->message()->code);
        }

        return collect($data->message());
    }

    /**
     * Get user profile
     *
     * @param string $accessToken
     *
     * @return Collection
     */
    public function profile(string $accessToken): Collection
    {
        $path  = '/dana/member/query/queryUserProfile.htm';
        $heads = ['function' => 'dana.member.query.queryUserProfile', 'accessToken' => $accessToken];
        $bodies = ['userResources' => config('dana.user_resources')];

        $data  = DanaCore::api($path, $heads, $bodies);

        if ($data->message()->status !== 'SUCCESS') {
            throw new DanaException($data->message()->msg, $data->message()->code);
        }

        $res = collect($data->body()->get('userResourceInfos'))->map(function ($val) {
            return [strtolower($val->resourceType) => $val->value];
        })->flatMap(function ($values) {
            return $values;
        });

        $res->put('topup_url', $res->get('topup_url') . '?ott=' . $res->get('ott'));
        $res->put('transaction_url', $res->get('transaction_url') . '?ott=' . $res->get('ott'));
        $res->forget('ott');

        return $res;
    }

    /**
     * Create order
     *
     * @param array $bodies
     *
     * @return Collection
     */
    public function createOrder(array $bodies): Collection
    {

        $path  = '/dana/acquiring/order/createOrder.htm';
        $heads = ['function' => 'dana.acquiring.order.createOrder'];
        $orderData = new CreateOrder($bodies);
        $payload = $orderData->payload();

        $res = DanaCore::api($path, $heads, $payload);

        if ($res->message()->status !== 'SUCCESS') {
            Log::error('[DANA] ' . json_encode($res->message()));
            throw new DanaCreateOrderException('DANA ' . $res->message()->msg, $res->message()->code);
        }

        return $res->body()->forget(['resultInfo', 'code', 'status', 'msg'])->map(function ($val, $key) {
            $data = ($key === 'transactionTime') ? Carbon::parse($val) : $val;
            return [$key => $data];
        })->flatMap(function ($value) {
            return $value;
        });
    }

    /**
     * Generate url oauth
     *
     * @param string $terminalType
     * @param string $redirectUrl
     *
     * @return string
     */
    public function generateOauthUrl(string $terminalType = 'WEB', string $redirectUrl = ''): string
    {
        if (!Validation::terminalType($terminalType)) {
            throw new DanaException('Terminal type is not valid', 400);
        }

        $baseAPIUrl = config('dana.web_url');
        $path = '/d/portal/oauth?';
        $params = [
            'clientId' => config('dana.client_id'),
            'scopes' => config('dana.oauth_scopes'),
            'requestId' => Str::uuid()->toString(),
            'terminalType' => $terminalType,
            'redirectUrl' => $redirectUrl,
        ];

        $oauthUrl = $baseAPIUrl . $path;
        $oauthUrl .= http_build_query($params);

        return $oauthUrl;
    }

    /**
     * Response for finish payment notify callback
     * @param boolean $status
     * @return array
     */
    public function handleFinishNotifyCallback($status = true): array
    {
        $header = DanaCore::getResponseHeader();
        $resultInfo = [
            'resultStatus' => 'S',
            'resultCodeId' => '00000000',
            'resultCode' => 'SUCCESS',
            'resultMsg' => 'success',
        ];

        if (!$status) {
            $resultInfo = [
                'resultStatus' => 'U',
                'resultCodeId' => '00000900',
                'resultCode' => 'SYSTEM_ERROR',
                'resultMsg' => 'System error',
            ];
        }

        $optionalHeader = ['function' => 'dana.acquiring.order.finishNotify'];
        $response = [
            'head' => array_merge($header, $optionalHeader),
            'body' => ['resultInfo' => $resultInfo],
        ];

        return ['response' => $response, 'signature' => DanaCore::signSignature($response)];
    }
}
