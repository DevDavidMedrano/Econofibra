import { describe, expect, it } from 'vitest';
import { canRetry, canShare, initialState, isConsulting, optionsEnabled, reduce } from '../../../resources/js/coverage/flow.js';

const LOCATION = { latitude: 20, longitude: -100, accuracy: 30 };
const RESULT_A = { scenario: 'A', reason: null };

const run = (events, state = initialState) => events.reduce(reduce, state);
const answered = (value = 'yes') => run([{ type: 'answer', value }]);
const locating = () => run([{ type: 'share' }], answered());
const checking = () => {
    const state = locating();

    return reduce(state, { type: 'position', queryId: state.queryId, location: LOCATION });
};
const withResult = (result = RESULT_A) => {
    const state = checking();

    return reduce(state, { type: 'response', queryId: state.queryId, result });
};
const withFailure = () => {
    const state = checking();

    return reduce(state, { type: 'failed', queryId: state.queryId });
};
const withLocationFailure = (cause) => {
    const state = locating();

    return reduce(state, { type: 'locationFailed', queryId: state.queryId, cause });
};

describe('estado inicial', () => {
    it('empieza sin respuesta, sin mensaje y sin ubicación (RF-48)', () => {
        expect(initialState).toMatchObject({ answer: null, phase: 'idle', message: null, location: null, result: null });
    });
});

describe('botón de compartir ubicación (RF-7)', () => {
    it('está inactivo sin respuesta', () => {
        expect(canShare(initialState)).toBe(false);
    });

    it('se activa con "Sí"', () => {
        expect(canShare(answered('yes'))).toBe(true);
    });

    it('está inactivo con "No"', () => {
        expect(canShare(answered('no'))).toBe(false);
    });

    it('vuelve a quedar inactivo al cambiar de "Sí" a "No"', () => {
        expect(canShare(run([{ type: 'answer', value: 'no' }], answered('yes')))).toBe(false);
    });

    it('está inactivo con un resultado en pantalla', () => {
        expect(canShare(withResult())).toBe(false);
    });

    it('sigue activo tras un mensaje de error, con "Sí" vigente', () => {
        expect(canShare(withFailure())).toBe(true);
    });

    it('no inicia una consulta si está inactivo', () => {
        const refused = answered('no');

        expect(reduce(initialState, { type: 'share' })).toBe(initialState);
        expect(reduce(refused, { type: 'share' })).toBe(refused);
    });
});

describe('respuesta "No" (RF-8)', () => {
    it('muestra el mensaje de fuera del domicilio sin iniciar una consulta', () => {
        const state = answered('no');

        expect(state.message).toBe('not-at-address');
        expect(state.queryId).toBe(0);
        expect(isConsulting(state)).toBe(false);
    });

    it('reemplaza un mensaje anterior y borra la ubicación conservada', () => {
        const before = withFailure();
        expect(before.location).toEqual(LOCATION);

        const state = reduce(before, { type: 'answer', value: 'no' });

        expect(state.message).toBe('not-at-address');
        expect(state.location).toBeNull();
    });
});

describe('cambio de respuesta (RF-55)', () => {
    it('retira el mensaje de "No" al cambiar a "Sí"', () => {
        const state = reduce(answered('no'), { type: 'answer', value: 'yes' });

        expect(state.message).toBeNull();
        expect(state.phase).toBe('idle');
        expect(canShare(state)).toBe(true);
    });

    it('descarta el mensaje y la ubicación al cambiar la respuesta tras un error', () => {
        const state = run([{ type: 'answer', value: 'no' }, { type: 'answer', value: 'yes' }], withFailure());

        expect(state.message).toBeNull();
        expect(state.location).toBeNull();
    });

    it('no cambia nada si la respuesta es la misma', () => {
        const state = withFailure();

        expect(reduce(state, { type: 'answer', value: 'yes' })).toBe(state);
    });
});

