<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Role extends BaseModel {
    public const CREATABLE_ROLES = ['office_staff', 'lab_officer'];
    protected $primaryKey = 'role_id';
    public function users(): HasMany { return $this->hasMany(User::class, 'role_id', 'role_id'); }
}
