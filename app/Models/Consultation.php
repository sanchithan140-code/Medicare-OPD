<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Consultation extends BaseModel {
    protected $primaryKey='consultation_id';
    protected $casts=['started_at'=>'datetime','completed_at'=>'datetime','follow_up_date'=>'date'];
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class,'appointment_id','appointment_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class,'patient_id','patient_id'); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class,'doctor_id','doctor_id'); }
    public function conditions(): BelongsToMany { return $this->belongsToMany(DiagnosisCondition::class,'consultation_conditions','consultation_id','condition_id')->withPivot(['is_primary','notes','finalized']); }
    public function prescriptions(): HasMany { return $this->hasMany(Prescription::class,'consultation_id','consultation_id'); }
    public function labOrders(): HasMany { return $this->hasMany(LabOrder::class,'consultation_id','consultation_id'); }
}
