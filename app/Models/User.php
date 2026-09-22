<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail {
    use Notifiable;
    protected $primaryKey = 'user_id';
    protected $guarded = [];
    protected $hidden = ['password_hash','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime','locked_until'=>'datetime','last_login_at'=>'datetime','password_changed_at'=>'datetime'];
    public function getAuthPasswordName(): string { return 'password_hash'; }
    public function getAuthPassword(): string { return (string) $this->password_hash; }
    public function role(): BelongsTo { return $this->belongsTo(Role::class,'role_id','role_id'); }
    public function doctorProfile(): HasOne { return $this->hasOne(Doctor::class,'user_id','user_id'); }
    public function patients(): HasMany { return $this->hasMany(Patient::class,'account_user_id','user_id'); }
    public function notifications(): HasMany { return $this->hasMany(Notification::class,'user_id','user_id'); }
    public function isRole(string ...$roles): bool { return in_array($this->role?->role_name,$roles,true); }
    public function dashboardRoute(): string {
        return match($this->role?->role_name) {
            'patient' => 'patient.dashboard',
            'doctor' => 'doctor.dashboard',
            'lab_officer' => 'lab.dashboard',
            'office_staff' => 'staff.dashboard',
            'admin','main_admin' => 'admin.dashboard',
            default => 'home',
        };
    }
}
