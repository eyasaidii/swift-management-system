<?php

namespace Database\Seeders;

use App\Models\MessageSwift;
use App\Services\AnomalyService;
use App\Services\SwiftMtBuilder;
use App\Services\UniversalMtToMxConverter;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MessageSwiftSeeder
 *
 * Creates 100 SWIFT messages via the full Eloquent pipeline:
 *   1. MessageSwift::create()               -> model persisted
 *   2. details()->create()                  -> swift_message_details
 *   3. $message->update([financial fields]) -> normalized fields + CATEGORIE
 *   4. UniversalMtToMxConverter->convert()  -> XML_BRUT (MT -> ISO 20022 MX)
 *   5. SwiftMtBuilder->build()              -> MT_CONTENT (SWIFT envelope)
 *   6. AnomalyService->analyze()            -> anomalies_swift
 *
 * Usage: php artisan db:seed --class=MessageSwiftSeeder
 */
class MessageSwiftSeeder extends Seeder
{
    // -- Normal BICs (BTL + regular correspondents) --
    private array $normalBics = [
        'BTLMTNTT', 'BIATTNTT', 'ATTIJARX', 'BNATNTTX', 'UBCITNTT',
        'STBKTNTT', 'BNPAFRPP', 'DEUTDEFF', 'CITIUS33', 'BARCGB22',
        'SOGEFRPP', 'HSBCGB2L',
    ];

    // -- Suspect BICs (offshore / unknown banks) --
    private array $suspectBics = [
        'SHELBHBB', // Belize
        'OFFSHKYY', // Cayman Islands
        'PRVBKXXL', // Unknown private bank
        'MHCBJPJT', // Mizuho Tokyo suspect
    ];

    private array $normalCurrencies  = ['EUR', 'USD', 'TND', 'GBP', 'CHF'];
    private array $suspectCurrencies = ['JPY', 'CNY', 'RUB', 'AED'];

    // -- Types supported by UniversalMtToMxConverter --
    private array $mtTypes = ['MT103', 'MT202', 'MT940'];

    // -- Real Tunisian corporate/individual senders --
    // Format: [name, iban, bank_bic]
    private array $tunisianSenders = [
        ['name' => 'SOCIETE MEDITERRANEENNE D EXPORT SARL',              'iban' => 'TN5910006035183456789012', 'bic' => 'BTLMTNTT'],
        ['name' => 'POULINA GROUP HOLDING SA',                           'iban' => 'TN5910006035264738195027', 'bic' => 'BTLMTNTT'],
        ['name' => 'TUNISAIR SA',                                        'iban' => 'TN5910006035371829465038', 'bic' => 'BTLMTNTT'],
        ['name' => 'SFBT SOCIETE FRIGORIFIQUE ET BRASSERIE DE TUNIS',    'iban' => 'TN5910006035456283741049', 'bic' => 'BTLMTNTT'],
        ['name' => 'ONE TECH HOLDING SA',                                'iban' => 'TN5910006035538271946050', 'bic' => 'BTLMTNTT'],
        ['name' => 'ENNAKL AUTOMOBILES SA',                              'iban' => 'TN5910006035612394857061', 'bic' => 'BTLMTNTT'],
        ['name' => 'CARTHAGE CEMENT SA',                                 'iban' => 'TN5910006035783628451072', 'bic' => 'BTLMTNTT'],
        ['name' => 'TPR SA INDUSTRIES PLASTIQUES',                       'iban' => 'TN5910006035861749253083', 'bic' => 'BTLMTNTT'],
        ['name' => 'MAGASIN GENERAL SA',                                 'iban' => 'TN5910006035927483615094', 'bic' => 'BTLMTNTT'],
        ['name' => 'BTL INTERNATIONAL TRADE FINANCE',                   'iban' => 'TN5910006035048372916105', 'bic' => 'BTLMTNTT'],
        ['name' => 'SOCIETE CHIMIQUE MAGHREB INDUSTRIES',                'iban' => 'TN5910006035159483027116', 'bic' => 'BTLMTNTT'],
        ['name' => 'COMPAGNIE DE PHOSPHATE DE GAFSA',                   'iban' => 'TN5910006035260594138127', 'bic' => 'BTLMTNTT'],
        ['name' => 'STEG SOCIETE TUNISIENNE ELECTRICITE GAZ',            'iban' => 'TN5910006035371605249138', 'bic' => 'BTLMTNTT'],
        ['name' => 'TUNISIE TELECOM SA',                                 'iban' => 'TN5910006035482716350149', 'bic' => 'BTLMTNTT'],
        ['name' => 'SOTUVER SA VERRERIES',                               'iban' => 'TN5910006035593827461150', 'bic' => 'BTLMTNTT'],
        ['name' => 'AHMED SALAH BEN YOUSSEF',                            'iban' => 'TN5910006035385169473138', 'bic' => 'BTLMTNTT'],
        ['name' => 'MEHDI RACHID BEN ROMDHANE',                          'iban' => 'TN5910006035461283574149', 'bic' => 'BTLMTNTT'],
        ['name' => 'SARRA HAMDI BELHADJ',                                'iban' => 'TN5910006035572394685150', 'bic' => 'BTLMTNTT'],
        ['name' => 'KARIM MANSOURI',                                     'iban' => 'TN5910006035683415796161', 'bic' => 'BTLMTNTT'],
        ['name' => 'LEILA CHAABANE TRABELSI',                            'iban' => 'TN5910006035794526817172', 'bic' => 'BTLMTNTT'],
    ];

    // -- Real Libyan corporate senders --
    private array $libyanSenders = [
        ['name' => 'NATIONAL OIL CORPORATION',                'iban' => 'LY82009001000000000044741', 'bic' => 'LAFBLYLTXXX',
         'address' => "BASHER ALSAADAOI STR. P.O 5335/2655\nTRIPOLI - LIBYA",
         'settlement' => 'LY57009001000000000007943'],
        ['name' => 'LIBYAN IRON AND STEEL COMPANY',           'iban' => 'LY82009001000000000055832', 'bic' => 'LAFBLYLTXXX',
         'address' => "AL FATAH INDUSTRIAL CITY P.O 3031\nMISURATH - LIBYA",
         'settlement' => 'LY57009001000000000015826'],
        ['name' => 'GENERAL ELECTRICITY COMPANY OF LIBYA',   'iban' => 'LY82009001000000000066923', 'bic' => 'LAFBLYLTXXX',
         'address' => "OMAR MUKHTAR STREET P.O 668\nTRIPOLI - LIBYA",
         'settlement' => 'LY57009001000000000023719'],
        ['name' => 'TRIPOLI PORT AUTHORITY',                  'iban' => 'LY82009001000000000077014', 'bic' => 'LAFBLYLTXXX',
         'address' => "PORT OF TRIPOLI ADMINISTRATIVE BLDG\nTRIPOLI - LIBYA",
         'settlement' => 'LY57009001000000000031612'],
    ];

