<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessCoaItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'business_coa_items';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'business_id',
        'coa_item_id',
        'account_code',
        'account_name',
        'account_class',
        'account_subclass',
        'account_type',
        'account_subtype',
        'normal_balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(BusinessRegistration::class, 'business_id');
    }

    public function templateItem()
    {
        return $this->belongsTo(COATemplateItem::class, 'coa_item_id');
    }
}
