import { Controller } from '@hotwired/stimulus';
import * as cart from '../lib/cart.js';
import { toast } from '../lib/toast.js';

/*
 * « À prévoir aussi » sur la fiche produit : ajoute d'un coup au panier les accessoires cochés.
 */
export default class extends Controller {
    static targets = ['item', 'button', 'label'];
    static values = { cartUrl: String };

    connect() {
        this.update();
    }

    get checked() {
        return this.itemTargets.filter((input) => input.checked);
    }

    update() {
        const selected = this.checked;
        const sum = selected.reduce((total, input) => total + Number(input.dataset.price), 0);
        this.buttonTarget.disabled = selected.length === 0;
        this.labelTarget.textContent = selected.length === 0
            ? 'Ajouter la sélection'
            : `Ajouter ${selected.length === 1 ? 'l\'article' : `les ${selected.length}`} · ${cart.formatPrice(sum)}`;
    }

    add() {
        const selected = this.checked;
        let added = 0;
        selected.forEach(({ dataset }) => {
            added += cart.add({
                id: Number(dataset.id),
                name: dataset.name,
                price: Number(dataset.price),
                stock: Number(dataset.stock),
                url: dataset.url,
            }, 1) > 0 ? 1 : 0;
        });

        if (added === 0) {
            toast('Ces articles sont déjà dans votre panier, au maximum du stock.', { type: 'error' });
            return;
        }

        toast(`${added} article${added > 1 ? 's' : ''} ajouté${added > 1 ? 's' : ''} au panier`, {
            type: 'success',
            actions: [{ label: 'Voir le panier', href: this.cartUrlValue }],
        });
    }
}