    // -- Real foreign beneficiaries by bank BIC --
    private array $foreignBeneficiaries = [
        'BNPAFRPP' => [
            'DUPONT ET FILS INDUSTRIES SA',
            'TOTAL ENERGIES SE',
            'AIRBUS SAS TOULOUSE',
            'RENAULT FRANCE SA',
            'COMPAGNIE SAINT GOBAIN',
            'BOUYGUES CONSTRUCTION SA',
            'SCHNEIDER ELECTRIC SE',
        ],
        'HSBCGB2L' => [
            'KINGS COLLEGE LONDON',
            'UNIVERSITY OF MANCHESTER',
            'LONDON BUSINESS SCHOOL',
            'IMPERIAL COLLEGE LONDON',
            'UNIVERSITY COLLEGE LONDON',
            'LOUGHBOROUGH UNIVERSITY',
            'EDINBURGH BUSINESS SCHOOL',
        ],
        'DEUTDEFF' => [
            'SIEMENS AG MUNICH',
            'BMW GROUP GMBH',
            'BASF SE LUDWIGSHAFEN',
            'VOLKSWAGEN AG WOLFSBURG',
            'THYSSENKRUPP AG',
            'CONTINENTAL AG',
        ],
        'BARCGB22' => [
            'BARCLAYS WEALTH MANAGEMENT LTD',
            'LLOYDS BANK PLC LONDON',
            'STANDARD CHARTERED BANK UK',
            'BP PLC LONDON',
        ],
        'CITIUS33' => [
            'EXXON MOBIL CORPORATION',
            'GENERAL ELECTRIC COMPANY',
            'JOHNSON AND JOHNSON INC',
            'SCHLUMBERGER NV',
            'HALLIBURTON COMPANY',
        ],
        'SOGEFRPP' => [
            'CREDIT AGRICOLE CORPORATE SA',
            'AXA SA PARIS',
            'VINCI CONSTRUCTION FRANCE',
            'ENGIE SA',
        ],
        'ATLDTNTTXXX' => [
            'MASOUD ATIYA HAMED',
            'KHALID IBRAHIM MANSOUR',
            'FATHI ABDELHAMID SALEH',
            'NADIA OMAR BENSAOUD',
        ],
        'BTLMTNTT' => [
            'BTL CORRESPONDANT ACCOUNT',
            'BTL TREASURY DEPT',
        ],
    ];

    // -- Purpose codes (tag 26T) --
    private array $purposeCodes = ['921', '150', '001', '452', '112', '814', '701', '441', '202'];

    private int    $counter = 1;
    private string $prefix;

    // ---------------------------------------------------------------
    // ENTRY POINT
    // ---------------------------------------------------------------

    public function run(): void
    {
        $this->prefix = 'SW' . date('ymdHi');
        $userId = DB::table('users')->value('id') ?? 1;

        // 1. Delete old seeded messages
        $this->deleteOldSeeded();

        // 2. Instantiate services
        $converter  = app(UniversalMtToMxConverter::class);
        $mtBuilder  = app(SwiftMtBuilder::class);
        $anomalySvc = app(AnomalyService::class);

        $created = $converted = $analyzed = 0;

        // 3. 70 normal messages
        for ($i = 0; $i < 70; $i++) {
            $type      = $this->pick($this->mtTypes);
            $direction = $this->pick(['IN', 'OUT']);
            $currency  = $this->pick($this->normalCurrencies);
            $amount    = $this->rnd(500, 150_000);
            $daysAgo   = rand(1, 180);
            $hour      = rand(8, 17);

            // Pick realistic sender/receiver based on type
            [$senderBic, $senderName, $senderIban] = $this->pickSender($type, $direction);
            [$receiverBic, $receiverName]           = $this->pickReceiver($type, $direction, $senderBic);

            $r = $this->createMessage(
                userId: $userId, type: $type, direction: $direction,
                senderBic: $senderBic, senderName: $senderName, senderIban: $senderIban,
                receiverBic: $receiverBic, receiverName: $receiverName,
                amount: $amount, currency: $currency,
                daysAgo: $daysAgo, hour: $hour,
                status: $this->pick(['processed', 'authorized', 'pending']),
                meta: ['anomaly' => false],
                converter: $converter, mtBuilder: $mtBuilder, anomalySvc: $anomalySvc,
            );
            $created++;
            if ($r['converted']) $converted++;
            if ($r['analyzed'])  $analyzed++;
        }

        // 4. 30 anomaly messages
        foreach ($this->anomalyScenarios() as $s) {
            $r = $this->createMessage(
                userId: $userId,
                type: $s['type'], direction: $s['direction'],
                senderBic: $s['senderBic'], senderName: $s['senderName'], senderIban: $s['senderIban'],
                receiverBic: $s['receiverBic'], receiverName: $s['receiverName'],
                amount: $s['amount'], currency: $s['currency'],
                daysAgo: $s['daysAgo'], hour: $s['hour'],
                status: 'pending',
                meta: ['anomaly' => true, 'raison' => $s['raison']],
                converter: $converter, mtBuilder: $mtBuilder, anomalySvc: $anomalySvc,
            );
            $created++;
            if ($r['converted']) $converted++;
            if ($r['analyzed'])  $analyzed++;
        }

        // 5. 10 targeted anomaly test messages (IN + OUT, tous types, anomalies HIGH/MEDIUM ciblées)
        foreach ($this->targetedAnomalyScenarios() as $s) {
            $r = $this->createMessage(
                userId: $userId,
                type: $s['type'], direction: $s['direction'],
                senderBic: $s['senderBic'], senderName: $s['senderName'], senderIban: $s['senderIban'],
                receiverBic: $s['receiverBic'], receiverName: $s['receiverName'],
                amount: $s['amount'], currency: $s['currency'],
                daysAgo: $s['daysAgo'], hour: $s['hour'],
                status: $s['status'],
                meta: ['anomaly' => true, 'raison' => $s['raison'], 'test' => true],
                converter: $converter, mtBuilder: $mtBuilder, anomalySvc: $anomalySvc,
            );
            $created++;
            if ($r['converted']) $converted++;
            if ($r['analyzed'])  $analyzed++;
        }

        // 6. 30 scénarios internationaux réels (HIGH/MEDIUM/LOW — FR/DE/GB/US/LY/AE/CN/JP/RU/CH)
        foreach ($this->internationalScenarios() as $s) {
            $r = $this->createMessage(
                userId: $userId,
                type: $s['type'], direction: $s['direction'],
                senderBic: $s['senderBic'], senderName: $s['senderName'], senderIban: $s['senderIban'],
                receiverBic: $s['receiverBic'], receiverName: $s['receiverName'],
                amount: $s['amount'], currency: $s['currency'],
                daysAgo: $s['daysAgo'], hour: $s['hour'],
                status: $s['status'],
                meta: ['anomaly' => ($s['status'] === 'rejected'), 'raison' => $s['raison'], 'international' => true],
                converter: $converter, mtBuilder: $mtBuilder, anomalySvc: $anomalySvc,
            );
            $created++;
            if ($r['converted']) $converted++;
            if ($r['analyzed'])  $analyzed++;
        }

        $this->command->info("OK {$created} messages created via full pipeline");
        $this->command->info("   -> {$converted} converted  MT->MX  (XML_BRUT via UniversalMtToMxConverter)");
        $this->command->info("   -> MT_CONTENT generated       (SwiftMtBuilder)");
        $this->command->info("   -> {$analyzed}  analyzed   (AnomalyService -> anomalies_swift)");
    }

