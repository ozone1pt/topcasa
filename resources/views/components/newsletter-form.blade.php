@if (session('status') && request()->routeIs('newsletter.*'))
    <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm mb-3">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('newsletter.store') }}" class="flex flex-wrap gap-2 items-end">
    @csrf
    <div>
        <label for="newsletter-email" class="sr-only">Email</label>
        <input id="newsletter-email" name="email" type="email" required placeholder="O teu email"
               class="rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 text-sm" />
    </div>
    <button type="submit" class="btn btn-dark btn-sm">
        Subscrever newsletter
    </button>
</form>
