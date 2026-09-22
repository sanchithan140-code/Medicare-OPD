<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends BaseModel {
    protected $primaryKey='payment_id'; public $timestamps=false; protected $casts=['paid_at'=>'datetime','amount'=>'decimal:2'];
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class,'invoice_id','invoice_id'); }
}
