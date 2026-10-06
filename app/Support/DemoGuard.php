<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Demo-mode abuse protection, enforced at the model layer so every form, action and bulk action is covered:
 * shared demo accounts keep their email and password and can't be deleted, each table gains a limited number
 * of rows between resets, and each visitor IP may create records a limited number of times per window.
 * A refusal shows a notification and throws Halt, which Filament's create, edit, profile and action handlers catch.
 */
class DemoGuard
{
    public static function register(): void
    {
        Event::listen('eloquent.creating: *', fn (string $event, array $payload) => self::beforeCreate($payload[0]));
        Event::listen('eloquent.created: *', fn (string $event, array $payload) => self::afterCreate($payload[0]));

        User::updating(fn (User $user) => self::guardAccount($user));
        User::deleting(fn (User $user) => Demo::protects($user) ? self::refuse('Shared demo accounts can\'t be deleted.') : null);
        Employee::updating(function (Employee $employee) {
            if ($employee->isDirty('email') && Demo::protectsEmployee($employee)) {
                self::refuse('Shared demo accounts keep their email address so the next visitor can sign in.');
            }
        });
        Employee::deleting(fn (Employee $employee) => Demo::protectsEmployee($employee) ? self::refuse('Employee records of the shared demo accounts can\'t be deleted.') : null);
    }

    /**
     * Allow a password write only when the demo password still works, so the login rehash and seeding pass.
     * Remember-token and name changes are never blocked.
     */
    private static function guardAccount(User $user): void
    {
        if (! Demo::protects($user)) {
            return;
        }
        if ($user->isDirty('email')) {
            self::refuse('Shared demo accounts keep their email address so the next visitor can sign in.');
        }
        if ($user->isDirty('password') && ! Hash::check(Demo::password(), (string) $user->password)) {
            self::refuse('Shared demo accounts keep the published password so the next visitor can sign in.');
        }
    }

    private static function beforeCreate(mixed $model): void
    {
        if (! $model instanceof Model || ! self::limitsApply($model)) {
            return;
        }

        $cap = (int) config('demo.max_new_rows_per_table');
        if ($cap > 0 && (int) Cache::get(self::rowCountKey($model), 0) >= $cap) {
            self::refuse('This demo already has plenty of new '.self::label($model).'.');
        }

        $limit = (int) config('demo.writes_per_window');
        $request = request();
        if ($limit <= 0 || $request->attributes->get('demo_write_counted')) {
            return;
        }
        $key = 'demo-writes:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            self::refuse('You have created a lot of records in a short time. Try again in '.self::waitFor($key).'.');
        }
        // One hit per request, so a bulk action or a payroll run with child rows counts once.
        RateLimiter::hit($key, max(1, (int) config('demo.write_window_minutes')) * 60);
        $request->attributes->set('demo_write_counted', true);
    }

    private static function afterCreate(mixed $model): void
    {
        if (! $model instanceof Model || ! self::limitsApply($model) || (int) config('demo.max_new_rows_per_table') <= 0) {
            return;
        }
        $key = self::rowCountKey($model);
        Cache::add($key, 0, Demo::nextResetAt());
        Cache::increment($key);
    }

    /**
     * Limits apply to visitors' requests only: seeding, demo:reset and queue workers run in the console.
     */
    private static function limitsApply(Model $model): bool
    {
        return Demo::enabled()
            && (! app()->runningInConsole() || app()->runningUnitTests())
            && ! in_array($model->getTable(), config('demo.unlimited_tables', []), true);
    }

    /**
     * Counters are keyed by the next reset, so they start again from zero after every reset.
     */
    private static function rowCountKey(Model $model): string
    {
        return 'demo-rows:'.Demo::nextResetAt()->getTimestamp().':'.$model->getTable();
    }

    private static function label(Model $model): string
    {
        return Str::of(class_basename($model))->snake(' ')->plural()->toString();
    }

    private static function waitFor(string $key): string
    {
        $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

        return $minutes.' '.Str::plural('minute', $minutes);
    }

    /**
     * @throws Halt
     */
    public static function refuse(string $message): never
    {
        Notification::make()
            ->title('Demo limit reached')
            ->body($message.' All data resets in '.Demo::resetsIn().', so feel free to keep exploring.')
            ->warning()
            ->persistent()
            ->send();

        throw (new Halt)->rollBackDatabaseTransaction();
    }
}
