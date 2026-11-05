import { describe, expect, it } from 'vitest';
import { canRetry } from '../../../resources/js/coverage/flow.js';
import { toViewModel } from '../../../resources/js/coverage/presenter.js';

const FIBER = {
    code: 'fiber',
    name: 'Fibra óptica',
    recommended: true,
    plans: [
        { name: 'Plan uno', speed_mbps: 50, price_label: '$399.00/mes', installation_label: 'Instalación: $500.00' },
        { name: 'Plan dos', speed_mbps: 100, price_label: '$549.00/mes', installation_label: 'Instalación sin costo' },
    ],
};

const ANTENNA = {
    code: 'antenna',
    name: 'Internet por antena',
    recommended: false,
    plans: [
        { name: 'Plan tres', speed_mbps: 20, price_label: '$349.00/mes', installation_label: 'Instalación: $1,200.00' },
    ],
};

const response = (overrides) => ({
    scenario: 'A',
    reason: null,
    zone_name: 'Zona de prueba',
    sample_data: false,
    technologies: [],
    ...overrides,
});

const NO_OFFER = [
    ['D con zona', { scenario: 'D' }],
    ['D sin zona', { scenario: 'D', zone_name: null }],
    ['E por revisión', { scenario: 'E', reason: 'pending_review' }],
    ['E por precisión baja', { scenario: 'E', reason: 'low_precision', zone_name: null }],
    ['E por ubicación ambigua', { scenario: 'E', reason: 'ambiguous_location', zone_name: null }],
];

describe('toViewModel con oferta', () => {
    it('Escenario A: fibra con sus planes, sin mensaje (RF-18)', () => {
        const view = toViewModel(response({ scenario: 'A', technologies: [FIBER] }));

        expect(view.message).toBeNull();
        expect(view.technologies).toEqual([{
            name: 'Fibra óptica',
            recommended: true,
            plans: [
                { name: 'Plan uno', speed: '50 Mbps', price: '$399.00/mes', installation: 'Instalación: $500.00' },
                { name: 'Plan dos', speed: '100 Mbps', price: '$549.00/mes', installation: 'Instalación sin costo' },
            ],
        }]);
    });

    it('Escenario B: antena recomendada (RF-19, RF-21)', () => {
        const view = toViewModel(response({
            scenario: 'B',
            technologies: [{ ...ANTENNA, recommended: true }],
        }));

        expect(view.message).toBeNull();
        expect(view.technologies).toHaveLength(1);
        expect(view.technologies[0]).toMatchObject({ name: 'Internet por antena', recommended: true });
    });

    it('Escenario C: ambas tecnologías, en el orden del servidor y solo fibra recomendada (RF-20, RF-21)', () => {
        const view = toViewModel(response({ scenario: 'C', technologies: [FIBER, ANTENNA] }));

        expect(view.message).toBeNull();
        expect(view.technologies.map(({ name, recommended }) => [name, recommended])).toEqual([
            ['Fibra óptica', true],
            ['Internet por antena', false],
        ]);
    });

    it('muestra las etiquetas del servidor sin cambiarlas (RF-35)', () => {
        const [technology] = toViewModel(response({ technologies: [FIBER] })).technologies;

        expect(technology.plans[1].price).toBe(FIBER.plans[1].price_label);
        expect(technology.plans[1].installation).toBe('Instalación sin costo');
    });

    it('no presenta la zona, que solo sirve para el mensaje de WhatsApp (RF-50)', () => {
        const view = toViewModel(response({ technologies: [FIBER] }));

        expect(JSON.stringify(view)).not.toContain('Zona de prueba');
    });
});

describe('toViewModel sin oferta', () => {
    it('Escenario D: mensaje de disponibilidad no confirmada (RF-22)', () => {
        expect(toViewModel(response({ scenario: 'D' })).message).toBe('scenario-d');
        expect(toViewModel(response({ scenario: 'D', zone_name: null })).message).toBe('scenario-d');
    });

    it('Escenario E por revisión: mensaje del equipo técnico sin reintento (RF-23)', () => {
        const view = toViewModel(response({ scenario: 'E', reason: 'pending_review' }));

        expect(view.message).toBe('scenario-e-review');
    });

    it.each(['low_precision', 'ambiguous_location'])('Escenario E por %s: mensaje de ubicación (RF-36)', (reason) => {
        const view = toViewModel(response({ scenario: 'E', reason, zone_name: null }));

        expect(view.message).toBe('scenario-e-location');
    });

    it.each(NO_OFFER)('%s: sin planes, precios ni recomendación (RF-22, RF-24)', (_name, overrides) => {
        expect(toViewModel(response(overrides)).technologies).toEqual([]);
    });

    it.each(NO_OFFER)('%s: descarta una oferta que llegue por error (RF-22, RF-24)', (_name, overrides) => {
        const view = toViewModel(response({ ...overrides, technologies: [FIBER] }));

        expect(view.technologies).toEqual([]);
        expect(JSON.stringify(view)).not.toContain('$');
    });

    it('el mensaje de ubicación coincide con los motivos que admiten reintento (RF-36, RF-38)', () => {
        const retryable = (reason) => canRetry({
            answer: 'yes',
            phase: 'result',
            result: response({ scenario: 'E', reason }),
        });

        for (const reason of ['low_precision', 'ambiguous_location', 'pending_review']) {
            const view = toViewModel(response({ scenario: 'E', reason }));

            expect(view.message === 'scenario-e-location').toBe(retryable(reason));
        }
    });
});

describe('toViewModel: aviso de datos de ejemplo', () => {
    const ALL = [
        ['A', { scenario: 'A', technologies: [FIBER] }],
        ['B', { scenario: 'B', technologies: [ANTENNA] }],
        ['C', { scenario: 'C', technologies: [FIBER, ANTENNA] }],
        ...NO_OFFER,
    ];

    it.each(ALL)('%s lo muestra con datos de ejemplo (RF-26)', (_name, overrides) => {
        expect(toViewModel(response({ ...overrides, sample_data: true })).sampleNotice).toBe(true);
    });

    it.each(ALL)('%s lo omite sin datos de ejemplo (RF-26)', (_name, overrides) => {
        expect(toViewModel(response({ ...overrides, sample_data: false })).sampleNotice).toBe(false);
    });
});
