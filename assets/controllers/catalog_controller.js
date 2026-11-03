import { Controller } from '@hotwired/stimulus';
import { load, save } from '../lib/storage.js';

const DEFAULTS = { sort: 'default', view: 'grid', inStock: false };
const normalize = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

/*
 * Filtre, tri et vue du catalogue, côté navigateur.
 * Tri, vue et « en stock » sont mémorisés ; la recherche vit dans l'URL (?q=) pour être partageable.
 */
export default class extends Controller {
    static targets = ['list', 'item', 'query', 'inStock', 'sort', 'view', 'count', 'empty', 'emptyQuery'];

    connect() {
        const prefs = { ...DEFAULTS, ...load('catalog', {}) };
        this.view = prefs.view;

        this.queryTarget.value = new URLSearchParams(window.location.search).get('q') ?? '';
        this.inStockTarget.checked = prefs.inStock;
        if ([...this.sortTarget.options].some((o) => o.value === prefs.sort)) this.sortTarget.value = prefs.sort;

        this.applyView();
        this.apply();
    }

    apply() {
        const q = normalize(this.queryTarget.value);
        const inStock = this.inStockTarget.checked;
        let visible = 0;

        this.itemTargets.forEach((item) => {
            const match = (!q || normalize(item.dataset.search).includes(q))
                && (!inStock || Number(item.dataset.stock) > 0);
            item.hidden = !match;
            if (match) visible++;
        });

        this.sortItems();
        this.renderCount(visible);
        this.emptyTarget.hidden = visible > 0;
        this.listTarget.hidden = visible === 0;
        this.emptyQueryTarget.textContent = this.describeFilters(q, inStock);

        this.persist();
    }

    sortItems() {
        const by = {
            default: (a, b) => a.dataset.index - b.dataset.index,
            'price-asc': (a, b) => a.dataset.price - b.dataset.price,
            'price-desc': (a, b) => b.dataset.price - a.dataset.price,
            name: (a, b) => a.dataset.name.localeCompare(b.dataset.name, 'fr'),
        }[this.sortTarget.value] ?? (() => 0);

        [...this.itemTargets].sort(by).forEach((item) => this.listTarget.append(item));
    }

    renderCount(visible) {
        const total = this.itemTargets.length;
        const plural = (n) => `${n} article${n > 1 ? 's' : ''}`;
        this.countTarget.textContent = visible === total ? plural(total) : `${visible} sur ${plural(total)}`;
    }

    describeFilters(q, inStock) {
        const parts = [];
        if (q) parts.push(`« ${this.queryTarget.value.trim()} »`);
        if (inStock) parts.push('en stock');
        return parts.length ? `Aucun article ne correspond à ${parts.join(' et ')}.` : 'Aucun article à afficher.';
    }

    clearOnEscape(event) {
        if (event.key === 'Escape' && this.queryTarget.value) {
            event.preventDefault();
            this.queryTarget.value = '';
            this.apply();
        }
    }

    reset() {
        this.queryTarget.value = '';
        this.inStockTarget.checked = false;
        this.apply();
        this.queryTarget.focus();
    }

    setView({ detail: { value } }) {
        this.view = value;
        this.applyView();
        this.persist();
    }

    applyView() {
        this.listTarget.dataset.view = this.view;
        // Le contrôle segmenté lit aria-checked à sa connexion ; s'il est déjà connecté, on le resynchronise
        this.viewTarget.querySelectorAll('[role="radio"]').forEach((option) => {
            option.setAttribute('aria-checked', String(option.dataset.value === this.view));
        });
        this.application.getControllerForElementAndIdentifier(this.viewTarget, 'segmented')?.sync(false);
    }

    persist() {
        save('catalog', { sort: this.sortTarget.value, view: this.view, inStock: this.inStockTarget.checked });

        const url = new URL(window.location.href);
        const q = this.queryTarget.value.trim();
        if (q) {
            url.searchParams.set('q', q);
        } else {
            url.searchParams.delete('q');
        }
        if (url.href !== window.location.href) {
            window.history.replaceState(window.history.state, '', url);
        }
    }
}
