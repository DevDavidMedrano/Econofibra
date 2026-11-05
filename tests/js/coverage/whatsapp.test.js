import { describe, expect, it } from 'vitest';
import { initialState, reduce } from '../../../resources/js/coverage/flow.js';
import {
    buildMessage,
    buildUrl,
    hasNumber,
    mapLink,
    messageData,
} from '../../../resources/js/coverage/whatsapp.js';

const LOCATION = { latitude: 20.123456, longitude: -100.654321, accuracy: 30 };
const LINK = 'https://www.google.com/maps?q=20.123456,-100.654321';
const FAKE_NUMBER = '+00 000 000 0000';

const located = (extra) => ({ ...initialState, answer: 'yes', queryId: 1, location: LOCATION, ...extra });

const resultState = (result) => located({
    phase: 'result',
    result: { reason: null, zone_name: null, ...result },
});

const nothing = (purpose) => ({ purpose, location: null, zoneName: null, planName: null });

describe('mapLink', () => {
    it('forma un enlace público de Google Maps con la latitud y la longitud (RF-51)', () => {
        expect(mapLink(LOCATION)).toBe(LINK);
    });
});

describe('buildMessage', () => {
    it.each([
        ['hire', 'contratar'],
        ['manual', 'validación manual'],
        ['expansion', 'futuras expansiones'],
        ['contact', 'contacto'],
    ])('escribe el motivo de %s (RF-41)', (purpose, reason) => {
        expect(buildMessage(nothing(purpose))).toContain(reason);
    });

    it('incluye el plan, la zona y el enlace cuando se conocen (RF-41)', () => {
        const message = buildMessage({
            purpose: 'hire',
            location: LOCATION,
            zoneName: 'Zona Norte',
            planName: 'Plan uno',
        });

        expect(message).toContain('Plan: Plan uno');
        expect(message).toContain('Zona: Zona Norte');
        expect(message).toContain(`Ubicación: ${LINK}`);
    });

    it('omite los datos que no se conocen (RF-41)', () => {
        const message = buildMessage(nothing('manual'));

        expect(message).not.toContain('Plan:');
        expect(message).not.toContain('Zona:');
        expect(message).not.toContain('Ubicación:');
        expect(message).not.toContain('null');
    });
});

