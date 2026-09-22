<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AiRecommendation extends BaseModel {
    protected $table='ai_recommendations'; protected $primaryKey='recommendation_id'; public $timestamps=false;
    protected $casts=['response_json'=>'array','disclaimer_accepted'=>'boolean','created_at'=>'datetime','confidence'=>'decimal:4'];
    public function specialty(): BelongsTo { return $this->belongsTo(Specialty::class,'recommended_specialty_id','specialty_id'); }
}