describe('inicio de una consulta (RF-9, RF-55)', () => {
    it('retira el mensaje anterior y descarta la ubicación conservada', () => {
        const state = reduce(withFailure(), { type: 'share' });

        expect(state.phase).toBe('locating');
        expect(state.message).toBeNull();
        expect(state.location).toBeNull();
    });

    it('usa un identificador nuevo en cada consulta', () => {
        const first = locating();
        const failed = reduce(first, { type: 'locationFailed', queryId: first.queryId, cause: 'timeout' });

        expect(reduce(failed, { type: 'retry' }).queryId).toBe(first.queryId + 1);
    });
});

describe('consulta en curso (RF-39, RF-49)', () => {
    it.each([['localizando', locating], ['esperando al servidor', checking]])('%s: todo queda inactivo', (_name, build) => {
        const state = build();

        expect(isConsulting(state)).toBe(true);
        expect(canShare(state)).toBe(false);
        expect(canRetry(state)).toBe(false);
        expect(optionsEnabled(state)).toBe(false);
    });

    it('una doble pulsación ejecuta una sola consulta', () => {
        const state = locating();

        expect(reduce(state, { type: 'share' })).toBe(state);
        expect(reduce(state, { type: 'retry' })).toBe(state);
    });

    it('ignora un cambio de respuesta', () => {
        const state = locating();

        expect(reduce(state, { type: 'answer', value: 'no' })).toBe(state);
    });
});

describe('ubicación no obtenida (RF-10)', () => {
    it.each([
        ['denied', 'location-denied', true],
        ['timeout', 'location-unavailable', true],
        ['unavailable', 'location-unavailable', true],
        ['unsupported', 'location-unsupported', false],
    ])('%s muestra %s', (cause, message, retry) => {
        const state = withLocationFailure(cause);

        expect(state.phase).toBe('message');
        expect(state.message).toBe(message);
        expect(state.location).toBeNull();
        expect(canRetry(state)).toBe(retry);
    });
});

describe('respuesta del servidor', () => {
    it('conserva la ubicación mientras espera y con el resultado (RF-47)', () => {
        expect(checking().location).toEqual(LOCATION);
        expect(withResult().location).toEqual(LOCATION);
    });

    it('guarda el resultado para presentarlo', () => {
        expect(withResult()).toMatchObject({ phase: 'result', result: RESULT_A, message: null });
    });

    it('una consulta rechazada muestra su mensaje con reintento (RF-32)', () => {
        const before = checking();
        const state = reduce(before, { type: 'rejected', queryId: before.queryId });

        expect(state).toMatchObject({ phase: 'message', message: 'rejected', result: null });
        expect(canRetry(state)).toBe(true);
    });

    it('un fallo muestra su mensaje con reintento y conserva la ubicación (RF-27, RF-47)', () => {
        const state = withFailure();

        expect(state).toMatchObject({ phase: 'message', message: 'system-error', result: null, location: LOCATION });
        expect(canRetry(state)).toBe(true);
    });
});

describe('resultado en pantalla (RF-49)', () => {
    it('deja las opciones inactivas e ignora un cambio de respuesta', () => {
        const state = withResult();

        expect(optionsEnabled(state)).toBe(false);
        expect(reduce(state, { type: 'answer', value: 'no' })).toBe(state);
    });

    it('con un mensaje, las opciones siguen activas', () => {
        expect(optionsEnabled(withFailure())).toBe(true);
        expect(optionsEnabled(answered('no'))).toBe(true);
        expect(optionsEnabled(initialState)).toBe(true);
    });
});

describe('Consultar de nuevo (RF-37)', () => {
    it('borra el resultado, la ubicación y la respuesta', () => {
        const state = reduce(withResult(), { type: 'newQuery' });

        expect(state).toMatchObject({ answer: null, phase: 'idle', message: null, location: null, result: null });
        expect(canShare(state)).toBe(false);
        expect(optionsEnabled(state)).toBe(true);
    });

    it('conserva el contador para no repetir identificadores', () => {
        const before = withResult();

        expect(reduce(before, { type: 'newQuery' }).queryId).toBe(before.queryId);
    });

    it('no hace nada sin un resultado en pantalla', () => {
        const state = withFailure();

        expect(reduce(state, { type: 'newQuery' })).toBe(state);
    });
});

