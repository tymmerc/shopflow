import { Controller } from '@hotwired/stimulus';
import * as cart from '../lib/cart.js';
import { toast } from '../lib/toast.js';

/*
 * Page panier : lignes rendues depuis le <template>, mises à jour sur place
 * (le focus reste sur le bouton − / + utilisé). Suppression immédiate + toast « Annuler ».
 */
export default class extends Controller {
    static targets = ['loading', 'content', 'list', 'template', 'empty', 'count', 'subtotal', 'total', 'checkout'];

    connect() {
        this.render = this.render.bind(this);
        window.addEventListener('cart:change', this.render);
        this.render();
    }

    disconnect() {
        window.removeEventListener('cart:change', this.render);
    }

    render() {
        // Tant que le serveur n'a pas répondu, le squelette reste affiché (et pas « panier vide »)
        if (!cart.isLoaded()) return;
        if (this.hasLoadingTarget) this.loadingTarget.remove();

        const items = cart.items();
        const n = items.reduce((sum, i) => sum + i.qty, 0);

        this.emptyTarget.hidden = items.length > 0;
        this.contentTarget.hidden = items.length === 0;
        this.countTarget.textContent = `${n} article${n > 1 ? 's' : ''}`;
        this.subtotalTarget.textContent = cart.formatPrice(cart.total());
        this.totalTarget.textContent = cart.formatPrice(cart.total());

        const rows = new Map([...this.listTarget.children].map((row) => [Number(row.dataset.id), row]));
        items.forEach((item, index) => {
            const row = rows.get(item.id) ?? this.buildRow(item);
            rows.delete(item.id);
            this.fillRow(row, item);
            if (this.listTarget.children[index] !== row) {
                this.listTarget.insertBefore(row, this.listTarget.children[index] ?? null);
            }
        });
        rows.forEach((row) => row.remove());
    }

    buildRow(item) {
        const row = this.templateTarget.content.firstElementChild.cloneNode(true);
        row.dataset.id = item.id;
        row.querySelectorAll('[data-cart-id-param]').forEach((el) => { el.dataset.cartIdParam = item.id; });
        const input = row.querySelector('input');
        input.id = `qty-${item.id}`;
        row.querySelector('label').htmlFor = input.id;

        return row;
    }

    fillRow(row, item) {
        const field = (name) => row.querySelector(`[data-field="${name}"]`);
        field('name').textContent = item.name;
        field('name').href = item.url;
        field('price').textContent = `${cart.formatPrice(item.price)} l'unité`;
        field('total').textContent = cart.formatPrice(item.price * item.qty);
        field('remove').setAttribute('aria-label', `Retirer « ${item.name} » du panier`);
        field('label').textContent = `Quantité de ${item.name}`;

        const input = row.querySelector('input');
        input.max = String(item.stock);
        if (document.activeElement !== input) input.value = String(item.qty);

        const dec = field('decrement');
        const inc = field('increment');
        dec.disabled = item.qty <= 1;
        inc.disabled = item.qty >= item.stock;
        this.tooltip(dec, item.qty <= 1 ? 'Utilisez « Retirer » pour supprimer l\'article' : null);
        this.tooltip(inc, item.qty >= item.stock ? `Stock disponible : ${item.stock}` : null);
    }

    tooltip(button, text) {
        if (text) {
            button.parentElement.dataset.tooltip = text;
        } else {
            delete button.parentElement.dataset.tooltip;
        }
    }

    increment({ params: { id } }) {
        cart.setQty(id, cart.quantityOf(id) + 1);
    }

    decrement({ params: { id } }) {
        cart.setQty(id, cart.quantityOf(id) - 1);
    }

    // Saisie directe : validée au blur / Entrée, jamais à chaque frappe
    change({ params: { id }, target }) {
        const value = Number.parseInt(target.value, 10);
        const item = cart.items().find((i) => i.id === id);
        if (!item) return;

        if (!Number.isFinite(value) || value < 1) {
            target.value = String(item.qty);
            toast('Saisissez une quantité d\'au moins 1, ou utilisez « Retirer ».', { type: 'error' });
            return;
        }
        if (value > item.stock) {
            toast(`Il ne reste que ${item.stock} exemplaire${item.stock > 1 ? 's' : ''} de « ${item.name} ».`, { type: 'info' });
        }
        cart.setQty(id, value);
        target.value = String(cart.quantityOf(id));
    }

    remove({ params: { id } }) {
        const removed = cart.remove(id);
        if (!removed) return;
        toast(`« ${removed.item.name} » retiré du panier`, {
            actions: [{ label: 'Annuler', run: () => cart.restore(removed) }],
        });
    }

    // Attend que les derniers +/− soient enregistrés : la commande est créée à partir du panier en base
    async checkout(event) {
        event.preventDefault();
        this.checkoutTarget.disabled = true;
        await cart.synced();
        event.target.submit();
    }

    clear() {
        const previous = cart.items();
        const n = previous.reduce((sum, i) => sum + i.qty, 0);
        cart.replaceAll([]);
        toast(`Panier vidé (${n} article${n > 1 ? 's' : ''})`, {
            actions: [{ label: 'Annuler', run: () => cart.replaceAll(previous) }],
        });
    }
}
