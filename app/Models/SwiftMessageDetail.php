<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwiftMessageDetail extends Model
{
    use HasFactory;

    protected $table = 'swift_message_details';

    protected $fillable = [
        'message_id',
        'tag_name',
        'tag_value',
    ];

    public function message()
    {
        return $this->belongsTo(MessageSwift::class, 'message_id');
    }
}