// Panier enregistré côté serveur (API /api/cart). L'état local change tout de suite pour que l'interface
// réagisse sans attendre, puis la requête part ; la réponse du serveur fait foi (prix, stock).
// Chaque changement émet `cart:change` sur window ; les autres onglets suivent via BroadcastChannel.
import { remove as forget } from './storage.js';
import { toast } from './toast.js';

const API = '/api/cart';

let state = [];
let loaded = false;
let pending = 0;
let queue = Promise.resolve();

const channel = 'BroadcastChannel' in window ? new BroadcastChannel('shopflow.cart') : null;

// L'ancien panier localStorage n'est pas repris : on fait juste le ménage
forget('cart');

// state n'est jamais modifié sur place : un instantané pris avant un changement reste valable (Annuler)
function commit(list) {
    state = list;
    window.dispatchEvent(new CustomEvent('cart:change', { detail: { items: list } }));
}

// Garde l'ordre affiché : un article restauré via « Annuler » reprend sa place
function receive(list) {
    const position = new Map(state.map((item, index) => [item.id, index]));
    loaded = true;
    commit([...list].sort((a, b) => (position.get(a.id) ?? state.length) - (position.get(b.id) ?? state.length)));
}

async function request(method, path = '', body = undefined) {
    const response = await fetch(API + path, {
        method,
        headers: body === undefined
            ? { Accept: 'application/json' }
            : { Accept: 'application/json', 'Content-Type': 'application/json' },
        body: body === undefined ? undefined : JSON.stringify(body),
    });
    if (!response.ok) throw new Error(`${method} ${API}${path} : HTTP ${response.status}`);

    return (await response.json()).items;
}

// Requêtes envoyées une par une, dans l'ordre des clics. Tant que d'autres attendent, on ignore
// les réponses intermédiaires pour ne pas faire « reculer » l'affichage pendant une rafale de +.
function sync(method, path = '', body = undefined) {
    pending++;
    queue = queue
        .then(() => request(method, path, body))
        .then((list) => {
            if (--pending > 0) return;
            receive(list);
            channel?.postMessage(list);
        })
        .catch((error) => {
            pending--;
            console.error(error);
            toast('Le panier n\'a pas pu être enregistré. Réessayez.', { type: 'error' });
            refresh();
        });
}

function refresh() {
    queue = queue
        .then(() => request('GET'))
        .then((list) => { if (pending === 0) receive(list); })
        .catch((error) => {
            console.error(error);
            if (!loaded) toast('Impossible de charger le panier. Rechargez la page.', { type: 'error' });
        });
}

channel?.addEventListener('message', ({ data }) => { if (pending === 0) receive(data); });
refresh();

export function isLoaded() {
    return loaded;
}

export function items() {
    return state;
}

export function count() {
    return state.reduce((sum, i) => sum + i.qty, 0);
}

export function total() {
    return state.reduce((sum, i) => sum + i.qty * i.price, 0);
}

export function quantityOf(id) {
    return state.find((i) => i.id === id)?.qty ?? 0;
}

// Renvoie la quantité réellement ajoutée (bornée par le stock).
export function add(product, qty = 1) {
    const existing = state.find((i) => i.id === product.id);
    const current = existing?.qty ?? 0;
    const max = Number.isFinite(product.stock) ? product.stock : Infinity;
    const added = Math.max(0, Math.min(qty, max - current));

    if (added === 0) return 0;

    commit(existing
        ? state.map((i) => (i.id === product.id ? { ...i, ...product, qty: current + added } : i))
        : [...state, { ...product, qty: added }]);
    // Ajout relatif : reste juste même si le panier n'était pas encore chargé
    sync('POST', `/items/${product.id}`, { quantity: added });

    return added;
}

export function setQty(id, qty) {
    const item = state.find((i) => i.id === id);
    if (!item) return;
    const next = Math.max(1, Math.min(qty, item.stock ?? Infinity));
    if (next === item.qty) return;
    commit(state.map((i) => (i.id === id ? { ...i, qty: next } : i)));
    sync('PUT', `/items/${id}`, { quantity: next });
}

// Renvoie de quoi annuler : { item, index }.
export function remove(id) {
    const index = state.findIndex((i) => i.id === id);
    if (index === -1) return null;
    const item = state[index];
    commit(state.filter((i) => i.id !== id));
    sync('DELETE', `/items/${id}`);

    return { item, index };
}

export function restore({ item, index }) {
    const list = state.filter((i) => i.id !== item.id);
    list.splice(Math.min(index, list.length), 0, item);
    commit(list);
    sync('PUT', `/items/${item.id}`, { quantity: item.qty });
}

export function replaceAll(list) {
    commit(list);
    if (list.length === 0) {
        sync('DELETE');
    } else {
        sync('PUT', '', { items: list.map(({ id, qty }) => ({ id, quantity: qty })) });
    }
}

export const formatPrice = (value) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
