<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Prescription extends BaseModel {
    protected $primaryKey='prescription_id'; public $timestamps=false;
    public function items(): HasMany { return $this->hasMany(PrescriptionItem::class,'prescription_id','prescription_id'); }
}
