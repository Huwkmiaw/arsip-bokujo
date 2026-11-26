<?php

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_admin_with_a_hashed_password(): void
    {
        $password = 'a-long-test-password';

        $this->artisan('admin:create', ['email' => 'new-admin@example.test'])
            ->expectsQuestion('Nama admin', 'Pengelola Arsip')
            ->expectsQuestion('Kata sandi (minimal 12 karakter)', $password)
            ->expectsQuestion('Konfirmasi kata sandi', $password)
            ->doesntExpectOutputToContain($password)
            ->assertExitCode(0);

        $admin = User::query()->where('email', 'new-admin@example.test')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertNotSame($password, $admin->password);
        $this->assertTrue(Hash::check($password, $admin->password));
    }

    public function test_command_refuses_to_replace_an_existing_user(): void
    {
        User::factory()->create(['email' => 'existing-user@example.test']);

        $this->artisan('admin:create', ['email' => 'existing-user@example.test'])
            ->expectsOutputToContain('Email tersebut sudah terdaftar.')
            ->assertExitCode(1);
    }
}
