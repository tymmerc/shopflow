import { Controller } from '@hotwired/stimulus';

/*
 * Modale / drawer sur <dialog> natif : focus piégé, Échap et voile fournis par le navigateur.
 * Ouverture : événement window `dialog:open` avec { id }. Le focus revient à l'élément d'origine.
 * Un clic sur le voile ferme, sauf si un champ du dialog a été modifié (saisie en cours).
 */
export default class extends Controller {
    connect() {
        this.dirty = false;
        this.markDirty = () => { this.dirty = true; };
        this.element.addEventListener('change', this.markDirty);
    }

    disconnect() {
        this.element.removeEventListener('change', this.markDirty);
        if (this.element.open) this.element.close();
    }

    openFromEvent({ detail }) {
        if (detail?.id === this.element.id) this.open();
    }

    open() {
        if (this.element.open) return;
        document.querySelectorAll('dialog[open]').forEach((d) => d.close());
        this.opener = document.activeElement;
        this.dirty = false;
        this.element.showModal();
        this.dispatch('opened');
    }

    close() {
        this.element.close();
    }

    backdropClose(event) {
        if (event.target !== this.element || this.dirty) return;
        // Le dialog n'a pas de padding : un clic sur lui-même est un clic sur le voile
        const r = this.element.getBoundingClientRect();
        const inside = event.clientX >= r.left && event.clientX <= r.right
            && event.clientY >= r.top && event.clientY <= r.bottom;
        if (!inside) this.close();
    }

    restoreFocus() {
        if (this.opener instanceof HTMLElement && this.opener.isConnected) {
            this.opener.focus({ preventScroll: true });
        }
        this.opener = null;
    }
}
