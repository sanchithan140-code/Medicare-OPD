<?php
namespace App\Models;
class QueueEntry extends BaseModel {
    protected $table='opd_queue_entries'; protected $primaryKey='queue_entry_id'; public $timestamps=false;
    protected $casts=['checked_in_at'=>'datetime','called_at'=>'datetime','service_started_at'=>'datetime','service_ended_at'=>'datetime'];
}
