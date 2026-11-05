const CAUSES = { 1: 'denied', 2: 'unavailable', 3: 'timeout' };

// El temporizador es propio porque el `timeout` de la API no cuenta el tiempo
// con el aviso de permiso abierto. Lo que llegue después se ignora (RF-40).
export function requestPosition({ geolocation, timeoutMs }) {
    if (!geolocation) {
        return Promise.resolve({ status: 'failed', cause: 'unsupported' });
    }

    return new Promise((resolve) => {
        const timer = setTimeout(() => resolve({ status: 'failed', cause: 'timeout' }), timeoutMs);

        const finish = (outcome) => {
            clearTimeout(timer);
            resolve(outcome);
        };

        try {
            geolocation.getCurrentPosition(
                ({ coords }) => finish({
                    status: 'ok',
                    location: {
                        latitude: coords.latitude,
                        longitude: coords.longitude,
                        accuracy: coords.accuracy ?? null,
                    },
                }),
                (error) => finish({ status: 'failed', cause: CAUSES[error?.code] ?? 'unavailable' }),
                { enableHighAccuracy: true, maximumAge: 0 },
            );
        } catch {
            finish({ status: 'failed', cause: 'unavailable' });
        }
    });
}
