<?php

// Portuguese texts for the validation errors visitors can run into (anything
// not listed here falls back to Laravel's English default).
return [
    'required' => 'O campo :attribute é obrigatório.',
    'email' => 'Indique um email válido (por exemplo nome@gmail.com).',
    'url' => 'O campo :attribute tem de ser um endereço válido (começado por https://).',
    'exists' => 'O valor escolhido em :attribute não é válido.',
    'image' => 'O ficheiro :attribute tem de ser uma imagem.',
    'mimes' => 'O ficheiro :attribute tem de ser do tipo: :values.',
    'uploaded' => 'Não foi possível carregar :attribute. Tente com uma imagem mais pequena.',
    'string' => 'O campo :attribute tem de ser texto.',
    'boolean' => 'O campo :attribute não é válido.',
    'array' => 'O campo :attribute não é válido.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
        'file' => 'O ficheiro :attribute não pode ter mais de :max KB.',
        'array' => 'Só pode enviar até :max imagens.',
    ],

    'attributes' => [
        'titulo' => 'título',
        'minha_empresa' => 'nome',
        'categoria_id' => 'categoria',
        'distrito_id' => 'distrito',
        'descricao' => 'descrição',
        'contacto' => 'contacto',
        'email' => 'email',
        'youtube_link' => 'link do YouTube',
        'instagram_link' => 'Instagram',
        'imagens' => 'imagens',
        'imagens.0' => 'imagem 1',
        'imagens.1' => 'imagem 2',
        'imagens.2' => 'imagem 3',
        'imagens.3' => 'imagem 4',
        'imagens.4' => 'imagem 5',
    ],
];
