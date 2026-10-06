<?php

namespace App\Http\Requests\Admin;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');

        // Dedicated admin guard
        if (!auth()->guard('admin')->attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Generic error message to prevent user enumeration
            throw ValidationException::withMessages([
                'email' => 'Kredensial yang diberikan tidak cocok dengan data kami atau akun tidak valid.',
            ]);
        }

        $admin = auth()->guard('admin')->user();
        if (!$admin->is_active) {
            auth()->guard('admin')->logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Kredensial yang diberikan tidak cocok dengan data kami atau akun tidak valid.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }
}
