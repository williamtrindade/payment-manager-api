<?php

namespace App\Gateways;

use App\DTOs\CardDTO;
use App\Models\Transaction;
use App\Models\Gateway;
use Illuminate\Http\Client\Response;

interface PaymentGatewayInterface
{
    public function setConfig(Gateway $gateway);
    public function charge(Transaction $transaction, CardDTO $cardData): Response;
    public function refund(Transaction $transaction): Response;
}
