<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecrutamentoRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:255'],
            'empresa_nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,filter', 'max:255'],
            'telefone' => ['required', 'string', 'max:50'],
            'distrito_id' => ['required', 'exists:distritos,id'],
            'descricao' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:8192'],
            'termo_aceite' => ['accepted'],
        ];
    }
}
