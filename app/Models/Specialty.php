<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Specialty extends BaseModel {
    protected $primaryKey='specialty_id'; protected $casts=['active'=>'boolean'];
    public function doctors(): HasMany { return $this->hasMany(Doctor::class,'specialty_id','specialty_id'); }
}
