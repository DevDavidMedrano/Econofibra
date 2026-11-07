import { RETRYABLE_REASONS } from './flow.js';

// Decide qué bloques y textos se muestran. Los precios llegan ya formateados del servidor.

const OFFER_SCENARIOS = ['A', 'B', 'C'];

const LOCAL_REASON = 'local';

// Sin servidor no hay zonas que consultar: la ubicación solo se prepara para WhatsApp.
export const localOutcome = () => ({ status: 'ok', result: { scenario: 'E', reason: LOCAL_REASON } });

const toPlan = (plan) => ({
    name: plan.name,
    speed: `${plan.speed_mbps} Mbps`,
    price: plan.price_label,
    installation: plan.installation_label,
});

const toTechnology = (technology) => ({
    name: technology.name,
    recommended: technology.recommended,
    plans: technology.plans.map(toPlan),
});

const messageFor = ({ scenario, reason }) => {
    if (reason === LOCAL_REASON) {
        return 'location-ready';
    }

    if (OFFER_SCENARIOS.includes(scenario)) {
        return null;
    }

    if (scenario === 'D') {
        return 'scenario-d';
    }

    return RETRYABLE_REASONS.includes(reason) ? 'scenario-e-location' : 'scenario-e-review';
};

export function toViewModel(response) {
    return {
        sampleNotice: response.sample_data === true,
        message: messageFor(response),
        technologies: OFFER_SCENARIOS.includes(response.scenario)
            ? response.technologies.map(toTechnology)
            : [],
    };
}
