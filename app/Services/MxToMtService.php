<?php

namespace App\Services;

/**
 * MxToMtService
 * Convertit les données parsées (MX/XML) en format texte MT SWIFT.
 *
 * IMPORTANT : Les clés du tableau $data doivent correspondre
 * à celles produites par ProcessSwiftFileJob::parseData()
 *
 * Clés standardisées :
 *   reference, amount, currency, value_date,
 *   sender_bic, receiver_bic, sender_name, receiver_name,
 *   description, details (tableau de tags MT)
 */
class MxToMtService
{
    public function convert(string $mxType, array $data): ?string
    {
        return match ($mxType) {
            'PACS.008' => $this->pacs008ToMt103($data),
            'PACS.009' => $this->pacs009ToMt202($data),
            'CAMT.053' => $this->camt053ToMt940($data),
            'CAMT.054' => $this->camt054ToMt942($data),
            'PAIN.001' => $this->pain001ToMt101($data),
            default => null,
        };
    }

    // =========================================================
    // PACS.008 → MT103
    // =========================================================
    public function pacs008ToMt103(array $data): string
    {
        // ── Récupération des champs ──
        $ref = $data['reference'] ?? ($data['details']['20'] ?? ('REF'.substr(uniqid(), -10)));
        $amount = (float) ($data['amount'] ?? 0);
        $currency = strtoupper($data['currency'] ?? 'EUR');
        $valueDate = $data['value_date'] ?? date('Y-m-d');
        $senderBic = $data['sender_bic'] ?? ($data['details']['52A'] ?? '');
        $receiverBic = $data['receiver_bic'] ?? ($data['details']['57A'] ?? '');
        $opCode = $data['details']['23B'] ?? 'CRED';   // toujours CRED pour MT103
        $chargeBear = $data['details']['71A'] ?? 'SHA';
        $uetr = $data['uetr'] ?? null;

        // Tag 50K : compte + nom + adresse (format multi-lignes)
        $tag50K = $data['details']['50K'] ?? ($data['details']['50'] ?? ($data['sender_name'] ?? ''));

        // Tag 59 : nom + adresse bénéficiaire
        $tag59 = $data['details']['59'] ?? ($data['receiver_name'] ?? '');

        // Tag 70 : remittance info
        $description = $data['details']['70'] ?? ($data['description'] ?? '/');

        // Tag 26T : purpose code (optionnel)
        $tag26T = $data['details']['26T'] ?? null;

        // Tag 53B : settlement account (optionnel)
        $tag53B = $data['details']['53B'] ?? null;

        // Format
        $date = $this->formatDate($valueDate);
        $montant = $this->formatAmount($amount);

        $mt = "{1:F01{$senderBic}AXXX0000000000}\n";
        $mt .= "{2:I103{$date}{$receiverBic}XXXX0000000000{$date}N}\n";
        if ($uetr) {
            $mt .= "{3:{111:001}{121:{$uetr}}}\n";
        }
        $mt .= "{4:\n";
        $mt .= ":20:{$ref}\n";
        $mt .= ":23B:{$opCode}\n";
        if ($tag26T) {
            $mt .= ":26T:{$tag26T}\n";
        }
        $mt .= ":32A:{$date}{$currency}{$montant}\n";
        if ($tag50K) {
            $mt .= ":50K:{$tag50K}\n";
        }
        if ($senderBic) {
            $mt .= ":52A:{$senderBic}\n";
        }
        if ($tag53B) {
            $mt .= ":53B:{$tag53B}\n";
        }
        if ($receiverBic) {
            $mt .= ":57A:{$receiverBic}\n";
        }
        if ($tag59) {
            $mt .= ":59:{$tag59}\n";
        }
        $mt .= ":70:{$description}\n";
        $mt .= ":71A:{$chargeBear}\n";
        $mt .= "-}\n";
        $mt .= '{5:{CHK:0000000000}}';

        return $mt;
    }

    // =========================================================
    // PACS.009 → MT202
    // =========================================================
    public function pacs009ToMt202(array $data): string
    {
        // ── Récupération des champs (clés de ProcessSwiftFileJob) ──
        $ref = $data['reference'] ?? ($data['details']['20'] ?? ('REF'.substr(uniqid(), -10)));
        $amount = (float) ($data['amount'] ?? 0);
        $currency = strtoupper($data['currency'] ?? 'USD');
        $valueDate = $data['value_date'] ?? date('Y-m-d');
        $senderBic = $data['sender_bic'] ?? ($data['details']['52A'] ?? '');
        $receiverBic = $data['receiver_bic'] ?? ($data['details']['58A'] ?? '');

        // Tag 21 = référence liée (EndToEndId ou InstrId)
        $relatedRef = $data['details']['21'] ?? $ref;
        $uetr = $data['uetr'] ?? null;

        // Intermédiaire (tag 56A) si présent
        $intermediary = $data['details']['56A'] ?? null;

        // Compte bénéficiaire (tag 58A)
        $creditorAccount = $data['details']['creditor_account'] ?? '';
        $creditorBic = $receiverBic;

        $date = $this->formatDate($valueDate);
        $montant = $this->formatAmount($amount);

        $mt = "{1:F01{$receiverBic}AXXX0000000000}\n";
        $mt .= "{2:O202{$date}{$senderBic}XXXX0000000000{$date}N}\n";
        if ($uetr) {
            $mt .= "{3:{121:{$uetr}}}\n";
        }
        $mt .= "{4:\n";
        $mt .= ":20:{$ref}\n";
        $mt .= ":21:{$relatedRef}\n";
        $mt .= ":32A:{$date}{$currency}{$montant}\n";
        $mt .= ":52A:{$senderBic}\n";
        if ($intermediary) {
            $mt .= ":56A:{$intermediary}\n";
        }
        $mt .= ":57A:{$receiverBic}\n";
        if ($creditorAccount) {
            $mt .= ":58A:/{$creditorAccount}\n";
            $mt .= "     {$creditorBic}\n";
        } else {
            $mt .= ":58A:{$creditorBic}\n";
        }
        $mt .= ":72:/INS/{$senderBic}\n";
        $mt .= "-}\n";
        $mt .= '{5:{CHK:0000000000}}';

        return $mt;
    }

