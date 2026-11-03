import { Controller } from '@hotwired/stimulus';
import { load, save, remove } from '../lib/storage.js';

const MODES = ['system', 'light', 'dark'];
const LABELS = { system: 'système', light: 'clair', dark: 'sombre' };

/* Thème : système → clair → sombre. Mémorisé, appliqué via html[data-theme]. */
export default class extends Controller {
    static targets = ['icon'];

    connect() {
        const stored = load('theme', 'system');
        this.mode = MODES.includes(stored) ? stored : 'system';
        this.render();
    }

    cycle() {
        this.mode = MODES[(MODES.indexOf(this.mode) + 1) % MODES.length];

        if (this.mode === 'system') {
            remove('theme');
            delete document.documentElement.dataset.theme;
        } else {
            save('theme', this.mode);
            document.documentElement.dataset.theme = this.mode;
        }

        this.render();
    }

    render() {
        const next = MODES[(MODES.indexOf(this.mode) + 1) % MODES.length];
        const label = `Thème ${LABELS[this.mode]} — passer en ${LABELS[next]}`;
        this.element.setAttribute('aria-label', label);
        this.element.dataset.tooltip = label;
        this.iconTargets.forEach((icon) => { icon.hidden = icon.dataset.mode !== this.mode; });
    }
}
