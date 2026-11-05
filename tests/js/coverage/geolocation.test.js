import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { requestPosition } from '../../../resources/js/coverage/geolocation.js';

const TIMEOUT = 20000;

const fakeDevice = () => {
    const device = { success: null, failure: null, options: null, calls: 0 };

    device.getCurrentPosition = (success, failure, options) => {
        Object.assign(device, { success, failure, options, calls: device.calls + 1 });
    };

    return device;
};

const position = (coords) => ({ coords, timestamp: 1 });

describe('requestPosition', () => {
    beforeEach(() => vi.useFakeTimers());
    afterEach(() => vi.useRealTimers());

    it('pide una posición nueva, sin reutilizar una guardada (RF-9)', () => {
        const device = fakeDevice();

        requestPosition({ geolocation: device, timeoutMs: TIMEOUT });

        expect(device.calls).toBe(1);
        expect(device.options).toMatchObject({ maximumAge: 0, enableHighAccuracy: true });
    });

    it('devuelve latitud, longitud y margen', async () => {
        const device = fakeDevice();
        const pending = requestPosition({ geolocation: device, timeoutMs: TIMEOUT });

        device.success(position({ latitude: 20.5, longitude: -100.25, accuracy: 30, altitude: 2000 }));

        await expect(pending).resolves.toEqual({
            status: 'ok',
            location: { latitude: 20.5, longitude: -100.25, accuracy: 30 },
        });
    });

    it('devuelve el margen nulo si el dispositivo no lo informa', async () => {
        const device = fakeDevice();
        const pending = requestPosition({ geolocation: device, timeoutMs: TIMEOUT });

        device.success(position({ latitude: 20, longitude: -100 }));

        await expect(pending).resolves.toMatchObject({ location: { accuracy: null } });
    });

    it.each([
        [1, 'denied'],
        [2, 'unavailable'],
        [3, 'timeout'],
    ])('traduce el código %i a la causa %s (RF-10)', async (code, cause) => {
        const device = fakeDevice();
        const pending = requestPosition({ geolocation: device, timeoutMs: TIMEOUT });

        device.failure({ code });

        await expect(pending).resolves.toEqual({ status: 'failed', cause });
    });

    it.each([[undefined], [null]])('sin función de ubicación (%s) no solicita nada', async (geolocation) => {
        await expect(requestPosition({ geolocation, timeoutMs: TIMEOUT })).resolves.toEqual({
            status: 'failed',
            cause: 'unsupported',
        });
    });

    it('trata como posición no disponible un error al solicitarla', async () => {
        const geolocation = {
            getCurrentPosition: () => {
                throw new Error('fallo');
            },
        };

        await expect(requestPosition({ geolocation, timeoutMs: TIMEOUT })).resolves.toEqual({
            status: 'failed',
            cause: 'unavailable',
        });
    });

    it('agota el tiempo a los 20 s con el aviso de permiso sin responder', async () => {
        const device = fakeDevice();
        const settled = vi.fn();

        requestPosition({ geolocation: device, timeoutMs: TIMEOUT }).then(settled);

        await vi.advanceTimersByTimeAsync(TIMEOUT - 1);
        expect(settled).not.toHaveBeenCalled();

        await vi.advanceTimersByTimeAsync(1);
        expect(settled).toHaveBeenCalledWith({ status: 'failed', cause: 'timeout' });
    });

    it('usa el tiempo configurado', async () => {
        const settled = vi.fn();

        requestPosition({ geolocation: fakeDevice(), timeoutMs: 5000 }).then(settled);

        await vi.advanceTimersByTimeAsync(5000);
        expect(settled).toHaveBeenCalledWith({ status: 'failed', cause: 'timeout' });
    });

    it('descarta una posición que llega después del tiempo máximo (RF-40)', async () => {
        const device = fakeDevice();
        const pending = requestPosition({ geolocation: device, timeoutMs: TIMEOUT });

        await vi.advanceTimersByTimeAsync(TIMEOUT);
        device.success(position({ latitude: 20, longitude: -100, accuracy: 30 }));

        await expect(pending).resolves.toEqual({ status: 'failed', cause: 'timeout' });
    });

    it('apaga el temporizador al obtener la posición', async () => {
        const device = fakeDevice();
        const pending = requestPosition({ geolocation: device, timeoutMs: TIMEOUT });

        device.success(position({ latitude: 20, longitude: -100, accuracy: 30 }));
        await pending;

        expect(vi.getTimerCount()).toBe(0);
    });
});
