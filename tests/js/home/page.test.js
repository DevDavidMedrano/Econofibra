import { afterEach, describe, expect, it, vi } from 'vitest';
import { mountHomePage } from '../../../resources/js/home/page.js';

const NUMBER = '+00 000 000 0000';
const PENDING = 'plan-card--pending';

const fakeButton = (...classes) => ({
    dataset: { whatsapp: 'contact' },
    disabled: false,
    inside: [],
    beside: [],
    classList: { contains: (name) => classes.includes(name) },
    append(node) { this.inside.push(node); },
    after(node) { this.beside.push(node); },
    closest(selector) { return selector === '[data-whatsapp]' ? this : null; },
});

const fakeCard = () => {
    const classes = new Set();

    return {
        classes,
        classList: { add: (name) => classes.add(name), remove: (name) => classes.delete(name) },
    };
};

const fakePage = (number) => {
    const listeners = {};
    const slot = { textContent: '' };
    const note = { querySelector: () => slot };
    const area = { hidden: true, replaceChildren: vi.fn() };
    const buttons = [fakeButton('btn-primary'), fakeButton('home-call__button'), fakeButton('social-icon')];
    const cards = [fakeCard(), fakeCard()];
    const parts = {
        '[data-whatsapp-number]': { dataset: { whatsappNumber: number } },
        '[data-whatsapp-fallback]': area,
        '#tpl-whatsapp-fallback': { content: { cloneNode: () => note } },
        '#tpl-whatsapp-unavailable': { content: { cloneNode: () => ({ unavailable: true }) } },
    };
    const root = {
        querySelector: (selector) => parts[selector] ?? null,
        querySelectorAll: (selector) => (selector === '[data-plan-card]' ? cards : buttons),
        addEventListener: (type, listener) => {
            listeners[type] = listener;
        },
    };

    return { root, listeners, slot, note, area, buttons, cards };
};

const motion = (allowed) => {
    const observer = { observe: vi.fn(), unobserve: vi.fn() };
    let notify;

    vi.stubGlobal('matchMedia', vi.fn(() => ({ matches: allowed })));
    vi.stubGlobal('IntersectionObserver', vi.fn(function fake(callback) {
        notify = callback;

        return observer;
    }));

    return { observer, notify: (entries) => notify(entries) };
};

describe('mountHomePage', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('no hace nada sin documento', () => {
        expect(() => mountHomePage(undefined)).not.toThrow();
    });

    it('no hace nada en una página que no es la principal', () => {
        const addEventListener = vi.fn();

        mountHomePage({ querySelector: () => null, addEventListener });

        expect(addEventListener).not.toHaveBeenCalled();
    });

    describe('con número configurado', () => {
        const click = (page, button) => page.listeners.click({ target: button });

        it('abre WhatsApp con el mensaje de contacto, sin ubicación, zona ni plan (RF-25)', () => {
            const open = vi.fn();
            const page = fakePage(NUMBER);

            vi.stubGlobal('open', open);
            mountHomePage(page.root);
            click(page, page.buttons[1]);

            const [url, target] = open.mock.calls[0];
            const text = new URL(url).searchParams.get('text');

            expect(open).toHaveBeenCalledTimes(1);
            expect(url.startsWith('https://wa.me/000000000000?text=')).toBe(true);
            expect(target).toBe('_blank');
            expect(text).toBe('Hola, quiero ponerme en contacto con el equipo de Econofibra JLINK.');
            expect(text).not.toMatch(/Ubicación|Zona|Plan/);
        });

        it('muestra el número en el aviso fijo (RF-27)', () => {
            const page = fakePage(NUMBER);

            vi.stubGlobal('open', vi.fn());
            mountHomePage(page.root);
            click(page, page.buttons[0]);

            expect(page.slot.textContent).toBe(NUMBER);
            expect(page.area.replaceChildren).toHaveBeenCalledWith(page.note);
            expect(page.area.hidden).toBe(false);
        });

        it('ignora los clics fuera de los botones de WhatsApp', () => {
            const open = vi.fn();
            const page = fakePage(NUMBER);

            vi.stubGlobal('open', open);
            mountHomePage(page.root);
            page.listeners.click({ target: { closest: () => null } });

            expect(open).not.toHaveBeenCalled();
            expect(page.area.hidden).toBe(true);
        });

        it('deja activos los botones', () => {
            const page = fakePage(NUMBER);

            mountHomePage(page.root);

            expect(page.buttons.every((button) => !button.disabled)).toBe(true);
        });
    });

    describe('sin número configurado (RF-26)', () => {
        it.each(['', '   ', undefined])('desactiva todos los botones con el número %j', (number) => {
            const page = fakePage(number);

            mountHomePage(page.root);

            expect(page.buttons.every((button) => button.disabled)).toBe(true);
            expect(page.listeners.click).toBeUndefined();
        });

        it('pone el texto dentro de los botones con texto', () => {
            const page = fakePage('');

            mountHomePage(page.root);

            expect(page.buttons[0].inside).toEqual([{ unavailable: true }]);
            expect(page.buttons[1].inside).toEqual([{ unavailable: true }]);
            expect(page.buttons[0].beside).toEqual([]);
        });

        it('pone el texto junto al botón de icono del footer, fuera de él', () => {
            const page = fakePage('');

            mountHomePage(page.root);

            expect(page.buttons[2].inside).toEqual([]);
            expect(page.buttons[2].beside).toEqual([{ unavailable: true }]);
        });
    });

    describe('aparición de las tarjetas', () => {
        it('las oculta hasta que entran en la vista y deja de observarlas (RF-29)', () => {
            const page = fakePage(NUMBER);
            const { observer, notify } = motion(true);

            mountHomePage(page.root);

            expect(page.cards.every((card) => card.classes.has(PENDING))).toBe(true);
            expect(observer.observe).toHaveBeenCalledTimes(2);

            notify([
                { isIntersecting: true, target: page.cards[0] },
                { isIntersecting: false, target: page.cards[1] },
            ]);

            expect(page.cards[0].classes.has(PENDING)).toBe(false);
            expect(page.cards[1].classes.has(PENDING)).toBe(true);
            expect(observer.unobserve).toHaveBeenCalledTimes(1);
            expect(observer.unobserve).toHaveBeenCalledWith(page.cards[0]);
        });

        it('no las toca si el dispositivo pide reducir el movimiento (RF-32)', () => {
            const page = fakePage(NUMBER);
            const { observer } = motion(false);

            mountHomePage(page.root);

            expect(globalThis.matchMedia).toHaveBeenCalledWith('(prefers-reduced-motion: no-preference)');
            expect(page.cards.some((card) => card.classes.has(PENDING))).toBe(false);
            expect(observer.observe).not.toHaveBeenCalled();
        });

        it('las deja visibles sin IntersectionObserver', () => {
            const page = fakePage(NUMBER);

            vi.stubGlobal('matchMedia', () => ({ matches: true }));
            mountHomePage(page.root);

            expect(page.cards.some((card) => card.classes.has(PENDING))).toBe(false);
        });
    });
});
