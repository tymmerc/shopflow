// Panier côté navigateur, en attendant le contexte Cart côté serveur.
// Chaque changement émet `cart:change` sur window (et `storage` synchronise les autres onglets).
import { load, save } from './storage.js';

const KEY = 'cart';

function read() {
    const items = load(KEY, []);
    return Array.isArray(items) ? items.filter((i) => i && i.id && i.qty > 0) : [];
}

function write(items) {
    save(KEY, items);
    window.dispatchEvent(new CustomEvent('cart:change', { detail: { items } }));
}

export function items() {
    return read();
}

export function count() {
    return read().reduce((sum, i) => sum + i.qty, 0);
}

export function total() {
    return read().reduce((sum, i) => sum + i.qty * i.price, 0);
}

export function quantityOf(id) {
    return read().find((i) => i.id === id)?.qty ?? 0;
}

// Renvoie la quantité réellement ajoutée (bornée par le stock).
export function add(product, qty = 1) {
    const list = read();
    const existing = list.find((i) => i.id === product.id);
    const current = existing?.qty ?? 0;
    const max = Number.isFinite(product.stock) ? product.stock : Infinity;
    const added = Math.max(0, Math.min(qty, max - current));

    if (added === 0) return 0;

    if (existing) {
        Object.assign(existing, product, { qty: current + added });
    } else {
        list.push({ ...product, qty: added });
    }
    write(list);

    return added;
}

export function setQty(id, qty) {
    const list = read();
    const item = list.find((i) => i.id === id);
    if (!item) return;
    item.qty = Math.max(1, Math.min(qty, item.stock ?? Infinity));
    write(list);
}

// Renvoie de quoi annuler : { item, index }.
export function remove(id) {
    const list = read();
    const index = list.findIndex((i) => i.id === id);
    if (index === -1) return null;
    const [item] = list.splice(index, 1);
    write(list);

    return { item, index };
}

export function restore({ item, index }) {
    const list = read().filter((i) => i.id !== item.id);
    list.splice(Math.min(index, list.length), 0, item);
    write(list);
}

export function replaceAll(list) {
    write(list);
}

export const formatPrice = (value) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
