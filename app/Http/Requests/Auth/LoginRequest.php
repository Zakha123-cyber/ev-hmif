<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** Batas salah password per akun (dari IP mana pun) dalam satu jendela waktu. */
    private const ACCOUNT_MAX_ATTEMPTS = 20;

    private const ACCOUNT_DECAY_SECONDS = 3600;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Isi username dulu.',
            'password.required' => 'Isi password dulu.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(bool $adminDoor = false): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('username', 'password');
        $user = Auth::validate($credentials) ? Auth::getProvider()->retrieveByCredentials($credentials) : null;

        // Pintu terpisah: akun admin hanya lewat login admin, akun lain hanya lewat /login.
        // Salah pintu diperlakukan persis seperti password salah (tidak membocorkan peran akun)
        // dan ikut dihitung dalam batas percobaan.
        if (! $user || $user->hasRole('Admin') !== $adminDoor) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->accountThrottleKey(), self::ACCOUNT_DECAY_SECONDS);

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->accountThrottleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Kunci per akun dicek dulu: berlaku dari IP mana pun, jadi ganti-ganti IP tidak membantu menebak password.
        if (RateLimiter::tooManyAttempts($this->accountThrottleKey(), self::ACCOUNT_MAX_ATTEMPTS)) {
            event(new Lockout($this));

            throw ValidationException::withMessages([
                'username' => trans('auth.throttle_account', [
                    'minutes' => (int) ceil(RateLimiter::availableIn($this->accountThrottleKey()) / 60),
                ]),
            ]);
        }

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }

    /**
     * Kunci per username saja (tanpa IP) untuk batas percobaan per akun.
     */
    public function accountThrottleKey(): string
    {
        return 'login-account|'.Str::transliterate(Str::lower($this->string('username')));
    }
}
