import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { checkCoverage } from '../../../resources/js/coverage/api.js';

const ENDPOINT = '/api/coverage/check';
const TIMEOUT = 15000;
const LOCATION = { latitude: 20, longitude: -100, accuracy: 30 };
const RESULT = { scenario: 'A', reason: null, zone_name: 'Ejemplo', sample_data: true, technologies: [] };

const reply = (status, body = RESULT) => ({
    status,
    ok: status >= 200 && status < 300,
    json: async () => body,
});

const check = (fetchImpl, location = LOCATION) =>
    checkCoverage({ endpoint: ENDPOINT, location, timeoutMs: TIMEOUT, fetchImpl });

const sentBody = (fetchImpl) => JSON.parse(fetchImpl.mock.calls[0][1].body);

describe('checkCoverage: envío (RF-6)', () => {
    it('envía por POST y en JSON solo los cuatro datos, sin nada en la URL', async () => {
        const fetchImpl = vi.fn(async () => reply(200));

        await check(fetchImpl);

        const [url, options] = fetchImpl.mock.calls[0];

        expect(url).toBe(ENDPOINT);
        expect(options.method).toBe('POST');
        expect(options.headers).toMatchObject({ 'Content-Type': 'application/json', Accept: 'application/json' });
        expect(sentBody(fetchImpl)).toEqual({
            latitude: 20,
            longitude: -100,
            accuracy_meters: 30,
            address_confirmed: true,
        });
    });

    it.each([[null], [undefined], ['']])('omite el margen %j', async (accuracy) => {
        const fetchImpl = vi.fn(async () => reply(200));

        await check(fetchImpl, { latitude: 20, longitude: -100, accuracy });

        expect(sentBody(fetchImpl)).toEqual({ latitude: 20, longitude: -100, address_confirmed: true });
    });

    it('envía un margen cero para que el servidor lo rechace (RF-12)', async () => {
        const fetchImpl = vi.fn(async () => reply(200));

        await check(fetchImpl, { latitude: 20, longitude: -100, accuracy: 0 });

        expect(sentBody(fetchImpl).accuracy_meters).toBe(0);
    });
});

describe('checkCoverage: respuestas', () => {
    it('devuelve el resultado de una respuesta 200', async () => {
        await expect(check(async () => reply(200))).resolves.toEqual({ status: 'ok', result: RESULT });
    });

    it('trata el 422 como consulta rechazada (RF-32)', async () => {
        await expect(check(async () => reply(422, { message: 'x' }))).resolves.toEqual({ status: 'rejected' });
    });

    it.each([[500], [503], [404], [419]])('trata el %i como fallo (RF-27)', async (status) => {
        await expect(check(async () => reply(status))).resolves.toEqual({ status: 'failed' });
    });

    it('trata la pérdida de conexión como fallo (RF-27)', async () => {
        const fetchImpl = async () => {
            throw new TypeError('Failed to fetch');
        };

        await expect(check(fetchImpl)).resolves.toEqual({ status: 'failed' });
    });

    it('trata un cuerpo que no es JSON como fallo', async () => {
        const fetchImpl = async () => ({
            status: 200,
            ok: true,
            json: async () => {
                throw new SyntaxError('no es JSON');
            },
        });

        await expect(check(fetchImpl)).resolves.toEqual({ status: 'failed' });
    });

    it.each([[null], [{}], [{ scenario: 'Z' }]])('trata un cuerpo sin escenario válido (%j) como fallo', async (body) => {
        await expect(check(async () => reply(200, body))).resolves.toEqual({ status: 'failed' });
    });
});

describe('checkCoverage: tiempo máximo del servidor (RF-27, RF-40)', () => {
    beforeEach(() => vi.useFakeTimers());
    afterEach(() => vi.useRealTimers());

    it('falla a los 15 s y cancela la petición', async () => {
        const calls = [];
        const fetchImpl = (_url, options) => {
            calls.push(options);

            return new Promise(() => {});
        };
        const settled = vi.fn();

        check(fetchImpl).then(settled);

        await vi.advanceTimersByTimeAsync(TIMEOUT - 1);
        expect(settled).not.toHaveBeenCalled();
        expect(calls[0].signal.aborted).toBe(false);

        await vi.advanceTimersByTimeAsync(1);
        expect(settled).toHaveBeenCalledWith({ status: 'failed' });
        expect(calls[0].signal.aborted).toBe(true);
    });

    it('descarta una respuesta que llega después, aunque la cancelación no surta efecto', async () => {
        let answer;
        const fetchImpl = () => new Promise((resolve) => {
            answer = resolve;
        });
        const pending = check(fetchImpl);

        await vi.advanceTimersByTimeAsync(TIMEOUT);
        answer(reply(200));

        await expect(pending).resolves.toEqual({ status: 'failed' });
    });

    it('cuenta también el tiempo de lectura del cuerpo', async () => {
        const fetchImpl = async () => ({ status: 200, ok: true, json: () => new Promise(() => {}) });
        const settled = vi.fn();

        check(fetchImpl).then(settled);

        await vi.advanceTimersByTimeAsync(TIMEOUT);
        expect(settled).toHaveBeenCalledWith({ status: 'failed' });
    });

    it('apaga el temporizador al recibir la respuesta', async () => {
        await check(async () => reply(200));

        expect(vi.getTimerCount()).toBe(0);
    });
});
