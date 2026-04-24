(function () {
    'use strict';

    window.onTurnstileSuccess = function (token) {
        document.querySelector('input[name="contact[turnstile_token]"]').value = token;
    };

    const form = document.getElementById('contact-form');
    if (!form) return;

    const submitBtn = document.getElementById('contact-submit');
    const successBox = document.getElementById('contact-success');
    const successBody = successBox && successBox.querySelector('.alert-body');
    const errorBox = document.getElementById('contact-error');
    const errorBody = errorBox && errorBox.querySelector('.alert-body');

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
            el.removeAttribute('aria-invalid');
        });
        form.querySelectorAll('.form-field__error').forEach(el => { el.textContent = ''; });
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        successBox.classList.add('is-hidden');
        errorBox.classList.add('is-hidden');
        clearFieldErrors();
        submitBtn.disabled = true;

        const data = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            const json = await response.json();

            if (json.success) {
                if (successBody) successBody.textContent = json.message || '';
                successBox.classList.remove('is-hidden');
                form.reset();
                submitBtn.disabled = false;
            } else {
                // Per-field errors (validation failure)
                if (json.errors && typeof json.errors === 'object' && !Array.isArray(json.errors)) {
                    for (const [field, msg] of Object.entries(json.errors)) {
                        const errorEl = document.getElementById('error-' + field);
                        if (errorEl) errorEl.textContent = msg;
                        const input = form.querySelector('[name="contact[' + field + ']"]');
                        if (input) {
                            input.classList.add('is-invalid');
                            input.setAttribute('aria-invalid', 'true');
                        }
                    }
                }

                // General error (Turnstile failure, server error, or summary)
                if (json.error) {
                    if (errorBody) errorBody.textContent = json.error;
                    errorBox.classList.remove('is-hidden');
                }

                submitBtn.disabled = false;
            }
        } catch (_) {
            if (errorBody) errorBody.textContent = 'Connection error. Please try again.';
            errorBox.classList.remove('is-hidden');
            submitBtn.disabled = false;
        }
    });
}());
