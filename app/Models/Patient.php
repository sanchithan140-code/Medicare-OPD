<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Patient extends BaseModel {
    protected $primaryKey='patient_id';
    protected $casts=['dob'=>'date','active'=>'boolean'];
    public function account(): BelongsTo { return $this->belongsTo(User::class,'account_user_id','user_id'); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class,'patient_id','patient_id'); }
    public function consultations(): HasMany { return $this->hasMany(Consultation::class,'patient_id','patient_id'); }
    public function getFullNameAttribute(): string { return trim($this->first_name.' '.$this->last_name); }
}
