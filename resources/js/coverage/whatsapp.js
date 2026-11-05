import { RETRYABLE_REASONS } from './flow.js';

// Prepara el mensaje de WhatsApp sin enviarlo: la decisión de enviarlo es del usuario (RF-41).

const GREETINGS = {
    hire: 'Hola, quiero contratar el servicio de internet.',
    manual: 'Hola, quiero solicitar la validación manual de mi cobertura.',
    expansion: 'Hola, quiero anotarme en el registro para futuras expansiones.',
    contact: 'Hola, quiero ponerme en contacto con el equipo de Econofibra JLINK.',
};

const digitsOf = (number) => String(number ?? '').replace(/\D/g, '');

export const hasNumber = (number) => digitsOf(number) !== '';

export const mapLink = ({ latitude, longitude }) => `https://www.google.com/maps?q=${latitude},${longitude}`;

export function buildMessage({ purpose, location, zoneName, planName }) {
    return [
        GREETINGS[purpose],
        planName && `Plan: ${planName}`,
        zoneName && `Zona: ${zoneName}`,
        location && `Ubicación: ${mapLink(location)}`,
    ].filter(Boolean).join('\n');
}

export const buildUrl = (number, message) => `https://wa.me/${digitsOf(number)}?text=${encodeURIComponent(message)}`;

// Solo un resultado o un fallo del sistema llevan la ubicación. Tras un rechazo sigue en el
// estado, pero no se envía; tampoco con la consulta en curso, que aún no tiene desenlace.
const sharesLocation = (state) => state.phase === 'result' || state.message === 'system-error';

const zoneOf = ({ result }) => (RETRYABLE_REASONS.includes(result?.reason) ? null : result?.zone_name ?? null);

// Elige los datos que corresponden a cada situación de RF-41.
export function messageData(state, purpose, planName = null) {
    if (!sharesLocation(state)) {
        return { purpose, location: null, zoneName: null, planName: null };
    }

    return {
        purpose,
        location: state.location,
        zoneName: zoneOf(state),
        planName: purpose === 'hire' ? planName : null,
    };
}
