import { afterEach, describe, expect, it, vi } from 'vitest';
import { createCoverageSession, mountCoveragePage } from '../../../resources/js/coverage/page.js';

const LOCATION = { latitude: 20, longitude: -100, accuracy: 30 };
const RESULT = { scenario: 'A', reason: null };

const deferred = () => {
    const parts = {};

    parts.promise = new Promise((resolve) => {
        parts.resolve = resolve;
    });

    return parts;
};

const tick = () => new Promise((resolve) => setTimeout(resolve));

const build = () => {
    const located = [];
    const checked = [];
    const onChange = vi.fn();
    const requestLocation = vi.fn(() => {
        const next = deferred();
        located.push(next);

        return next.promise;
    });
    const checkCoverage = vi.fn(() => {
        const next = deferred();
        checked.push(next);

        return next.promise;
    });
    const session = createCoverageSession({ requestLocation, checkCoverage, onChange });

    session.dispatch({ type: 'answer', value: 'yes' });

    return { session, located, checked, onChange, requestLocation, checkCoverage };
};

describe('createCoverageSession', () => {
    it('no solicita la ubicación sin confirmación (RF-7, RF-8)', () => {
        const requestLocation = vi.fn();
        const session = createCoverageSession({ requestLocation, checkCoverage: vi.fn(), onChange: vi.fn() });

        session.dispatch({ type: 'share' });
        session.dispatch({ type: 'answer', value: 'no' });
        session.dispatch({ type: 'share' });

        expect(requestLocation).not.toHaveBeenCalled();
    });

    it('recorre ubicación, servidor y resultado', async () => {
        const { session, located, checked, checkCoverage } = build();

        session.dispatch({ type: 'share' });
        expect(session.getState().phase).toBe('locating');

        located[0].resolve({ status: 'ok', location: LOCATION });
        await tick();
        expect(session.getState().phase).toBe('checking');
        expect(checkCoverage).toHaveBeenCalledWith(LOCATION);

        checked[0].resolve({ status: 'ok', result: RESULT });
        await tick();
        expect(session.getState()).toMatchObject({ phase: 'result', result: RESULT, location: LOCATION });
    });

    it('avisa de cada cambio con el estado nuevo y el anterior', () => {
        const { session, onChange } = build();

        onChange.mockClear();
        session.dispatch({ type: 'share' });

        expect(onChange).toHaveBeenCalledTimes(1);
        expect(onChange.mock.calls[0][0].phase).toBe('locating');
        expect(onChange.mock.calls[0][1].phase).toBe('idle');
    });

    it('no avisa si el evento se descarta', () => {
        const { session, onChange } = build();

        session.dispatch({ type: 'share' });
        onChange.mockClear();
        session.dispatch({ type: 'share' });

        expect(onChange).not.toHaveBeenCalled();
    });

    it('una doble pulsación solicita la ubicación una sola vez (RF-39)', () => {
        const { session, requestLocation } = build();

        session.dispatch({ type: 'share' });
        session.dispatch({ type: 'share' });

        expect(requestLocation).toHaveBeenCalledTimes(1);
    });

    it('sin posición no llama al servidor (RF-10)', async () => {
        const { session, located, checkCoverage } = build();

        session.dispatch({ type: 'share' });
        located[0].resolve({ status: 'failed', cause: 'denied' });
        await tick();

        expect(session.getState().message).toBe('location-denied');
        expect(checkCoverage).not.toHaveBeenCalled();
    });

    it('un 422 lleva al mensaje de rechazo (RF-32)', async () => {
        const { session, located, checked } = build();

        session.dispatch({ type: 'share' });
        located[0].resolve({ status: 'ok', location: LOCATION });
        await tick();
        checked[0].resolve({ status: 'rejected' });
        await tick();

        expect(session.getState().message).toBe('rejected');
    });

    it('un fallo lleva al mensaje de error (RF-27)', async () => {
        const { session, located, checked } = build();

        session.dispatch({ type: 'share' });
        located[0].resolve({ status: 'ok', location: LOCATION });
        await tick();
        checked[0].resolve({ status: 'failed' });
        await tick();

        expect(session.getState().message).toBe('system-error');
    });

    it('cada reintento solicita una posición nueva (RF-9, RF-38)', async () => {
        const { session, located, requestLocation } = build();

        session.dispatch({ type: 'share' });
        located[0].resolve({ status: 'failed', cause: 'timeout' });
        await tick();

        session.dispatch({ type: 'retry' });

        expect(requestLocation).toHaveBeenCalledTimes(2);
    });

    it('la posición de una consulta anterior no llega al servidor (RF-40)', async () => {
        const { session, located, checkCoverage } = build();

        session.dispatch({ type: 'share' });
        session.dispatch({ type: 'locationFailed', queryId: 1, cause: 'timeout' });
        session.dispatch({ type: 'retry' });

        located[0].resolve({ status: 'ok', location: LOCATION });
        await tick();

        expect(session.getState().phase).toBe('locating');
        expect(checkCoverage).not.toHaveBeenCalled();
    });

    it('la respuesta de una consulta anterior no toca el reintento en curso (RF-40)', async () => {
        const { session, located, checked } = build();

        session.dispatch({ type: 'share' });
        located[0].resolve({ status: 'ok', location: LOCATION });
        await tick();
        session.dispatch({ type: 'failed', queryId: 1 });
        session.dispatch({ type: 'retry' });

        checked[0].resolve({ status: 'ok', result: RESULT });
        await tick();

        expect(session.getState()).toMatchObject({ phase: 'locating', result: null });
    });
});

