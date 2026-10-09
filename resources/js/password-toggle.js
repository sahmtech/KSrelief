export function initPasswordToggles(root = document) {
    root.querySelectorAll('[data-password-toggle]').forEach((button) => {
        if (button.dataset.bound === '1') {
            return;
        }

        button.dataset.bound = '1';

        button.addEventListener('click', () => {
            const field = button.closest('.password-field');

            if (! field) {
                return;
            }

            const input = field.querySelector('.password-field__input');
            const icon = button.querySelector('i');

            if (! input || ! icon) {
                return;
            }

            const revealing = input.type === 'password';
            input.type = revealing ? 'text' : 'password';
            button.setAttribute('aria-pressed', revealing ? 'true' : 'false');
            icon.classList.toggle('ti-eye', ! revealing);
            icon.classList.toggle('ti-eye-off', revealing);
        });
    });
}
