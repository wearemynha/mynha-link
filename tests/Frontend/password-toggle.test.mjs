import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { runInNewContext } from 'node:vm';

const script = readFileSync(new URL('../../assets/mynha-assets/mynha.js', import.meta.url), 'utf8');

function field(id) {
    const input = { type: 'password', value: 'Example-password-123' };
    const attributes = { 'aria-controls': id, 'aria-pressed': 'false' };
    const classes = new Set(['bi-eye']);
    const listeners = {};
    const button = {
        hidden: true,
        dataset: { labelShow: 'Exibir senha', labelHide: 'Ocultar senha' },
        getAttribute: (name) => attributes[name],
        setAttribute: (name, value) => { attributes[name] = value; },
        addEventListener: (name, listener) => { listeners[name] = listener; },
        querySelector: () => ({ classList: { toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name) } }),
    };
    return { id, input, button, attributes, classes, listeners };
}

function initialize(fields, missingTarget = false) {
    runInNewContext(script, { document: {
        querySelectorAll: () => fields.map((item) => item.button),
        getElementById: (id) => missingTarget ? null : fields.find((item) => item.id === id)?.input,
    } });
}

test('toggles each password independently, preserving values and updating accessible labels', () => {
    const password = field('password');
    const confirmation = field('confirmation');
    initialize([password, confirmation]);

    for (const item of [password, confirmation]) {
        assert.equal(item.button.hidden, false);
        assert.equal(item.input.type, 'password');
    }
    password.listeners.click();
    assert.equal(password.input.type, 'text');
    assert.equal(confirmation.input.type, 'password');
    assert.equal(password.attributes['aria-pressed'], 'true');
    assert.equal(password.attributes['aria-label'], 'Ocultar senha');
    assert.equal(password.button.title, 'Ocultar senha');
    assert.equal(password.classes.has('bi-eye-slash'), true);
    assert.equal(password.classes.has('bi-eye'), false);

    password.listeners.click();
    assert.equal(password.input.type, 'password');
    assert.equal(password.attributes['aria-pressed'], 'false');
    assert.equal(password.attributes['aria-label'], 'Exibir senha');
    assert.equal(password.classes.has('bi-eye'), true);
    assert.equal(password.classes.has('bi-eye-slash'), false);
    confirmation.listeners.click();
    assert.equal(confirmation.input.type, 'text');
    assert.equal(password.input.type, 'password');
    assert.equal(password.input.value, 'Example-password-123');
    assert.equal(confirmation.input.value, 'Example-password-123');
});

test('leaves buttons hidden when the target field is absent', () => {
    const item = field('missing');
    initialize([item], true);
    assert.equal(item.button.hidden, true);
    assert.equal(item.listeners.click, undefined);
});
