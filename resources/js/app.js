document.querySelectorAll('[data-loading-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');

        if (!button || button.disabled) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.querySelector('[data-loading-label]')?.classList.add('hidden');
        button.querySelector('[data-loading-indicator]')?.classList.remove('hidden');
        button.querySelector('[data-loading-indicator]')?.classList.add('inline-flex');
    });
});
