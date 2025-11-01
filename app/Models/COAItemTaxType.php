<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COAItemTaxType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'coa_item_tax_types';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'account_item_id',
        'tax_type_id',
    ];

    public function accountItem()
    {
        return $this->belongsTo(COATemplateItem::class, 'account_item_id');
    }

    public function taxType()
    {
        return $this->belongsTo(TaxType::class);
    }
}
