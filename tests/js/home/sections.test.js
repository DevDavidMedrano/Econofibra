import { afterEach, describe, expect, it, vi } from 'vitest';
import { watchSections } from '../../../resources/js/home/sections.js';

const fakeLink = (href) => {
    const classes = new Set();
    const attributes = { href };

    return {
        classes,
        attributes,
        classList: { toggle: (name, on) => (on ? classes.add(name) : classes.delete(name)) },
        getAttribute: (name) => attributes[name],
        setAttribute: (name, value) => { attributes[name] = value; },
        removeAttribute: (name) => { delete attributes[name]; },
    };
};

const fakeRoot = (sections, links) => ({
    querySelectorAll: (selector) => (selector === '[data-page-section]' ? sections : links),
});

describe('watchSections', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('does nothing without a document or sections', () => {
        expect(() => watchSections(undefined)).not.toThrow();
        expect(() => watchSections(fakeRoot([], []))).not.toThrow();
    });

    it('marks only the menu option of the section in view', () => {
        let notify;

        vi.stubGlobal('IntersectionObserver', class {
            constructor(callback) { notify = callback; }

            observe() {}
        });

        const home = fakeLink('#inicio');
        const services = fakeLink('#servicios');

        home.classes.add('nav-option--active');
        home.attributes['aria-current'] = 'true';

        const section = { id: 'servicios' };

        watchSections(fakeRoot([section], [home, services]));
        notify([{ isIntersecting: true, target: section }]);

        expect(services.classes.has('nav-option--active')).toBe(true);
        expect(services.attributes['aria-current']).toBe('true');
        expect(home.classes.has('nav-option--active')).toBe(false);
        expect(home.attributes['aria-current']).toBeUndefined();
    });
});
