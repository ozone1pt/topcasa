<x-crm-layout title="Novo banner">
<div class="space-y-4">
        <form method="POST" action="{{ route('crm.banners.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @csrf
            @include('admin.banners._form', ['banner' => null])
            <x-primary-button>Criar banner</x-primary-button>
        </form>
    </div>
</x-crm-layout>
