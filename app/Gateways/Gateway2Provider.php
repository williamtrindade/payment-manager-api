<?php

namespace App\Gateways;

use App\DTOs\CardDTO;
use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Gateway2Provider implements PaymentGatewayInterface
{
    protected Gateway $config;

    public function setConfig(Gateway $gateway): void
    {
        $this->config = $gateway;
    }

    /**
     * @throws ConnectionException
     */
    public function charge(Transaction $transaction, CardDTO $cardData): Response
    {
        return Http::withHeaders($this->getHeaders())
            ->post("{$this->config->api_url}/transacoes",
            [
                'valor'        => $transaction->amount,
                'nome'         => $transaction->client->name,
                'email'        => $transaction->client->email,
                'numeroCartao' => $cardData->number,
                'cvv'          => $cardData->cvv,
            ]
        );
    }

    /**
     * @throws ConnectionException
     */
    public function refund(Transaction $transaction): Response
    {
        return Http::withHeaders($this->getHeaders())
            ->post("{$this->config->api_url}/transactions/{$transaction->external_id}/charge_back");
    }

    private function getHeaders(): array
    {
        return [
            'Gateway-Auth-Token' => $this->config->client_id,
            'Gateway-Auth-Secret' => $this->config->client_secret,
        ];
    }
}
