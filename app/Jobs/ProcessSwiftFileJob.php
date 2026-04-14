<?php

namespace App\Jobs;

use App\Models\MessageSwift;
use App\Models\SwiftMessageDetail;
use App\Services\AnomalyService;                          // ← AJOUT IA
use App\Services\MxToMtService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;
use App\Helpers\SwiftParser;

/**
 * ProcessSwiftFileJob
 *
 * Flux REÇU (IN) :
 *   1. Lire le XML (éventuellement enveloppé dans SAA DataPDU)
 *   2. Détecter le type MX (PACS.008, PACS.009, CAMT.053…)
 *   3. Parser les champs métier
 *   4. Convertir MX → MT  (MxToMtService)
 *   5. MessageSwift::create() avec TOUS les champs financiers
 *      → L'Observer created() se déclenche avec amount/currency/value_date ✅
 *      → La Transaction est créée automatiquement ✅
 *   6. AnomalyService::analyze() — analyse IA automatique              ← AJOUT IA
 */
class ProcessSwiftFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 600;

    protected string  $filePath;
    protected ?int    $importedBy;
    protected ?string $xmlContent;

    protected array $mxToMtMap = [
        'PACS.008' => 'MT103',
        'PACS.009' => 'MT202',
        'CAMT.053' => 'MT940',
        'CAMT.054' => 'MT942',
        'PAIN.001' => 'MT101',
    ];

    public function __construct(string $filePath, ?int $importedBy = null, ?string $xmlContent = null)
    {
        $this->filePath   = $filePath;
        $this->importedBy = $importedBy;
        $this->xmlContent = $xmlContent;
    }

    // =========================================================
    // HANDLE
    // =========================================================

    public function handle(): void
    {
        try {
            // 1. Lire le contenu
            if ($this->xmlContent === null) {
                if (!file_exists($this->filePath)) {
                    throw new \Exception("Fichier introuvable : {$this->filePath}");
                }
                $this->xmlContent = file_get_contents($this->filePath);
                if ($this->xmlContent === false || trim($this->xmlContent) === '') {
                    throw new \Exception("Fichier vide ou illisible.");
                }
            }

            // 2. Extraire le payload (gère SAA DataPDU)
            $payload = $this->extractPayload($this->xmlContent);

            // 3. Parser le XML
            $xml       = new SimpleXMLElement($payload);
            $ns        = $xml->getDocNamespaces(true);
            $defaultNs = reset($ns) ?: '';

            // 4. Détecter le type
            $typeShort = $this->detectAndNormalizeType($xml, $defaultNs, $payload);
            $categorie = $this->determineCategory($typeShort);
            $mtType    = $this->mxToMtMap[$typeShort] ?? $typeShort;

            // 5. Parser les données métier
            $parsedData = $this->parseData($xml, $typeShort, $payload);

            // 6. Convertir MX → MT
            $mtContent = $this->convertToMt($parsedData, $typeShort);

            // 7. ─── CLEF DU FIX ───
            // MessageSwift::create() AVEC TOUS LES CHAMPS FINANCIERS
            // Ainsi l'Observer created() les trouve immédiatement
            // et crée la Transaction automatiquement.
            $message = MessageSwift::create([
                'TYPE_MESSAGE'  => $typeShort,
                'CATEGORIE'     => $categorie,
                'DIRECTION'     => 'IN',
                'REFERENCE'     => $parsedData['reference']    ?? ('IMPORT-' . Str::random(10)),
                'SENDER_BIC'    => $parsedData['sender_bic']   ?? null,
                'RECEIVER_BIC'  => $parsedData['receiver_bic'] ?? null,
                'SENDER_NAME'   => $parsedData['sender_name']  ?? null,
                'RECEIVER_NAME' => $parsedData['receiver_name']?? null,
                // ↓ Champs financiers inclus dès la création ↓
                'AMOUNT'        => $parsedData['amount']       ?? 0.00,
                'CURRENCY'      => $parsedData['currency']     ?? 'EUR',
                'VALUE_DATE'    => $parsedData['value_date']   ?? now()->format('Y-m-d'),
                'DESCRIPTION'   => $parsedData['description']  ?? null,
                'XML_BRUT'      => $this->xmlContent,
                'MT_CONTENT'    => $mtContent,
                'STATUS'        => 'processed',
                'CREATED_BY'    => $this->importedBy,
                'CREATED_AT'    => now(),
                'UPDATED_AT'    => now(),
            ]);

            // 8. Sauvegarder les détails (tags MT)
            foreach ($parsedData['details'] ?? [] as $tag => $value) {
                if (!empty($value)) {
                    SwiftMessageDetail::create([
                        'message_id' => $message->id,
                        'tag_name'   => $tag,
                        'tag_value'  => $value,
                    ]);
                }
            }

            // 9. Mise à jour via common_mapping
            $this->applyCommonMapping($message, $parsedData, $mtType);

            // =========================================================
            // 10. ANALYSE IA — Détection d'anomalies automatique       ← AJOUT IA
            // =========================================================
            try {
                $result = app(AnomalyService::class)->analyze($message->fresh());
                Log::info('Analyse IA SWIFT reçu', [
                    'message_id' => $message->id,
                    'reference'  => $message->REFERENCE,
                    'score'      => $result['score'],
                    'niveau'     => $result['niveau_risque'],
                    'raisons'    => $result['raisons'],
                ]);
            } catch (Throwable $e) {
                Log::warning('Analyse IA échouée (non bloquant)', [
                    'message_id' => $message->id,
                    'error'      => $e->getMessage(),
                ]);
            }
            // =========================================================

            Log::info('Import SWIFT réussi', [
                'file'      => basename($this->filePath),
                'type'      => $typeShort,
                'reference' => $parsedData['reference'] ?? 'N/A',
                'amount'    => $parsedData['amount']    ?? 0,
                'currency'  => $parsedData['currency']  ?? 'N/A',
            ]);

        } catch (Throwable $e) {
            Log::error('Échec import XML', [
                'file'  => basename($this->filePath),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Créer un message d'erreur
            $errorMessage = MessageSwift::create([
                'TYPE_MESSAGE'       => 'ERROR',
                'REFERENCE'          => 'IMPORT-FAILED-' . basename($this->filePath),
                'XML_BRUT'           => $this->xmlContent ?? 'Contenu non lu',
                'STATUS'             => 'rejected',
                'CREATED_BY'         => $this->importedBy,
                'TRANSLATION_ERRORS' => json_encode(['error' => $e->getMessage()]),
            ]);

            // =========================================================
            // ANALYSE IA sur le message d'erreur aussi                 ← AJOUT IA
            // =========================================================
            try {
                app(AnomalyService::class)->analyze($errorMessage->fresh());
            } catch (Throwable $iaException) {
                Log::warning('Analyse IA erreur message échouée', [
                    'error' => $iaException->getMessage(),
                ]);
            }
            // =========================================================

            $this->fail($e);
        } finally {
            if (file_exists($this->filePath)) {
                @unlink($this->filePath);
            }
        }
    }

    // =========================================================
    // EXTRACTION DU PAYLOAD (SAA DataPDU)
    // =========================================================

    private function extractPayload(string $rawXml): string
    {
        if (stripos($rawXml, 'DataPDU') !== false || stripos($rawXml, 'Saa:Body') !== false) {
            return $this->extractFromSaaEnvelope($rawXml);
        }
        return $rawXml;
    }

    private function extractFromSaaEnvelope(string $rawXml): string
    {
        try {
            $xml   = new SimpleXMLElement($rawXml);
            $allNs = $xml->getDocNamespaces(true);
            foreach ($allNs as $prefix => $uri) {
                $xml->registerXPathNamespace($prefix ?: 'def', $uri);
            }

            // Chercher Document MX (pacs, camt, pain)
            $docNodes = $xml->xpath("//*[local-name()='Document' and (
                contains(namespace-uri(), 'pacs') or
                contains(namespace-uri(), 'camt') or
                contains(namespace-uri(), 'pain') or
                contains(namespace-uri(), 'iso:20022')
            )]");

            if (!empty($docNodes)) {
                $docXml = $docNodes[0]->asXML();
                if ($docXml) return $docXml;
            }

            // Fallback
            $docNodes = $xml->xpath("//*[local-name()='Document']");
            if (!empty($docNodes)) {
                $docXml = $docNodes[0]->asXML();
                if ($docXml) return $docXml;
            }

        } catch (Throwable $e) {
            Log::warning('Extraction SAA échouée', ['error' => $e->getMessage()]);
        }

        return $rawXml;
    }

    // =========================================================
    // DÉTECTION DU TYPE
    // =========================================================

    private function detectAndNormalizeType(SimpleXMLElement $xml, string $defaultNs, string $rawXml): string
    {
        $lowerNs  = strtolower($defaultNs);
        $lowerRaw = strtolower($rawXml);

        foreach (['pacs.008', 'pacs.009', 'camt.053', 'camt.054', 'pain.001'] as $t) {
            if (str_contains($lowerNs, $t))  return strtoupper($t);
        }
        foreach (['pacs.008', 'pacs.009', 'camt.053', 'camt.054', 'pain.001'] as $t) {
            if (str_contains($lowerRaw, $t)) return strtoupper($t);
        }

        $ns = $xml->getDocNamespaces(true);
        foreach ($ns as $nsUri) {
            $lower = strtolower($nsUri);
            foreach (['pacs.008', 'pacs.009', 'camt.053', 'camt.054', 'pain.001'] as $t) {
                if (str_contains($lower, $t)) return strtoupper($t);
            }
        }

        $root = strtolower($xml->getName());
        if (str_contains($root, 'ficstmrcdttrf') || str_contains($root, 'fitoficstmrcdttrf')) return 'PACS.008';
        if (str_contains($root, 'ficdttrf'))       return 'PACS.009';
        if (str_contains($root, 'bktocstmrstmt'))  return 'CAMT.053';

        if (preg_match('/<MT(\d{3})/i', $rawXml, $m)) return 'MT' . $m[1];

        return 'UNKNOWN';
    }

    private function determineCategory(string $typeShort): string
    {
        $upper = strtoupper($typeShort);
        if (str_starts_with($upper, 'PACS')) return 'PACS';
        if (str_starts_with($upper, 'CAMT')) return 'CAMT';
        if (str_starts_with($upper, 'PAIN')) return 'PAIN';
        if (preg_match('/^MT(\d)/', $upper, $m)) return $m[1];
        return 'AUTRE';
    }

    // =========================================================
    // PARSING DES DONNÉES
    // =========================================================

    private function parseData(SimpleXMLElement $xml, string $typeShort, string $rawXml): array
    {
        $data = [
            'reference'    => 'IMPORT-' . Str::random(10),
            'amount'       => 0.00,
            'currency'     => 'EUR',
            'value_date'   => now()->format('Y-m-d'),
            'sender_bic'   => null,
            'receiver_bic' => null,
            'sender_name'  => null,
            'receiver_name'=> null,
            'description'  => null,
            'uetr'         => null,
            'details'      => [],
        ];

        try {
            $val = function (string $xpath) use ($xml): ?string {
                $nodes = $xml->xpath($xpath);
                return !empty($nodes) ? trim((string) $nodes[0]) : null;
            };

            $attr = function (string $xpath, string $attrName) use ($xml): ?string {
                $nodes = $xml->xpath($xpath);
                if (!empty($nodes)) {
                    return (string) $nodes[0][$attrName] ?: null;
                }
                return null;
            };

            switch ($typeShort) {

                // ══════════════════════════════════════════
                // PACS.008 → MT103
                // ══════════════════════════════════════════
                case 'PACS.008':
                    $ref      = $val("//*[local-name()='MsgId']")
                             ?? $val("//*[local-name()='InstrId']")
                             ?? $data['reference'];
                    $amount   = (float) ($val("//*[local-name()='IntrBkSttlmAmt']") ?? 0);
                    $currency = $attr("//*[local-name()='IntrBkSttlmAmt']", 'Ccy') ?? 'EUR';
                    $valDate  = $val("//*[local-name()='IntrBkSttlmDt']") ?? now()->format('Y-m-d');
                    $uetr     = $val("//*[local-name()='UETR']");

                    $senderBic   = $val("//*[local-name()='InstgAgt']//*[local-name()='BICFI']")
                                ?? $val("//*[local-name()='DbtrAgt']//*[local-name()='BICFI']");
                    $receiverBic = $val("//*[local-name()='InstdAgt']//*[local-name()='BICFI']")
                                ?? $val("//*[local-name()='CdtrAgt']//*[local-name()='BICFI']");

                    $senderName    = $val("//*[local-name()='Dbtr']/*[local-name()='Nm']");
                    $receiverName  = $val("//*[local-name()='Cdtr']/*[local-name()='Nm']");
                    $description   = $val("//*[local-name()='RmtInf']/*[local-name()='Ustrd']");
                    $opCode        = 'CRED';
                    $chargeBearRaw = $val("//*[local-name()='ChrgBr']") ?? 'SHAR';
                    $chargeBear    = match(strtoupper($chargeBearRaw)) {
                        'SHAR' => 'SHA', 'DEBT' => 'BEN', 'CRED' => 'OUR', default => 'SHA',
                    };

                    $debtorIban     = $val("//*[local-name()='DbtrAcct']//*[local-name()='IBAN']")
                                   ?? $val("//*[local-name()='DbtrAcct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']");
                    $debtorAdrNodes = $xml->xpath("//*[local-name()='Dbtr']/*[local-name()='PstlAdr']/*[local-name()='AdrLine']");
                    $debtorAddr     = implode("\n", array_map('strval', $debtorAdrNodes ?? []));
                    $tag50 = '';
                    if ($debtorIban) $tag50 .= '/' . $debtorIban . "\n";
                    if ($senderName) $tag50 .= $senderName;
                    if ($debtorAddr) $tag50 .= "\n" . $debtorAddr;

                    $creditorAdrNodes = $xml->xpath("//*[local-name()='Cdtr']/*[local-name()='PstlAdr']/*[local-name()='AdrLine']");
                    $creditorAddr     = implode("\n", array_map('strval', $creditorAdrNodes ?? []));
                    $tag59 = $receiverName ?? '';
                    if ($creditorAddr) $tag59 .= "\n" . $creditorAddr;

                    $purposeRaw = $val("//*[local-name()='Purp']/*[local-name()='Prtry']")
                               ?? $val("//*[local-name()='Purp']/*[local-name()='Cd']");
                    $tag26T = $purposeRaw ? preg_replace('/^:26T:/', '', trim($purposeRaw)) : null;

                    $sttlmIban = $val("//*[local-name()='SttlmAcct']//*[local-name()='IBAN']")
                              ?? $val("//*[local-name()='SttlmAcct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']");
                    $tag53B = $sttlmIban ? '/C/' . $sttlmIban : null;

                    $dateMT = date('ymd', strtotime($valDate));
                    $amtMT  = number_format($amount, 2, ',', '');

                    $details = [
                        '20'  => $ref,
                        '23B' => $opCode,
                        '32A' => $dateMT . $currency . $amtMT,
                        '50K' => trim($tag50),
                        '50'  => $senderName ?? '',
                        '52A' => $senderBic   ?? '',
                        '57A' => $receiverBic ?? '',
                        '59'  => trim($tag59),
                        '70'  => $description ?? '/',
                        '71A' => $chargeBear,
                    ];
                    if ($tag26T) $details['26T'] = $tag26T;
                    if ($tag53B) $details['53B'] = $tag53B;

                    $data = array_merge($data, [
                        'reference'    => $ref,
                        'amount'       => $amount,
                        'currency'     => $currency,
                        'value_date'   => $valDate,
                        'sender_bic'   => $senderBic,
                        'receiver_bic' => $receiverBic,
                        'sender_name'  => $senderName,
                        'receiver_name'=> $receiverName,
                        'description'  => $description,
                        'uetr'         => $uetr,
                        'details'      => $details,
                    ]);
                    break;

                // ══════════════════════════════════════════
                // PACS.009 → MT202
                // ══════════════════════════════════════════
                case 'PACS.009':
                    $ref      = $val("//*[local-name()='MsgId']")
                             ?? $val("//*[local-name()='InstrId']")
                             ?? $data['reference'];
                    $amount   = (float) ($val("//*[local-name()='IntrBkSttlmAmt']") ?? 0);
                    $currency = $attr("//*[local-name()='IntrBkSttlmAmt']", 'Ccy') ?? 'USD';
                    $valDate  = $val("//*[local-name()='IntrBkSttlmDt']") ?? now()->format('Y-m-d');
                    $uetr     = $val("//*[local-name()='UETR']");

                    $endToEnd = $val("//*[local-name()='EndToEndId']")
                             ?? $val("//*[local-name()='InstrId']")
                             ?? 'NONREF';

                    $senderBic    = $val("//*[local-name()='InstgAgt']//*[local-name()='BICFI']")
                                 ?? $val("//*[local-name()='DbtrAgt']//*[local-name()='BICFI']");
                    $receiverBic  = $val("//*[local-name()='InstdAgt']//*[local-name()='BICFI']")
                                 ?? $val("//*[local-name()='CdtrAgt']//*[local-name()='BICFI']");
                    $debtorBic    = $val("//*[local-name()='Dbtr']//*[local-name()='BICFI']");
                    $creditorBic  = $val("//*[local-name()='Cdtr']//*[local-name()='BICFI']");
                    $senderName   = $val("//*[local-name()='Dbtr']/*[local-name()='Nm']") ?? $debtorBic;
                    $receiverName = $val("//*[local-name()='Cdtr']/*[local-name()='Nm']") ?? $creditorBic;

                    $creditorAccount = $val("//*[local-name()='CdtrAcct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']")
                                    ?? $val("//*[local-name()='CdtrAcct']//*[local-name()='IBAN']");
                    $intermediary    = $val("//*[local-name()='IntrmyAgt1']//*[local-name()='BICFI']");

                    $dateMT = date('ymd', strtotime($valDate));
                    $amtMT  = number_format($amount, 2, ',', '');

                    $data = array_merge($data, [
                        'reference'        => $ref,
                        'amount'           => $amount,
                        'currency'         => $currency,
                        'value_date'       => $valDate,
                        'sender_bic'       => $senderBic,
                        'receiver_bic'     => $receiverBic,
                        'sender_name'      => $senderName,
                        'receiver_name'    => $receiverName,
                        'uetr'             => $uetr,
                        'details'          => [
                            '20'              => $ref,
                            '21'              => $endToEnd,
                            '32A'             => $dateMT . $currency . $amtMT,
                            '52A'             => $senderBic   ?? '',
                            '56A'             => $intermediary ?? '',
                            '57A'             => $receiverBic  ?? '',
                            '58A'             => $receiverBic  ?? '',
                            'creditor_account'=> $creditorAccount ?? '',
                        ],
                    ]);
                    break;

                // ══════════════════════════════════════════
                // CAMT.053 → MT940
                // ══════════════════════════════════════════
                case 'CAMT.053':
                    $ref     = $val("//*[local-name()='Stmt']/*[local-name()='Id']")
                            ?? $val("//*[local-name()='MsgId']")
                            ?? $data['reference'];
                    $account = $val("//*[local-name()='Acct']//*[local-name()='IBAN']")
                            ?? $val("//*[local-name()='Acct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']")
                            ?? '';
                    $valDate = substr($val("//*[local-name()='Stmt']/*[local-name()='CreDtTm']") ?? now()->toIso8601String(), 0, 10);

                    $closingNodes = $xml->xpath("//*[local-name()='Bal'][.//*[local-name()='Cd']='CLBD']/*[local-name()='Amt']");
                    $closingAmt   = !empty($closingNodes) ? (float) $closingNodes[0] : 0.0;
                    $closingCcy   = !empty($closingNodes) ? (string) ($closingNodes[0]['Ccy'] ?? 'EUR') : 'EUR';

                    $entries = $xml->xpath("//*[local-name()='Ntry']");
                    $lines   = [];
                    foreach (array_slice($entries ?? [], 0, 5) as $entry) {
                        $eAmt = (string) ($entry->xpath(".//*[local-name()='Amt']")[0] ?? '');
                        $eCcy = (string) ($entry->xpath(".//*[local-name()='Amt']/@Ccy")[0] ?? 'EUR');
                        $eDt  = (string) ($entry->xpath(".//*[local-name()='ValDt']/*[local-name()='Dt']")[0] ?? $valDate);
                        if ($eAmt) $lines[] = "{$eDt} {$eCcy} {$eAmt}";
                    }

                    $dateMT      = date('ymd', strtotime($valDate));
                    $closingAmtMT= number_format($closingAmt, 2, ',', '');

                    $data = array_merge($data, [
                        'reference'  => $ref,
                        'amount'     => $closingAmt,
                        'currency'   => $closingCcy,
                        'value_date' => $valDate,
                        'details'    => [
                            '20'  => $ref,
                            '25'  => $account,
                            '62F' => "C{$dateMT}{$closingCcy}{$closingAmtMT}",
                            '61'  => implode("\n", $lines),
                        ],
                    ]);
                    break;

                // ══════════════════════════════════════════
                // CAMT.054 → MT942
                // ══════════════════════════════════════════
                case 'CAMT.054':
                    $ref     = $val("//*[local-name()='Stmt']/*[local-name()='Id']") ?? $data['reference'];
                    $account = $val("//*[local-name()='Acct']//*[local-name()='IBAN']") ?? '';
                    $valDate = substr($val("//*[local-name()='Stmt']/*[local-name()='CreDtTm']") ?? now()->toIso8601String(), 0, 10);

                    $amtNodes = $xml->xpath("//*[local-name()='Bal']/*[local-name()='Amt']");
                    $amt = !empty($amtNodes) ? (float) $amtNodes[0] : 0.0;
                    $ccy = !empty($amtNodes) ? (string) ($amtNodes[0]['Ccy'] ?? 'EUR') : 'EUR';

                    $data = array_merge($data, [
                        'reference'  => $ref,
                        'amount'     => $amt,
                        'currency'   => $ccy,
                        'value_date' => $valDate,
                        'details'    => ['20' => $ref, '25' => $account],
                    ]);
                    break;

                // ══════════════════════════════════════════
                // MT brut (.txt)
                // ══════════════════════════════════════════
                default:
                    if (str_starts_with($typeShort, 'MT')) {
                        if (preg_match('/:20:(.*?)[\r\n]/', $rawXml, $m)) {
                            $data['reference']     = trim($m[1]);
                            $data['details']['20'] = $data['reference'];
                        }
                        if (preg_match('/:32A:(\d{6})([A-Z]{3})([\d,]+)/', $rawXml, $m)) {
                            $data['value_date']     = '20' . substr($m[1],0,2) . '-' . substr($m[1],2,2) . '-' . substr($m[1],4,2);
                            $data['currency']       = $m[2];
                            $data['amount']         = (float) str_replace(',', '.', $m[3]);
                            $data['details']['32A'] = $m[1] . $m[2] . $m[3];
                        }
                        if (preg_match('/:50.?:(.*?)(?=:\d)/s', $rawXml, $m)) {
                            $data['sender_name']   = trim(preg_replace('/\s+/', ' ', $m[1]));
                            $data['details']['50'] = $data['sender_name'];
                        }
                        if (preg_match('/:59.?:(.*?)(?=:\d)/s', $rawXml, $m)) {
                            $data['receiver_name']  = trim(preg_replace('/\s+/', ' ', $m[1]));
                            $data['details']['59']  = $data['receiver_name'];
                        }
                    }
                    break;
            }

        } catch (Throwable $e) {
            Log::warning('Parsing partiel échoué', ['type' => $typeShort, 'error' => $e->getMessage()]);
        }

        return $data;
    }

    // =========================================================
    // CONVERSION MX → MT
    // =========================================================

    private function convertToMt(array $data, string $typeShort): ?string
    {
        try {
            return app(MxToMtService::class)->convert($typeShort, $data);
        } catch (Throwable $e) {
            Log::warning('Conversion MT échouée', ['type' => $typeShort, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // =========================================================
    // APPLY COMMON MAPPING
    // =========================================================

    private function applyCommonMapping(MessageSwift $message, array $parsedData, string $mtType): void
    {
        $commonMapping = Config::get("swift_fields.{$mtType}.common_mapping", []);
        if (empty($commonMapping)) return;

        $updateData = [];
        foreach ($commonMapping as $field => $tag) {
            $value = $parsedData['details'][$tag] ?? null;
            if (!$value) continue;

            if ($tag === '32A') {
                [$date, $currency, $amount] = SwiftParser::parse32A($value);
                if ($date)     $updateData['VALUE_DATE'] = $date;
                if ($currency) $updateData['CURRENCY']   = $currency;
                if ($amount)   $updateData['AMOUNT']     = $amount;
            } else {
                $updateData[$field] = $value;
            }
        }

        if (!empty($updateData)) {
            $message->update($updateData);
        }
    }
}