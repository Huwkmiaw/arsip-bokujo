<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

#[Signature('admin:create {email : Email untuk akun admin baru}')]
#[Description('Create an admin account with a hidden password prompt')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        $emailValidator = Validator::make(['email' => $email], [
            'email' => ['required', 'string', 'email'],
        ]);

        if ($emailValidator->fails()) {
            $this->error($emailValidator->errors()->first('email'));

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error('Email tersebut sudah terdaftar.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama admin'));
        $password = (string) $this->secret('Kata sandi (minimal 12 karakter)');
        $passwordConfirmation = (string) $this->secret('Konfirmasi kata sandi');

        $validator = Validator::make([
            'name' => $name,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->is_admin = true;
        $user->save();

        $this->info('Akun admin berhasil dibuat.');

        return self::SUCCESS;
    }
}
