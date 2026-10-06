<?php

use App\Filament\Pages\App\Profile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Help\HowItWorks;
use App\Filament\Resources\SkillResource\Pages\CreateSkill;
use App\Models\Skill;
use App\Models\User;
use App\Support\Demo;
use Carbon\CarbonImmutable;
use Database\Seeders\DemoAccountSeeder;
use Database\Seeders\ExtraPermissionsSeeder;
use Database\Seeders\ShieldSeeder;
use Filament\Facades\Filament;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
    $this->seed(ExtraPermissionsSeeder::class);
    $this->seed(DemoAccountSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    config(['demo.enabled' => true]);

    $this->admin = User::where('email', Demo::accounts()->firstWhere('role', 'Admin')['email'])->firstOrFail();
    $this->demoEmployee = User::where('email', Demo::accounts()->firstWhere('role', 'Employee')['email'])->firstOrFail();
});

/**
 * Each record creation in a test runs in one PHP request; forget the per-request flag to simulate the next request.
 */
function nextDemoRequest(): void
{
    request()->attributes->remove('demo_write_counted');
}

describe('reset', function () {
    it('refuses to reset when demo mode is off', function () {
        config(['demo.enabled' => false]);

        $this->artisan('demo:reset')
            ->expectsOutputToContain('Refusing to reset')
            ->assertFailed();
        expect(User::count())->toBeGreaterThan(1);
    });

    it('schedules the reset only in demo mode, on the configured cron', function () {
        expect(Demo::nextResetAt())->toBeInstanceOf(CarbonImmutable::class)
            ->and(Demo::nextResetAt()->isFuture())->toBeTrue()
            ->and(Demo::resetsIn())->toContain('minute');

        config(['demo.reset_cron' => '30 3 * * *']);
        $from = now()->addDay()->setTime(2, 0);
        $next = Demo::nextResetAt($from);
        expect($next->format('H:i'))->toBe('03:30')->and($next->gt($from))->toBeTrue();
    });
});

describe('login', function () {
    beforeEach(fn () => auth()->logout());

    it('pre-fills the first demo account and lists every account', function () {
        $first = Demo::accounts()->first();

        livewire(Login::class)
            ->assertSet('data.email', $first['email'])
            ->assertSet('data.password', Demo::password());

        // The list comes from a panel render hook, which is registered while the panel serves a request.
        $page = $this->get(Filament::getLoginUrl())->assertOk()->assertSee('Try the live demo');
        foreach (Demo::accounts() as $account) {
            $page->assertSee($account['role'])->assertSee($account['summary'])->assertSee("loginAs('{$account['email']}')", false);
        }
        $page->assertSee(Demo::password())->assertDontSee('Forgot password');
    });

    it('signs in with one click as a demo account through the normal login', function () {
        livewire(Login::class)
            ->call('loginAs', $this->demoEmployee->email)
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertAuthenticatedAs($this->demoEmployee);
    });

    it('rejects one-click login for any email that is not a listed demo account', function () {
        $other = User::factory()->create(['password' => Hash::make(Demo::password())]);

        livewire(Login::class)->call('loginAs', $other->email)->assertNotified();
        livewire(Login::class)->call('loginAs', 'nobody@example.com')->assertNotified();

        $this->assertGuest();
    });

    it('does not offer one-click login when demo mode is off', function () {
        config(['demo.enabled' => false]);

        livewire(Login::class)
            ->assertSet('data.email', null)
            ->assertDontSee('Try the live demo')
            ->call('loginAs', $this->demoEmployee->email);

        $this->assertGuest();
    });

    it('still logs in when the password hash is rehashed on login', function () {
        $this->demoEmployee->forceFill(['password' => Hash::make(Demo::password(), ['rounds' => 5])])->saveQuietly();

        livewire(Login::class)->call('loginAs', $this->demoEmployee->email)->assertRedirect();

        $this->assertAuthenticatedAs($this->demoEmployee);
        expect(Hash::check(Demo::password(), $this->demoEmployee->fresh()->password))->toBeTrue();
    });

    it('shows the reset banner', function () {
        $this->get(Filament::getLoginUrl())->assertOk()->assertSee('Live demo. Explore freely: all data resets in');
    });
});

