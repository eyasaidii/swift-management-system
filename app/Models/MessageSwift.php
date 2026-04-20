<?php

// app/Models/MessageSwift.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleXMLElement;

class MessageSwift extends Model
{
    use HasFactory;

    protected $table = 'messages_swift';

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $primaryKey = 'id';

    const TYPES = [
        'MT103' => 'MT103 - Paiement client',
        'MT101' => 'MT101 - Demande de transfert',
        'MT202' => 'MT202 - Transfert interbancaire',
        'MT210' => 'MT210 - Avis d\'encaissement',
        'MT300' => 'MT300 - Confirmation de change',
        'MT320' => 'MT320 - Prêt/Emprunt',
        'MT700' => 'MT700 - Crédit documentaire',
        'MT760' => 'MT760 - Garantie / SBLC',
        'MT940' => 'MT940 - Relevé de compte détaillé',
        'MT900' => 'MT900 - Avis de débit',
        'MT910' => 'MT910 - Avis de crédit',
        'PACS.008' => 'PACS.008 - Paiement (ISO 20022)',
    ];

    const CATEGORIES = [
        'PACS' => 'PACS Messages',
        'CAMT' => 'CAMT Messages',
        '1' => 'Category 1 - Paiements client',
        '2' => 'Category 2 - Transferts financiers',
        '3' => 'Category 3 - Trésorerie',
        '4' => 'Category 4 - Encaissements',
        '5' => 'Category 5 - Titres',
        '7' => 'Category 7 - Crédits documentaires',
        '9' => 'Category 9 - Comptes et relevés',
    ];

    const DIRECTION = [
        'IN' => 'Reçu',
        'OUT' => 'Émis',
    ];

    const STATUS = [
        'pending' => 'En attente',
        'processed' => 'Traité',
        'rejected' => 'Rejeté',
        'cancelled' => 'Annulé',
    ];

    // =========================================================
    // FILLABLE — TOUTES les colonnes de MESSAGES_SWIFT
    // =========================================================
    protected $fillable = [
        'TYPE_MESSAGE',
        'CATEGORIE',
        'REFERENCE',
        'XML_BRUT',
        'MT_CONTENT',
        'IMPORT_JOB_ID',
        'DIRECTION',
        'SENDER_BIC',
        'RECEIVER_BIC',
        'SENDER_ACCOUNT',
        'RECEIVER_ACCOUNT',
        'SENDER_NAME',
        'RECEIVER_NAME',
        'AMOUNT',
        'CURRENCY',
        'VALUE_DATE',
        'DESCRIPTION',
        'STATUS',
        'CREATED_BY',
        'PROCESSED_AT',
        'METADATA',
        'TRANSLATION_ERRORS',
        'AUTHORIZED_BY',        // ← FIX : note autorisation
        'AUTHORIZED_AT',        // ← FIX : date autorisation
        'AUTHORIZATION_NOTE',   // ← FIX : note stockée en base
    ];

