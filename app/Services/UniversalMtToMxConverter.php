<?php

namespace App\Services;

use App\Models\MessageSwift;
use Illuminate\Support\Str;

/**
 * UniversalMtToMxConverter
 *
 * Convertit un message SWIFT MT (émis) en XML ISO 20022 (MX).
 * Flux ÉMIS (OUT) :
 *   1. L'utilisateur remplit le formulaire MT (tags 20, 23B, 32A, 50K, 52A, 57A, 59, 70, 71A…)
 *   2. Ce converter lit tous les tags depuis message->details
 *   3. Génère un XML MX complet et fidèle
 */
class UniversalMtToMxConverter
{
    public function convert(MessageSwift $message): ?string
    {
        return match($message->TYPE_MESSAGE) {
            'MT103' => $this->convertMT103($message),
            'MT202' => $this->convertMT202($message),
            'MT940' => $this->convertMT940($message),
            'MT101' => $this->convertMT101($message),
            'MT210' => $this->convertMT210($message),
            'MT300' => $this->convertMT300($message),
            'MT320' => $this->convertMT320($message),
            'MT700' => $this->convertMT700($message),
            'MT760' => $this->convertMT760($message),
            'MT900' => $this->convertMT900($message),
            'MT910' => $this->convertMT910($message),
            default => null,
        };
    }

