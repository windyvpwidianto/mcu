<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string')]
    public string $credential = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Menangani permintaan autentikasi yang masuk.
     */
    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        // Tentukan apakah input adalah email atau username
        $fieldType = filter_var($this->credential, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $this->credential,
            'password' => $this->password,
        ];

        // Eksekusi login ke Database lokal
        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'credential' => __('auth.failed'),
            ]);
        }

        // Jika berhasil
        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(
            default: route('dashboard', absolute: false),
            navigate: true
        );
    }

    /**
     * Memastikan permintaan autentikasi tidak dibatasi (Rate Limiting).
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'credential' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Mendapatkan kunci pembatas (throttle key).
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->credential)) . '|' . request()->ip();
    }
}
