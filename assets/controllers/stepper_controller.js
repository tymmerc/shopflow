import { Controller } from '@hotwired/stimulus';

/* Sélecteur de quantité − [n] + borné par min/max de l'input. ↑ ↓ fonctionnent nativement. */
export default class extends Controller {
    static targets = ['input', 'decrement', 'increment'];

    connect() {
        this.clamp();
    }

    decrement() {
        this.step(-1);
    }

    increment() {
        this.step(1);
    }

    step(delta) {
        this.inputTarget.value = String(this.value + delta);
        this.clamp();
        this.dispatch('change', { detail: { value: this.value } });
    }

    get value() {
        return Number.parseInt(this.inputTarget.value, 10) || 0;
    }

    get min() {
        return Number(this.inputTarget.min || 1);
    }

    get max() {
        return Number(this.inputTarget.max || Infinity);
    }

    clamp() {
        const value = Math.min(Math.max(this.value, this.min), this.max);
        if (String(value) !== this.inputTarget.value) this.inputTarget.value = String(value);

        this.decrementTarget.disabled = value <= this.min;
        this.incrementTarget.disabled = value >= this.max;
        this.tooltip(this.decrementTarget, value <= this.min ? `Minimum ${this.min}` : null);
        this.tooltip(this.incrementTarget, value >= this.max ? `Stock disponible : ${this.max}` : null);
    }

    // Infobulle sur le parent : un bouton désactivé ne reçoit pas le survol
    tooltip(button, text) {
        if (text) {
            button.parentElement.dataset.tooltip = text;
        } else {
            delete button.parentElement.dataset.tooltip;
        }
    }
}
