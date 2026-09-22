<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportThread extends BaseModel
{
    protected $primaryKey='support_thread_id';
    public function user(): BelongsTo { return $this->belongsTo(User::class,'user_id','user_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class,'patient_id','patient_id'); }
    public function messages(): HasMany { return $this->hasMany(SupportMessage::class,'support_thread_id','support_thread_id'); }
}
