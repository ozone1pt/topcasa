@if ($errors->any())
    <div class="bg-red-100 text-red-800 rounded-md p-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <x-input-label for="titulo" value="Título" />
    <x-text-input id="titulo" name="titulo" class="block mt-1 w-full" :value="old('titulo', $banner?->titulo)" required />
</div>

<div>
    <x-input-label for="descricao" value="Descrição (opcional)" />
    <textarea id="descricao" name="descricao" rows="2" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md">{{ old('descricao', $banner?->descricao) }}</textarea>
</div>

<div>
    <x-input-label for="url_destino" value="URL de destino" />
    <x-text-input id="url_destino" name="url_destino" class="block mt-1 w-full" :value="old('url_destino', $banner?->url_destino)" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="localizacao" value="Localização" />
        <select id="localizacao" name="localizacao" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required>
            @foreach (['topo' => 'Topo', 'meio' => 'Meio', 'rodape' => 'Rodapé'] as $value => $label)
                <option value="{{ $value }}" @selected(old('localizacao', $banner?->localizacao) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="ordem" value="Ordem" />
        <x-text-input id="ordem" name="ordem" type="number" class="block mt-1 w-full" :value="old('ordem', $banner?->ordem ?? 0)" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="data_inicio" value="Data início (opcional)" />
        <x-text-input id="data_inicio" name="data_inicio" type="date" class="block mt-1 w-full" :value="old('data_inicio', $banner?->data_inicio?->toDateString())" />
    </div>
    <div>
        <x-input-label for="data_fim" value="Data fim (opcional)" />
        <x-text-input id="data_fim" name="data_fim" type="date" class="block mt-1 w-full" :value="old('data_fim', $banner?->data_fim?->toDateString())" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="imagem_desktop" value="Imagem desktop" />
        <input id="imagem_desktop" name="imagem_desktop" type="file" accept="image/*" class="mt-1 block w-full text-sm" />
    </div>
    <div>
        <x-input-label for="imagem_mobile" value="Imagem mobile" />
        <input id="imagem_mobile" name="imagem_mobile" type="file" accept="image/*" class="mt-1 block w-full text-sm" />
    </div>
</div>

<label class="flex items-center gap-2">
    <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $banner?->ativo ?? true)) />
    <span class="text-sm text-slate-700 dark:text-slate-300">Ativo</span>
</label>
