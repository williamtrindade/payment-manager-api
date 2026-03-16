<?php

namespace App\Gateways;

use App\DTOs\CardDTO;
use App\Models\Gateway;
use App\Models\Transaction;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Gateway1Provider implements PaymentGatewayInterface
{
    protected Gateway $config;

    public function setConfig(Gateway $gateway): void
    {
        $this->config = $gateway;
    }

    /**
     * @throws ConnectionException
     */
    private function makeRequest(string $token, Transaction $transaction, CardDTO $card): LazyPromise|PromiseInterface|Response
    {
        return Http::withToken($token)
            ->post("{$this->config->api_url}/transactions", [
                'amount'      => $transaction->amount,
                'name'        => $transaction->client->name,
                'email'       => $transaction->client->email,
                'cardNumber' => $card->number,
                'cvv'         => $card->cvv,
            ]);
    }

    /**
     * @throws ConnectionException
     */
    public function charge(Transaction $transaction, CardDTO $cardData): Response
    {
        $token = $this->getAccessToken();
        $response = $this->makeRequest($token, $transaction, $cardData);
        if ($response->status() === 401 && $response->json('error') === 'jwt expired') {

            Cache::forget($this->getCacheKey());

            $newToken = $this->getAccessToken();
            return $this->makeRequest($newToken, $transaction, $cardData);
        }

        return $response;
    }

    private function getCacheKey(): string
    {
        return "g1_token_" . md5($this->config->client_id);
    }

    /**
     * @throws ConnectionException
     */
    public function refund(Transaction $transaction): Response
    {
        $token = $this->getAccessToken();

        return Http::withToken($token)
            ->post("{$this->config->api_url}/transactions/{$transaction->external_id}/chargeback");
    }

    private function getAccessToken(): string
    {
        return Cache::remember($this->getCacheKey(), 3600, function () {
            $response = Http::post("{$this->config->api_url}/login", [
                'email' => $this->config->client_id,
                'token' => $this->config->client_secret
            ]);

            if ($response->failed()) {
                Log::error("Erro crítico de login no G1", ['body' => $response->body()]);
                throw new Exception("Auth failed for Gateway 1");
            }

            return $response->json('token');
        });
    }
}
