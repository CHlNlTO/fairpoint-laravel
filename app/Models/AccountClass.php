<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountClass extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'account_classes';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'normal_balance',
        'is_active',
        'description',
        'hint',
    ];

    protected $casts = [
        'code' => 'integer',
        'is_active' => 'boolean',
    ];
}
