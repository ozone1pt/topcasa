<div x-data="{ open: false, mensagens: [], texto: '', aEnviar: false }" class="fixed bottom-4 right-4 z-50">
    <div x-show="open" x-cloak class="mb-2 w-80 bg-white dark:bg-slate-800 rounded-lg shadow-xl border dark:border-slate-700 flex flex-col" style="height: 400px;">
        <div class="p-3 border-b dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200">Assistente TopCasa</div>
        <div class="flex-1 overflow-y-auto p-3 space-y-2 text-sm">
            <template x-for="(m, i) in mensagens" :key="i">
                <div :class="m.role === 'user' ? 'text-right' : 'text-left'">
                    <span :class="m.role === 'user' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 dark:text-slate-200'" class="inline-block rounded-lg px-3 py-1.5 max-w-[85%]" x-text="m.content"></span>
                </div>
            </template>
        </div>
        <form @submit.prevent="
            if (!texto.trim() || aEnviar) return;
            mensagens.push({role: 'user', content: texto});
            const enviado = texto; texto = ''; aEnviar = true;
            fetch('{{ route('chatbot.mensagem') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({mensagem: enviado}),
            }).then(r => r.json()).then(data => {
                mensagens.push({role: 'assistant', content: data.resposta});
                aEnviar = false;
            }).catch(() => { aEnviar = false; });
        " class="p-3 border-t dark:border-slate-700 flex gap-2">
            <input x-model="texto" type="text" placeholder="Escreve a tua pergunta..." class="flex-1 text-sm rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <button type="submit" class="text-sm bg-blue-600 text-white rounded-md px-3">Enviar</button>
        </form>
    </div>

    <button @click="open = !open" class="w-14 h-14 rounded-full bg-blue-600 text-white shadow-lg flex items-center justify-center text-2xl">
        💬
    </button>
</div>
