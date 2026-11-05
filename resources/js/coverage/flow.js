// Máquina de estados de la consulta, sin DOM ni red.
// La ubicación vive solo en este estado, en memoria de la página (RF-47).

const LOCATION_MESSAGES = {
    denied: 'location-denied',
    timeout: 'location-unavailable',
    unavailable: 'location-unavailable',
    unsupported: 'location-unsupported',
};

const RETRYABLE_MESSAGES = ['location-denied', 'location-unavailable', 'rejected', 'system-error'];
export const RETRYABLE_REASONS = ['low_precision', 'ambiguous_location'];

export const initialState = Object.freeze({
    answer: null,
    phase: 'idle',
    queryId: 0,
    message: null,
    location: null,
    result: null,
});

export const isConsulting = (state) => state.phase === 'locating' || state.phase === 'checking';

export const canShare = (state) => state.answer === 'yes' && (state.phase === 'idle' || state.phase === 'message');

export const optionsEnabled = (state) => !isConsulting(state) && state.phase !== 'result';

export const canRetry = (state) => {
    if (state.answer !== 'yes') {
        return false;
    }

    if (state.phase === 'message') {
        return RETRYABLE_MESSAGES.includes(state.message);
    }

    return state.phase === 'result'
        && state.result.scenario === 'E'
        && RETRYABLE_REASONS.includes(state.result.reason);
};

const startQuery = (state) => ({
    ...state,
    phase: 'locating',
    queryId: state.queryId + 1,
    message: null,
    location: null,
    result: null,
});

const changeAnswer = (state, value) => {
    if (!optionsEnabled(state) || value === state.answer) {
        return state;
    }

    return {
        ...state,
        answer: value,
        phase: value === 'no' ? 'message' : 'idle',
        message: value === 'no' ? 'not-at-address' : null,
        location: null,
    };
};

// Un evento de otra consulta, o que llega cuando esa fase ya terminó, se descarta (RF-40).
const isCurrent = (state, event, phase) => state.phase === phase && event.queryId === state.queryId;

export function reduce(state, event) {
    switch (event.type) {
        case 'answer':
            return changeAnswer(state, event.value);

        case 'share':
            return canShare(state) ? startQuery(state) : state;

        case 'retry':
            return canRetry(state) ? startQuery(state) : state;

        case 'newQuery':
            return state.phase === 'result' ? { ...initialState, queryId: state.queryId } : state;

        // Conservar queryId evita lanzar otra consulta; las respuestas tardías ya no encuentran su fase.
        case 'leave':
            return { ...initialState, queryId: state.queryId };

        case 'position':
            return isCurrent(state, event, 'locating')
                ? { ...state, phase: 'checking', location: event.location }
                : state;

        case 'locationFailed':
            return isCurrent(state, event, 'locating')
                ? { ...state, phase: 'message', message: LOCATION_MESSAGES[event.cause] }
                : state;

        case 'response':
            return isCurrent(state, event, 'checking')
                ? { ...state, phase: 'result', result: event.result }
                : state;

        case 'rejected':
            return isCurrent(state, event, 'checking')
                ? { ...state, phase: 'message', message: 'rejected' }
                : state;

        case 'failed':
            return isCurrent(state, event, 'checking')
                ? { ...state, phase: 'message', message: 'system-error' }
                : state;

        default:
            return state;
    }
}
