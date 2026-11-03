// Affiche une notification via le contrôleur toast de la page.
//   toast('Article ajouté', { type: 'success', actions: [{ label: 'Annuler', run: () => … }] })
// type : 'success' (disparaît après 4 s), 'info' (idem), 'error' (reste jusqu'à fermeture).
export function toast(message, { type = 'success', actions = [], duration } = {}) {
    window.dispatchEvent(new CustomEvent('toast:show', {
        detail: { message, type, actions, duration },
    }));
}
