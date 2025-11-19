<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSubtype extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'account_subtypes';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_type_id',
        'user_id',
        'business_registration_id',
        'name',
        'is_active',
        'is_system_defined',
        'description',
        'hint',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system_defined' => 'boolean',
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
}