    // =========================================================
    // CAMT.053 → MT940
    // =========================================================
    public function camt053ToMt940(array $data): string
    {
        $ref = $data['reference'] ?? ($data['details']['20'] ?? 'REF');
        $account = $data['details']['25'] ?? '';
        $balanceOpen = $data['details']['60F'] ?? 'C'.date('ymd').($data['currency'] ?? 'EUR').'0,';
        $balanceClose = $data['details']['62F'] ?? $balanceOpen;
        $entries = $data['details']['61'] ?? '';
        $date = $this->formatDate($data['value_date'] ?? date('Y-m-d'));

        $mt = "{1:F01XXXXAXXX0000000000}\n";
        $mt .= "{2:O940{$date}XXXXAXXX0000000000{$date}N}\n";
        $mt .= "{4:\n";
        $mt .= ":20:{$ref}\n";
        $mt .= ":25:{$account}\n";
        $mt .= ":28C:00001/001\n";
        $mt .= ":60F:{$balanceOpen}\n";

        // Lignes de mouvement
        if (! empty($entries)) {
            foreach (explode("\n", $entries) as $line) {
                $line = trim($line);
                if ($line) {
                    // Format: YYYY-MM-DD CCY AMOUNT → YYMMDD C/D AMOUNT
                    if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+([A-Z]{3})\s+([\d.]+)/', $line, $m)) {
                        $eDate = $this->formatDate($m[1]);
                        $eAmount = $this->formatAmount((float) $m[3]);
                        $mt .= ":61:{$eDate}C{$eAmount}NTRFNONREF\n";
                        $mt .= ":86:Transaction\n";
                    }
                }
            }
        }

        $mt .= ":62F:{$balanceClose}\n";
        $mt .= "-}\n";
        $mt .= '{5:{CHK:0000000000}}';

        return $mt;
    }

    // =========================================================
    // CAMT.054 → MT942
    // =========================================================
    public function camt054ToMt942(array $data): string
    {
        $ref = $data['reference'] ?? 'REF';
        $account = $data['details']['25'] ?? '';
        $date = $this->formatDate($data['value_date'] ?? date('Y-m-d'));

        $mt = "{1:F01XXXXAXXX0000000000}\n";
        $mt .= "{2:O942{$date}XXXXAXXX0000000000{$date}N}\n";
        $mt .= "{4:\n";
        $mt .= ":20:{$ref}\n";
        $mt .= ":25:{$account}\n";
        $mt .= ":28C:00001/001\n";
        $mt .= ":34F:{$date}".($data['currency'] ?? 'EUR').$this->formatAmount((float) ($data['amount'] ?? 0))."\n";
        $mt .= "-}\n";
        $mt .= '{5:{CHK:0000000000}}';

        return $mt;
    }

    // =========================================================
    // PAIN.001 → MT101
    // =========================================================
    public function pain001ToMt101(array $data): string
    {
        $ref = $data['reference'] ?? 'REF';
        $date = $this->formatDate($data['value_date'] ?? date('Y-m-d'));

        $mt = "{4:\n";
        $mt .= ":20:{$ref}\n";
        $mt .= ":28D:1/1\n";
        $mt .= ":30:{$date}\n";
        $mt .= '-}';

        return $mt;
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Convertit une date ISO (YYYY-MM-DD ou Y-m-d\TH:i:s)
     * en format MT YYMMDD.
     */
    private function formatDate(string $date): string
    {
        // Nettoyer les formats datetime
        $date = substr($date, 0, 10);
        try {
            $ts = strtotime($date);

            return $ts ? date('ymd', $ts) : date('ymd');
        } catch (\Throwable $e) {
            return date('ymd');
        }
    }

    /**
     * Formate un montant en style MT SWIFT :
     * - Séparateur décimal = virgule
     * - Pas de séparateur de milliers
     * - Supprime les décimales si ,00
     * Ex: 500000.00 → "500000," | 1250.50 → "1250,50"
     */
    private function formatAmount(float $amount): string
    {
        // Arrondi à 2 décimales
        $formatted = number_format($amount, 2, ',', '');
        // Supprimer les zéros décimaux inutiles : 500000,00 → 500000,
        // mais garder si non nul : 1250,50 reste 1250,50
        if (str_ends_with($formatted, ',00')) {
            $formatted = rtrim($formatted, '0'); // → 500000,
        }

        return $formatted;
    }
}
