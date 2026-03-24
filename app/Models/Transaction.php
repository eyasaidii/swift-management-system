<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'montant',
        'devise',
        'emetteur',
        'recepteur',
        'date_transaction',
        'message_swift_id',
    ];

    public function messageSwift()
    {
        return $this->belongsTo(MessageSwift::class, 'message_swift_id');
    }
}