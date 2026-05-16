<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MessageSwift;
use App\Models\AnomalySwift;

class SwiftController extends Controller
{
    /**
     * Chat global: envoie question + historique + stats au microservice Python
     * Retourne {"answer":"..."}
     */
    public function chatGlobal(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'history' => 'nullable|array',
        ]);

        $stats = [
            'total'     => MessageSwift::count(),
            'pending'   => MessageSwift::where('status','processed')->count(),
            'anomalies' => AnomalySwift::where('niveau_risque','HIGH')->whereNull('verifie_par')->count(),
            'volume'    => '$' . number_format(MessageSwift::sum('amount')/1000000,1) . 'M',
            'received'  => MessageSwift::where('direction','RECU')->count(),
            'emitted'   => MessageSwift::where('direction','EMIS')->count(),
        ];

        $payload = [
            'role' => optional(auth()->user())->getRoleNames()->first() ?? 'user',
            'page' => 'dashboard',
            'stats' => $stats,
            'question' => $request->input('question'),
            'history' => $request->input('history', []),
        ];

        try {
            $iaUrl = env('SWIFT_IA_URL', 'http://python-api:8001');
            $client = new \GuzzleHttp\Client(['timeout' => 45]);
            $response = $client->post("{$iaUrl}/api/chat-global", [
                'json' => $payload,
                'headers' => ['Accept' => 'application/json'],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'answer' => $data['answer'] ?? 'Réponse indisponible.',
            ]);

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            \Log::warning('ChatGlobal — service IA injoignable : '.$e->getMessage());

            return response()->json([
                'answer' => 'Service temporairement indisponible',
            ], 503);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('ChatGlobal — erreur HTTP : '.$e->getMessage());

            return response()->json([
                'answer' => 'Erreur lors de la communication avec le service IA.',
            ], 502);

        } catch (\Exception $e) {
            \Log::error('ChatGlobal — erreur inattendue : '.$e->getMessage());

            return response()->json([
                'answer' => 'Une erreur inattendue s\'est produite.',
            ], 500);
        }
    }
}
