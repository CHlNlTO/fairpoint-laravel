<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYearPeriod extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'fiscal_year_periods';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'start_month',
        'start_day',
        'end_month',
        'end_day',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'start_month' => 'integer',
        'start_day' => 'integer',
        'end_month' => 'integer',
        'end_day' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
