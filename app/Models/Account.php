<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    // Define a tabela do modelo
    protected $table = 'accounts';

    // Permite atribuições para estes campos
    protected $fillable = [
        "agencia",
        "conta",
        "digito",
        "saldo",
        "status",
        "updated_at"
    ];

}
