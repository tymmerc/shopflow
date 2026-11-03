import { Controller } from '@hotwired/stimulus';
import { count } from '../lib/cart.js';

/* Compteur d'articles sur l'icône panier de la navbar (synchro entre onglets). */
export default class extends Controller {
    static targets = ['count'];

    connect() {
        this.render = this.render.bind(this);
        window.addEventListener('cart:change', this.render);
        this.render();
    }

    disconnect() {
        window.removeEventListener('cart:change', this.render);
    }

    render() {
        const n = count();
        this.countTarget.hidden = n === 0;
        this.countTarget.textContent = n > 99 ? '99+' : String(n);
        const label = n === 0 ? 'Panier vide' : `Panier, ${n} article${n > 1 ? 's' : ''}`;
        this.element.setAttribute('aria-label', label);
        this.element.dataset.tooltip = label;
    }
}
