<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessRegistration extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'business_registrations';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'business_name',
        'tin_number',
        'business_email',
        'fiscal_year_period_id',
        'business_type_id',
        'region_id',
        'province_id',
        'city_id',
        'barangay_id',
        'street_address',
        'building_name',
        'unit_number',
        'postal_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fiscalYearPeriod()
    {
        return $this->belongsTo(FiscalYearPeriod::class);
    }

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function taxTypes()
    {
        return $this->belongsToMany(TaxType::class, 'business_registration_tax_types')
            ->withTimestamps();
    }

    public function industryTypes()
    {
        return $this->belongsToMany(IndustryType::class, 'business_registration_industry_types')
            ->withTimestamps();
    }

    public function governmentRegistrations()
    {
        return $this->hasMany(BusinessGovernmentRegistration::class);
    }

    public function region()
    {
        return $this->belongsTo(\Yajra\Address\Entities\Region::class, 'region_id');
    }

    public function province()
    {
        return $this->belongsTo(\Yajra\Address\Entities\Province::class, 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(\Yajra\Address\Entities\City::class, 'city_id');
    }

    public function barangay()
    {
        return $this->belongsTo(\Yajra\Address\Entities\Barangay::class, 'barangay_id');
    }

    public function coaItems()
    {
        return $this->hasMany(BusinessCoaItem::class, 'business_id');
    }
}
