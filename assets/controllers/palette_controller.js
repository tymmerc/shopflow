import { Controller } from '@hotwired/stimulus';
import { visit } from '../lib/storage.js';

/*
 * Palette de commandes : filtre les options, ↑ ↓ pour naviguer, Entrée pour exécuter.
 * La première option « Rechercher … dans le catalogue » apparaît dès qu'on tape.
 */
export default class extends Controller {
    static targets = ['input', 'list', 'option', 'group', 'searchGroup', 'searchOption', 'query', 'empty'];
    static values = { catalogUrl: String };

    reset() {
        this.inputTarget.value = '';
        this.filter();
        this.inputTarget.focus();
    }

    filter() {
        const q = this.inputTarget.value.trim().toLowerCase();

        this.searchOptionTarget.hidden = q === '';
        this.searchGroupTarget.hidden = q === '';
        this.queryTarget.textContent = this.inputTarget.value.trim();

        this.optionTargets.forEach((option) => {
            if (option === this.searchOptionTarget) return;
            option.hidden = q !== '' && !option.dataset.search.includes(q);
        });
        this.groupTargets.forEach((group) => {
            group.hidden = !this.optionTargets.some((o) => o.dataset.group === group.dataset.group && !o.hidden);
        });

        this.emptyTarget.classList.toggle('hidden', this.visibleOptions.length > 0);
        this.select(this.visibleOptions[0]);
    }

    get visibleOptions() {
        return this.optionTargets.filter((o) => !o.hidden);
    }

    navigate(event) {
        const options = this.visibleOptions;
        const index = options.findIndex((o) => o.getAttribute('aria-selected') === 'true');

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const step = event.key === 'ArrowDown' ? 1 : -1;
            this.select(options[(index + step + options.length) % options.length]);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            const current = options[index];
            if (current) {
                this.execute(current);
            } else if (this.inputTarget.value.trim()) {
                this.execute(this.searchOptionTarget);
            }
        }
    }

    hover(event) {
        this.select(event.currentTarget);
    }

    run(event) {
        this.execute(event.currentTarget);
    }

    select(option) {
        this.optionTargets.forEach((o) => o.setAttribute('aria-selected', String(o === option)));
        if (option) {
            this.inputTarget.setAttribute('aria-activedescendant', option.id);
            option.scrollIntoView({ block: 'nearest' });
        } else {
            this.inputTarget.removeAttribute('aria-activedescendant');
        }
    }

    execute(option) {
        this.element.close();

        if (option === this.searchOptionTarget) {
            const url = new URL(this.catalogUrlValue, window.location.origin);
            url.searchParams.set('q', this.inputTarget.value.trim());
            visit(url.pathname + url.search);
        } else if (option.dataset.href) {
            visit(option.dataset.href);
        } else if (option.dataset.command === 'shortcuts') {
            window.dispatchEvent(new CustomEvent('dialog:open', { detail: { id: 'shortcuts' } }));
        } else if (option.dataset.command) {
            window.dispatchEvent(new CustomEvent(option.dataset.command));
        }
    }
}
