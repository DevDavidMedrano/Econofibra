import { checkCoverage } from './api.js';
import { canRetry, canShare, initialState, isConsulting, optionsEnabled, reduce } from './flow.js';
import { requestPosition } from './geolocation.js';
import { toViewModel } from './presenter.js';
import { buildMessage, buildUrl, hasNumber, messageData } from './whatsapp.js';

// Une la máquina de estados con la ubicación y el servidor, sin tocar el DOM.
export function createCoverageSession({ requestLocation, checkCoverage: check, onChange }) {
    let state = initialState;

    const dispatch = (event) => {
        const previous = state;

        state = reduce(previous, event);

        if (state === previous) {
            return;
        }

        onChange(state, previous);

        if (state.queryId !== previous.queryId) {
            runQuery(state.queryId);
        }
    };

    async function runQuery(queryId) {
        const located = await requestLocation();

        if (located.status !== 'ok') {
            dispatch({ type: 'locationFailed', queryId, cause: located.cause });

            return;
        }

        dispatch({ type: 'position', queryId, location: located.location });

        if (state.queryId !== queryId || state.phase !== 'checking') {
            return;
        }

        const outcome = await check(located.location);

        if (outcome.status === 'ok') {
            dispatch({ type: 'response', queryId, result: outcome.result });
        } else {
            dispatch({ type: outcome.status, queryId });
        }
    }

    return { dispatch, getState: () => state };
}

const cloneTemplate = (root, name) => root.querySelector(`#tpl-${name}`).content.cloneNode(true);

function showTemplate(root, container, name) {
    container.append(cloneTemplate(root, name));
}

function fillSlot(fragment, slot, text) {
    fragment.querySelector(`[data-slot="${slot}"]`).textContent = text;
}

function planCard(root, plan) {
    const card = cloneTemplate(root, 'plan');

    fillSlot(card, 'plan-name', plan.name);
    fillSlot(card, 'plan-speed', plan.speed);
    fillSlot(card, 'plan-price', plan.price);
    fillSlot(card, 'plan-installation', plan.installation);

    return card;
}

function technologyCard(root, technology) {
    const card = cloneTemplate(root, 'technology');

    fillSlot(card, 'technology-name', technology.name);
    card.querySelector('[data-slot="recommended"]').hidden = !technology.recommended;
    card.querySelector('[data-slot="plans"]').append(...technology.plans.map((plan) => planCard(root, plan)));

    return card;
}

function showTechnologies(root, container, technologies) {
    const list = cloneTemplate(root, 'technologies');

    list.querySelector('[data-slot="technologies"]')
        .append(...technologies.map((technology) => technologyCard(root, technology)));
    container.append(list);
}

// Único punto donde se presenta un resultado A a E.
function showResult(root, container, result) {
    const view = toViewModel(result);

    if (view.sampleNotice) {
        showTemplate(root, container, 'sample-notice');
    }

    if (view.message) {
        showTemplate(root, container, view.message);
    } else {
        showTechnologies(root, container, view.technologies);
    }

    showTemplate(root, container, 'new-query');
}

function paintResults(root, elements, state) {
    const { results } = elements;

    results.replaceChildren();

    if (state.message) {
        showTemplate(root, results, state.message);
    } else if (state.result) {
        showResult(root, results, state.result);
    }

    results.hidden = results.childElementCount === 0;
}

// Sin número, los botones quedan inactivos y la consulta sigue funcionando (RF-43).
export function disableWhatsapp(root, container, placeNote = (button, note) => button.append(note)) {
    container.querySelectorAll('[data-whatsapp]').forEach((button) => {
        button.disabled = true;
        placeNote(button, cloneTemplate(root, 'whatsapp-unavailable'));
    });
}

export function showWhatsappFallback(root, area, number) {
    const note = cloneTemplate(root, 'whatsapp-fallback');

    fillSlot(note, 'whatsapp-number', number);
    area.replaceChildren(note);
    area.hidden = false;
}

function hideWhatsappFallback(area) {
    area.replaceChildren();
    area.hidden = true;
}

function planNameOf(button) {
    return button.closest('.result-plan')?.querySelector('[data-slot="plan-name"]').textContent ?? null;
}