describe('reintentar (RF-36, RF-38)', () => {
    it('inicia una consulta, conserva la confirmación y quita el mensaje y la ubicación', () => {
        const before = withFailure();
        const state = reduce(before, { type: 'retry' });

        expect(state).toMatchObject({ phase: 'locating', answer: 'yes', message: null, location: null });
        expect(state.queryId).toBe(before.queryId + 1);
    });

    it.each(['low_precision', 'ambiguous_location'])('se ofrece en el Escenario E por %s y quita el resultado', (reason) => {
        const before = withResult({ scenario: 'E', reason });
        const state = reduce(before, { type: 'retry' });

        expect(canRetry(before)).toBe(true);
        expect(state).toMatchObject({ phase: 'locating', answer: 'yes', result: null, location: null });
    });

    it.each([
        ['A', null],
        ['B', null],
        ['C', null],
        ['D', null],
        ['E', 'pending_review'],
    ])('no se ofrece en el Escenario %s con motivo %s (RF-37)', (scenario, reason) => {
        const state = withResult({ scenario, reason });

        expect(canRetry(state)).toBe(false);
        expect(reduce(state, { type: 'retry' })).toBe(state);
    });

    it('no se ofrece sin consulta previa, con "No" ni en navegador sin ubicación', () => {
        const unsupported = withLocationFailure('unsupported');

        expect(canRetry(initialState)).toBe(false);
        expect(canRetry(answered('no'))).toBe(false);
        expect(reduce(unsupported, { type: 'retry' })).toBe(unsupported);
    });

    it('si el reintento no logra la posición, no queda ubicación anterior (RF-38, RF-41)', () => {
        const retrying = reduce(withFailure(), { type: 'retry' });
        const state = reduce(retrying, { type: 'locationFailed', queryId: retrying.queryId, cause: 'timeout' });

        expect(state.location).toBeNull();
    });
});

describe('posiciones y respuestas tardías (RF-40)', () => {
    it('descarta una posición que llega tras agotarse la espera', () => {
        const state = withLocationFailure('timeout');

        expect(reduce(state, { type: 'position', queryId: state.queryId, location: LOCATION })).toBe(state);
    });

    it('descarta una posición de una consulta anterior durante un reintento', () => {
        const failed = withLocationFailure('timeout');
        const retrying = reduce(failed, { type: 'retry' });

        expect(reduce(retrying, { type: 'position', queryId: failed.queryId, location: LOCATION })).toBe(retrying);
    });

    it('descarta una respuesta que llega tras agotarse la espera del servidor', () => {
        const state = withFailure();

        expect(reduce(state, { type: 'response', queryId: state.queryId, result: RESULT_A })).toBe(state);
        expect(reduce(state, { type: 'rejected', queryId: state.queryId })).toBe(state);
    });

    it('descarta una respuesta de una consulta anterior durante un reintento', () => {
        const failed = withFailure();
        const retrying = reduce(failed, { type: 'retry' });
        const next = reduce(retrying, { type: 'position', queryId: retrying.queryId, location: LOCATION });

        expect(reduce(next, { type: 'response', queryId: failed.queryId, result: RESULT_A })).toBe(next);
        expect(reduce(next, { type: 'failed', queryId: failed.queryId })).toBe(next);
    });

    it('descarta un fallo de ubicación tardío', () => {
        const state = checking();

        expect(reduce(state, { type: 'locationFailed', queryId: state.queryId, cause: 'denied' })).toBe(state);
    });

    it('descarta una respuesta tardía después de responder "No"', () => {
        const state = reduce(withFailure(), { type: 'answer', value: 'no' });

        expect(reduce(state, { type: 'response', queryId: state.queryId, result: RESULT_A })).toBe(state);
    });
});
