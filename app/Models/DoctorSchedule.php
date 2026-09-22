<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class DoctorSchedule extends BaseModel {
    protected $primaryKey='schedule_id'; protected $casts=['schedule_date'=>'date'];
    public function scopeNotStarted(Builder $query): Builder {
        return $query->whereRaw('TIMESTAMP(schedule_date, start_time) > ?', [now()->toDateTimeString()])
            ->whereHas('doctor',fn($doctor)=>$doctor->where('active',1)->whereHas('user',fn($user)=>$user->where('account_status','ACTIVE')));
    }
    public function scopeNotEnded(Builder $query): Builder {
        return $query->whereRaw('TIMESTAMP(schedule_date, end_time) > ?', [now()->toDateTimeString()]);
    }
    public function acceptsBookings(): bool {
        return $this->status === 'OPEN' && $this->doctor?->active && $this->doctor->user?->account_status === 'ACTIVE'
            && \Carbon\Carbon::parse($this->schedule_date->toDateString().' '.$this->start_time)->isFuture();
    }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class,'doctor_id','doctor_id'); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class,'schedule_id','schedule_id'); }
    public function slots(): HasMany { return $this->hasMany(AppointmentSlot::class,'schedule_id','schedule_id'); }
}
