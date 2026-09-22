<?php
namespace App\Models;
class InsuranceProvider extends BaseModel {
    protected $primaryKey='provider_id'; public $timestamps=false; protected $casts=['active'=>'boolean'];
}
