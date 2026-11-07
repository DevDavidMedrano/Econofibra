import { mountCoveragePage } from './coverage/page.js';
import { mountHomePage } from './home/page.js';
import { watchSections } from './home/sections.js';

mountCoveragePage(globalThis.document);
mountHomePage(globalThis.document);
watchSections(globalThis.document);