    // ---------------------------------------------------------------
    // DELETE OLD SEEDED MESSAGES
    // ---------------------------------------------------------------

    private function deleteOldSeeded(): void
    {
        // ON DELETE CASCADE removes swift_message_details + anomalies_swift
        $deleted = MessageSwift::where('REFERENCE', 'like', 'SW%')
            ->orWhere('REFERENCE', 'like', 'SD%')
            ->delete();
        $this->command->info("Deleted {$deleted} old seeded messages (cascade).");
    }

    // ---------------------------------------------------------------
    // FULL PIPELINE -- ONE MESSAGE
    // ---------------------------------------------------------------

    private function createMessage(
        int $userId, string $type, string $direction,
        string $senderBic, string $senderName, string $senderIban,
        string $receiverBic, string $receiverName,
        float $amount, string $currency,
        int $daysAgo, int $hour, string $status,
        array $meta,
        UniversalMtToMxConverter $converter,
        SwiftMtBuilder $mtBuilder,
        AnomalyService $anomalySvc,
    ): array {
        $ref   = $this->prefix . str_pad($this->counter++, 5, '0', STR_PAD_LEFT);
        $dt    = Carbon::now()->subDays($daysAgo)->setHour($hour)->setMinute(rand(0, 59));
        $vdate = $dt->copy()->addDays(rand(0, 2))->toDateString();

        // STEP 1: create model (without observer to avoid jobs)
        /** @var MessageSwift $message */
        $message = MessageSwift::withoutEvents(fn () => MessageSwift::create([
            'TYPE_MESSAGE' => $type,
            'DIRECTION'    => $direction,
            'STATUS'       => $status,
            'CREATED_BY'   => $userId,
            'REFERENCE'    => $ref,
            'METADATA'     => json_encode($meta),
            'CREATED_AT'   => $dt->toDateTimeString(),
            'UPDATED_AT'   => $dt->toDateTimeString(),
        ]));
        // Save ID before any case transformation
        $msgId = $message->getKey();

        // STEP 2: MT tags -> swift_message_details
        $tags = $this->tagsFor(
            $type, $ref, $amount, $currency,
            $senderBic, $senderName, $senderIban,
            $receiverBic, $receiverName, $dt
        );
        foreach ($tags as $tag => $value) {
            $message->details()->create(['tag_name' => $tag, 'tag_value' => $value]);
        }

        // STEP 3: normalized financial fields (without observer)
        MessageSwift::withoutEvents(fn () => $message->update([
            'AMOUNT'           => $amount,
            'CURRENCY'         => $currency,
            'VALUE_DATE'       => $vdate,
            'SENDER_BIC'       => substr($senderBic, 0, 11),
            'RECEIVER_BIC'     => substr($receiverBic, 0, 11),
            'SENDER_NAME'      => substr($senderName, 0, 255),
            'RECEIVER_NAME'    => substr($receiverName, 0, 255),
            'SENDER_ACCOUNT'   => $senderIban,
            'RECEIVER_ACCOUNT' => $this->genTnIban(),
            'DESCRIPTION'      => isset($tags['70']) ? substr($tags['70'], 0, 500) : $this->narrative('MT103'),
            'CATEGORIE'        => $this->categorie($type),
        ]));

        // STEP 3b: Create Transaction (mimics Observer::syncTransaction())
        if ($amount > 0 && $currency) {
            try {
                \App\Models\Transaction::updateOrCreate(
                    ['message_swift_id' => $msgId],
                    [
                        'montant'          => $amount,
                        'devise'           => $currency,
                        'emetteur'         => $senderName,
                        'recepteur'        => $receiverName,
                        'date_transaction' => $dt->toDateString(),
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Seeder Transaction #{$msgId}: {$e->getMessage()}");
            }
        }

        // STEP 4: MT -> MX  (UniversalMtToMxConverter)
        $converted = false;
        try {
            $message->load('details');
            // Oracle returns lowercase attrs after update/load -> fix
            $message->setRawAttributes(
                array_change_key_case($message->getAttributes(), CASE_UPPER), true
            );
            $xmlMx = $converter->convert($message);
            if ($xmlMx) {
                // Use where('id') — $message->getKey() is null after setRawAttributes(CASE_UPPER)
                MessageSwift::withoutEvents(fn () => MessageSwift::where('id', $msgId)->update(['XML_BRUT' => $xmlMx]));
                $converted = true;
            }
        } catch (\Throwable $e) {
            Log::warning("Seeder MT->MX #{$msgId}: {$e->getMessage()}");
        }

        // STEP 5: -> MT_CONTENT  (SwiftMtBuilder)
        try {
            $message->setRawAttributes(
                array_change_key_case($message->getAttributes(), CASE_UPPER), true
            );
            $mtContent = $mtBuilder->build($message, $tags);
            if ($mtContent) {
                // Use where('id') — $message->getKey() is null after setRawAttributes(CASE_UPPER)
                MessageSwift::withoutEvents(fn () => MessageSwift::where('id', $msgId)->update(['MT_CONTENT' => $mtContent]));
            }
        } catch (\Throwable $e) {
            Log::warning("Seeder MT_CONTENT #{$msgId}: {$e->getMessage()}");
        }

        // STEP 6: AnomalyService -> anomalies_swift
        $analyzed = false;
        try {
            // Reload from DB without case transformation so getKey() -> id works correctly
            $forAnalysis = MessageSwift::find($msgId);
            if ($forAnalysis) {
                $anomalySvc->analyze($forAnalysis);
                $analyzed = true;
            }
        } catch (\Throwable $e) {
            Log::warning("Seeder AnomalyService #{$msgId}: {$e->getMessage()}");
        }

        return ['converted' => $converted, 'analyzed' => $analyzed];
    }

    // ---------------------------------------------------------------
    // MT TAGS (SWIFT format expected by UniversalMtToMxConverter)
    // ---------------------------------------------------------------

    private function tagsFor(
        string $type, string $ref, float $amount, string $currency,
        string $senderBic, string $senderName, string $senderIban,
        string $receiverBic, string $receiverName,
        Carbon $dt
    ): array {
        $ymd    = $dt->format('ymd');
        $amtFmt = number_format($amount, 2, ',', '');  // SWIFT comma decimal

        return match ($type) {

            // MT103 - Customer payment -> pacs.008.001.08
            'MT103' => $this->mt103Tags(
                $ref, $ymd, $currency, $amtFmt,
                $senderBic, $senderName, $senderIban,
                $receiverBic, $receiverName
            ),

            // MT202 - Interbank transfer -> pacs.009
            'MT202' => [
                '20'  => $ref,
                '21'  => $ref,
                '32A' => $ymd . $currency . $amtFmt,
                '52A' => $senderBic,
                '57A' => $receiverBic,
                '58A' => "/{$senderIban}\n{$receiverName}",
                '72'  => "/INS/{$senderBic}",
            ],

            // MT940 - Account statement -> camt.053.001.08
            'MT940' => [
                '20'  => $ref,
                '25'  => 'TN59' . substr($senderIban, 4),
                '28C' => rand(100, 999) . '/1',
                '60F' => 'C' . $ymd . $currency . number_format($amount * 1.1, 2, ',', ''),
                '61'  => $ymd . $ymd . 'C' . $amtFmt . "NTRFNONREF\n//{$ref}",
                '86'  => $this->narrative('MT940'),
                '62F' => 'C' . $ymd . $currency . $amtFmt,
            ],

            default => ['20' => $ref],
        };
    }

    /**
     * Build MT103 tags matching exactly the format of real imported PACS.008 messages.
     * Includes optional 26T (purpose code) and 53B (settlement account).
     */
    private function mt103Tags(
        string $ref, string $ymd, string $currency, string $amtFmt,
        string $senderBic, string $senderName, string $senderIban,
        string $receiverBic, string $receiverName
    ): array {
        // 50K: /IBAN\nNAME format (for Libyan senders add address lines)
        $isLibyan = str_starts_with($senderIban, 'LY');
        if ($isLibyan) {
            $lyData = $this->findLibyanSender($senderIban);
            $field50k = "/{$senderIban}\n{$senderName}\n" . ($lyData['address'] ?? 'TRIPOLI - LIBYA');
        } else {
            $field50k = "/{$senderIban}\n{$senderName}";
        }

        $tags = [
            '20'  => $ref,
            '23B' => 'CRED',
            '32A' => $ymd . $currency . $amtFmt,
            '50'  => $senderName,
            '50K' => $field50k,
            '52A' => $senderBic,
            '57A' => $receiverBic,
            '59'  => $receiverName,
            '70'  => $this->narrative('MT103'),
            '71A' => (rand(0, 4) === 0) ? 'OUR' : 'SHA',
        ];

        // Add purpose code 26T for ~55% of messages
        if (rand(0, 1) === 1) {
            $tags['26T'] = $this->pick($this->purposeCodes);
        }

        // Add settlement account 53B for ~40% of messages (especially Libyan)
        if ($isLibyan || rand(0, 4) >= 3) {
            if ($isLibyan) {
                $lyData = $this->findLibyanSender($senderIban);
                $tags['53B'] = '/C/' . ($lyData['settlement'] ?? 'LY57009001000000000007943');
            } else {
                $tags['53B'] = '/C/' . $senderIban;
            }
        }

        return $tags;
    }

    // ---------------------------------------------------------------
    // ANOMALY SCENARIOS (30)
    // ---------------------------------------------------------------

    private function anomalyScenarios(): array
    {
        $s = [];

        // 1. Amount > 1M (6)
        for ($i = 0; $i < 6; $i++) {
            [$sb, $sn, $si] = $this->pickSender('MT103', 'OUT');
            [$rb, $rn]      = $this->pickReceiver('MT103', 'OUT', $sb);
            $s[] = ['type' => 'MT103', 'direction' => 'OUT',
                'senderBic' => $sb, 'senderName' => $sn, 'senderIban' => $si,
                'receiverBic' => $rb, 'receiverName' => $rn,
                'amount' => $this->rnd(1_000_001, 9_000_000), 'currency' => 'USD',
                'daysAgo' => rand(1, 20), 'hour' => rand(9, 16), 'raison' => 'MONTANT_ELEVE'];
        }
        // 2. Unusual currency (5)
        for ($i = 0; $i < 5; $i++) {
            [$sb, $sn, $si] = $this->pickSender('MT103', 'IN');
            [$rb, $rn]      = $this->pickReceiver('MT103', 'IN', $sb);
            $s[] = ['type' => 'MT103', 'direction' => 'IN',
                'senderBic' => $sb, 'senderName' => $sn, 'senderIban' => $si,
                'receiverBic' => $rb, 'receiverName' => $rn,
                'amount' => $this->rnd(10_000, 500_000), 'currency' => $this->pick($this->suspectCurrencies),
                'daysAgo' => rand(1, 15), 'hour' => rand(9, 16), 'raison' => 'DEVISE_INHABITUELLE'];
        }
        // 3. Suspect/offshore BIC (5)
        for ($i = 0; $i < 5; $i++) {
            [$sb, $sn, $si] = $this->pickSender('MT202', 'OUT');
            $rb = $this->pick($this->suspectBics);
            $rn = $this->bankName($rb);
            $s[] = ['type' => 'MT202', 'direction' => 'OUT',
                'senderBic' => $sb, 'senderName' => $sn, 'senderIban' => $si,
                'receiverBic' => $rb, 'receiverName' => $rn,
                'amount' => $this->rnd(50_000, 800_000), 'currency' => 'USD',
                'daysAgo' => rand(1, 30), 'hour' => rand(9, 16), 'raison' => 'BIC_SUSPECT'];
        }
        // 4. Night transfer 00h-04h (5)
        for ($i = 0; $i < 5; $i++) {
            [$sb, $sn, $si] = $this->pickSender('MT103', 'OUT');
            [$rb, $rn]      = $this->pickReceiver('MT103', 'OUT', $sb);
            $s[] = ['type' => 'MT103', 'direction' => 'OUT',
                'senderBic' => $sb, 'senderName' => $sn, 'senderIban' => $si,
                'receiverBic' => $rb, 'receiverName' => $rn,
                'amount' => $this->rnd(20_000, 300_000), 'currency' => 'EUR',
                'daysAgo' => rand(1, 40), 'hour' => rand(0, 4), 'raison' => 'HEURE_NOCTURNE'];
        }
        // 5. Zero amount (4)
        for ($i = 0; $i < 4; $i++) {
            [$sb, $sn, $si] = $this->pickSender('MT103', 'IN');
            [$rb, $rn]      = $this->pickReceiver('MT103', 'IN', $sb);
            $s[] = ['type' => 'MT103', 'direction' => 'IN',
                'senderBic' => $sb, 'senderName' => $sn, 'senderIban' => $si,
                'receiverBic' => $rb, 'receiverName' => $rn,
                'amount' => 0.00, 'currency' => 'EUR',
                'daysAgo' => rand(1, 10), 'hour' => rand(9, 16), 'raison' => 'MONTANT_ZERO'];
        }
        // 6. Self-transfer sender = receiver (5)
        for ($i = 0; $i < 5; $i++) {
            [$sb, $sn, $si] = $this->pickSender('MT103', 'OUT');
            $s[] = ['type' => 'MT103', 'direction' => 'OUT',
                'senderBic' => $sb, 'senderName' => $sn, 'senderIban' => $si,
                'receiverBic' => $sb, 'receiverName' => $sn,
                'amount' => $this->rnd(5_000, 100_000), 'currency' => 'EUR',
                'daysAgo' => rand(1, 20), 'hour' => rand(9, 16), 'raison' => 'AUTO_VIREMENT'];
        }

        return $s;
    }

    // ---------------------------------------------------------------
    // 10 TARGETED ANOMALY SCENARIOS — IN + OUT, tous types, HIGH/MEDIUM
    // Chaque scénario cible une anomalie spécifique visible dans l'interface
    // ---------------------------------------------------------------

    private function targetedAnomalyScenarios(): array
    {
        $tn = $this->tunisianSenders;
        $ly = $this->libyanSenders;

        return [

            // 1. OUT MT103 — Montant ULTRA élevé + devise suspecte + BIC manquant (HIGH attendu)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'BTLMTNTT',
                'senderName'  => 'BTL TUNISIAN LIBYAN BANK',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'OFFSHKYY',
                'receiverName'=> 'CAYMAN OFFSHORE LTD',
                'amount'      => 4_750_000.00, 'currency' => 'RUB',
                'daysAgo'     => 2, 'hour' => 1,   // nuit
                'raison'      => 'MONTANT_ELEVE+DEVISE_INHABITUELLE+STATUT_REJETE',
            ],

            // 2. IN MT103 — Montant zéro + statut rejeté (HIGH attendu)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'rejected',
                'senderBic'   => $ly[0]['bic'],
                'senderName'  => $ly[0]['name'],
                'senderIban'  => $ly[0]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL TUNISIAN LIBYAN BANK',
                'amount'      => 0.00, 'currency' => 'EUR',
                'daysAgo'     => 1, 'hour' => 14,
                'raison'      => 'MONTANT_ZERO+STATUT_REJETE',
            ],

            // 3. OUT MT202 — BIC offshore + devise AED + rejeté (HIGH attendu)
            [
                'type' => 'MT202', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'BIATTNTT',
                'senderName'  => 'BIAT BANQUE INTERNATIONALE ARABE DE TUNISIE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'SHELBHBB',
                'receiverName'=> 'BELIZE SHELL BANK',
                'amount'      => 890_000.00, 'currency' => 'AED',
                'daysAgo'     => 3, 'hour' => 23,
                'raison'      => 'DEVISE_INHABITUELLE+MONTANT_ELEVE+STATUT_REJETE',
            ],

            // 4. IN MT103 — Société libyenne + montant élevé USD (MEDIUM attendu)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => $ly[1]['bic'],
                'senderName'  => $ly[1]['name'],
                'senderIban'  => $ly[1]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDANT ACCOUNT',
                'amount'      => 325_000.00, 'currency' => 'USD',
                'daysAgo'     => 5, 'hour' => 10,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // 5. OUT MT103 — Auto-virement + CNY + rejeté (HIGH attendu)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => $tn[2]['bic'],
                'senderName'  => $tn[2]['name'],
                'senderIban'  => $tn[2]['iban'],
                'receiverBic' => $tn[2]['bic'],
                'receiverName'=> $tn[2]['name'],
                'amount'      => 55_000.00, 'currency' => 'CNY',
                'daysAgo'     => 4, 'hour' => 3,
                'raison'      => 'AUTO_VIREMENT+DEVISE_INHABITUELLE+STATUT_REJETE',
            ],

            // 6. IN MT940 — Devise JPY + montant massif + rejeté (HIGH attendu)
            [
                'type' => 'MT940', 'direction' => 'IN', 'status' => 'rejected',
                'senderBic'   => 'MHCBJPJT',
                'senderName'  => 'MIZUHO BANK TOKYO',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL TUNISIAN LIBYAN BANK',
                'amount'      => 12_000_000.00, 'currency' => 'JPY',
                'daysAgo'     => 7, 'hour' => 8,
                'raison'      => 'DEVISE_INHABITUELLE+MONTANT_ELEVE+STATUT_REJETE',
            ],

            // 7. OUT MT103 — Montant > 2M + devise RUB + rejeté (HIGH attendu)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'STBKTNTT',
                'senderName'  => 'STB SOCIETE TUNISIENNE DE BANQUE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'PRVBKXXL',
                'receiverName'=> 'PRIVATE BANK XL',
                'amount'      => 2_100_000.00, 'currency' => 'RUB',
                'daysAgo'     => 6, 'hour' => 2,
                'raison'      => 'MONTANT_ELEVE+DEVISE_INHABITUELLE+STATUT_REJETE',
            ],

