<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InsuranceClaim extends BaseModel {
    protected $primaryKey='claim_id'; protected $casts=['submitted_at'=>'datetime','decided_at'=>'datetime','requested_amount'=>'decimal:2','approved_amount'=>'decimal:2'];
    public function provider(): BelongsTo { return $this->belongsTo(InsuranceProvider::class,'provider_id','provider_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class,'invoice_id','invoice_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class,'patient_id','patient_id'); }
}
