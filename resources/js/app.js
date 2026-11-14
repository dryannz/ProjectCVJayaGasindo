document.addEventListener('click', (event) => {
    const trigger = event.target instanceof Element
        ? event.target.closest('[data-password-toggle]')
        : null;

    if (!(trigger instanceof HTMLButtonElement)) {
        return;
    }

    const targetSelector = trigger.getAttribute('data-password-toggle');
    const field = targetSelector ? document.querySelector(targetSelector) : null;

    if (!(field instanceof HTMLInputElement)) {
        return;
    }

    const shouldShow = field.type === 'password';

    field.type = shouldShow ? 'text' : 'password';
    trigger.textContent = shouldShow ? 'Sembunyikan' : 'Tampilkan';
    trigger.setAttribute('aria-pressed', String(shouldShow));
    trigger.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
});