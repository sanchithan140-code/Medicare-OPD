<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Invoice extends BaseModel {
    protected $primaryKey='invoice_id';
    public function labOrder(): BelongsTo { return $this->belongsTo(LabOrder::class,'lab_order_id','lab_order_id'); }
    protected $casts=['issued_at'=>'datetime','subtotal'=>'decimal:2','discount_amount'=>'decimal:2','insurance_covered'=>'decimal:2','total_amount'=>'decimal:2','paid_amount'=>'decimal:2','balance_amount'=>'decimal:2'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class,'patient_id','patient_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class,'appointment_id','appointment_id'); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class,'invoice_id','invoice_id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class,'invoice_id','invoice_id'); }
}
