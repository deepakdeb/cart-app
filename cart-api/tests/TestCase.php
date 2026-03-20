<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Helper to mock Firebase auth by setting auth_user on request
    protected function actingAsFirebaseUser(User $user = null): self
    {
        if (!$user) {
            $user = User::factory()->create();
        }

        $this->app['request']->merge(['auth_user' => $user]);
        return $this;
    }
}