describe('buildUrl', () => {
    it('abre wa.me solo con los dígitos del número (RF-25)', () => {
        expect(buildUrl(FAKE_NUMBER, 'Hola')).toBe('https://wa.me/000000000000?text=Hola');
    });

    it('codifica el mensaje y conserva el enlace al decodificarlo (RF-41, RF-51)', () => {
        const message = buildMessage({
            purpose: 'hire',
            location: LOCATION,
            zoneName: 'Peñas & Lomas #2',
            planName: 'Plan 50+',
        });

        const url = new URL(buildUrl(FAKE_NUMBER, message));

        expect(url.origin).toBe('https://wa.me');
        expect([...url.searchParams.keys()]).toEqual(['text']);
        expect(url.searchParams.get('text')).toBe(message);
        expect(url.search).not.toMatch(/[\s#+ñ]/u);
        expect(url.search).toContain('https%3A%2F%2Fwww.google.com%2Fmaps%3Fq%3D20.123456%2C-100.654321');
    });
});

describe('hasNumber', () => {
    it.each([[null], [undefined], [''], ['   '], ['+']])('no considera número a %j (RF-43)', (value) => {
        expect(hasNumber(value)).toBe(false);
    });

    it('reconoce un número configurado (RF-42)', () => {
        expect(hasNumber(FAKE_NUMBER)).toBe(true);
    });
});

describe('messageData: filas de RF-41', () => {
    it.each(['A', 'B', 'C'])('Contratar en el Escenario %s lleva enlace, zona y plan', (scenario) => {
        const state = resultState({ scenario, zone_name: 'Zona Norte' });

        expect(messageData(state, 'hire', 'Plan uno')).toEqual({
            purpose: 'hire',
            location: LOCATION,
            zoneName: 'Zona Norte',
            planName: 'Plan uno',
        });
    });

    it.each(['A', 'B', 'C'])('otro botón en el Escenario %s lleva enlace y zona, sin plan', (scenario) => {
        const state = resultState({ scenario, zone_name: 'Zona Norte' });

        expect(messageData(state, 'contact')).toEqual({
            purpose: 'contact',
            location: LOCATION,
            zoneName: 'Zona Norte',
            planName: null,
        });
    });

    it('un plan recibido fuera de Contratar no se incluye', () => {
        const state = resultState({ scenario: 'A', zone_name: 'Zona Norte' });

        expect(messageData(state, 'contact', 'Plan uno').planName).toBeNull();
    });

    it.each([
        ['D con zona', { scenario: 'D' }, 'expansion'],
        ['D con zona', { scenario: 'D' }, 'manual'],
        ['E por revisión', { scenario: 'E', reason: 'pending_review' }, 'manual'],
    ])('%s lleva enlace y zona (%#)', (_name, result, purpose) => {
        const state = resultState({ ...result, zone_name: 'Zona Norte' });

        expect(messageData(state, purpose)).toEqual({
            purpose,
            location: LOCATION,
            zoneName: 'Zona Norte',
            planName: null,
        });
    });

    it('D sin zona lleva el enlace, sin zona', () => {
        expect(messageData(resultState({ scenario: 'D' }), 'expansion')).toEqual({
            purpose: 'expansion',
            location: LOCATION,
            zoneName: null,
            planName: null,
        });
    });

    it.each(['low_precision', 'ambiguous_location'])('E por %s lleva el enlace, sin zona', (reason) => {
        const state = resultState({ scenario: 'E', reason, zone_name: 'No debe salir' });

        expect(messageData(state, 'manual')).toEqual({
            purpose: 'manual',
            location: LOCATION,
            zoneName: null,
            planName: null,
        });
    });

    it('un fallo de RF-27 lleva el enlace, sin zona', () => {
        const state = reduce(located({ phase: 'checking' }), { type: 'failed', queryId: 1 });

        expect(messageData(state, 'manual')).toEqual({
            purpose: 'manual',
            location: LOCATION,
            zoneName: null,
            planName: null,
        });
    });

    it('una consulta rechazada va sin enlace, aunque la ubicación siga en el estado', () => {
        const state = reduce(located({ phase: 'checking' }), { type: 'rejected', queryId: 1 });

        expect(state.location).toEqual(LOCATION);
        expect(messageData(state, 'manual')).toEqual(nothing('manual'));
    });

    it.each(['denied', 'timeout', 'unavailable', 'unsupported'])('sin ubicación por %s va sin enlace', (cause) => {
        const state = reduce(
            { ...initialState, answer: 'yes', queryId: 1, phase: 'locating' },
            { type: 'locationFailed', queryId: 1, cause },
        );

        expect(messageData(state, 'manual')).toEqual(nothing('manual'));
    });

    it('fuera del domicilio va sin enlace', () => {
        const state = reduce(initialState, { type: 'answer', value: 'no' });

        expect(messageData(state, 'manual')).toEqual(nothing('manual'));
    });

    it('sin consulta realizada va sin enlace', () => {
        expect(messageData(initialState, 'contact')).toEqual(nothing('contact'));
    });

    it('con la consulta en curso va sin enlace, porque aún no hay resultado', () => {
        expect(messageData(located({ phase: 'checking' }), 'contact')).toEqual(nothing('contact'));
    });

    it('tras Consultar de nuevo va sin enlace (RF-37)', () => {
        const state = reduce(resultState({ scenario: 'A', zone_name: 'Zona Norte' }), { type: 'newQuery' });

        expect(messageData(state, 'contact')).toEqual(nothing('contact'));
    });
});
