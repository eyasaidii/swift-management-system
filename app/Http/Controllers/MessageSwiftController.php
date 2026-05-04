<?php

namespace App\Http\Controllers;

use App\Helpers\SwiftParser;
use App\Jobs\AnalyzeAnomalyJob;
use App\Jobs\ProcessSwiftFileJob;
use App\Models\MessageSwift;
use App\Services\SwiftMtBuilder;
use App\Services\UniversalMtToMxConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class MessageSwiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================
    // LISTE — index()
    // =========================================================

    public function index(Request $request)
    {
        $user = Auth::user();

        $direction = match ($request->query('direction')) {
            'RECU' => 'IN',
            'EMIS' => 'OUT',
            default => null,
        };

        $isGlobal = $user->hasRole(['super-admin', 'swift-manager', 'swift-operator']);

        $query = $isGlobal
            ? MessageSwift::with('creator')
            : MessageSwift::with('creator')->readable($user);

        if ($direction) {
            $query->where('DIRECTION', $direction);
        }
        if ($request->filled('categorie')) {
            $query->where('CATEGORIE', $request->categorie);
        }
        if ($request->filled('type_message')) {
            $query->where('TYPE_MESSAGE', $request->type_message);
        }
        if ($request->filled('status')) {
            $query->where('STATUS', $request->status);
        }
        if ($request->filled('sender_bic')) {
            $query->where('SENDER_BIC', 'like', '%'.trim($request->sender_bic).'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('VALUE_DATE', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('VALUE_DATE', '<=', $request->date_to);
        }
        if ($request->filled('currency')) {
            $query->where('CURRENCY', $request->currency);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('REFERENCE', 'like', "%{$s}%")
                    ->orWhere('SENDER_NAME', 'like', "%{$s}%")
                    ->orWhere('RECEIVER_NAME', 'like', "%{$s}%")
                    ->orWhere('DESCRIPTION', 'like', "%{$s}%");
            });
        }

        $messages = $query->orderBy('CREATED_AT', 'desc')->paginate(20)->withQueryString();

        $base = $isGlobal ? MessageSwift::query() : MessageSwift::readable($user);

        $stats = [
            'total' => (clone $base)->count(),
            'in' => ($user->can('view-received-messages') || $user->hasRole('super-admin'))
                             ? (clone $base)->where('DIRECTION', 'IN')->count() : 0,
            'out' => ($user->can('view-emitted-messages') || $user->hasRole('super-admin'))
                             ? (clone $base)->where('DIRECTION', 'OUT')->count() : 0,
            'pending' => (clone $base)->where('STATUS', 'pending')->count(),
        ];

        $receivedCategories = $this->getSidebarCategories('IN');
        $emittedCategories = $this->getSidebarCategories('OUT');
        $receivedTotal = collect($receivedCategories)->sum('total');
        $emittedTotal = collect($emittedCategories)->sum('total');
        $types = MessageSwift::getAvailableTypes($user, $direction ?? 'IN');

        return view('swift.index', compact(
            'messages', 'stats', 'types',
            'receivedCategories', 'emittedCategories',
            'receivedTotal', 'emittedTotal'
        ));
    }

    // =========================================================
    // IMPORT
    // =========================================================

    public function importForm()
    {
        $this->authorize('import', MessageSwift::class);

        return view('swift.import');
    }

    public function import(Request $request)
    {
        $this->authorize('import', MessageSwift::class);

        $request->validate([
            'file' => 'required|file|mimes:xml,txt|max:10240',
        ]);

        $file = $request->file('file');
        $xmlContent = file_get_contents($file->getRealPath());

        if ($xmlContent === false || trim($xmlContent) === '') {
            return back()->with('error', 'Le fichier est vide ou illisible.');
        }

        $storedPath = $file->store('swift_imports');
        $fullPath = storage_path('app/'.$storedPath);

        if (app()->environment('local') || config('queue.default') === 'sync') {
            ProcessSwiftFileJob::dispatchSync($fullPath, auth()->id(), $xmlContent);
        } else {
            ProcessSwiftFileJob::dispatch($fullPath, auth()->id(), $xmlContent);
        }

        return redirect()->route('swift.index')
            ->with('success', 'Import lancé. Le message apparaîtra dans "Received Messages" avec le statut En attente.');
    }

    // =========================================================
    // DÉTAIL
    // =========================================================

    public function show($id)
    {
        $message = MessageSwift::with(['creator', 'details', 'transaction', 'anomaly'])->findOrFail($id); // ← 'anomaly' ajouté
        abort_unless($message->isReadableBy(auth()->user()), 403,
            'Vous n\'êtes pas autorisé à voir ce message.');

        return view('swift.show', compact('message'));
    }

    // =========================================================
    // CRÉATION
    // =========================================================

    public function create()
    {
        $types = MessageSwift::getAvailableTypes(auth()->user(), 'OUT');
        $swiftFields = Config::get('swift_fields', []);

        return view('swift.create', compact('types', 'swiftFields'));
    }

    public function getFields(string $type)
    {
        $fields = Config::get("swift_fields.{$type}.fields", []);

        return response()->json($fields);
    }

    // =========================================================
    // STORE — avec création automatique de la transaction
    // =========================================================

    public function store(Request $request)
    {
        $user = auth()->user();
        $availableTypes = MessageSwift::getAvailableTypes($user, 'OUT');
        $type = $request->type_message;

        $request->validate([
            'type_message' => 'required|in:'.implode(',', array_keys($availableTypes)),
        ]);

        $fieldsConfig = Config::get("swift_fields.{$type}.fields", []);
        $rules = [];
        foreach ($fieldsConfig as $tag => $config) {
            $rules["details.{$tag}"] = ($config['required'] ? 'required' : 'nullable').'|string|max:5000';
        }
        $request->validate($rules);

        // Vérifier l'unicité de la référence (tag 20)
        $reference = $request->input('details.20');
        if ($reference && MessageSwift::where('REFERENCE', $reference)->exists()) {
            return redirect()->back()
                ->withErrors(['details.20' => 'Cette référence existe déjà. Veuillez utiliser une référence unique.'])
                ->withInput();
        }

        $message = MessageSwift::create([
            'TYPE_MESSAGE' => $type,
            'DIRECTION' => 'OUT',
            'STATUS' => 'pending',
            'CREATED_BY' => $user->id,
            'CREATED_AT' => now(),
        ]);

        $details = $request->input('details', []);
        foreach ($details as $tag => $value) {
            if (! empty($value)) {
                $message->details()->create(['tag_name' => $tag, 'tag_value' => $value]);
            }
        }

        $commonMapping = Config::get("swift_fields.{$type}.common_mapping", []);
        $updateData = [];

        foreach ($commonMapping as $field => $tag) {
            $value = $details[$tag] ?? null;
            if (! $value) {
                continue;
            }

            if ($tag === '32A') {
                [$date, $currency, $amount] = SwiftParser::parse32A($value);
                if ($date) {
                    $updateData['VALUE_DATE'] = $date;
                }
                if ($currency) {
                    $updateData['CURRENCY'] = $currency;
                }
                if ($amount) {
                    $updateData['AMOUNT'] = $amount;
                }
            } elseif ($tag === '32B') {
                [$currency, $amount] = SwiftParser::parse32B($value);
                if ($currency) {
                    $updateData['CURRENCY'] = $currency;
                }
                if ($amount) {
                    $updateData['AMOUNT'] = $amount;
                }
            } elseif (in_array($tag, ['60F', '62F'])) {
                [$date, $currency, $amount] = SwiftParser::parseBalance($value);
                if ($date) {
                    $updateData['VALUE_DATE'] = $date;
                }
                if ($currency) {
                    $updateData['CURRENCY'] = $currency;
                }
                if ($amount) {
                    $updateData['AMOUNT'] = $amount;
                }
            } else {
                $updateData[$field] = $value;
            }
        }

        if (empty($updateData['AMOUNT']) && ! empty($details['32A'])) {
            [$date, $currency, $amount] = SwiftParser::parse32A($details['32A']);
            if ($date) {
                $updateData['VALUE_DATE'] = $updateData['VALUE_DATE'] ?? $date;
            }
            if ($currency) {
                $updateData['CURRENCY'] = $updateData['CURRENCY'] ?? $currency;
            }
            if ($amount) {
                $updateData['AMOUNT'] = $updateData['AMOUNT'] ?? $amount;
            }
        }

        if (empty($updateData['AMOUNT']) && ! empty($details['32B'])) {
            [$currency, $amount] = SwiftParser::parse32B($details['32B']);
            if ($currency) {
                $updateData['CURRENCY'] = $updateData['CURRENCY'] ?? $currency;
            }
            if ($amount) {
                $updateData['AMOUNT'] = $updateData['AMOUNT'] ?? $amount;
            }
        }

        if (empty($updateData['SENDER_NAME']) && ! empty($details['50'])) {
            $updateData['SENDER_NAME'] = $details['50'];
        }
        if (empty($updateData['SENDER_NAME']) && ! empty($details['25'])) {
            $updateData['SENDER_NAME'] = $details['25'];
        }
        if (empty($updateData['RECEIVER_NAME']) && ! empty($details['59'])) {
            $updateData['RECEIVER_NAME'] = $details['59'];
        }
        if (empty($updateData['RECEIVER_NAME']) && ! empty($details['58A'])) {
            $updateData['RECEIVER_NAME'] = $details['58A'];
        }
        if (empty($updateData['SENDER_BIC']) && ! empty($details['52A'])) {
            $updateData['SENDER_BIC'] = $details['52A'];
        }
        if (empty($updateData['RECEIVER_BIC']) && ! empty($details['57A'])) {
            $updateData['RECEIVER_BIC'] = $details['57A'];
        }
        if (empty($updateData['DESCRIPTION']) && ! empty($details['72'])) {
            $updateData['DESCRIPTION'] = $details['72'];
        }
        if (empty($updateData['DESCRIPTION']) && ! empty($details['70'])) {
            $updateData['DESCRIPTION'] = $details['70'];
        }
        if (empty($updateData['DESCRIPTION']) && ! empty($details['45A'])) {
            $updateData['DESCRIPTION'] = $details['45A'];
        }

        if (! empty($updateData['SENDER_BIC'])) {
            $updateData['SENDER_BIC'] = substr(trim($updateData['SENDER_BIC']), 0, 11);
        }
        if (! empty($updateData['RECEIVER_BIC'])) {
            $updateData['RECEIVER_BIC'] = substr(trim($updateData['RECEIVER_BIC']), 0, 11);
        }
        if (! empty($updateData['CURRENCY'])) {
            $updateData['CURRENCY'] = substr(strtoupper(trim($updateData['CURRENCY'])), 0, 3);
        }
        if (! empty($updateData['SENDER_NAME'])) {
            $updateData['SENDER_NAME'] = substr(trim($updateData['SENDER_NAME']), 0, 255);
        }
        if (! empty($updateData['RECEIVER_NAME'])) {
            $updateData['RECEIVER_NAME'] = substr(trim($updateData['RECEIVER_NAME']), 0, 255);
        }
        if (! empty($updateData['REFERENCE'])) {
            $updateData['REFERENCE'] = substr(trim($updateData['REFERENCE']), 0, 100);
        }

        $updateData['CATEGORIE'] = $message->determineCategorie();

        if (! empty($updateData)) {
            $message->update($updateData);
        } else {
            $message->CATEGORIE = $message->determineCategorie();
            $message->save();
        }

        // Création directe de la transaction
        try {
            $txAmount = (float) ($updateData['AMOUNT'] ?? $message->AMOUNT ?? $message->amount ?? 0);
            $txCurrency = $updateData['CURRENCY'] ?? $message->CURRENCY ?? $message->currency ?? null;
            $txDate = $updateData['VALUE_DATE'] ?? $message->VALUE_DATE ?? $message->value_date ?? now();
            $txReceiver = $updateData['RECEIVER_NAME']
                       ?? $message->RECEIVER_NAME
                       ?? $message->receiver_name
                       ?? $details['59'] ?? $details['57A'] ?? 'Bénéficiaire externe';

            if ($txAmount > 0 && $txCurrency) {
                \App\Models\Transaction::updateOrCreate(
                    ['message_swift_id' => $message->id],
                    [
                        'montant' => $txAmount,
                        'devise' => $txCurrency,
                        'emetteur' => 'BTL Bank',
                        'recepteur' => $txReceiver,
                        'date_transaction' => $txDate,
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Log::warning("Transaction OUT non créée #{$message->id} : {$e->getMessage()}");
        }

        // Génération du XML
        try {
            $message->load('details');
            $xmlContent = app(UniversalMtToMxConverter::class)->convert($message);
            if ($xmlContent) {
                $message->update(['XML_BRUT' => $xmlContent]);
            }
        } catch (\Throwable $e) {
            \Log::warning("Échec génération XML #{$message->id} : {$e->getMessage()}");
        }

        // Génération du MT_CONTENT (enveloppe SWIFT complète)
        try {
            $mtContent = app(SwiftMtBuilder::class)->build($message, $details);
            if ($mtContent) {
                $message->update(['MT_CONTENT' => $mtContent]);
            }
        } catch (\Throwable $e) {
            \Log::warning("Échec génération MT_CONTENT #{$message->id} : {$e->getMessage()}");
        }

        // =========================================================
        // ANALYSE IA — Géré automatiquement par MessageSwiftObserver
        // (dispatche AnalyzeAnomalyJob quand les champs financiers changent)
        // =========================================================

        return redirect()->route('swift.index')
            ->with('success', 'Message SWIFT émis créé avec succès !');
    }

    // =========================================================
    // TRAITEMENT — process()
    // =========================================================

    public function process($id)
    {
        $message = MessageSwift::findOrFail($id);
        $user = Auth::user();

        if ($message->status !== 'pending') {
            $statusText = $message->status === 'processed' ? 'déjà traité' : 'déjà rejeté';

            return back()->with('error', "Ce message est {$statusText}.");
        }

        $direction = $message->DIRECTION ?? $message->direction ?? null;

        $canProcess =
            $user->hasRole('super-admin')
            || $user->hasRole('swift-manager')
            || ($user->hasRole(['chef-agence', 'chargee']) && $direction === 'OUT');

        if (! $canProcess) {
            abort(403, 'Vous n\'êtes pas autorisé à traiter ce message.');
        }

        $message->update(['STATUS' => 'processed', 'PROCESSED_AT' => now()]);

        // =========================================================
        // ANALYSE IA — Dispatché en arrière-plan (non bloquant)
        // =========================================================
        AnalyzeAnomalyJob::dispatch($message->id)->onQueue('default');
        // =========================================================

        $reference = $message->REFERENCE ?? $message->reference ?? "#{$id}";

        return redirect()->route($this->dashboardRoute($user))
            ->with('success', "Message {$reference} traité avec succès.");
    }

    // =========================================================
    // REJET — reject()
    // =========================================================

    public function reject($id)
    {
        $message = MessageSwift::findOrFail($id);
        $user = Auth::user();

        if ($message->status !== 'pending') {
            return back()->with('error', 'Ce message n\'est pas en attente.');
        }

        $direction = $message->DIRECTION ?? $message->direction ?? null;

        $canReject =
            $user->hasRole('super-admin')
            || $user->hasRole('swift-manager')
            || ($user->hasRole(['chef-agence', 'chargee']) && $direction === 'OUT');

        if (! $canReject) {
            abort(403, 'Vous n\'êtes pas autorisé à rejeter ce message.');
        }

        $message->update(['STATUS' => 'rejected', 'PROCESSED_AT' => now()]);

        // =========================================================
        // ANALYSE IA — Dispatché en arrière-plan (non bloquant)
        // =========================================================
        AnalyzeAnomalyJob::dispatch($message->id)->onQueue('default');
        // =========================================================

        $reference = $message->REFERENCE ?? $message->reference ?? "#{$id}";

        return redirect()->route($this->dashboardRoute($user))
            ->with('error', "Message {$reference} rejeté.");
    }

    // =========================================================
    // AUTORISER — approveMessage()
    // =========================================================

    public function approveMessage($id)
    {
        $message = MessageSwift::findOrFail($id);
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Seul le Swift Manager (ou super-admin) peut autoriser un message.');
        }

        if ($message->status !== 'processed') {
            return back()->with('error',
                'Ce message doit être à l\'état "Traité" pour être autorisé.');
        }

        $message->update([
            'STATUS' => 'authorized',
            'AUTHORIZED_BY' => $user->id,
            'AUTHORIZED_AT' => now(),
            'AUTHORIZATION_NOTE' => request('note'),
        ]);

        $reference = $message->REFERENCE ?? $message->reference ?? "#{$id}";

        return redirect()->route($this->dashboardRoute($user))
            ->with('success', "Virement {$reference} autorisé.");
    }

    // =========================================================
    // SUSPENDRE — suspend()
    // =========================================================

    public function suspend($id)
    {
        $message = MessageSwift::findOrFail($id);
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        if (! in_array($message->status, ['processed', 'authorized'])) {
            return back()->with('error', 'Ce message ne peut pas être suspendu.');
        }

        $message->update([
            'STATUS' => 'suspended',
            'AUTHORIZED_BY' => $user->id,
            'AUTHORIZED_AT' => now(),
            'AUTHORIZATION_NOTE' => request('note', 'Suspendu par le Swift Manager'),
        ]);

        $reference = $message->REFERENCE ?? $message->reference ?? "#{$id}";

        return redirect()->route($this->dashboardRoute($user))
            ->with('error', "Message {$reference} suspendu.");
    }

    // =========================================================
    // SUPPRESSION
    // =========================================================

    public function destroy($id)
    {
        MessageSwift::findOrFail($id)->delete();

        return redirect()->route('swift.index')->with('success', 'Message supprimé avec succès.');
    }

    // =========================================================
    // VIEW MX / MT
    // =========================================================

    public function viewMx($id)
    {
        $message = MessageSwift::with('details')->findOrFail($id);
        $xml = $message->XML_BRUT ?? $message->xml_brut ?? null;

        // Génération à la volée si XML absent
        if (empty($xml)) {
            try {
                $xml = app(UniversalMtToMxConverter::class)->convert($message);
                if ($xml) {
                    $message->update(['XML_BRUT' => $xml]);
                }
            } catch (\Throwable $e) {
                \Log::warning("Génération XML à la volée échouée #{$id} : {$e->getMessage()}");
            }
        }

        if (empty($xml)) {
            $ref = $message->REFERENCE ?? $message->reference ?? $id;

            return Response::make(
                '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                "<!-- MX non disponible pour le message {$ref}. -->",
                200, ['Content-Type' => 'application/xml; charset=UTF-8']
            );
        }

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="mx_'.($message->REFERENCE ?? $id).'.xml"',
        ]);
    }

    public function viewMt($id)
    {
        $message = MessageSwift::with('details')->findOrFail($id);
        $mt = $message->MT_CONTENT ?? $message->mt_content ?? null;

        // Génération à la volée si MT absent
        if (empty($mt)) {
            try {
                $details = $message->details->pluck('tag_value', 'tag_name')->toArray();
                $mt = app(SwiftMtBuilder::class)->build($message, $details);
                if ($mt) {
                    $message->update(['MT_CONTENT' => $mt]);
                }
            } catch (\Throwable $e) {
                \Log::warning("Génération MT à la volée échouée #{$id} : {$e->getMessage()}");
            }
        }

        if (empty($mt)) {
            $ref = $message->REFERENCE ?? $message->reference ?? $id;

            return Response::make(
                "MT non disponible pour le message {$ref}.\nLe contenu MT est généré après traitement.",
                200, ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        return Response::make($mt, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="mt_'.($message->REFERENCE ?? $id).'.txt"',
        ]);
    }

    public function downloadPdf($id)
    {
        $message = MessageSwift::with('details', 'creator')->findOrFail($id);

        abort_unless($message->isReadableBy(auth()->user()), 403,
            'Vous n\'êtes pas autorisé à télécharger ce document.');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('swift.pdf-transaction', compact('message'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 150,
            ]);

        $reference = $message->REFERENCE ?? $message->reference ?? $id;
        $filename = 'BTL_SWIFT_'.$reference.'_'.now()->format('Ymd').'.pdf';

        try {
            \DB::table('export_jobs')->insert([
                'format' => 'pdf',
                'date_demande' => now(),
                'statut' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
        }

        return $pdf->download($filename);
    }

    // =========================================================
    // EXPORT — CSV ou Excel selon ?format=xlsx|csv
    // =========================================================

    public function export(Request $request)
    {
        $user = Auth::user();
        $isGlobal = $user->hasRole(['super-admin', 'swift-manager', 'swift-operator']);
        $query = $isGlobal ? MessageSwift::query() : MessageSwift::readable($user);

        if ($request->filled('direction')) {
            $query->where('DIRECTION', $request->direction === 'RECU' ? 'IN' : 'OUT');
        }
        if ($request->filled('status')) {
            $query->where('STATUS', $request->status);
        }
        if ($request->filled('currency')) {
            $query->where('CURRENCY', $request->currency);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('VALUE_DATE', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('VALUE_DATE', '<=', $request->date_to);
        }

        $messages = $query->orderBy('CREATED_AT', 'desc')->get();

        try {
            DB::table('export_jobs')->insert([
                'format' => $request->get('format', 'xlsx'),
                'date_demande' => now(),
                'statut' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::info('ExportJob non tracé : '.$e->getMessage());
        }

        if ($request->get('format', 'xlsx') === 'csv') {
            return $this->exportCsv($messages);
        }

        return $this->exportExcel($messages, $user);
    }

    // =========================================================
    // EXPORT EXCEL — PhpSpreadsheet
    // =========================================================

    private function exportExcel($messages, $user)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Messages SWIFT');

        // Insert BTL logo if available (inline image)
        try {
            $logoPath = public_path('images/logo-btl.png');
            if (file_exists($logoPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
                $img = @imagecreatefrompng($logoPath);
                if ($img) {
                    ob_start();
                    imagepng($img);
                    $imgData = ob_get_clean();
                    imagedestroy($img);
                    $gdImage = imagecreatefromstring($imgData);
                    $drawing->setImageResource($gdImage);
                    $drawing->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_PNG);
                    $drawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG);
                    $drawing->setHeight(36);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                }
            }
        } catch (\Throwable $e) {
            // ignore image errors
        }

        $greenBtl = '1A5C38';
        $greenLight = 'E8F5E9';
        $white = 'FFFFFF';
        $grayLight = 'F5F5F5';

        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'BTL Bank — Tunisian Libyan Bank');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => $greenBtl]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'Export Messages SWIFT — Généré le '.now()->format('d/m/Y à H:i').' par '.$user->name);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => $greenBtl]],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(8);

        $headers = [
            'A' => 'DATE',        'B' => 'TYPE',             'C' => 'DIRECTION',
            'D' => 'RÉFÉRENCE',   'E' => 'ÉMETTEUR',         'F' => 'BIC ÉMETTEUR',
            'G' => 'BÉNÉFICIAIRE', 'H' => 'BIC BÉNÉFICIAIRE', 'I' => 'MONTANT',
            'J' => 'DEVISE',      'K' => 'DATE VALEUR',      'L' => 'STATUT',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'4', $label);
        }
        $sheet->getStyle('A4:L4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $white], 'size' => 11],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => $greenBtl]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => $white]]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $row = 5;
        foreach ($messages as $i => $m) {
            $bg = ($i % 2 === 0) ? $white : $grayLight;
            $sheet->setCellValue('A'.$row, optional($m->CREATED_AT ?? $m->created_at)->format('d/m/Y H:i') ?? '—');
            $sheet->setCellValue('B'.$row, $m->TYPE_MESSAGE ?? $m->type_message ?? '—');
            $sheet->setCellValue('C'.$row, ($m->DIRECTION ?? $m->direction) === 'IN' ? 'REÇU' : 'ÉMIS');
            $sheet->setCellValue('D'.$row, $m->REFERENCE ?? $m->reference ?? '—');
            $sheet->setCellValue('E'.$row, $m->SENDER_NAME ?? $m->sender_name ?? '—');
            $sheet->setCellValue('F'.$row, $m->SENDER_BIC ?? $m->sender_bic ?? '—');
            $sheet->setCellValue('G'.$row, $m->RECEIVER_NAME ?? $m->receiver_name ?? '—');
            $sheet->setCellValue('H'.$row, $m->RECEIVER_BIC ?? $m->receiver_bic ?? '—');
            $sheet->setCellValue('I'.$row, (float) ($m->AMOUNT ?? $m->amount ?? 0));
            $sheet->setCellValue('J'.$row, $m->CURRENCY ?? $m->currency ?? '—');
            $sheet->setCellValue('K'.$row, optional($m->VALUE_DATE ?? $m->value_date)->format('d/m/Y') ?? '—');
            $status = $m->STATUS ?? $m->status ?? '—';
            $statusNormalized = strtolower($status);
            $sheet->setCellValue('L'.$row, strtoupper($status));

            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => $bg]],
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'E0E0E0']]],
                'alignment' => ['vertical' => 'center'],
            ]);
            // Format amount with space thousands and comma decimals for francophone format
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('# ##0,00');
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal('right');

            $statusColor = match ($statusNormalized) {
                'authorized' => $greenBtl,
                'processed' => '1565C0',
                'pending' => 'F57F17',
                'suspended' => 'E53935',
                'rejected' => 'E53935',
                default => '757575',
            };
            $sheet->getStyle("L{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $white]],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => $statusColor]],
                'alignment' => ['horizontal' => 'center'],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        // Apply final amount formatting on the whole column (in case no rows)
        $lastDataRow = max(5, $row - 1);
        $sheet->getStyle("I5:I{$lastDataRow}")->getNumberFormat()->setFormatCode('# ##0,00');
        $sheet->getStyle("I5:I{$lastDataRow}")->getAlignment()->setHorizontal('right');

        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL — '.count($messages).' message(s)');
        $sheet->setCellValue("I{$row}", '=SUM(I5:I'.($row - 1).')');
        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => $greenBtl]],
        ]);
        $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getRowDimension($row)->setRowHeight(20);

        foreach ([
            'A' => 18, 'B' => 12, 'C' => 10, 'D' => 20,
            'E' => 30, 'F' => 14, 'G' => 30, 'H' => 14,
            'I' => 14, 'J' => 8,  'K' => 14, 'L' => 14,
        ] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A5');
        $sheet->setAutoFilter('A4:L4');

        $summary = $spreadsheet->createSheet();
        $summary->setTitle('Résumé');

        $summary->mergeCells('A1:B1');
        $summary->setCellValue('A1', 'Résumé de l\'export');
        $summary->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => $greenBtl]],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $summary->getRowDimension(1)->setRowHeight(25);

        $summaryData = [
            ['Total messages',  count($messages)],
            ['Messages reçus',  $messages->where('DIRECTION', 'IN')->count() ?: $messages->where('direction', 'IN')->count()],
            ['Messages émis',   $messages->where('DIRECTION', 'OUT')->count() ?: $messages->where('direction', 'OUT')->count()],
            ['Autorisés',       $messages->whereIn('STATUS', ['authorized'])->count() ?: $messages->whereIn('status', ['authorized'])->count()],
            ['Traités',         $messages->whereIn('STATUS', ['processed'])->count() ?: $messages->whereIn('status', ['processed'])->count()],
            ['En attente',      $messages->whereIn('STATUS', ['pending'])->count() ?: $messages->whereIn('status', ['pending'])->count()],
            ['Suspendus',       $messages->whereIn('STATUS', ['suspended'])->count() ?: $messages->whereIn('status', ['suspended'])->count()],
            ['Rejetés',         $messages->whereIn('STATUS', ['rejected'])->count() ?: $messages->whereIn('status', ['rejected'])->count()],
            ['Volume USD',      number_format($messages->where('CURRENCY', 'USD')->sum('AMOUNT') ?: $messages->where('currency', 'USD')->sum('amount'), 2).' USD'],
            ['Volume EUR',      number_format($messages->where('CURRENCY', 'EUR')->sum('AMOUNT') ?: $messages->where('currency', 'EUR')->sum('amount'), 2).' EUR'],
            ['Généré par',      $user->name],
            ['Date export',     now()->format('d/m/Y H:i')],
        ];

        foreach ($summaryData as $i => $line) {
            $r = $i + 2;
            $bg = ($i % 2 === 0) ? $greenLight : $white;
            $summary->setCellValue('A'.$r, $line[0]);
            $summary->setCellValue('B'.$r, $line[1]);
            $summary->getStyle("A{$r}:B{$r}")->applyFromArray([
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => str_replace('#', '', $bg)]],
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'E0E0E0']]],
            ]);
            $summary->getStyle("A{$r}")->getFont()->setBold(true);
            $summary->getRowDimension($r)->setRowHeight(18);
        }
        $summary->getColumnDimension('A')->setWidth(22);
        $summary->getColumnDimension('B')->setWidth(20);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'BTL_SWIFT_Export_'.now()->format('Ymd_His').'.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // =========================================================
    // EXPORT CSV
    // =========================================================

    private function exportCsv($messages)
    {
        $filename = 'BTL_SWIFT_Export_'.now()->format('Ymd_His').'.csv';

        return Response::stream(function () use ($messages) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'DATE', 'TYPE', 'DIRECTION', 'RÉFÉRENCE',
                'ÉMETTEUR', 'BIC ÉMETTEUR', 'DESTINATAIRE', 'BIC DESTINATAIRE',
                'MONTANT', 'DEVISE', 'DATE VALEUR', 'STATUT',
            ]);

            foreach ($messages as $m) {
                fputcsv($handle, [
                    optional($m->CREATED_AT ?? $m->created_at)->format('d/m/Y H:i'),
                    $m->TYPE_MESSAGE ?? $m->type_message,
                    ($m->DIRECTION ?? $m->direction) === 'IN' ? 'REÇU' : 'ÉMIS',
                    $m->REFERENCE ?? $m->reference,
                    $m->SENDER_NAME ?? $m->sender_name,
                    $m->SENDER_BIC ?? $m->sender_bic,
                    $m->RECEIVER_NAME ?? $m->receiver_name,
                    $m->RECEIVER_BIC ?? $m->receiver_bic,
                    number_format((float) ($m->AMOUNT ?? $m->amount ?? 0), 2, '.', ''),
                    $m->CURRENCY ?? $m->currency,
                    optional($m->VALUE_DATE ?? $m->value_date)->format('d/m/Y'),
                    $m->STATUS ?? $m->status,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // =========================================================
    // SIDEBAR — getSidebarCategories()
    // =========================================================

    public function getSidebarCategories(string $direction): array
    {
        $categories = MessageSwift::where('DIRECTION', $direction)
            ->whereNotNull('CATEGORIE')
            ->where('CATEGORIE', '!=', '')
            ->distinct()
            ->pluck('CATEGORIE')
            ->toArray();

        if (empty($categories)) {
            $categories = ['PACS', 'CAMT', '1', '2', '3', '4', '5', '7', '9'];
        }

        $result = [];

        foreach ($categories as $cat) {

            $typesData = MessageSwift::where('DIRECTION', $direction)
                ->where('CATEGORIE', $cat)
                ->selectRaw('TYPE_MESSAGE, COUNT(*) as total_count')
                ->groupBy('TYPE_MESSAGE')
                ->get();

            $types = $typesData->map(function ($row) {
                $attrs = $row->getAttributes();

                $typeName = $attrs['TYPE_MESSAGE']
                         ?? $attrs['type_message']
                         ?? null;

                $count = (int) (
                    $attrs['total_count']
                 ?? $attrs['TOTAL_COUNT']
                 ?? 0
                );

                return [
                    'type' => (string) ($typeName ?? ''),
                    'count' => $count,
                ];

            })->filter(fn ($item) => $item['type'] !== '')
                ->values()
                ->toArray();

            $result[] = [
                'category' => $cat,
                'name' => $this->getCategoryDisplayName($cat),
                'total' => array_sum(array_column($types, 'count')),
                'types' => $types,
            ];
        }

        return $result;
    }

    // =========================================================
    // NOM AFFICHAGE CATÉGORIE
    // =========================================================

    private function getCategoryDisplayName(string $cat): string
    {
        return match ($cat) {
            'PACS' => 'CATEGORY PACS — Paiements',
            'CAMT' => 'CATEGORY CAMT — Relevés',
            '1' => 'CATEGORY 1 — Paiements Client',
            '2' => 'CATEGORY 2 — Transferts Financiers',
            '3' => 'CATEGORY 3 — Trésorerie & Marchés',
            '4' => 'CATEGORY 4 — Encaissements',
            '5' => 'CATEGORY 5 — Titres',
            '7' => 'CATEGORY 7 — Crédits Documentaires',
            '9' => 'CATEGORY 9 — Relevés de Compte',
            default => "CATEGORY {$cat}",
        };
    }

    // =========================================================
    // PRIVÉ — Route dashboard selon rôle
    // =========================================================

    private function dashboardRoute($user): string
    {
        return match ($user->getRoleNames()->first()) {
            'admin' => 'admin.dashboard',
            'international-admin' => 'international-admin.dashboard',
            'international-user' => 'international-user.dashboard',
            'super-admin' => 'admin.dashboard',
            'swift-manager' => 'international-admin.dashboard',
            'swift-operator' => 'international-user.dashboard',
            'backoffice' => 'backoffice.dashboard',
            'chef-agence' => 'chef-agence.dashboard',
            'chargee' => 'chargee.dashboard',
            default => 'swift.index',
        };
    }
}
