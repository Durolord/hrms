<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Cron\CronExpression;
use Illuminate\Support\Collection;

/**
 * Public demo mode: the shared role accounts and the reset schedule (config/demo.php).
 */
class Demo
{
    public static function enabled(): bool
    {
        return (bool) config('demo.enabled');
    }

    /**
     * @return Collection<int, array{role: string, name: string, email: string, summary: string}>
     */
    public static function accounts(): Collection
    {
        return collect(config('demo.accounts', []))->values();
    }

    public static function password(): string
    {
        return (string) config('demo.password');
    }

    public static function isDemoEmail(?string $email): bool
    {
        return filled($email) && self::accounts()->contains(fn (array $account) => strcasecmp($account['email'], $email) === 0);
    }

    /**
     * Whether the user is a shared demo account that must stay usable for the next visitor.
     */
    public static function protects(?User $user): bool
    {
        return self::enabled() && $user !== null && self::isDemoEmail($user->getOriginal('email') ?? $user->email);
    }

    /**
     * Whether the employee record belongs to a protected demo account (loads the user only in demo mode).
     */
    public static function protectsEmployee(Employee $employee): bool
    {
        return self::enabled() && self::protects($employee->user);
    }

    public static function nextResetAt(?CarbonInterface $from = null): CarbonImmutable
    {
        $timezone = config('app.timezone');
        $from = CarbonImmutable::instance($from ?? now())->setTimezone($timezone);

        return CarbonImmutable::instance(
            (new CronExpression((string) config('demo.reset_cron')))->getNextRunDate($from, 0, false, $timezone)
        );
    }

    /**
     * Human-readable time until the next reset, e.g. "42 minutes".
     */
    public static function resetsIn(): string
    {
        $minutes = (int) max(1, ceil(now()->diffInSeconds(self::nextResetAt()) / 60));

        return $minutes < 120
            ? $minutes.' '.str('minute')->plural($minutes)
            : now()->diffForHumans(self::nextResetAt(), ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]);
    }
}
