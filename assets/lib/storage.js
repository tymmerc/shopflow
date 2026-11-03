// Préférences utilisateur (thème, vue, tri…). localStorage peut être absent ou bloqué :
// on ne casse jamais la page pour ça, on retombe sur la valeur par défaut.
const PREFIX = 'shopflow.';

export function load(key, fallback = null) {
    try {
        const raw = localStorage.getItem(PREFIX + key);
        return raw === null ? fallback : JSON.parse(raw);
    } catch {
        return fallback;
    }
}

export function save(key, value) {
    try {
        localStorage.setItem(PREFIX + key, JSON.stringify(value));
    } catch {
        // stockage plein ou désactivé : la préférence ne sera simplement pas mémorisée
    }
}

export function remove(key) {
    try {
        localStorage.removeItem(PREFIX + key);
    } catch {}
}

export const isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);

export function isEditable(el) {
    return el instanceof HTMLElement
        && (el.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName));
}

export function visit(url) {
    if (window.Turbo) {
        window.Turbo.visit(url);
    } else {
        window.location.href = url;
    }
}
