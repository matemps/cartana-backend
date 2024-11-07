<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'state',
        'city',
        'zip',
        'address',
        'payment',
        'acctType',
        'accountFName',
        'accountLName',
        'accountNumber',
        'routing',
        'cardFName',
        'cardLName',
        'cardNumber',
        'exp',
        'cvv'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
