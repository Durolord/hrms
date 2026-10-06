<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'email' => config('app.default_user.email'),
            'password' => Hash::make(config('app.default_user.password')),
        ]));
        $this->withoutVite();
    }
}
