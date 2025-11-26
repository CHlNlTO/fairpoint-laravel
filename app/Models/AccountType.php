<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'account_types';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_subclass_id',
        'user_id',
        'business_registration_id',
        'name',
        'sort_order',
        'is_active',
        'is_system_defined',
        'description',
        'hint',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system_defined' => 'boolean',
    ];

    public function accountSubclass() { return $this->belongsTo(AccountSubclass::class); }
}
