<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Doctor extends BaseModel {
    protected $primaryKey='doctor_id'; protected $casts=['active'=>'boolean','consultation_fee'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class,'user_id','user_id'); }
    public function specialty(): BelongsTo { return $this->belongsTo(Specialty::class,'specialty_id','specialty_id'); }
    public function schedules(): HasMany { return $this->hasMany(DoctorSchedule::class,'doctor_id','doctor_id'); }
    public function leaves(): HasMany { return $this->hasMany(DoctorLeave::class,'doctor_id','doctor_id'); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class,'doctor_id','doctor_id'); }
}
