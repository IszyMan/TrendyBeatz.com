(() => {
    const hasLinks = value => {
        return /(?:[a-z][a-z0-9+.-]*\s*:\s*\/\s*\/|\bwww\s*\.|\b(?:mailto|javascript|data)\s*:|\b[\p{L}\p{N}][\p{L}\p{N}.-]*\.[a-z]{2,63}(?:\b|\/)|<[^>]*>)/iu.test(value);
    };

    document.addEventListener('submit', async event => {
        const form = event.target.closest('.tb-comment-form');

        if (!form) {
            return;
        }

        event.preventDefault();

        if (form.dataset.submitting === 'true') {
            return;
        }

        const feedback = form.querySelector(
            '.tb-comment-feedback'
        );

        const button = form.querySelector(
            'button[type="submit"]'
        );

        const showFeedback = (message, success = false) => {
            feedback.hidden = false;
            feedback.textContent = message;

            feedback.classList.toggle(
                'is-success',
                success
            );

            feedback.setAttribute(
                'role',
                success ? 'status' : 'alert'
            );
        };

        const nameInput = form.elements.name;
        const messageInput = form.elements.message;

        nameInput.value = nameInput.value.trim();
        messageInput.value = messageInput.value.trim();

        if (!form.reportValidity()) {
            return;
        }

        if (
            hasLinks(nameInput.value)
            || hasLinks(messageInput.value)
        ) {
            showFeedback(
                'Links and HTML are not allowed. '
                + 'Please enter plain text.'
            );

            return;
        }

        form.dataset.submitting = 'true';

        button.disabled = true;
        button.textContent = 'Submitting…';
        feedback.hidden = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },

                body: new FormData(form),
            });

            const data = await response.json().catch(() => {
                return {};
            });

            if (!response.ok) {
                let errorMessage = Object.values(
                    data.errors || {}
                )
                    .flat()
                    .join(' ');

                if (response.status === 419) {
                    errorMessage =
                        'Your session expired. '
                        + 'Refresh the page and try again.';
                }

                if (response.status === 429) {
                    errorMessage =
                        'Too many submissions. '
                        + 'Please wait a minute and try again.';
                }

                throw new Error(
                    errorMessage
                    || 'Your comment could not be submitted. '
                    + 'Please try again.'
                );
            }

            form.reset();

            showFeedback(
                data.message
                    || 'Your message has been submitted '
                    + 'and is waiting for approval.',
                true
            );

            feedback.focus();
        } catch (error) {
            showFeedback(
                error.message
                    || 'Unable to connect. Please try again.'
            );
        } finally {
            delete form.dataset.submitting;

            button.disabled = false;
            button.textContent = 'Submit comment →';
        }
    });
})();