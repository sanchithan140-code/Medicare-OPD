<?php
namespace App\Models;
class DiagnosisCondition extends BaseModel {
    protected $primaryKey='condition_id'; public $timestamps=false; protected $casts=['active'=>'boolean'];
}
