<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COATemplateItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'coa_template_items';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'account_name',
        'account_subtype_id',
        'is_active',
        'is_default',
        'normal_balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function accountSubtype()
    {
        return $this->belongsTo(AccountSubtype::class);
    }

    public function businessTypes()
    {
        return $this->hasMany(COAItemBusinessType::class, 'account_item_id');
    }

    public function industryTypes()
    {
        return $this->hasMany(COAItemIndustryType::class, 'account_item_id');
    }

    public function taxTypes()
    {
        return $this->hasMany(COAItemTaxType::class, 'account_item_id');
    }
}
