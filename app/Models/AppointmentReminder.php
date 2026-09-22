<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AppointmentReminder extends BaseModel {
    protected $primaryKey='reminder_id'; public $timestamps=false;
    protected $casts=['scheduled_for'=>'datetime','sent_at'=>'datetime','created_at'=>'datetime'];
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class,'appointment_id','appointment_id'); }
}
