const ACTIVE_OPTION = 'nav-option--active';

function markActive(links, id) {
    links.forEach((link) => {
        const active = link.getAttribute('href') === `#${id}`;

        link.classList.toggle(ACTIVE_OPTION, active);

        if (active) {
            link.setAttribute('aria-current', 'true');
        } else {
            link.removeAttribute('aria-current');
        }
    });
}

// En la página única, el menú señala la sección que está a media pantalla.
export function watchSections(root) {
    const sections = root?.querySelectorAll('[data-page-section]') ?? [];

    if (sections.length === 0 || !globalThis.IntersectionObserver) {
        return;
    }

    const links = [...root.querySelectorAll('.site-nav a[href^="#"]')];

    const observer = new globalThis.IntersectionObserver((entries) => {
        entries.filter((entry) => entry.isIntersecting).forEach(({ target }) => markActive(links, target.id));
    }, { rootMargin: '-50% 0px -50% 0px' });

    sections.forEach((section) => observer.observe(section));
}
