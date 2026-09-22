<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalAttachment extends BaseModel
{
    protected $table = 'clinical_attachments';
    protected $primaryKey = 'attachment_id';
    public $timestamps = false;
    protected $casts = ['file_size' => 'integer', 'created_at' => 'datetime'];

    public function result(): BelongsTo { return $this->belongsTo(LabResult::class,'lab_result_id','lab_result_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class,'patient_id','patient_id'); }
}
