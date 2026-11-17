@props([
    'images' => [],
    'aspectClass' => 'aspect-[4/3] sm:aspect-[16/9]',
    'objectPosition' => 'center 25%',
    'frameClass' => 'rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800',
])

<div
    x-data="{
        slides: {{ Illuminate\Support\Js::from($images) }},
        active: 0,
        timer: null,
        start() {
            this.stop();
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (this.slides.length > 1 && ! reduceMotion) {
                this.timer = setInterval(() => this.next(), 4500);
            }
        },
        stop() {
            if (this.timer) { clearInterval(this.timer); this.timer = null; }
        },
        next() { this.active = (this.active + 1) % this.slides.length; },
        prev() { this.active = (this.active - 1 + this.slides.length) % this.slides.length; },
        touchX: null,
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
    @touchstart="stop(); touchX = $event.changedTouches[0].clientX"
    @touchend="
        let dx = $event.changedTouches[0].clientX - touchX;
        if (dx > 40) prev(); else if (dx < -40) next();
        start();
    "
    class="relative w-full overflow-hidden transition-[aspect-ratio] duration-500 {{ $aspectClass }} {{ $frameClass }}"
    :style="slides[active]?.ratio ? ('aspect-ratio: ' + slides[active].ratio) : ''"
>
    <template x-for="(slide, index) in slides" :key="index">
        <img
            :src="slide.src"
            :alt="slide.alt"
            x-show="active === index"
            x-transition:enter="transition ease-out duration-700 motion-reduce:duration-0"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-700 motion-reduce:duration-0"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 w-full h-full object-cover"
            :style="'object-position: ' + (slide.position || '{{ $objectPosition }}') + ';'"
        />
    </template>

    <div x-show="slides.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-2">
        <template x-for="(slide, index) in slides" :key="index">
            <button
                type="button"
                @click="active = index"
                :aria-label="'Ir para imagem ' + (index + 1)"
                class="w-2.5 h-2.5 rounded-full transition"
                :class="active === index ? 'bg-white' : 'bg-white/50'"
            ></button>
        </template>
    </div>
</div>
