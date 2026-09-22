<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EmailOtp extends BaseModel {
    protected $table='email_otps'; protected $primaryKey='otp_id'; public $timestamps=false;
    protected $casts=['expires_at'=>'datetime','used_at'=>'datetime','created_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class,'user_id','user_id'); }
}
