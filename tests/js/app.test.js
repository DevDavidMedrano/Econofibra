import { describe, expect, it } from 'vitest';

describe('app.js', () => {
    it('loads as an ES module from resources/js', async () => {
        await expect(import('../../resources/js/app.js')).resolves.toBeDefined();
    });
});
