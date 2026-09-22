<?php
namespace App\Models;
class AuditLog extends BaseModel {
    protected $primaryKey='audit_id'; public $timestamps=false; protected $casts=['old_values'=>'array','new_values'=>'array','created_at'=>'datetime'];
}
