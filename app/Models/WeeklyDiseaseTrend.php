<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WeeklyDiseaseTrend extends BaseModel {
    protected $primaryKey='trend_id'; public $timestamps=false;
    protected $casts=['week_start'=>'date','generated_at'=>'datetime','is_spike'=>'boolean','percentage'=>'decimal:2','spike_score'=>'decimal:3'];
    public function condition(): BelongsTo { return $this->belongsTo(DiagnosisCondition::class,'condition_id','condition_id'); }
}