    // =========================================================
    // CASTS
    // =========================================================
    protected $casts = [
        'amount' => 'decimal:2',
        'value_date' => 'date',
        'processed_at' => 'datetime',
        'authorized_at' => 'datetime',   // ← FIX
        'metadata' => 'array',
        'translation_errors' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =========================================================
    // ACCESSEURS
    // =========================================================

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getProcessedAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getAuthorizedAtAttribute($value)         // ← FIX
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getValueDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    // =========================================================
    // MUTATEURS
    // =========================================================

    public function setCreatedAtAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['CREATED_AT'] = $value->format('Y-m-d H:i:s');
        } else {
            $this->attributes['CREATED_AT'] = $value;
        }
    }

    public function setUpdatedAtAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['UPDATED_AT'] = $value->format('Y-m-d H:i:s');
        } else {
            $this->attributes['UPDATED_AT'] = $value;
        }
    }

    public function setProcessedAtAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['PROCESSED_AT'] = $value->format('Y-m-d H:i:s');
        } else {
            $this->attributes['PROCESSED_AT'] = $value;
        }
    }

    public function setAuthorizedAtAttribute($value)         // ← FIX
    {
        if ($value instanceof Carbon) {
            $this->attributes['AUTHORIZED_AT'] = $value->format('Y-m-d H:i:s');
        } else {
            $this->attributes['AUTHORIZED_AT'] = $value;
        }
    }

    public function setValueDateAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['VALUE_DATE'] = $value->format('Y-m-d');
        } else {
            $this->attributes['VALUE_DATE'] = $value;
        }
    }

    // =========================================================
    // RELATIONS
    // =========================================================

    public function creator()
    {
        return $this->belongsTo(User::class, 'CREATED_BY');
    }

    public function authorizer()                              // ← FIX : relation vers le manager qui a autorisé
    {
        return $this->belongsTo(User::class, 'AUTHORIZED_BY');
    }

    public function details()
    {
        return $this->hasMany(SwiftMessageDetail::class, 'message_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'message_swift_id');
    }

    public function anomaly()
    {
        return $this->hasOne(AnomalySwift::class, 'message_id');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    public function getTagValue($tag)
    {
        return $this->details->where('tag_name', $tag)->first()?->tag_value;
    }

    public function detectTypeFromXml(): string
    {
        if (empty($this->XML_BRUT)) {
            return 'UNKNOWN';
        }

        try {
            $xml = new SimpleXMLElement($this->XML_BRUT);
            $ns = $xml->getDocNamespaces(true);

            $defaultNs = reset($ns) ?: '';

            if (str_contains($defaultNs, 'pacs.008')) {
                return 'PACS.008';
            }
            if (str_contains($defaultNs, 'pacs.009')) {
                return 'PACS.009';
            }
            if (str_contains($defaultNs, 'camt.053')) {
                return 'CAMT.053';
            }
            if (str_contains($defaultNs, 'camt.054')) {
                return 'CAMT.054';
            }

            $rootTag = $xml->getName();
            if ($rootTag === 'SWIFTMessage') {
                return (string) ($xml->MessageType ?? 'UNKNOWN');
            }

            return 'UNKNOWN';
        } catch (\Exception $e) {
            return 'ERROR-XML';
        }
    }

    public function determineCategorie(): string
    {
        $type = strtoupper($this->TYPE_MESSAGE ?? '');

        if (str_starts_with($type, 'PACS')) {
            return 'PACS';
        }
        if (str_starts_with($type, 'CAMT')) {
            return 'CAMT';
        }
        if (str_starts_with($type, 'MT1')) {
            return '1';
        }
        if (str_starts_with($type, 'MT2')) {
            return '2';
        }
        if (str_starts_with($type, 'MT3')) {
            return '3';
        }
        if (str_starts_with($type, 'MT4')) {
            return '4';
        }
        if (str_starts_with($type, 'MT5')) {
            return '5';
        }
        if (str_starts_with($type, 'MT7')) {
            return '7';
        }
        if (str_starts_with($type, 'MT9')) {
            return '9';
        }

        return 'AUTRE';
    }

    // =========================================================
    // PERMISSIONS
    // =========================================================

    public function scopeReadable($query, $user)
    {
        if ($user->hasRole(['super-admin', 'swift-manager', 'swift-operator'])) {
            return $query;
        }

        $permissions = $user->getAllPermissions()->pluck('name');

        $canViewAllIn = $permissions->contains('view-received-messages');
        $canViewAllOut = $permissions->contains('view-emitted-messages');

        $inTypes = [];
        $outTypes = [];

        foreach ($permissions as $perm) {
            if (Str::startsWith($perm, 'IN.')) {
                $inTypes[] = substr($perm, 3);
            } elseif (Str::startsWith($perm, 'OUT.')) {
                $outTypes[] = substr($perm, 4);
            }
        }

        if ($canViewAllIn && $canViewAllOut) {
            return $query;
        }

        $query->where(function ($q) use ($canViewAllIn, $canViewAllOut, $inTypes, $outTypes) {
            if ($canViewAllIn) {
                $q->orWhere('DIRECTION', 'IN');
            } elseif (! empty($inTypes)) {
                $q->orWhere(function ($sub) use ($inTypes) {
                    $sub->where('DIRECTION', 'IN')
                        ->whereIn('TYPE_MESSAGE', $inTypes);
                });
            }

            if ($canViewAllOut) {
                $q->orWhere('DIRECTION', 'OUT');
            } elseif (! empty($outTypes)) {
                $q->orWhere(function ($sub) use ($outTypes) {
                    $sub->where('DIRECTION', 'OUT')
                        ->whereIn('TYPE_MESSAGE', $outTypes);
                });
            }
        });

        return $query;
    }

    public function isReadableBy(User $user): bool
    {
        if ($user->hasRole(['super-admin', 'swift-manager', 'swift-operator', 'backoffice', 'monetique', 'chef-agence', 'chargee'])) {
            return true;
        }

        $direction = $this->direction;

        if ($direction === 'IN' && $user->can('view-received-messages')) {
            return true;
        }
        if ($direction === 'OUT' && $user->can('view-emitted-messages')) {
            return true;
        }

        $permName = $direction === 'IN'
            ? 'IN.'.$this->TYPE_MESSAGE
            : 'OUT.'.$this->TYPE_MESSAGE;

        return $user->can($permName);
    }

    public static function canCreate(User $user, string $type): bool
    {
        $available = self::getAvailableTypes($user, 'OUT');

        return isset($available[$type]);
    }

    public static function getAvailableTypes(User $user, string $direction): array
    {
        $types = self::TYPES;

        if ($user->hasRole(['super-admin', 'swift-manager', 'swift-operator'])) {
            return $types;
        }

        $prefix = $direction === 'IN' ? 'IN.' : 'OUT.';
        $permittedTypes = [];

        foreach ($user->getAllPermissions() as $perm) {
            if (Str::startsWith($perm->name, $prefix)) {
                $permittedTypes[] = substr($perm->name, strlen($prefix));
            }
        }

        $globalPerm = $direction === 'IN' ? 'view-received-messages' : 'view-emitted-messages';
        if ($user->can($globalPerm)) {
            return $types;
        }

        if (empty($permittedTypes)) {
            return [];
        }

        return array_filter($types, function ($key) use ($permittedTypes) {
            return in_array($key, $permittedTypes);
        }, ARRAY_FILTER_USE_KEY);
    }

    // =========================================================
    // FORMATEURS
    // =========================================================

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->AMOUNT, 2).' '.$this->CURRENCY;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->STATUS] ?? $this->STATUS;
    }

    public function getDirectionLabelAttribute(): string
    {
        return self::DIRECTION[$this->DIRECTION] ?? $this->DIRECTION;
    }

    public function getCategorieLabelAttribute(): string
    {
        $categorie = $this->CATEGORIE ?? $this->determineCategorie();

        return self::CATEGORIES[$categorie] ?? $categorie;
    }

    public function getFormattedCreatedAt(string $format = 'd/m/Y H:i:s'): string
    {
        return $this->CREATED_AT ? $this->CREATED_AT->format($format) : '-';
    }

    public function getFormattedUpdatedAt(string $format = 'd/m/Y H:i:s'): string
    {
        return $this->UPDATED_AT ? $this->UPDATED_AT->format($format) : '-';
    }

    public function getFormattedProcessedAt(string $format = 'd/m/Y H:i:s'): string
    {
        return $this->PROCESSED_AT ? $this->PROCESSED_AT->format($format) : '-';
    }

    public function getFormattedAuthorizedAt(string $format = 'd/m/Y H:i:s'): string // ← FIX
    {
        return $this->AUTHORIZED_AT ? $this->AUTHORIZED_AT->format($format) : '-';
    }

    public function getFormattedValueDate(string $format = 'd/m/Y'): string
    {
        return $this->VALUE_DATE ? $this->VALUE_DATE->format($format) : '-';
    }
}
