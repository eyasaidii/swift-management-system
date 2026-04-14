<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnomalySwift extends Model
{
    protected $table = 'anomalies_swift';

    protected $fillable = [
        'message_id',
        'score',
        'niveau_risque',
        'raisons',
        'verifie_par',
        'verifie_at',
    ];

    protected $casts = [
        'raisons'    => 'array',
        'verifie_at' => 'datetime',
        'score'      => 'float',
    ];

    // Relation vers MessageSwift
    public function message()
    {
        return $this->belongsTo(MessageSwift::class, 'message_id');
    }

    // Relation vers User
    public function verificateur()
    {
        return $this->belongsTo(User::class, 'verifie_par');
    }
}