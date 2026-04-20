<?php

namespace App\Services;

use SimpleXMLElement;

class XmlParserService
{
    /**
     * Détection du type de message SWIFT à partir du contenu XML
     */
    public function detectType(string $xmlContent): string
    {
        $lower = strtolower($xmlContent);

        if (str_contains($lower, 'pacs.008')) {
            return 'pacs.008';
        }
        if (str_contains($lower, 'pacs.009')) {
            return 'pacs.009';
        }
        if (str_contains($lower, 'camt.053')) {
            return 'camt.053';
        }
        if (str_contains($lower, 'camt.054')) {
            return 'camt.054';
        }

        // Fallback sur tag root
        try {
            $xml = new SimpleXMLElement($xmlContent);
            $root = $xml->getName();
            if ($root === 'SWIFTMessage') {
                return (string) ($xml->MessageType ?? 'UNKNOWN');
            }
        } catch (\Exception $e) {
            // Ignorer
        }

        return 'UNKNOWN';
    }

    /**
     * Parse pacs.008.001.08 (reçu)
     */
    public function parsePacs008(string $xmlContent): array
    {
        $xml = new SimpleXMLElement($xmlContent);

        // find Document node regardless of prefix/envelope
        $docs = $xml->xpath("//*[local-name()='Document']");
        $doc = $docs[0] ?? $xml;

        // Look for FIToFICstmrCdtTrf inside Document (or anywhere)
        $nodes = $doc->xpath(".//*[local-name()='FIToFICstmrCdtTrf']");
        $trf = $nodes[0] ?? null;
        if (! $trf) {
            // also try without Document wrapper
            $nodes = $xml->xpath(".//*[local-name()='FIToFICstmrCdtTrf']");
            $trf = $nodes[0] ?? null;
        }

        $grpHdr = $trf ? ($trf->GrpHdr ?? null) : null;
        $tx = $trf ? ($trf->CdtTrfTxInf ?? null) : null;

        // helper to get xpath single value
        $val = fn ($node, $path) => ($node && ($res = $node->xpath($path)) && isset($res[0])) ? (string) $res[0] : null;

        return [
            'type_message' => 'PACS.008',
            'categorie' => 'PACS',
            'direction' => 'IN',
            'message_id' => $val($grpHdr, ".//*[local-name()='MsgId']"),
            'transaction_ref' => $val($tx, ".//*[local-name()='InstrId']"),
            'end_to_end_id' => $val($tx, ".//*[local-name()='EndToEndId']"),
            'uetr' => $val($tx, ".//*[local-name()='UETR']"),
            'amount' => (float) ($val($tx, ".//*[local-name()='IntrBkSttlmAmt']") ?? 0),
            'currency' => ($tx && isset($tx->IntrBkSttlmAmt['Ccy'])) ? (string) $tx->IntrBkSttlmAmt['Ccy'] : ($val($tx, ".//*[local-name()='IntrBkSttlmAmt']/@Ccy") ?? null),
            'value_date' => $val($tx, ".//*[local-name()='IntrBkSttlmDt']"),
            'debtor_name' => $val($tx, ".//*[local-name()='Dbtr']/*[local-name()='Nm']"),
            'debtor_account' => $val($tx, ".//*[local-name()='DbtrAcct']//*[local-name()='IBAN']|.//*[local-name()='DbtrAcct']//*[local-name()='Id']/*[local-name()='IBAN']|.//*[local-name()='DbtrAcct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']"),
            'debtor_agent_bic' => $val($tx, ".//*[local-name()='DbtrAgt']//*[local-name()='BICFI']"),
            'creditor_name' => $val($tx, ".//*[local-name()='Cdtr']/*[local-name()='Nm']"),
            'creditor_account' => $val($tx, ".//*[local-name()='CdtrAcct']//*[local-name()='IBAN']|.//*[local-name()='CdtrAcct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']"),
            'creditor_agent_bic' => $val($tx, ".//*[local-name()='CdtrAgt']//*[local-name()='BICFI']"),
            'remittance_info' => $val($tx, ".//*[local-name()='RmtInf']/*[local-name()='Ustrd']"),
        ];
    }

    /**
     * Parse pacs.009.001.08 (émis ou couverture)
     */
    public function parsePacs009(string $xmlContent): array
    {
        $xml = new SimpleXMLElement($xmlContent);

        $docs = $xml->xpath("//*[local-name()='Document']");
        $doc = $docs[0] ?? $xml;

        $nodes = $doc->xpath(".//*[local-name()='FICdtTrf']");
        $trf = $nodes[0] ?? null;
        if (! $trf) {
            $nodes = $xml->xpath(".//*[local-name()='FICdtTrf']");
            $trf = $nodes[0] ?? null;
        }

        $grpHdr = $trf ? ($trf->GrpHdr ?? null) : null;
        $tx = $trf ? ($trf->CdtTrfTxInf ?? null) : null;

        $val = fn ($node, $path) => ($node && ($res = $node->xpath($path)) && isset($res[0])) ? (string) $res[0] : null;

        return [
            'type_message' => 'PACS.009',
            'categorie' => 'PACS',
            'direction' => 'IN',
            'message_id' => $val($grpHdr, "//*[local-name()='MsgId']"),
            'transaction_ref' => $val($tx, ".//*[local-name()='InstrId']"),
            'end_to_end_id' => $val($tx, ".//*[local-name()='EndToEndId']"),
            'uetr' => $val($tx, ".//*[local-name()='UETR']"),
            'amount' => (float) ($val($tx, ".//*[local-name()='IntrBkSttlmAmt']") ?? 0),
            'currency' => ($tx && isset($tx->IntrBkSttlmAmt['Ccy'])) ? (string) $tx->IntrBkSttlmAmt['Ccy'] : ($val($tx, ".//*[local-name()='IntrBkSttlmAmt']/@Ccy") ?? null),
            'value_date' => $val($tx, ".//*[local-name()='IntrBkSttlmDt']"),
            'debtor_name' => $val($tx, ".//*[local-name()='Dbtr']/*[local-name()='Nm']"),
            'debtor_account' => $val($tx, ".//*[local-name()='DbtrAcct']//*[local-name()='IBAN']|.//*[local-name()='DbtrAcct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']"),
            'debtor_agent_bic' => $val($tx, ".//*[local-name()='DbtrAgt']//*[local-name()='BICFI']"),
            'creditor_name' => $val($tx, ".//*[local-name()='Cdtr']/*[local-name()='Nm']"),
            'creditor_account' => $val($tx, ".//*[local-name()='CdtrAcct']//*[local-name()='IBAN']|.//*[local-name()='CdtrAcct']//*[local-name()='Id']/*[local-name()='Othr']/*[local-name()='Id']"),
            'creditor_agent_bic' => $val($tx, ".//*[local-name()='CdtrAgt']//*[local-name()='BICFI']"),
            'intermediary_bic' => $val($tx, ".//*[local-name()='IntrmyAgt1']//*[local-name()='BICFI']"),
        ];
    }

    // Ajoute ici parseCamt053, parseCamt054, etc. si besoin
}
