<?php

namespace Esyede\Dana\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Esyede\Dana\Exception\DanaCoreException;
use Esyede\Dana\Exception\DanaSignSignatureException;
use Esyede\Dana\Exception\DanaVerifySignatureException;


class DanaCoreService
{
    private static $response;
    private static $heads;
    private static $bodies;

    /**
     * Initialize Request Header
     *
     * @return array
     */
    public static function getRequestHeader(): array
    {
        return [
            'version' => config('dana.version'),
            'clientId' => config('dana.client_id'),
            'clientSecret' => config('dana.client_secret'),
            'reqTime' => date(config('dana.date_format')),
            'reqMsgId' => Str::uuid()->toString(),
            'reserve' => '{}',
        ];
    }

    /**
     * Initialize Response Header
     *
     * @return array
     */
    public static function  getResponseHeader(): array
    {
        return [
            'version' => config('dana.version'),
            'clientId' => config('dana.client_id'),
            'respTime' => date(config('dana.date_format')),
            'reqMsgId' => Str::uuid()->toString(),
        ];
    }

    /**
     * Main api function to call to Dana
     *
     * @param string $path
     * @param array  $heads
     * @param array  $bodies
     *
     * @return DanaCoreService
     */
    public static function api(string $path, array $heads = [], array $bodies = []): DanaCoreService
    {
        $defaultHead = static::getRequestHeader();
        $request = ['head' => array_merge($defaultHead, $heads), 'body' => $bodies];
        $payloads = ['request' => $request, 'signature' => static::signSignature($request)];

        $res = Http::post(config('dana.api_url') . $path, $payloads);

        Log::info('[DANA] Sending request to: ' . config('dana.api_url'));
        Log::info($payloads);
        Log::info('[DANA] Response:');
        Log::info($res->json());

        if ($res->failed()) {
            Log::critical('[DANA] Error when requesting dana dana.oauth.auth.applyToken');
            Log::critical($res->json());
            throw new DanaCoreException('Error when processing dana request', 400);
        }

        static::$response = $res;
        static::$heads = $heads;
        static::$bodies = $bodies;

        return new static;
    }

    /**
     * Return all response from http client as is
     *
     * @return Response
     */
    public function all(): Response
    {
        return static::$response;
    }

    /**
     * Return only message code and status from dana API
     *
     * @return object
     */
    public function message(): object
    {
        $data = json_decode(static::$response->body())->response;

        if (json_last_error() !== JSON_ERROR_NONE) {
            (object) [
                'code' => 500,
                'status' => 'ERROR',
                'msg' => 'Unable to decode json data',
            ];
        }

        return (object) [
            'code' => ($data->body->resultInfo->resultCode !== 'SUCCESS') ? 400 : 200,
            'status' => $data->body->resultInfo->resultCode,
            'msg' => $data->body->resultInfo->resultMsg,
        ];
    }

    /**
     * Return data body with format object json
     *
     * @return Collection
     */
    public function body(): Collection
    {
        $msg  = (array) $this->message();
        $resp = collect((array) json_decode(static::$response->body())->response);
        $data = collect($resp->get('body'))->put('transactionTime', $resp->get('head')->respTime);

        return (collect($msg)->merge($data->toArray()));
    }
    /**
     * Sign signature
     * See this doc API Dana
     * https://dashboard.dana.id/api-docs/read/45
     *
     * @param array $bodies
     * @return string
     */
    public static function signSignature(array $data): string
    {
        $signature  = '';
        $privateKey = config('dana.rsa_private_key', '');

        if (!$privateKey) {
            throw new DanaSignSignatureException('Please set your app private key');
        }

        openssl_sign(json_encode($data), $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * @param array  $data string data in json
     * @param string $signature string of signature in base64 encoded
     *
     * @return string base 64 signature
     */
    public function verifySignature(array $data, string $signature)
    {
        $publicKey = config('dana.rsa_public_key', '');

        if (!$publicKey) {
            throw new DanaVerifySignatureException('Please set your dana public key');
        }

        $binarySignature = base64_decode($signature);

        return openssl_verify(json_encode($data), $binarySignature, $publicKey, OPENSSL_ALGO_SHA256);
    }
}
