<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function toggle(Gateway $gateway): JsonResponse
    {
        // To not deactivate the last active gateway
        if ($gateway->is_active && Gateway::where('is_active', true)->count() <= 1) {
            return response()->json(['error' => 'Não é possível desativar o último gateway ativo.'], 422);
        }
        $gateway->update([
            'is_active' => !$gateway->is_active
        ]);

        return response()->json([
            'message' => "Gateway {$gateway->name} " . ($gateway->is_active ? 'ativado' : 'desativado') . " com sucesso.",
            'data' => [
                'id' => $gateway->id,
                'name' => $gateway->name,
                'is_active' => $gateway->is_active
            ]
        ]);
    }

    public function updatePriority(Request $request, Gateway $gateway): JsonResponse
    {
        $request->validate([
            'priority' => 'required|integer|min:1',
        ]);

        $gateway->update([
            'priority' => $request->priority
        ]);

        return response()->json([
            'message' => "Prioridade do gateway {$gateway->name} atualizada para {$gateway->priority}.",
            'data' => $gateway->only(['id', 'name', 'priority', 'is_active'])
        ]);
    }
}
