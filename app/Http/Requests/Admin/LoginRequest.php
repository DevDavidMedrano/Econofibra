<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\Admin\ValueObjects\AdminEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    private const WRONG_CREDENTIALS = 'Correo o contraseña incorrectos.';

    private const LOCKED = 'Demasiados intentos. Vuelve a intentarlo más tarde';

    /** @return array<string, list<string>> The email format is not checked, so it gives no different answer. */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['*' => self::WRONG_CREDENTIALS];
    }

    /** @throws ValidationException When the login is locked or the credentials are wrong. */
    public function authenticate(): void
    {
        $key = $this->throttleKey();
        $maxAttempts = (int) config('admin.login_max_attempts');

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages(['email' => self::LOCKED]);
        }

        if (! Auth::attempt(['email' => $this->normalizedEmail(), 'password' => $this->string('password')->value()])) {
            $this->recordFailure($key, $maxAttempts);

            throw ValidationException::withMessages(['email' => self::WRONG_CREDENTIALS]);
        }

        RateLimiter::clear($key);
    }

    /**
     * The limiter times its window from the first hit. Starting it again on the last failure
     * makes the lock last its full time from that failure.
     */
    private function recordFailure(string $key, int $maxAttempts): void
    {
        RateLimiter::hit($key, (int) config('admin.login_failure_memory_hours') * 3600);

        if (RateLimiter::attempts($key) >= $maxAttempts) {
            RateLimiter::clear($key);
            RateLimiter::increment($key, (int) config('admin.login_lockout_minutes') * 60, $maxAttempts);
        }
    }

    private function throttleKey(): string
    {
        return 'admin-login:'.hash('sha256', $this->normalizedEmail().'|'.$this->ip());
    }

    private function normalizedEmail(): string
    {
        return AdminEmail::normalize($this->string('email')->value());
    }
}
