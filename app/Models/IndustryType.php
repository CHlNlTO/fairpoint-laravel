<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndustryType extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'industry_types';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'hint',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
