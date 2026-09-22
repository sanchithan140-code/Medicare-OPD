<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class LabResult extends BaseModel {
    protected $primaryKey='lab_result_id'; protected $casts=['result_json'=>'array','abnormal_flag'=>'boolean','verified_at'=>'datetime','released_to_patient_at'=>'datetime'];
    public function item(): BelongsTo { return $this->belongsTo(LabOrderItem::class,'lab_order_item_id','lab_order_item_id'); }
    public function attachments(): HasMany { return $this->hasMany(ClinicalAttachment::class,'lab_result_id','lab_result_id'); }
}
