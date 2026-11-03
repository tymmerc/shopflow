import { Controller } from '@hotwired/stimulus';
import * as cart from '../lib/cart.js';
import { toast } from '../lib/toast.js';

/*
 * Bouton « Ajouter au panier ». Retour visuel immédiat (« Ajouté »), toast avec lien vers le panier,
 * et blocage explicite quand le stock disponible est atteint.
 */
export default class extends Controller {
    static targets = ['button', 'label', 'quantity'];
    static values = { id: Number, name: String, price: Number, stock: Number, url: String, cartUrl: String };

    connect() {
        this.idleLabel = this.hasLabelTarget ? this.labelTarget.textContent : '';
        this.render = this.render.bind(this);
        window.addEventListener('cart:change', this.render);
        this.render();
    }

    disconnect() {
        window.removeEventListener('cart:change', this.render);
        clearTimeout(this.timer);
    }

    get remaining() {
        return this.stockValue - cart.quantityOf(this.idValue);
    }

    add() {
        const wanted = this.hasQuantityTarget ? Number(this.quantityTarget.value) || 1 : 1;
        const added = cart.add({
            id: this.idValue,
            name: this.nameValue,
            price: this.priceValue,
            stock: this.stockValue,
            url: this.urlValue,
        }, wanted);

        if (added === 0) {
            toast(`Vous avez déjà tout le stock de « ${this.nameValue} » dans votre panier.`, { type: 'error' });
            return;
        }

        if (this.hasLabelTarget) {
            this.labelTarget.textContent = 'Ajouté';
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.labelTarget.textContent = this.idleLabel; }, 1500);
        }

        const partial = added < wanted ? ` (${added} sur ${wanted}, stock atteint)` : '';
        toast(`« ${this.nameValue} » ajouté au panier${partial}`, {
            type: partial ? 'info' : 'success',
            actions: [
                { label: 'Annuler', run: () => this.undo(added) },
                { label: 'Voir le panier', href: this.cartUrlValue },
            ],
        });
    }

    undo(added) {
        const qty = cart.quantityOf(this.idValue) - added;
        if (qty > 0) {
            cart.setQty(this.idValue, qty);
        } else {
            cart.remove(this.idValue);
        }
    }

    render() {
        if (!this.hasButtonTarget || this.stockValue <= 0) return;
        const full = this.remaining <= 0;
        this.buttonTarget.toggleAttribute('disabled', full);
        // L'infobulle est portée par le parent : un bouton désactivé ne reçoit pas le survol
        const wrapper = this.buttonTarget.parentElement;
        if (full) {
            wrapper.dataset.tooltip = 'Tout le stock disponible est déjà dans votre panier';
        } else {
            delete wrapper.dataset.tooltip;
        }
        if (this.hasQuantityTarget) {
            this.quantityTarget.max = String(Math.max(1, this.remaining));
            this.quantityTarget.dispatchEvent(new Event('change'));
        }
    }
}
