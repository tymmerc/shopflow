import { Controller } from '@hotwired/stimulus';
import { isEditable, isMac } from '../lib/storage.js';

/*
 * Raccourcis globaux de l'application (posé sur <body>).
 *   ⌘K / Ctrl+K  palette de commandes
 *   ?            aide des raccourcis
 *   / ou ⌘F      focus sur le filtre de la page (s'il existe)
 */
export default class extends Controller {
    static targets = ['filter'];

    connect() {
        if (!isMac) {
            this.element.querySelectorAll('[data-mod-key]').forEach((el) => {
                el.textContent = el.textContent.replace('⌘', 'Ctrl ').trim();
            });
        }
    }

    open({ params: { dialog } }) {
        this.openDialog(dialog);
    }

    openDialog(id) {
        window.dispatchEvent(new CustomEvent('dialog:open', { detail: { id } }));
    }

    onKey(event) {
        const mod = isMac ? event.metaKey : event.ctrlKey;
        const key = event.key.toLowerCase();

        if (mod && key === 'k') {
            event.preventDefault();
            const palette = document.getElementById('palette');
            palette?.open ? palette.close() : this.openDialog('palette');
            return;
        }

        if (mod && key === 'f' && this.hasFilterTarget) {
            event.preventDefault();
            this.focusFilter();
            return;
        }

        if (event.defaultPrevented || mod || event.altKey || isEditable(event.target)) return;
        if (document.querySelector('dialog[open]')) return;

        if (event.key === '?') {
            event.preventDefault();
            this.openDialog('shortcuts');
        } else if (event.key === '/') {
            event.preventDefault();
            this.hasFilterTarget ? this.focusFilter() : this.openDialog('palette');
        }
    }

    focusFilter() {
        this.filterTarget.focus();
        this.filterTarget.select();
    }
}
