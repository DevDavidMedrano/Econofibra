import { disableWhatsapp, showWhatsappFallback } from '../coverage/page.js';
import { buildMessage, buildUrl, hasNumber } from '../coverage/whatsapp.js';

const PENDING_CARD = 'plan-card--pending';

// El botón de icono del footer conserva su forma: el texto va a su lado (RF-26).
function placeUnavailableNote(button, note) {
    if (button.classList.contains('social-icon')) {
        button.after(note);
    } else {
        button.append(note);
    }
}

function listenWhatsapp(root, number) {
    const area = root.querySelector('[data-whatsapp-fallback]');

    root.addEventListener('click', (event) => {
        if (event.target.closest('[data-whatsapp]')) {
            globalThis.open(buildUrl(number, buildMessage({ purpose: 'contact' })), '_blank', 'noopener');
            showWhatsappFallback(root, area, number);
        }
    });
}

// Sin JavaScript o con movimiento reducido las tarjetas se ven directamente (RF-32).
function revealCards(root) {
    const canAnimate = globalThis.matchMedia?.('(prefers-reduced-motion: no-preference)').matches;

    if (!canAnimate || !globalThis.IntersectionObserver) {
        return;
    }

    const observer = new globalThis.IntersectionObserver((entries) => {
        entries.filter((entry) => entry.isIntersecting).forEach(({ target }) => {
            target.classList.remove(PENDING_CARD);
            observer.unobserve(target);
        });
    }, { threshold: 0.1 });

    root.querySelectorAll('[data-plan-card]').forEach((card) => {
        card.classList.add(PENDING_CARD);
        observer.observe(card);
    });
}

export function mountHomePage(root) {
    const page = root?.querySelector('[data-whatsapp-number]');

    if (!page) {
        return;
    }

    const number = page.dataset.whatsappNumber;

    if (hasNumber(number)) {
        listenWhatsapp(root, number);
    } else {
        disableWhatsapp(root, root, placeUnavailableNote);
    }

    revealCards(root);
}
