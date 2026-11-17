<?php

namespace App\Http\Requests\Auth;

use App\Services\AutenticacaoUtilizador;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials. Matches by username
     * OR email, same as the legacy site's login form.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $user = app(AutenticacaoUtilizador::class)->autenticar($this);

        Auth::login($user, $this->boolean('remember'));
    }
}
