const SCENARIOS = ['A', 'B', 'C', 'D', 'E'];
const REJECTED = Object.freeze({ status: 'rejected' });
const FAILED = Object.freeze({ status: 'failed' });

const hasAccuracy = (accuracy) => accuracy !== null && accuracy !== undefined && accuracy !== '';

const toBody = ({ latitude, longitude, accuracy }) => JSON.stringify({
    latitude,
    longitude,
    ...(hasAccuracy(accuracy) ? { accuracy_meters: accuracy } : {}),
    address_confirmed: true,
});

async function send(endpoint, location, signal, fetchImpl) {
    try {
        const response = await fetchImpl(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: toBody(location),
            signal,
        });

        if (response.status === 422) {
            return REJECTED;
        }

        if (!response.ok) {
            return FAILED;
        }

        const result = await response.json();

        return SCENARIOS.includes(result?.scenario) ? { status: 'ok', result } : FAILED;
    } catch {
        return FAILED;
    }
}

// Las coordenadas van solo en el cuerpo, para que no lleguen a los registros de acceso.
export async function checkCoverage({ endpoint, location, timeoutMs, fetchImpl = (...args) => globalThis.fetch(...args) }) {
    const controller = new AbortController();
    let timer;

    const expired = new Promise((resolve) => {
        timer = setTimeout(() => {
            controller.abort();
            resolve(FAILED);
        }, timeoutMs);
    });

    try {
        return await Promise.race([send(endpoint, location, controller.signal, fetchImpl), expired]);
    } finally {
        clearTimeout(timer);
    }
}