    // =========================================================
    // MT103 → pacs.008.001.08
    // =========================================================
    private function convertMT103(MessageSwift $message): string
    {
        // Récupérer tous les tags depuis les détails
        $d = $message->details->pluck('tag_value', 'tag_name')->toArray();

        // ── Référence (tag 20) ──
        $ref = $d['20'] ?? $message->REFERENCE ?? ('REF' . Str::random(10));

        // ── Montant / Devise / Date (tag 32A) ──
        [$valueDate, $currency, $amount] = $this->parse32A($d['32A'] ?? '');
        // Fallback sur les champs dénormalisés
        if (!$valueDate) $valueDate = optional($message->VALUE_DATE)->format('Y-m-d') ?? now()->format('Y-m-d');
        if (!$currency)  $currency  = $message->CURRENCY ?? 'EUR';
        if (!$amount)    $amount    = (float) ($message->AMOUNT ?? 0);

        // ── Devise instruction (tag 33B) ──
        $instdAmt = null;
        $instdCcy = null;
        if (!empty($d['33B'])) {
            preg_match('/^([A-Z]{3})([\d,]+)$/', $d['33B'], $m33);
            if (!empty($m33)) {
                $instdCcy = $m33[1];
                $instdAmt = (float) str_replace(',', '.', $m33[2]);
            }
        }

        // ── Donneur d'ordre (tag 50K ou 50A) ──
        $tag50 = $d['50K'] ?? $d['50'] ?? $message->SENDER_NAME ?? '';
        [$debtorAccount, $debtorName, $debtorAddr] = $this->parseTag50($tag50);

        // ── Institution ordonnatrice (tag 52A) ──
        $instgBic = $this->cleanBic($d['52A'] ?? $message->SENDER_BIC ?? '');

        // ── Compte de règlement (tag 53B) ──
        $sttlmAccount = null;
        if (!empty($d['53B'])) {
            $sttlmAccount = preg_replace('/^\/[CD]\//', '', $d['53B']);
        }

        // ── Institution bénéficiaire (tag 57A) ──
        $cdtrAgtBic = $this->cleanBic($d['57A'] ?? $message->RECEIVER_BIC ?? '');

        // ── Bénéficiaire (tag 59 ou 59A) ──
        $tag59 = $d['59'] ?? $d['59A'] ?? $message->RECEIVER_NAME ?? '';
        [$creditorAccount, $creditorName, $creditorAddr] = $this->parseTag59($tag59);

        // ── Description (tag 70) ──
        $remittance = $d['70'] ?? $message->DESCRIPTION ?? '';

        // ── Frais (tag 71A) ──
        $chargeBear = $this->normalizeChrg71A($d['71A'] ?? 'SHA');

        // ── Purpose (tag 26T) ──
        $purposeCode = $d['26T'] ?? null;

        // ── Code opération (tag 23B) → SvcLvl ──
        // CRED → G001 (cbprplus), SPAY → SEPA, etc.
        $svcLvlCd = $this->mapSvcLvl($d['23B'] ?? 'CRED');

        // ── UETR ──
        $uetr = (string) Str::uuid();

        // ── Construction XML ──
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $doc = $dom->createElementNS(
            'urn:iso:std:iso:20022:tech:xsd:pacs.008.001.08',
            'Document'
        );
        $dom->appendChild($doc);

        $trf = $dom->createElement('FIToFICstmrCdtTrf');
        $doc->appendChild($trf);

        // GrpHdr
        $grpHdr = $dom->createElement('GrpHdr');
        $trf->appendChild($grpHdr);
        $grpHdr->appendChild($dom->createElement('MsgId', $ref));
        $grpHdr->appendChild($dom->createElement('CreDtTm', now()->format('Y-m-d\TH:i:sP')));
        $grpHdr->appendChild($dom->createElement('NbOfTxs', '1'));

        $sttlmInf = $dom->createElement('SttlmInf');
        $grpHdr->appendChild($sttlmInf);
        $sttlmInf->appendChild($dom->createElement('SttlmMtd', 'INGA'));

        // Settlement account (53B)
        if ($sttlmAccount) {
            $sttlmAcct = $dom->createElement('SttlmAcct');
            $sttlmInf->appendChild($sttlmAcct);
            $sttlmId = $dom->createElement('Id');
            $sttlmAcct->appendChild($sttlmId);
            if ($this->isIban($sttlmAccount)) {
                $sttlmId->appendChild($dom->createElement('IBAN', $sttlmAccount));
            } else {
                $othr = $dom->createElement('Othr');
                $sttlmId->appendChild($othr);
                $othr->appendChild($dom->createElement('Id', $sttlmAccount));
            }
        }

        // InstgAgt (52A)
        if ($instgBic) {
            $instgAgt = $dom->createElement('InstgAgt');
            $grpHdr->appendChild($instgAgt);
            $finInstn = $dom->createElement('FinInstnId');
            $instgAgt->appendChild($finInstn);
            $finInstn->appendChild($dom->createElement('BICFI', $instgBic));
        }

        // InstdAgt (57A)
        if ($cdtrAgtBic) {
            $instdAgt = $dom->createElement('InstdAgt');
            $grpHdr->appendChild($instdAgt);
            $finInstn = $dom->createElement('FinInstnId');
            $instdAgt->appendChild($finInstn);
            $finInstn->appendChild($dom->createElement('BICFI', $cdtrAgtBic));
        }

        // CdtTrfTxInf
        $txInf = $dom->createElement('CdtTrfTxInf');
        $trf->appendChild($txInf);

        // PmtId
        $pmtId = $dom->createElement('PmtId');
        $txInf->appendChild($pmtId);
        $pmtId->appendChild($dom->createElement('InstrId', $ref));
        $pmtId->appendChild($dom->createElement('EndToEndId', 'NOTPROVIDED'));
        $pmtId->appendChild($dom->createElement('UETR', $uetr));

        // PmtTpInf / SvcLvl
        if ($svcLvlCd) {
            $pmtTpInf = $dom->createElement('PmtTpInf');
            $txInf->appendChild($pmtTpInf);
            $svcLvl = $dom->createElement('SvcLvl');
            $pmtTpInf->appendChild($svcLvl);
            $svcLvl->appendChild($dom->createElement('Cd', $svcLvlCd));
        }

        // IntrBkSttlmAmt
        $amtEl = $dom->createElement('IntrBkSttlmAmt', number_format($amount, 2, '.', ''));
        $amtEl->setAttribute('Ccy', $currency);
        $txInf->appendChild($amtEl);

        // IntrBkSttlmDt
        $txInf->appendChild($dom->createElement('IntrBkSttlmDt', $valueDate));

        // InstdAmt (33B)
        if ($instdAmt && $instdCcy) {
            $instdAmtEl = $dom->createElement('InstdAmt', number_format($instdAmt, 2, '.', ''));
            $instdAmtEl->setAttribute('Ccy', $instdCcy);
            $txInf->appendChild($instdAmtEl);
        }

        // ChrgBr
        $txInf->appendChild($dom->createElement('ChrgBr', $chargeBear));

        // InstgAgt (52A) dans TxInf
        if ($instgBic) {
            $instgAgtTx = $dom->createElement('InstgAgt');
            $txInf->appendChild($instgAgtTx);
            $fi = $dom->createElement('FinInstnId');
            $instgAgtTx->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $instgBic));
        }

        // InstdAgt (57A) dans TxInf
        if ($cdtrAgtBic) {
            $instdAgtTx = $dom->createElement('InstdAgt');
            $txInf->appendChild($instdAgtTx);
            $fi = $dom->createElement('FinInstnId');
            $instdAgtTx->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $cdtrAgtBic));
        }

        // Dbtr (50K)
        $dbtr = $dom->createElement('Dbtr');
        $txInf->appendChild($dbtr);
        if ($debtorName) $dbtr->appendChild($dom->createElement('Nm', $debtorName));
        if ($debtorAddr) {
            $pstlAdr = $dom->createElement('PstlAdr');
            $dbtr->appendChild($pstlAdr);
            foreach (explode("\n", $debtorAddr) as $line) {
                if (trim($line)) {
                    $pstlAdr->appendChild($dom->createElement('AdrLine', trim($line)));
                }
            }
        }

        // DbtrAcct
        if ($debtorAccount) {
            $dbtrAcct = $dom->createElement('DbtrAcct');
            $txInf->appendChild($dbtrAcct);
            $dbtrId = $dom->createElement('Id');
            $dbtrAcct->appendChild($dbtrId);
            if ($this->isIban($debtorAccount)) {
                $dbtrId->appendChild($dom->createElement('IBAN', $debtorAccount));
            } else {
                $othr = $dom->createElement('Othr');
                $dbtrId->appendChild($othr);
                $othr->appendChild($dom->createElement('Id', $debtorAccount));
            }
        }

        // DbtrAgt (52A)
        if ($instgBic) {
            $dbtrAgt = $dom->createElement('DbtrAgt');
            $txInf->appendChild($dbtrAgt);
            $fi = $dom->createElement('FinInstnId');
            $dbtrAgt->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $instgBic));
        }

        // CdtrAgt (57A)
        if ($cdtrAgtBic) {
            $cdtrAgt = $dom->createElement('CdtrAgt');
            $txInf->appendChild($cdtrAgt);
            $fi = $dom->createElement('FinInstnId');
            $cdtrAgt->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $cdtrAgtBic));
        }

        // Cdtr (59)
        $cdtr = $dom->createElement('Cdtr');
        $txInf->appendChild($cdtr);
        if ($creditorName) $cdtr->appendChild($dom->createElement('Nm', $creditorName));
        if ($creditorAddr) {
            $pstlAdr = $dom->createElement('PstlAdr');
            $cdtr->appendChild($pstlAdr);
            foreach (explode("\n", $creditorAddr) as $line) {
                if (trim($line)) {
                    $pstlAdr->appendChild($dom->createElement('AdrLine', trim($line)));
                }
            }
        }

        // CdtrAcct
        if ($creditorAccount) {
            $cdtrAcct = $dom->createElement('CdtrAcct');
            $txInf->appendChild($cdtrAcct);
            $cdtrId = $dom->createElement('Id');
            $cdtrAcct->appendChild($cdtrId);
            if ($this->isIban($creditorAccount)) {
                $cdtrId->appendChild($dom->createElement('IBAN', $creditorAccount));
            } else {
                $othr = $dom->createElement('Othr');
                $cdtrId->appendChild($othr);
                $othr->appendChild($dom->createElement('Id', $creditorAccount));
            }
        }

        // Purp (26T)
        if ($purposeCode) {
            $purp = $dom->createElement('Purp');
            $txInf->appendChild($purp);
            $purp->appendChild($dom->createElement('Prtry', ':26T:' . $purposeCode));
        }

        // RmtInf (70)
        if ($remittance) {
            $rmtInf = $dom->createElement('RmtInf');
            $txInf->appendChild($rmtInf);
            $rmtInf->appendChild($dom->createElement('Ustrd', $remittance));
        }

        return $dom->saveXML();
    }

    // =========================================================
    // MT202 → pacs.009.001.08
    // =========================================================
    private function convertMT202(MessageSwift $message): string
    {
        $d = $message->details->pluck('tag_value', 'tag_name')->toArray();

        $ref          = $d['20'] ?? $message->REFERENCE ?? ('REF' . Str::random(10));
        $relatedRef   = $d['21'] ?? 'NONREF';
        [$valueDate, $currency, $amount] = $this->parse32A($d['32A'] ?? '');
        if (!$valueDate) $valueDate = optional($message->VALUE_DATE)->format('Y-m-d') ?? now()->format('Y-m-d');
        if (!$currency)  $currency  = $message->CURRENCY ?? 'USD';
        if (!$amount)    $amount    = (float) ($message->AMOUNT ?? 0);

        $instgBic     = $this->cleanBic($d['52A'] ?? $message->SENDER_BIC   ?? '');
        $intermediary = $this->cleanBic($d['56A'] ?? '');
        $instdBic     = $this->cleanBic($d['57A'] ?? $message->RECEIVER_BIC ?? '');

        // Tag 58A : bénéficiaire final
        $tag58       = $d['58A'] ?? '';
        $creditorAcc = '';
        $creditorBic = '';
        if (str_contains($tag58, '/')) {
            [$creditorAcc, $creditorBic] = explode("\n", str_replace('/', '', $tag58) . "\n", 2);
            $creditorBic = trim($creditorBic);
            $creditorAcc = trim($creditorAcc);
        } else {
            $creditorBic = $this->cleanBic($tag58);
        }

        $uetr = (string) Str::uuid();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $doc = $dom->createElementNS(
            'urn:iso:std:iso:20022:tech:xsd:pacs.009.001.08',
            'Document'
        );
        $dom->appendChild($doc);

        $trf = $dom->createElement('FICdtTrf');
        $doc->appendChild($trf);

        $grpHdr = $dom->createElement('GrpHdr');
        $trf->appendChild($grpHdr);
        $grpHdr->appendChild($dom->createElement('MsgId', $ref));
        $grpHdr->appendChild($dom->createElement('CreDtTm', now()->format('Y-m-d\TH:i:sP')));
        $grpHdr->appendChild($dom->createElement('NbOfTxs', '1'));
        $sttlmInf = $dom->createElement('SttlmInf');
        $grpHdr->appendChild($sttlmInf);
        $sttlmInf->appendChild($dom->createElement('SttlmMtd', 'INDA'));

        $txInf = $dom->createElement('CdtTrfTxInf');
        $trf->appendChild($txInf);

        $pmtId = $dom->createElement('PmtId');
        $txInf->appendChild($pmtId);
        $pmtId->appendChild($dom->createElement('InstrId', $ref));
        $pmtId->appendChild($dom->createElement('EndToEndId', $relatedRef));
        $pmtId->appendChild($dom->createElement('UETR', $uetr));

        $amtEl = $dom->createElement('IntrBkSttlmAmt', number_format($amount, 2, '.', ''));
        $amtEl->setAttribute('Ccy', $currency);
        $txInf->appendChild($amtEl);
        $txInf->appendChild($dom->createElement('IntrBkSttlmDt', $valueDate));

        // InstgAgt (52A)
        if ($instgBic) {
            $el = $dom->createElement('InstgAgt');
            $txInf->appendChild($el);
            $fi = $dom->createElement('FinInstnId');
            $el->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $instgBic));
        }

        // InstdAgt (57A)
        if ($instdBic) {
            $el = $dom->createElement('InstdAgt');
            $txInf->appendChild($el);
            $fi = $dom->createElement('FinInstnId');
            $el->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $instdBic));
        }

        // Dbtr = instructing agent
        if ($instgBic) {
            $dbtr = $dom->createElement('Dbtr');
            $txInf->appendChild($dbtr);
            $fi = $dom->createElement('FinInstnId');
            $dbtr->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $instgBic));
        }

        // DbtrAgt
        if ($instgBic) {
            $el = $dom->createElement('DbtrAgt');
            $txInf->appendChild($el);
            $fi = $dom->createElement('FinInstnId');
            $el->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $instgBic));
        }

        // IntrmyAgt1 (56A)
        if ($intermediary) {
            $el = $dom->createElement('IntrmyAgt1');
            $txInf->appendChild($el);
            $fi = $dom->createElement('FinInstnId');
            $el->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $intermediary));
        }

        // CdtrAgt (57A)
        if ($instdBic) {
            $el = $dom->createElement('CdtrAgt');
            $txInf->appendChild($el);
            $fi = $dom->createElement('FinInstnId');
            $el->appendChild($fi);
            $fi->appendChild($dom->createElement('BICFI', $instdBic));
        }

        // Cdtr (58A)
        $cdtr = $dom->createElement('Cdtr');
        $txInf->appendChild($cdtr);
        $fi = $dom->createElement('FinInstnId');
        $cdtr->appendChild($fi);
        if ($creditorBic) $fi->appendChild($dom->createElement('BICFI', $creditorBic));

        // CdtrAcct (58A account)
        if ($creditorAcc) {
            $el = $dom->createElement('CdtrAcct');
            $txInf->appendChild($el);
            $id = $dom->createElement('Id');
            $el->appendChild($id);
            $othr = $dom->createElement('Othr');
            $id->appendChild($othr);
            $othr->appendChild($dom->createElement('Id', $creditorAcc));
        }

        return $dom->saveXML();
    }

    // =========================================================
    // MT940 → camt.053.001.08
    // =========================================================
    private function convertMT940(MessageSwift $message): string
    {
        $d = $message->details->pluck('tag_value', 'tag_name')->toArray();

        $ref     = $d['20'] ?? $message->REFERENCE ?? 'REF';
        $account = $d['25'] ?? '';
        $seq     = $d['28C'] ?? '00001/001';
        $balOpen = $d['60F'] ?? ('C' . now()->format('ymd') . ($message->CURRENCY ?? 'EUR') . '0,');
        $balClose= $d['62F'] ?? $balOpen;

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $doc = $dom->createElementNS(
            'urn:iso:std:iso:20022:tech:xsd:camt.053.001.08',
            'Document'
        );
        $dom->appendChild($doc);

        $stmt = $dom->createElement('BkToCstmrStmt');
        $doc->appendChild($stmt);

        $grpHdr = $dom->createElement('GrpHdr');
        $stmt->appendChild($grpHdr);
        $grpHdr->appendChild($dom->createElement('MsgId', $ref));
        $grpHdr->appendChild($dom->createElement('CreDtTm', now()->format('Y-m-d\TH:i:sP')));

        $stmtEl = $dom->createElement('Stmt');
        $stmt->appendChild($stmtEl);
        $stmtEl->appendChild($dom->createElement('Id', $ref));
        $stmtEl->appendChild($dom->createElement('CreDtTm', now()->format('Y-m-d\TH:i:sP')));

        // Account
        if ($account) {
            $acct = $dom->createElement('Acct');
            $stmtEl->appendChild($acct);
            $acctId = $dom->createElement('Id');
            $acct->appendChild($acctId);
            if ($this->isIban($account)) {
                $acctId->appendChild($dom->createElement('IBAN', $account));
            } else {
                $othr = $dom->createElement('Othr');
                $acctId->appendChild($othr);
                $othr->appendChild($dom->createElement('Id', $account));
            }
        }

        return $dom->saveXML();
    }

    // =========================================================
    // MT101 → pain.001.001.08
    // =========================================================
    private function convertMT101(MessageSwift $message): string
    {
        $d   = $message->details->pluck('tag_value', 'tag_name')->toArray();
        $ref = $d['20'] ?? $message->REFERENCE ?? 'REF';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $doc = $dom->createElementNS(
            'urn:iso:std:iso:20022:tech:xsd:pain.001.001.08',
            'Document'
        );
        $dom->appendChild($doc);
        $ctDt = $dom->createElement('CstmrCdtTrfInitn');
        $doc->appendChild($ctDt);

        $grpHdr = $dom->createElement('GrpHdr');
        $ctDt->appendChild($grpHdr);
        $grpHdr->appendChild($dom->createElement('MsgId', $ref));
        $grpHdr->appendChild($dom->createElement('CreDtTm', now()->format('Y-m-d\TH:i:sP')));
        $grpHdr->appendChild($dom->createElement('NbOfTxs', '1'));

        return $dom->saveXML();
    }

    // =========================================================
    // Types non implémentés (stub)
    // =========================================================
    private function convertMT210(MessageSwift $message): string
    {
        return $this->stub('camt.057.001.06', $message->REFERENCE ?? 'REF', 'MT210');
    }
    private function convertMT300(MessageSwift $message): string
    {
        return $this->stub('fxtr.014.001.05', $message->REFERENCE ?? 'REF', 'MT300');
    }
    private function convertMT320(MessageSwift $message): string
    {
        return $this->stub('fxtr.014.001.05', $message->REFERENCE ?? 'REF', 'MT320');
    }
    private function convertMT700(MessageSwift $message): string
    {
        return $this->stub('tsin.009.001.01', $message->REFERENCE ?? 'REF', 'MT700');
    }
    private function convertMT760(MessageSwift $message): string
    {
        return $this->stub('tsin.009.001.01', $message->REFERENCE ?? 'REF', 'MT760');
    }
    private function convertMT900(MessageSwift $message): string
    {
        $d = $message->details->pluck('tag_value', 'tag_name')->toArray();
        $ref     = $d['20'] ?? $message->REFERENCE ?? 'REF';
        $relRef  = $d['21'] ?? $ref;
        $account = $d['25'] ?? '';

        [$valueDate, $currency, $amount] = $this->parse32A($d['32A'] ?? '');
        if (!$valueDate) $valueDate = optional($message->VALUE_DATE)->format('Y-m-d') ?? now()->format('Y-m-d');
        if (!$currency)  $currency  = $message->CURRENCY ?? 'TND';
        if (!$amount)    $amount    = (float) ($message->AMOUNT ?? 0);

        $instgBic = $this->cleanBic($d['52A'] ?? $message->SENDER_BIC ?? '');
        $narrative = $d['72'] ?? '';

        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:camt.054.001.08\">\n";
        $xml .= "  <BkToCstmrDbtCdtNtfctn>\n";
        $xml .= "    <GrpHdr>\n";
        $xml .= "      <MsgId>{$ref}</MsgId>\n";
        $xml .= "      <CreDtTm>{$valueDate}T00:00:00</CreDtTm>\n";
        $xml .= "    </GrpHdr>\n";
        $xml .= "    <Ntfctn>\n";
        $xml .= "      <Id>{$ref}</Id>\n";
        $xml .= "      <Acct><Id><Othr><Id>{$account}</Id></Othr></Id></Acct>\n";
        $xml .= "      <Ntry>\n";
        $xml .= "        <Amt Ccy=\"{$currency}\">{$amount}</Amt>\n";
        $xml .= "        <CdtDbtInd>DBIT</CdtDbtInd>\n";
        $xml .= "        <Sts>BOOK</Sts>\n";
        $xml .= "        <BookgDt><Dt>{$valueDate}</Dt></BookgDt>\n";
        $xml .= "        <ValDt><Dt>{$valueDate}</Dt></ValDt>\n";
        $xml .= "        <NtryDtls><TxDtls>\n";
        $xml .= "          <Refs><EndToEndId>{$relRef}</EndToEndId></Refs>\n";
        if ($instgBic) {
            $xml .= "          <RltdAgts><DbtrAgt><FinInstnId><BICFI>{$instgBic}</BICFI></FinInstnId></DbtrAgt></RltdAgts>\n";
        }
        if ($narrative) {
            $narEsc = htmlspecialchars($narrative, ENT_XML1, 'UTF-8');
            $xml .= "          <AddtlTxInf>{$narEsc}</AddtlTxInf>\n";
        }
        $xml .= "        </TxDtls></NtryDtls>\n";
        $xml .= "      </Ntry>\n";
        $xml .= "    </Ntfctn>\n";
        $xml .= "  </BkToCstmrDbtCdtNtfctn>\n";
        $xml .= "</Document>";

        return $xml;
    }

    private function convertMT910(MessageSwift $message): string
    {
        return $this->stub('camt.054.001.08', $message->REFERENCE ?? 'REF', 'MT910');
    }

    private function stub(string $xsd, string $ref, string $mtType): string
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
             . "<!-- {$mtType} conversion not yet implemented -->\n"
             . "<Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:{$xsd}\">"
             . "<Ref>{$ref}</Ref></Document>";
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Parse le tag 32A : YYMMDD + CCY + AMOUNT
     * Ex : "160325CHF5000,00" → ["2025-03-16", "CHF", 5000.0]
     */
    private function parse32A(string $value): array
    {
        $value = preg_replace('/\s+/', '', $value);
        if (preg_match('/^(\d{2})(\d{2})(\d{2})([A-Z]{3})([\d,]+)$/', $value, $m)) {
            $year  = '20' . $m[1];
            $month = $m[2];
            $day   = $m[3];
            $date  = "{$year}-{$month}-{$day}";
            $amt   = (float) str_replace(',', '.', $m[5]);
            return [$date, $m[4], $amt];
        }
        return [null, null, null];
    }

    /**
     * Parse le tag 50K (donneur d'ordre) :
     * /IBAN\nNom\nAdresse ligne1\nAdresse ligne2
     */
    private function parseTag50(string $raw): array
    {
        $lines = explode("\n", str_replace("\r", '', trim($raw)));
        $account = '';
        $name    = '';
        $addrLines = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if ($i === 0 && str_starts_with($line, '/')) {
                $account = ltrim($line, '/');
            } elseif ($i === 0 || ($i === 1 && empty($account))) {
                $name = $line;
            } elseif ($i === 1 && !empty($account)) {
                $name = $line;
            } else {
                if ($line) $addrLines[] = $line;
            }
        }

        return [$account, $name, implode("\n", $addrLines)];
    }

    /**
     * Parse le tag 59 (bénéficiaire) :
     * /IBAN\nNom\nAdresse
     */
    private function parseTag59(string $raw): array
    {
        $lines = explode("\n", str_replace("\r", '', trim($raw)));
        $account = '';
        $name    = '';
        $addrLines = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if ($i === 0 && str_starts_with($line, '/')) {
                $account = ltrim($line, '/');
            } elseif ($i === 0 || ($i === 1 && empty($account))) {
                $name = $line;
            } elseif ($i === 1 && !empty($account)) {
                $name = $line;
            } else {
                if ($line) $addrLines[] = $line;
            }
        }

        return [$account, $name, implode("\n", $addrLines)];
    }

    /**
     * Normalise le tag 71A (MT) en ChrgBr (MX ISO 20022)
     * SHA → SHAR | OUR → DEBT | BEN → CRED
     */
    private function normalizeChrg71A(string $tag71A): string
    {
        return match(strtoupper(trim($tag71A))) {
            'SHA'  => 'SHAR',
            'OUR'  => 'DEBT',
            'BEN'  => 'CRED',
            default => 'SHAR',
        };
    }

    /**
     * Mappe le code 23B MT vers SvcLvl MX
     */
    private function mapSvcLvl(string $tag23B): ?string
    {
        return match(strtoupper(trim($tag23B))) {
            'CRED' => 'G001',
            'SPAY' => 'SEPA',
            default => null,
        };
    }

    /**
     * Nettoie un BIC (supprime les Xs de remplissage si 11 car)
     */
    private function cleanBic(string $bic): string
    {
        return strtoupper(trim($bic));
    }

    /**
     * Vérifie si une chaîne ressemble à un IBAN
     */
    private function isIban(string $val): bool
    {
        return (bool) preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{4,}$/', strtoupper(trim($val)));
    }
}