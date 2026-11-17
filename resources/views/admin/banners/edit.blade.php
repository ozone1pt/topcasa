<x-crm-layout title="Editar banner">
<div class="space-y-4">
        <form method="POST" action="{{ route('crm.banners.update', $banner) }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @csrf
            @method('PUT')
            @include('admin.banners._form')
            <x-primary-button>Guardar</x-primary-button>
        </form>
    </div>
</x-crm-layout>
