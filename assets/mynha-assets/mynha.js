document.querySelectorAll('.mynha-ui [data-password-toggle]').forEach((button) => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    if (!input || input.type !== 'password') return;

    button.hidden = false;
    button.addEventListener('click', () => {
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';

        const label = visible ? button.dataset.labelHide : button.dataset.labelShow;
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', label);
        button.title = label;

        const icon = button.querySelector('i');
        if (icon) {
            icon.classList.toggle('bi-eye', !visible);
            icon.classList.toggle('bi-eye-slash', visible);
        }
    });
});