            // 8. IN MT103 — Montant zéro entrée Tunisie (MEDIUM attendu)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'pending',
                'senderBic'   => $tn[5]['bic'],
                'senderName'  => $tn[5]['name'],
                'senderIban'  => $tn[5]['iban'],
                'receiverBic' => 'BNPAFRPP',
                'receiverName'=> 'BNP PARIBAS',
                'amount'      => 0.00, 'currency' => 'EUR',
                'daysAgo'     => 1, 'hour' => 11,
                'raison'      => 'MONTANT_ZERO',
            ],

            // 9. OUT MT202 — Rejeté + RUB + montant élevé (HIGH attendu)
            [
                'type' => 'MT202', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'UBCITNTT',
                'senderName'  => 'UIB UNION INTERNATIONALE DE BANQUES',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'OFFSHKYY',
                'receiverName'=> 'CAYMAN OFFSHORE LTD',
                'amount'      => 600_000.00, 'currency' => 'RUB',
                'daysAgo'     => 10, 'hour' => 0,
                'raison'      => 'STATUT_REJETE+DEVISE_INHABITUELLE+MONTANT_ELEVE',
            ],

            // 10. IN MT103 — Libyen + 5.8M USD + rejeté (HIGH attendu)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'rejected',
                'senderBic'   => $ly[2]['bic'],
                'senderName'  => $ly[2]['name'],
                'senderIban'  => $ly[2]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL TREASURY DEPT',
                'amount'      => 5_800_000.00, 'currency' => 'USD',
                'daysAgo'     => 8, 'hour' => 15,
                'raison'      => 'MONTANT_ELEVE+STATUT_REJETE',
            ],
        ];
    }

    // ---------------------------------------------------------------
    // DATA HELPERS
    // ---------------------------------------------------------------

    /**
     * Pick a realistic sender [bic, name, iban] based on message type and direction.
     * For MT103 IN: Tunisian or Libyan companies paying into BTL
     * For MT103 OUT: BTL sending to foreign bank
     * For MT202/MT940: bank BICs
     */
    private function pickSender(string $type, string $direction): array
    {
        if ($type === 'MT103') {
            // Occasionally use a Libyan sender for variety
            if (rand(0, 9) === 0 && !empty($this->libyanSenders)) {
                $p = $this->libyanSenders[array_rand($this->libyanSenders)];
                return [$p['bic'], $p['name'], $p['iban']];
            }
            $p = $this->tunisianSenders[array_rand($this->tunisianSenders)];
            return [$p['bic'], $p['name'], $p['iban']];
        }
        // MT202 / MT940: use bank BICs
        $bic  = $this->pick($this->normalBics);
        $name = $this->bankName($bic);
        $iban = $this->genTnIban();
        return [$bic, $name, $iban];
    }

    /**
     * Pick a realistic receiver [bic, name] based on message type/direction.
     */
    private function pickReceiver(string $type, string $direction, string $senderBic): array
    {
        if ($type === 'MT103') {
            // Pick a foreign bank BIC (not BTL)
            $foreignBics = ['BNPAFRPP', 'HSBCGB2L', 'DEUTDEFF', 'BARCGB22', 'CITIUS33', 'SOGEFRPP', 'ATLDTNTTXXX'];
            $bic = $this->pick($foreignBics);
            $beneficiaries = $this->foreignBeneficiaries[$bic] ?? ['FOREIGN BENEFICIARY'];
            $name = $beneficiaries[array_rand($beneficiaries)];
            return [$bic, $name];
        }
        // MT202 / MT940
        $bic  = $this->pick($this->normalBics);
        $name = $this->bankName($bic);
        return [$bic, $name];
    }

    private function findLibyanSender(string $iban): array
    {
        foreach ($this->libyanSenders as $s) {
            if ($s['iban'] === $iban) {
                return $s;
            }
        }
        return ['address' => 'TRIPOLI - LIBYA', 'settlement' => 'LY57009001000000000007943'];
    }

    private function genTnIban(): string
    {
        return 'TN5910006035' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
    }

    // ---------------------------------------------------------------
    // NARRATIVES (tag 70 / tag 86 / tag 72)
    // ---------------------------------------------------------------

    private function narrative(string $type): string
    {
        if ($type === 'MT940') {
            $opts = [
                'CUSTOMER STATEMENT END OF DAY',
                'ACCOUNT STATEMENT MONTHLY CLOSING',
                'DAILY BALANCE STATEMENT REF ' . strtoupper(substr(md5((string) rand()), 0, 8)),
            ];
            return $opts[array_rand($opts)];
        }
        if ($type === 'MT202') {
            return 'INTERBANK SETTLEMENT REF ' . strtoupper(substr(md5((string) rand()), 0, 10));
        }

        $year  = date('Y');
        $month = date('F', mktime(0, 0, 0, rand(1, 12), 1));
        $invNb = sprintf('INV-%d-%04d', $year, rand(100, 9999));
        $ctrNb = sprintf('CTR-%04d-%04d', rand(1000, 9999), rand(1000, 9999));
        $stuId = sprintf('STU-%06d', rand(100000, 999999));
        $empId = sprintf('EMP-%06d', rand(100000, 999999));

        $opts = [
            "PAIEMENT FACTURE {$invNb} MATERIEL INDUSTRIEL {$month} {$year}",
            "PAIEMENT FACTURE {$invNb} EQUIPEMENT ELECTRONIQUE {$month} {$year}",
            "TUITION FEES ACADEMIC YEAR {$year}-" . ($year + 1) . " STUDENT ID {$stuId}",
            "PAYMENT OIL CONSULTING SERVICES CONTRACT {$ctrNb}",
            "SALARY PAYMENT MONTH {$month} {$year} EMPLOYEE REF {$empId}",
            "PAYMENT AIRCRAFT MAINTENANCE SERVICES {$invNb} Q" . rand(1, 4) . " {$year}",
            "REIMBURSEMENT EXPENSES REF-" . rand(1000, 9999),
            "EXPORT PROCEEDS SHIPMENT SHP-" . sprintf('%06d', rand(100000, 999999)) . " GOODS TEXTILE",
            "ADVANCE PAYMENT CONTRACT {$ctrNb} CIVIL ENGINEERING WORKS",
            "PAYMENT MACHINERY AND EQUIPMENT ORDER ORD-" . sprintf('%06d', rand(100000, 999999)),
            "SETTLEMENT TRADE FINANCE LETTER OF CREDIT LC-" . sprintf('%06d', rand(100000, 999999)),
            "DIVIDENDS PAYMENT FISCAL YEAR {$year} REF {$invNb}",
            "SUBSCRIPTION FEES PROFESSIONAL ASSOCIATION {$year}",
            "PAYMENT PHARMACEUTICAL PRODUCTS {$invNb} BATCH " . rand(1000, 9999),
        ];
        return $opts[array_rand($opts)];
    }

    // ---------------------------------------------------------------
    // GENERIC HELPERS
    // ---------------------------------------------------------------

    private function pick(array $arr): string
    {
        return $arr[array_rand($arr)];
    }

    private function rnd(float $min, float $max): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
    }

    private function categorie(string $type): string
    {
        if (str_starts_with($type, 'MT')) {
            return substr($type, 2, 1); // MT103->'1', MT202->'2', MT940->'9'
        }
        return match (true) {
            str_starts_with($type, 'PACS') => 'PACS',
            str_starts_with($type, 'CAMT') => 'CAMT',
            str_starts_with($type, 'PAIN') => 'PAIN',
            default => 'OTHER',
        };
    }

    private function bankName(string $bic): string
    {
        return [
            'BTLMTNTT'    => 'BTL TUNISIAN LIBYAN BANK',
            'BIATTNTT'    => 'BIAT BANQUE INTERNATIONALE ARABE DE TUNISIE',
            'ATTIJARX'    => 'ATTIJARI BANK TUNISIE',
            'BNATNTTX'    => 'BANQUE NATIONALE AGRICOLE',
            'UBCITNTT'    => 'UIB UNION INTERNATIONALE DE BANQUES',
            'STBKTNTT'    => 'STB SOCIETE TUNISIENNE DE BANQUE',
            'BNPAFRPP'    => 'BNP PARIBAS',
            'DEUTDEFF'    => 'DEUTSCHE BANK AG',
            'CITIUS33'    => 'CITIBANK NA',
            'BARCGB22'    => 'BARCLAYS BANK PLC',
            'SOGEFRPP'    => 'SOCIETE GENERALE',
            'HSBCGB2L'    => 'HSBC BANK PLC LONDON',
            'ATLDTNTTXXX' => 'ARAB TUNISIAN LIBYAN DEVELOPMENT BANK',
            'LAFBLYLTXXX' => 'LIBYAN ARAB FOREIGN BANK',
            'SHELBHBB'    => 'BELIZE SHELL BANK',
            'OFFSHKYY'    => 'CAYMAN OFFSHORE LTD',
            'PRVBKXXL'    => 'PRIVATE BANK XL',
            'MHCBJPJT'    => 'MIZUHO BANK TOKYO',
            'SABRRUMM'    => 'SBERBANK RUSSIA MOSCOW',
            'ADCBAEAA'    => 'ABU DHABI COMMERCIAL BANK UAE',
            'ICBKCNBJ'    => 'INDUSTRIAL AND COMMERCIAL BANK OF CHINA',
            'CHASUS33'    => 'JPMORGAN CHASE BANK NA NEW YORK',
            'UBSWCHZH'    => 'UBS AG SWITZERLAND',
        ][$bic] ?? $bic;
    }

    // ---------------------------------------------------------------
    // 30 INTERNATIONAL SCENARIOS — Scénarios AML internationaux réels
    // Pays : FR, DE, GB, US, LY, AE, CN, JP, RU, CH
    // Distribution : HIGH (9) + MEDIUM (11) + LOW (10) = 30
    // ---------------------------------------------------------------

    private function internationalScenarios(): array
    {
        $ly = $this->libyanSenders;

        return [

            // ===== HIGH (9) — rejetés, montants élevés, devises suspectes =====

            // INT-01: OFAC Russia — MT103 OUT RUB 3.2M Sberbank rejeté nuit (HIGH)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'BTLMTNTT',
                'senderName'  => 'BTL TUNISIAN LIBYAN BANK',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'SABRRUMM',
                'receiverName'=> 'SBERBANK RUSSIA MOSCOW',
                'amount'      => 3_200_000.00, 'currency' => 'RUB',
                'daysAgo'     => 3, 'hour' => 2,
                'raison'      => 'MONTANT_ELEVE+DEVISE_INHABITUELLE+STATUT_REJETE',
            ],

            // INT-02: Panama Papers — MT202 IN USD 8.5M offshore BVI rejeté (HIGH)
            [
                'type' => 'MT202', 'direction' => 'IN', 'status' => 'rejected',
                'senderBic'   => 'OFFSHKYY',
                'senderName'  => 'BVI CAPITAL MANAGEMENT LTD',
                'senderIban'  => 'VG96VPVG0000012345678901',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL TREASURY DEPT',
                'amount'      => 8_500_000.00, 'currency' => 'USD',
                'daysAgo'     => 5, 'hour' => 15,
                'raison'      => 'MONTANT_ELEVE+STATUT_REJETE',
            ],

            // INT-03: TBML UAE — MT103 OUT AED 620K Dubai shell nuit rejeté (HIGH)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'BIATTNTT',
                'senderName'  => 'BIAT BANQUE INTERNATIONALE ARABE DE TUNISIE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'ADCBAEAA',
                'receiverName'=> 'DUBAI SHELL TRADING LLC',
                'amount'      => 620_000.00, 'currency' => 'AED',
                'daysAgo'     => 2, 'hour' => 3,
                'raison'      => 'DEVISE_INHABITUELLE+MONTANT_ELEVE+STATUT_REJETE',
            ],

            // INT-04: Probe transaction zéro — MT103 IN 0 EUR Libye NOC rejeté (HIGH)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'rejected',
                'senderBic'   => $ly[0]['bic'],
                'senderName'  => $ly[0]['name'],
                'senderIban'  => $ly[0]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 0.00, 'currency' => 'EUR',
                'daysAgo'     => 1, 'hour' => 11,
                'raison'      => 'MONTANT_ZERO+STATUT_REJETE',
            ],

            // INT-05: China layering — MT103 OUT CNY 1.8M ICBC rejeté 3h (HIGH)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'STBKTNTT',
                'senderName'  => 'STB SOCIETE TUNISIENNE DE BANQUE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'ICBKCNBJ',
                'receiverName'=> 'ICBC INTERNATIONAL TRADING SHANGHAI',
                'amount'      => 1_800_000.00, 'currency' => 'CNY',
                'daysAgo'     => 4, 'hour' => 3,
                'raison'      => 'DEVISE_INHABITUELLE+MONTANT_ELEVE+STATUT_REJETE',
            ],

            // INT-06: Layering RUB — MT103 OUT RUB 5.5M banque privée rejeté (HIGH)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'UBCITNTT',
                'senderName'  => 'UIB UNION INTERNATIONALE DE BANQUES',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'PRVBKXXL',
                'receiverName'=> 'PRIVATE BANK XL OFFSHORE',
                'amount'      => 5_500_000.00, 'currency' => 'RUB',
                'daysAgo'     => 6, 'hour' => 1,
                'raison'      => 'MONTANT_ELEVE+DEVISE_INHABITUELLE+STATUT_REJETE',
            ],

            // INT-07: Financement suspect — MT103 IN 0 USD Belize rejeté (HIGH)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'rejected',
                'senderBic'   => 'SHELBHBB',
                'senderName'  => 'BELIZE SHELL FINANCIAL SA',
                'senderIban'  => 'BZ04BELZ00001000006300029',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL TUNISIAN LIBYAN BANK',
                'amount'      => 0.00, 'currency' => 'USD',
                'daysAgo'     => 2, 'hour' => 22,
                'raison'      => 'MONTANT_ZERO+STATUT_REJETE',
            ],

            // INT-08: Sanctions multiples — MT202 OUT USD 3.1M offshore rejeté nuit (HIGH)
            [
                'type' => 'MT202', 'direction' => 'OUT', 'status' => 'rejected',
                'senderBic'   => 'ATTIJARX',
                'senderName'  => 'ATTIJARI BANK TUNISIE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'PRVBKXXL',
                'receiverName'=> 'PRIVATE BANK XL',
                'amount'      => 3_100_000.00, 'currency' => 'USD',
                'daysAgo'     => 7, 'hour' => 0,
                'raison'      => 'MONTANT_ELEVE+STATUT_REJETE',
            ],

            // INT-09: MT940 JPY balance massive — IN JPY 18M rejeté (HIGH)
            [
                'type' => 'MT940', 'direction' => 'IN', 'status' => 'rejected',
                'senderBic'   => 'MHCBJPJT',
                'senderName'  => 'MIZUHO BANK TOKYO',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL NOSTRO ACCOUNT JPY',
                'amount'      => 18_000_000.00, 'currency' => 'JPY',
                'daysAgo'     => 8, 'hour' => 7,
                'raison'      => 'DEVISE_INHABITUELLE+MONTANT_ELEVE+STATUT_REJETE',
            ],

            // ===== MEDIUM (11) — pending/processed, montants élevés légitimes =====

            // INT-10: Deutsche Bank — MT103 IN USD 280K import acier (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'DEUTDEFF',
                'senderName'  => 'SIEMENS AG MUNICH',
                'senderIban'  => 'DE89370400440532013000',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 280_000.00, 'currency' => 'USD',
                'daysAgo'     => 10, 'hour' => 9,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-11: Libye NOC — MT103 IN USD 175K paiement pétrolier (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'pending',
                'senderBic'   => $ly[0]['bic'],
                'senderName'  => $ly[0]['name'],
                'senderIban'  => $ly[0]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 175_000.00, 'currency' => 'USD',
                'daysAgo'     => 3, 'hour' => 10,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-12: HSBC London — MT202 OUT GBP 450K interbank (MEDIUM)
            [
                'type' => 'MT202', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'BTLMTNTT',
                'senderName'  => 'BTL TUNISIAN LIBYAN BANK',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'HSBCGB2L',
                'receiverName'=> 'HSBC BANK PLC LONDON',
                'amount'      => 450_000.00, 'currency' => 'GBP',
                'daysAgo'     => 12, 'hour' => 11,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-13: Libye Libyan Iron Steel — MT103 IN USD 220K acier (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'pending',
                'senderBic'   => $ly[1]['bic'],
                'senderName'  => $ly[1]['name'],
                'senderIban'  => $ly[1]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 220_000.00, 'currency' => 'USD',
                'daysAgo'     => 4, 'hour' => 14,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-14: Citibank USA — MT103 OUT USD 320K énergie (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'BNATNTTX',
                'senderName'  => 'BANQUE NATIONALE AGRICOLE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'CITIUS33',
                'receiverName'=> 'EXXON MOBIL CORPORATION',
                'amount'      => 320_000.00, 'currency' => 'USD',
                'daysAgo'     => 15, 'hour' => 10,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-15: MT940 relevé BNP 750K EUR — IN journalier (MEDIUM)
            [
                'type' => 'MT940', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'BNPAFRPP',
                'senderName'  => 'BNP PARIBAS',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL NOSTRO EUR',
                'amount'      => 750_000.00, 'currency' => 'EUR',
                'daysAgo'     => 5, 'hour' => 8,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-16: Libye General Electricity — MT103 IN EUR 95K travaux (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'pending',
                'senderBic'   => $ly[2]['bic'],
                'senderName'  => $ly[2]['name'],
                'senderIban'  => $ly[2]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 95_000.00, 'currency' => 'EUR',
                'daysAgo'     => 6, 'hour' => 13,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-17: Barclays correspondant — MT202 IN EUR 200K (MEDIUM)
            [
                'type' => 'MT202', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'BARCGB22',
                'senderName'  => 'BARCLAYS BANK PLC',
                'senderIban'  => 'GB29NWBK60161331926819',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL NOSTRO GBP',
                'amount'      => 200_000.00, 'currency' => 'EUR',
                'daysAgo'     => 20, 'hour' => 10,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-18: Vinci Construction — MT103 OUT EUR 85K travaux BTP (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'ATTIJARX',
                'senderName'  => 'ATTIJARI BANK TUNISIE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'SOGEFRPP',
                'receiverName'=> 'VINCI CONSTRUCTION FRANCE',
                'amount'      => 85_000.00, 'currency' => 'EUR',
                'daysAgo'     => 8, 'hour' => 9,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-19: Tripoli Port Authority — MT103 IN USD 130K ports (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'pending',
                'senderBic'   => $ly[3]['bic'],
                'senderName'  => $ly[3]['name'],
                'senderIban'  => $ly[3]['iban'],
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL TREASURY DEPT',
                'amount'      => 130_000.00, 'currency' => 'USD',
                'daysAgo'     => 9, 'hour' => 11,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // INT-20: JPMorgan Chase — MT103 OUT USD 165K pétrochimie (MEDIUM)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'UBCITNTT',
                'senderName'  => 'UIB UNION INTERNATIONALE DE BANQUES',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'CHASUS33',
                'receiverName'=> 'JPMORGAN CHASE BANK NEW YORK',
                'amount'      => 165_000.00, 'currency' => 'USD',
                'daysAgo'     => 14, 'hour' => 12,
                'raison'      => 'MONTANT_ELEVE',
            ],

            // ===== LOW (10) — processed, montants normaux, EUR/USD/GBP =====

            // INT-21: Remittance salaire chercheur TN-France — IN EUR 2.8K (LOW)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'BNPAFRPP',
                'senderName'  => 'DUPONT ET FILS INDUSTRIES SA',
                'senderIban'  => 'FR7630006000011234567890143',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 2_800.00, 'currency' => 'EUR',
                'daysAgo'     => 30, 'hour' => 10,
                'raison'      => 'NORMAL',
            ],

            // INT-22: Facture fournisseur allemand Siemens — OUT EUR 12.5K (LOW)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'BTLMTNTT',
                'senderName'  => 'BTL TUNISIAN LIBYAN BANK',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'DEUTDEFF',
                'receiverName'=> 'SIEMENS AG MUNICH',
                'amount'      => 12_500.00, 'currency' => 'EUR',
                'daysAgo'     => 25, 'hour' => 11,
                'raison'      => 'NORMAL',
            ],

            // INT-23: Virement client Société Générale — IN EUR 8.5K (LOW)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'SOGEFRPP',
                'senderName'  => 'CREDIT AGRICOLE CORPORATE SA',
                'senderIban'  => 'FR7630003000701234567890185',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 8_500.00, 'currency' => 'EUR',
                'daysAgo'     => 20, 'hour' => 9,
                'raison'      => 'NORMAL',
            ],

            // INT-24: Frais académiques UK — OUT GBP 15.2K Université Manchester (LOW)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'BNATNTTX',
                'senderName'  => 'BANQUE NATIONALE AGRICOLE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'HSBCGB2L',
                'receiverName'=> 'UNIVERSITY OF MANCHESTER',
                'amount'      => 15_200.00, 'currency' => 'GBP',
                'daysAgo'     => 45, 'hour' => 14,
                'raison'      => 'NORMAL',
            ],

            // INT-25: Export produits agroalimentaires TN — IN USD 22.5K (LOW)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'CITIUS33',
                'senderName'  => 'JOHNSON AND JOHNSON INC',
                'senderIban'  => 'ACT6789012345',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 22_500.00, 'currency' => 'USD',
                'daysAgo'     => 35, 'hour' => 10,
                'raison'      => 'NORMAL',
            ],

            // INT-26: Prestations IT Suisse UBS — OUT CHF 18.9K (LOW)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'ATTIJARX',
                'senderName'  => 'ATTIJARI BANK TUNISIE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'UBSWCHZH',
                'receiverName'=> 'UBS SWITZERLAND IT SERVICES SA',
                'amount'      => 18_900.00, 'currency' => 'CHF',
                'daysAgo'     => 28, 'hour' => 10,
                'raison'      => 'NORMAL',
            ],

            // INT-27: Nostro BNP Paribas correspondant — MT202 IN EUR 145K (LOW)
            [
                'type' => 'MT202', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'BNPAFRPP',
                'senderName'  => 'BNP PARIBAS',
                'senderIban'  => 'FR7630006000011234567890143',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL NOSTRO EUR',
                'amount'      => 145_000.00, 'currency' => 'EUR',
                'daysAgo'     => 40, 'hour' => 9,
                'raison'      => 'NORMAL',
            ],

            // INT-28: Fournisseur textile Italie — OUT EUR 9.8K (LOW)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'BIATTNTT',
                'senderName'  => 'BIAT BANQUE INTERNATIONALE ARABE DE TUNISIE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'BNPAFRPP',
                'receiverName'=> 'COMPAGNIE SAINT GOBAIN',
                'amount'      => 9_800.00, 'currency' => 'EUR',
                'daysAgo'     => 50, 'hour' => 11,
                'raison'      => 'NORMAL',
            ],

            // INT-29: Remittance famille TN-France — IN EUR 1.2K (LOW)
            [
                'type' => 'MT103', 'direction' => 'IN', 'status' => 'processed',
                'senderBic'   => 'BNPAFRPP',
                'senderName'  => 'RENAULT FRANCE SA',
                'senderIban'  => 'FR7630006000011234567890189',
                'receiverBic' => 'BTLMTNTT',
                'receiverName'=> 'BTL CORRESPONDENT ACCOUNT',
                'amount'      => 1_200.00, 'currency' => 'EUR',
                'daysAgo'     => 60, 'hour' => 12,
                'raison'      => 'NORMAL',
            ],

            // INT-30: Formation professionnelle France AXA — OUT EUR 6.5K (LOW)
            [
                'type' => 'MT103', 'direction' => 'OUT', 'status' => 'processed',
                'senderBic'   => 'STBKTNTT',
                'senderName'  => 'STB SOCIETE TUNISIENNE DE BANQUE',
                'senderIban'  => $this->genTnIban(),
                'receiverBic' => 'SOGEFRPP',
                'receiverName'=> 'AXA SA PARIS',
                'amount'      => 6_500.00, 'currency' => 'EUR',
                'daysAgo'     => 55, 'hour' => 10,
                'raison'      => 'NORMAL',
            ],
        ];
    }
}