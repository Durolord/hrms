<?php
namespace App\Models;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    protected function getDefaultGuardName(): string
    {
        return 'web';
    }
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
    public function leaves()
    {
        return $this->hasMany(Leave::class, 'approver_id');
    }
    public function canApproveLeave(Leave $leave): bool
    {
        $allowedRoles = ['Admin', 'HR Manager'];
        $userHasRole = collect($allowedRoles)->contains(fn ($role) => $this->hasRole($role));
        return $userHasRole || ($leave->employee->manager_id === $this->employee?->id);
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
    public static function notifyRole($role, $title, $message, $url)
    {
        $users = User::role($role)->get();
        foreach ($users as $user) {
            $user->notify(new UserNotification(
                title: $title,
                message: $message,
                url: $url
            ));
        }
    }
    protected static function boot()
    {
        parent::boot();
        static::updated(function ($user) {
            if ($user->employee) {
                $user->employee->update([
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            }
        });
    }
}