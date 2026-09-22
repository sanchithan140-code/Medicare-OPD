<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DoctorLeave extends BaseModel {
    protected $table='doctor_leave'; protected $primaryKey='leave_id';
    protected $casts=['start_at'=>'datetime','end_at'=>'datetime','approved_at'=>'datetime'];
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class,'doctor_id','doctor_id'); }
}
