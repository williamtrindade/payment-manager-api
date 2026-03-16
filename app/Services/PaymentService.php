<?php

namespace App\Services;

use App\DTOs\CardDTO;
use App\Enums\TransactionStatus;
use App\Gateways\Gateway1Provider;
use App\Gateways\Gateway2Provider;
use App\Gateways\PaymentGatewayInterface;
use App\Models\Gateway;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function charge(Transaction $transaction, CardDTO $cardData): bool
    {
        $gateways = Gateway::where('is_active', true)->orderBy('priority')->get();

        foreach ($gateways as $gatewayModel) {
            try {
                $provider = $this->getProvider($gatewayModel);
                $provider->setConfig($gatewayModel);

                $response = $provider->charge($transaction, $cardData);

                if ($response->successful()) {
                    $transaction->update([
                        'status' => TransactionStatus::PAID,
                        'gateway_id' => $gatewayModel->id,
                        'external_id' => $response->json('id') ?? $response->json('transacao_id')
                    ]);
                    return true;
                }
            } catch (Exception $e) {
                Log::error("Falha na cobrança com {$gatewayModel->name}: " . $e->getMessage());
                continue; // Tenta o próximo gateway
            }
        }
        return false;
    }

    public function refund(Transaction $transaction): bool
    {
        $gatewayModel = $transaction->gateway;

        if (!$gatewayModel || !$transaction->external_id) {
            return false;
        }

        try {
            $provider = $this->getProvider($gatewayModel);
            $provider->setConfig($gatewayModel);

            $response = $provider->refund($transaction);

            if ($response->successful()) {
                $transaction->update(['status' => TransactionStatus::REFUNDED]);
                return true;
            }
        } catch (Exception $e) {
            Log::error("Falha no estorno da transação {$transaction->id}: " . $e->getMessage());
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function getProvider(Gateway $gateway): PaymentGatewayInterface
    {
        return match($gateway->name) {
            'Gateway 1' => new Gateway1Provider(),
            'Gateway 2' => new Gateway2Provider(),
            default => throw new Exception("Gateway não suportado"),
        };
    }
}
