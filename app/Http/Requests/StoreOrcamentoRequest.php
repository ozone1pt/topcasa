<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrcamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['nullable', 'string', 'max:255'],
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc,filter', 'max:255'],
            'distrito_id' => ['required', 'exists:distritos,id'],
            'descricao' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:8192'],
            'termo_aceite' => ['accepted'],
        ];
    }
}
