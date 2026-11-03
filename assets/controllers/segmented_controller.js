import { Controller } from '@hotwired/stimulus';

/*
 * Contrôle segmenté (role="radiogroup") : pastille glissante 150 ms, flèches ← → / ↑ ↓,
 * un seul segment dans l'ordre de tabulation (roving tabindex). Émet `segmented:change`.
 */
export default class extends Controller {
    static targets = ['option', 'thumb'];

    connect() {
        this.element.classList.add('is-ready');
        this.sync(false);
        this.observer = new ResizeObserver(() => this.moveThumb(false));
        this.observer.observe(this.element);
    }

    disconnect() {
        this.observer?.disconnect();
    }

    get current() {
        return this.optionTargets.find((o) => o.getAttribute('aria-checked') === 'true') ?? this.optionTargets[0];
    }

    choose(event) {
        this.set(event.currentTarget);
    }

    keydown(event) {
        const keys = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 };
        if (!(event.key in keys)) return;
        event.preventDefault();
        const options = this.optionTargets;
        const next = options[(options.indexOf(this.current) + keys[event.key] + options.length) % options.length];
        this.set(next);
        next.focus();
    }

    set(option) {
        if (option === this.current) return;
        this.optionTargets.forEach((o) => o.setAttribute('aria-checked', String(o === option)));
        this.sync();
        this.dispatch('change', { detail: { value: option.dataset.value } });
    }

    sync(animate = true) {
        this.optionTargets.forEach((o) => { o.tabIndex = o === this.current ? 0 : -1; });
        this.moveThumb(animate);
    }

    moveThumb(animate = true) {
        if (!this.hasThumbTarget) return;
        const option = this.current;
        if (!animate) this.thumbTarget.style.transition = 'none';
        this.thumbTarget.style.width = `${option.offsetWidth}px`;
        this.thumbTarget.style.transform = `translateX(${option.offsetLeft}px)`;
        if (!animate) {
            this.thumbTarget.getBoundingClientRect();
            this.thumbTarget.style.transition = '';
        }
    }
}
