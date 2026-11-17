<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnuncioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('anuncio')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'minha_empresa' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', Rule::exists('categorias', 'id')],
            'distrito_id' => ['required', Rule::exists('distritos', 'id')],
            'descricao' => ['required', 'string'],
            'contacto' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:rfc,filter', 'max:255'],
            'youtube_link' => ['nullable', 'url', 'max:255'],
            'instagram_link' => ['nullable', 'string', 'max:255'],
            'destacar' => ['sometimes', 'boolean'],
            'certificacao_profissional' => ['sometimes', 'boolean'],
            'seguro_responsabilidade_civil' => ['sometimes', 'boolean'],
            'imagens' => ['nullable', 'array', 'max:5'],
            'imagens.*' => ['image', 'mimes:jpeg,png,webp', 'max:8192'],
        ];
    }
}