function openWhatsapp(number, state, button) {
    const data = messageData(state, button.dataset.whatsapp, planNameOf(button));

    globalThis.open(buildUrl(number, buildMessage(data)), '_blank', 'noopener');
}

function paintControls(elements, state) {
    const { radios, confirmation, share, status, changeHint, results } = elements;

    radios.forEach((radio) => {
        radio.checked = radio.value === state.answer;
    });

    confirmation.disabled = !optionsEnabled(state);
    share.disabled = !canShare(state);
    status.hidden = !isConsulting(state);
    changeHint.hidden = state.phase !== 'result';

    results.querySelectorAll('[data-coverage-retry]').forEach((button) => {
        button.disabled = !canRetry(state);
    });
}

// El botón pulsado desaparece al repintar, así que el foco se lleva a donde sigue la tarea.
function moveFocus(elements, state, previous) {
    const finishedQuery = isConsulting(previous) && !isConsulting(state);

    if (finishedQuery) {
        elements.results.focus();
    } else if (previous.phase === 'result' && state.phase === 'idle') {
        elements.radios[0].focus();
    }
}

export function mountCoveragePage(root) {
    const form = root?.querySelector('[data-coverage-form]');

    if (!form) {
        return null;
    }

    const elements = {
        results: root.querySelector('[data-coverage-results]'),
        radios: [...form.querySelectorAll('input[name="address_confirmed"]')],
        confirmation: form.querySelector('[data-coverage-confirmation]'),
        share: form.querySelector('[data-coverage-share]'),
        status: form.querySelector('[data-coverage-status]'),
        changeHint: form.querySelector('[data-coverage-change-hint]'),
        whatsappFallback: root.querySelector('[data-whatsapp-fallback]'),
    };

    const {
        coverageEndpoint,
        coverageLocationTimeout,
        coverageServerTimeout,
        coverageWhatsappNumber: whatsappNumber,
    } = form.dataset;

    const whatsappEnabled = hasNumber(whatsappNumber);

    const session = createCoverageSession({
        requestLocation: () => requestPosition({
            geolocation: navigator.geolocation,
            timeoutMs: Number(coverageLocationTimeout) * 1000,
        }),
        checkCoverage: (location) => checkCoverage({
            endpoint: coverageEndpoint,
            location,
            timeoutMs: Number(coverageServerTimeout) * 1000,
        }),
        onChange: (state, previous) => {
            if (state.message !== previous.message || state.result !== previous.result) {
                paintResults(root, elements, state);

                if (!whatsappEnabled) {
                    disableWhatsapp(root, elements.results);
                }
            }

            paintControls(elements, state);
            moveFocus(elements, state, previous);
        },
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        session.dispatch({ type: 'share' });
    });

    form.addEventListener('change', (event) => {
        if (event.target.name === 'address_confirmed') {
            session.dispatch({ type: 'answer', value: event.target.value });
        }
    });

    elements.results.addEventListener('click', (event) => {
        if (event.target.closest('[data-coverage-retry]')) {
            session.dispatch({ type: 'retry' });
        } else if (event.target.closest('[data-coverage-new-query]')) {
            session.dispatch({ type: 'newQuery' });
        }
    });

    // Los botones de WhatsApp están en toda la página, también en la zona que se repinta.
    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-whatsapp]');

        if (button && whatsappEnabled) {
            openWhatsapp(whatsappNumber, session.getState(), button);
            showWhatsappFallback(root, elements.whatsappFallback, whatsappNumber);
        }
    });

    if (!whatsappEnabled) {
        disableWhatsapp(root, root);
    }

    // pagehide y no visibilitychange: abrir WhatsApp oculta la página sin salir de ella (RF-30).
    // Se pinta aquí para que la copia que guarda el navegador ya esté en su estado inicial (RF-33).
    globalThis.addEventListener('pagehide', () => {
        session.dispatch({ type: 'leave' });
        hideWhatsappFallback(elements.whatsappFallback);
    });

    globalThis.addEventListener('pageshow', () => {
        paintControls(elements, session.getState());
    });

    // Algunos navegadores restauran los campos al recargar; la respuesta no se conserva (RF-47).
    paintControls(elements, session.getState());

    return session;
}
