<?php

namespace App\Services;

use App\Models\MessageSwift;

/**
 * Génère le MT_CONTENT (enveloppe SWIFT complète) pour les messages émis (OUT).
 * Blocs : {1: Basic Header} {2: Application Header} {3: User Header} {4: Body} {5: Trailer}
 */
class SwiftMtBuilder
{
    /**
     * Construit le message MT complet avec enveloppe pour un message sortant.
     */
    public function build(MessageSwift $message, array $details): string
    {
        $type      = $message->TYPE_MESSAGE;
        $typeNum   = str_replace('MT', '', $type);  // ex: "900", "103"
        $senderBic = $message->SENDER_BIC ?: config('swift.sender_bic', 'ATLDTNTTAXXX');
        $date      = now()->format('ymdHi');         // YYMMDDHHmm

        // Block 1 — Basic Header
        $block1 = "{1:F01{$senderBic}" . str_pad('', max(0, 12 - strlen($senderBic)), 'X') . "0000000000}";

        // Block 2 — Application Header (Output)
        $block2 = "{2:O{$typeNum}{$date}" . str_pad($senderBic, 12, 'X') . "0000000000{$date}N}";

        // Block 3 — User Header
        $block3 = "{3:{113:0020}}";

        // Block 4 — Body (tags métier)
        $block4 = $this->buildBlock4($details);

        // Block 5 — Trailer (checksum)
        $chk = strtoupper(substr(md5($block4), 0, 12));
        $block5 = "{5:{CHK:{$chk}}}";

        return "{$block1}\n{$block2}\n{$block3}\n{$block4}\n{$block5}";
    }

    /**
     * Construit le Block 4 (corps du message) à partir des tags saisis.
     */
    private function buildBlock4(array $details): string
    {
        $body = "{4:\n";
        foreach ($details as $tag => $value) {
            if (empty($value)) {
                continue;
            }
            // Les tags multi-lignes (textarea) : chaque ligne préfixée par //
            $lines = explode("\n", trim($value));
            $body .= ":{$tag}:{$lines[0]}\n";
            for ($i = 1; $i < count($lines); $i++) {
                $body .= "//" . trim($lines[$i]) . "\n";
            }
        }
        $body .= "-}";

        return $body;
    }
}