describe('createCoverageSession al salir de la página', () => {
    it('una respuesta tardía tras leave no pinta nada (RF-30)', async () => {
        const { session, located, checked, onChange } = build();

        session.dispatch({ type: 'share' });
        located[0].resolve({ status: 'ok', location: LOCATION });
        await tick();

        session.dispatch({ type: 'leave' });
        onChange.mockClear();
        checked[0].resolve({ status: 'ok', result: RESULT });
        await tick();

        expect(onChange).not.toHaveBeenCalled();
        expect(session.getState()).toMatchObject({ phase: 'idle', answer: null, location: null, result: null });
    });

    it('una posición tardía tras leave no llega al servidor (RF-30)', async () => {
        const { session, located, checkCoverage } = build();

        session.dispatch({ type: 'share' });
        session.dispatch({ type: 'leave' });
        located[0].resolve({ status: 'ok', location: LOCATION });
        await tick();

        expect(checkCoverage).not.toHaveBeenCalled();
        expect(session.getState().location).toBeNull();
    });

    it('leave no lanza otra consulta', () => {
        const { session, requestLocation } = build();

        session.dispatch({ type: 'share' });
        session.dispatch({ type: 'leave' });

        expect(requestLocation).toHaveBeenCalledTimes(1);
    });
});

const fakeCoveragePage = () => {
    const listeners = {};
    const listens = () => ({ addEventListener: vi.fn() });
    const radios = [
        { name: 'address_confirmed', value: 'yes', checked: false },
        { name: 'address_confirmed', value: 'no', checked: false },
    ];
    const results = {
        ...listens(),
        childElementCount: 0,
        hidden: true,
        focus: vi.fn(),
        querySelectorAll: () => [],
        replaceChildren() { this.childElementCount = 0; },
        append() { this.childElementCount += 1; },
    };
    const fallback = { hidden: true, replaceChildren: vi.fn() };
    const form = {
        ...listens(),
        dataset: { coverageWhatsappNumber: '+00 000 000 0000' },
        querySelector: () => ({}),
        querySelectorAll: () => radios,
    };
    const parts = {
        '[data-coverage-form]': form,
        '[data-coverage-results]': results,
        '[data-whatsapp-fallback]': fallback,
    };
    const root = {
        ...listens(),
        querySelector: (selector) => parts[selector] ?? { content: { cloneNode: () => ({}) } },
    };

    vi.stubGlobal('addEventListener', (type, listener) => {
        listeners[type] = listener;
    });

    return { root, radios, results, fallback, listeners };
};

describe('mountCoveragePage', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('no hace nada sin documento', () => {
        expect(mountCoveragePage(undefined)).toBeNull();
    });

    it('no hace nada en una página sin formulario de cobertura', () => {
        expect(mountCoveragePage({ querySelector: () => null })).toBeNull();
    });

    it('al salir deja pintado el estado inicial y oculta el aviso de WhatsApp (RF-30)', () => {
        const { root, radios, results, fallback, listeners } = fakeCoveragePage();
        const session = mountCoveragePage(root);

        session.dispatch({ type: 'answer', value: 'no' });
        fallback.hidden = false;
        expect(radios[1].checked).toBe(true);
        expect(results.hidden).toBe(false);

        listeners.pagehide();

        expect(session.getState()).toMatchObject({ phase: 'idle', answer: null, message: null });
        expect(radios.some((radio) => radio.checked)).toBe(false);
        expect(results.hidden).toBe(true);
        expect(fallback.hidden).toBe(true);
        expect(fallback.replaceChildren).toHaveBeenCalledWith();
    });

    it('al volver repinta los controles aunque el navegador los restaure (RF-33)', () => {
        const { root, radios, listeners } = fakeCoveragePage();

        mountCoveragePage(root);
        radios[0].checked = true;

        listeners.pageshow();

        expect(radios[0].checked).toBe(false);
    });

    it('no escucha visibilitychange: abrir WhatsApp no cuenta como salir (RF-30)', () => {
        const { root, listeners } = fakeCoveragePage();

        mountCoveragePage(root);

        expect(listeners.visibilitychange).toBeUndefined();
    });
});
