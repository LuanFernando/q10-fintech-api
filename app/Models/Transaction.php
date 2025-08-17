<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'valor',
        'data_transacao',
        'hora_transacao',
        'status',
        'account_id',
        'contact_id'
    ];
}
