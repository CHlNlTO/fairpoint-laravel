<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSubclass extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'account_subclasses';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_class_id',
        'code',
        'name',
        'is_active',
        'description',
        'hint',
    ];

    protected $casts = [
        'code' => 'integer',
        'is_active' => 'boolean',
    ];

    public function accountClass()
    {
        return $this->belongsTo(AccountClass::class);
    }
}
