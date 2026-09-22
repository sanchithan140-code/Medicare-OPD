<?php
namespace App\Models;
class Notification extends BaseModel {
    protected $primaryKey='notification_id'; public $timestamps=false;
    protected $casts=['delivered_at'=>'datetime','read_at'=>'datetime','created_at'=>'datetime'];
}
