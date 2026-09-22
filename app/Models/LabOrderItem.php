<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class LabOrderItem extends BaseModel {
    protected $primaryKey='lab_order_item_id'; public $timestamps=false; protected $casts=['fee'=>'decimal:2'];
    public function order(): BelongsTo { return $this->belongsTo(LabOrder::class,'lab_order_id','lab_order_id'); }
    public function test(): BelongsTo { return $this->belongsTo(LabTest::class,'lab_test_id','lab_test_id'); }
    public function result(): HasOne { return $this->hasOne(LabResult::class,'lab_order_item_id','lab_order_item_id'); }
}
