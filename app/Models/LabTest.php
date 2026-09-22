<?php
namespace App\Models;
class LabTest extends BaseModel {
    protected $primaryKey='lab_test_id'; public $timestamps=false; protected $casts=['active'=>'boolean','standard_fee'=>'decimal:2'];
}