describe('account protection', function () {
    it('stops anyone deleting or editing a demo account, but not other users', function () {
        $other = User::factory()->create();

        expect($this->admin->can('delete', $this->demoEmployee))->toBeFalse()
            ->and($this->admin->can('update', $this->demoEmployee))->toBeFalse()
            ->and($this->admin->can('delete', $other))->toBeTrue()
            ->and($this->admin->can('update', $other))->toBeTrue();

        expect(fn () => $this->demoEmployee->delete())->toThrow(Halt::class);
        expect($this->demoEmployee->fresh())->not->toBeNull();
    });

    it('keeps the email and password of demo accounts', function () {
        expect(fn () => $this->demoEmployee->update(['email' => 'taken-over@example.com']))->toThrow(Halt::class);
        expect(fn () => $this->demoEmployee->fresh()->update(['password' => Hash::make('something-else')]))->toThrow(Halt::class);

        $fresh = $this->demoEmployee->fresh();
        expect($fresh->email)->toBe(Demo::accounts()->firstWhere('role', 'Employee')['email'])
            ->and(Hash::check(Demo::password(), $fresh->password))->toBeTrue();

        // Name, remember token and a rehash of the same password stay allowed.
        $fresh->update(['name' => 'Renamed Visitor', 'password' => Hash::make(Demo::password())]);
        $fresh->setRememberToken('token');
        $fresh->save();
        expect($fresh->fresh()->name)->toBe('Renamed Visitor');
    });

    it('locks email and password on the profile page of a demo account', function () {
        $this->actingAs($this->demoEmployee);

        livewire(Profile::class)
            ->assertFormFieldIsDisabled('email')
            ->assertFormFieldIsDisabled('password')
            ->assertFormFieldIsEnabled('name');
    });

    it('leaves the profile of other users editable', function () {
        livewire(Profile::class)
            ->assertFormFieldIsEnabled('email')
            ->assertFormFieldIsEnabled('password');
    });

    it('does not protect anyone when demo mode is off', function () {
        config(['demo.enabled' => false]);

        expect($this->admin->can('delete', $this->demoEmployee))->toBeTrue();
        $this->demoEmployee->update(['email' => 'changed@example.com']);
        expect($this->demoEmployee->fresh()->email)->toBe('changed@example.com');
    });
});

describe('write limits', function () {
    beforeEach(fn () => RateLimiter::clear('demo-writes:127.0.0.1'));

    it('caps the rows a table can gain between resets', function () {
        config(['demo.max_new_rows_per_table' => 2, 'demo.writes_per_window' => 0]);

        Skill::create(['name' => 'Negotiation']);
        Skill::create(['name' => 'Excel']);
        expect(fn () => Skill::create(['name' => 'Public speaking']))->toThrow(Halt::class);

        expect(Skill::count())->toBe(2);
    });

    it('rate-limits record creation per visitor IP', function () {
        config(['demo.max_new_rows_per_table' => 0, 'demo.writes_per_window' => 2]);

        Skill::create(['name' => 'Negotiation']);
        nextDemoRequest();
        Skill::create(['name' => 'Excel']);
        nextDemoRequest();
        expect(fn () => Skill::create(['name' => 'Public speaking']))->toThrow(Halt::class);

        // Several rows in one request count once.
        RateLimiter::clear('demo-writes:127.0.0.1');
        nextDemoRequest();
        Skill::create(['name' => 'Bookkeeping']);
        Skill::create(['name' => 'Forecasting']);
        Skill::create(['name' => 'Payroll']);
        expect(Skill::count())->toBe(5);
    });

    it('stops a Filament create with a friendly notification', function () {
        config(['demo.max_new_rows_per_table' => 1]);
        $this->actingAs($this->admin);
        Skill::create(['name' => 'Negotiation']);
        nextDemoRequest();

        livewire(CreateSkill::class)
            ->fillForm(['name' => 'Excel'])
            ->call('create')
            ->assertNotified('Demo limit reached');

        expect(Skill::where('name', 'Excel')->exists())->toBeFalse();
    });

    it('has no limits when demo mode is off', function () {
        config(['demo.enabled' => false, 'demo.max_new_rows_per_table' => 1, 'demo.writes_per_window' => 1]);

        foreach (['Negotiation', 'Excel', 'Public speaking'] as $name) {
            Skill::create(['name' => $name]);
            nextDemoRequest();
        }
        expect(Skill::count())->toBe(3);
    });
});

describe('help', function () {
    it('shows the walkthrough with demo roles only in demo mode', function () {
        $this->actingAs($this->admin);

        livewire(HowItWorks::class)
            ->assertSuccessful()
            ->assertSee('Try it')
            ->assertSee('Sign in as Department Head');

        config(['demo.enabled' => false]);
        livewire(HowItWorks::class)
            ->assertSuccessful()
            ->assertSee('Finance Manager')
            ->assertDontSee('Sign in as');
    });
});
