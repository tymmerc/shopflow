import { Controller } from '@hotwired/stimulus';

const ICONS = {
    success: '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
    error: '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
};
const TONE = { success: 'success', error: 'danger', info: 'info' };
const svg = (paths, size = 20) =>
    `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;

/*
 * Zone de toasts (bas droite, 360px, empilés). Succès/info : 4 s ; erreur : jusqu'à fermeture.
 * Quand une modale est ouverte, le toast s'affiche dans son [data-toast-host] pour rester visible
 * (le reste de la page est inerte derrière un <dialog> modal).
 */
export default class extends Controller {
    show({ detail }) {
        const { message, type = 'success', actions = [], duration } = detail;
        const el = document.createElement('div');
        el.className = 'toast';
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');

        el.innerHTML = `
            <span class="mt-px toast-icon-${TONE[type] ?? 'info'}">${svg(ICONS[type] ?? ICONS.info)}</span>
            <div class="min-w-0 flex-1 pt-0.5">
                <p></p>
                <div class="-ml-2.5 mt-1 flex flex-wrap gap-1" hidden></div>
            </div>`;
        el.querySelector('p').textContent = message;

        const bar = el.querySelector('div > div');
        bar.hidden = actions.length === 0;
        actions.forEach(({ label, run, href }) => {
            const btn = document.createElement(href ? 'a' : 'button');
            btn.className = 'btn btn-ghost btn-sm font-semibold text-link';
            btn.textContent = label;
            if (href) {
                btn.href = href;
            } else {
                btn.type = 'button';
            }
            btn.addEventListener('click', () => {
                run?.();
                this.dismiss(el);
            });
            bar.append(btn);
        });

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn btn-ghost btn-icon btn-sm -my-0.5';
        close.setAttribute('aria-label', 'Fermer la notification');
        close.innerHTML = svg('<path d="M18 6 6 18"/><path d="m6 6 12 12"/>', 16);
        close.addEventListener('click', () => this.dismiss(el));
        el.append(close);

        this.host.append(el);

        const ttl = duration ?? (type === 'error' ? 0 : actions.length ? 5000 : 4000);
        if (ttl > 0) {
            let timer = setTimeout(() => this.dismiss(el), ttl);
            // On ne retire pas un toast pendant qu'on le lit ou qu'on va cliquer dessus
            el.addEventListener('mouseenter', () => clearTimeout(timer));
            el.addEventListener('focusin', () => clearTimeout(timer));
            el.addEventListener('mouseleave', () => { timer = setTimeout(() => this.dismiss(el), 2000); });
        }
    }

    get host() {
        const dialog = document.querySelector('dialog[open]');
        if (!dialog) return this.element;

        let zone = dialog.querySelector(':scope > [data-toast-host]');
        if (!zone) {
            zone = document.createElement('div');
            zone.className = 'toast-region';
            zone.dataset.toastHost = '';
            zone.setAttribute('aria-live', 'polite');
            dialog.append(zone);
        }

        return zone;
    }

    dismiss(el) {
        if (el.classList.contains('is-leaving')) return;
        el.classList.add('is-leaving');
        el.addEventListener('animationend', () => el.remove(), { once: true });
        setTimeout(() => el.remove(), 300);
    }
}
