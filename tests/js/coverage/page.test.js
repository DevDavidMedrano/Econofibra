import { describe, expect, it, vi } from 'vitest';
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

describe('mountCoveragePage', () => {
    it('no hace nada sin documento', () => {
        expect(mountCoveragePage(undefined)).toBeNull();
    });

    it('no hace nada en una página sin formulario de cobertura', () => {
        expect(mountCoveragePage({ querySelector: () => null })).toBeNull();
    });
});
