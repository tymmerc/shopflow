import { Controller } from '@hotwired/stimulus';
import { toast } from '../lib/toast.js';

/*
 * Affiche un message flash Symfony ($this->addFlash()) sous forme de toast.
 * L'élément est retiré aussitôt : le cache de Turbo ne le rejouera pas en revenant en arrière.
 */
export default class extends Controller {
    static values = { type: String, message: String };

    connect() {
        toast(this.messageValue, { type: ['success', 'error', 'info'].includes(this.typeValue) ? this.typeValue : 'info' });
        this.element.remove();
    }
}
