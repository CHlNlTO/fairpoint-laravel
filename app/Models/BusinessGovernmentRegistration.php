<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessGovernmentRegistration extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'business_government_registrations';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'business_registration_id',
        'government_agency_id',
        'registration_number',
        'registration_date',
        'expiry_date',
        'status',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function businessRegistration()
    {
        return $this->belongsTo(BusinessRegistration::class);
    }

    public function governmentAgency()
    {
        return $this->belongsTo(GovernmentAgency::class);
    }
}
