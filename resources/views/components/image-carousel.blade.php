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
        // Optional per-slide clickable hotspot ('link' + 'hotspot' => [x, y, w, h, imgW, imgH] in image pixels).
        // The image uses object-cover, so replicate that geometry to keep the hotspot aligned at any frame size.
        fw: 0,
        fh: 0,
        measure() { this.fw = this.$el.clientWidth; this.fh = this.$el.clientHeight; },
        posFactors(p) {
            const map = { left: 0, top: 0, center: 0.5, right: 1, bottom: 1 };
            const parts = String(p || 'center').trim().split(/\s+/);
            const f = (t) => t.endsWith('%') ? parseFloat(t) / 100 : (t in map ? map[t] : 0.5);
            if (parts.length === 1) {
                if (parts[0] === 'top' || parts[0] === 'bottom') return [0.5, f(parts[0])];
                return [f(parts[0]), 0.5];
            }
            let [a, b] = parts;
            if (a === 'top' || a === 'bottom') [a, b] = [b, a];
            return [f(a), f(b)];
        },
        hotspotStyle(slide) {
            const h = slide.hotspot;
            if (! h || ! this.fw || ! this.fh) return 'display:none;';
            const s = Math.max(this.fw / h.imgW, this.fh / h.imgH);
            const [px, py] = this.posFactors(slide.position || '{{ $objectPosition }}');
            const ox = (this.fw - h.imgW * s) * px;
            const oy = (this.fh - h.imgH * s) * py;
            return 'left:' + (ox + h.x * s) + 'px;top:' + (oy + h.y * s) + 'px;width:' + (h.w * s) + 'px;height:' + (h.h * s) + 'px;';
        },
    }"
    x-init="start(); measure(); new ResizeObserver(() => measure()).observe($el)"
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

    <template x-for="slide in slides.map((s, i) => ({ ...s, i })).filter((s) => s.link && s.hotspot)" :key="'hotspot-' + slide.i">
        <a
            :href="slide.link"
            :aria-label="slide.linkLabel || slide.alt"
            x-show="active === slide.i"
            class="tc-carousel-hotspot"
            :style="'position:absolute;z-index:5;display:block;cursor:pointer;border-radius:0.75rem;' + hotspotStyle(slide)"
        ></a>
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

@once
    <style>
        .tc-carousel-hotspot { transition: backdrop-filter .2s ease; }
        .tc-carousel-hotspot:hover { backdrop-filter: brightness(1.08); }
        .tc-carousel-hotspot:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
    </style>
@endonce
