<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class LabOrder extends BaseModel {
    protected $primaryKey='lab_order_id'; protected $casts=['ordered_at'=>'datetime','completed_at'=>'datetime'];
    public function invoice() { return $this->hasOne(Invoice::class,'lab_order_id','lab_order_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class,'patient_id','patient_id'); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class,'ordered_by_doctor_id','doctor_id'); }
    public function items(): HasMany { return $this->hasMany(LabOrderItem::class,'lab_order_id','lab_order_id'); }
}
