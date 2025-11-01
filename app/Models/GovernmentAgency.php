<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernmentAgency extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'government_agencies';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'full_name',
        'description',
        'website_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function businessRegistrations()
    {
        return $this->hasMany(BusinessGovernmentRegistration::class);
    }
}
