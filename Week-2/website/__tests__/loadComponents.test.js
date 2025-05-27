// Test für "loadComponents.js" ::: website/__tests__/loadComponents.test.js
const loadComponents = require('../loadComponents');

describe('loadComponents', () => {
    it('should be an object', () => {
        expect(typeof loadComponents).toBe('object');
    });

    it('should load components without throwing', () => {
        expect(() => loadComponents()).not.toThrow();
    });

    // Add more specific tests based on your implementation
    // For example, if loadComponents returns an object:
    // it('should return an object with expected keys', () => {
    //   const components = loadComponents();
    //   expect(components).toHaveProperty('Header');
    //   expect(components).toHaveProperty('Footer');
    // });
});