<?php
namespace App\Models;
class SupportMessage extends BaseModel {
    public function sender() { return $this->belongsTo(User::class,'sender_user_id','user_id'); }
    protected $primaryKey='support_message_id'; public $timestamps=false;
    protected $casts=['created_at'=>'datetime'];
}
