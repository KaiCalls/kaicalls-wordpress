jQuery(document).ready(function ($) {
    'use strict';

    if (window.kaicallsAiIntakeInitialized) {
        return;
    }
    window.kaicallsAiIntakeInitialized = true;

    const config = window.kaicallsAiIntakeFrontend || {};
    const messages = config.messages || {};
    const $form = $('#kaicalls-ai-intake-form');
    const $messageDiv = $('#kaicalls-ai-intake-form-message');
    const $submitBtn = $form.find('.kaicalls-ai-intake-submit-btn');

    if (!$form.length) {
        return;
    }

    function getMessage(key, fallback) {
        return messages[key] || fallback;
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function showMessage(message, type) {
        $messageDiv.removeClass('success error info').addClass(type);
        $messageDiv.text(message).show();
        if (type === 'success') {
            setTimeout(function () {
                $messageDiv.fadeOut();
            }, 5000);
        }
    }

    function validateForm() {
        const name = $form.find('#kaicalls_ai_intake_name').val().trim();
        const email = $form.find('#kaicalls_ai_intake_email').val().trim();
        const message = $form.find('#kaicalls_ai_intake_message').val().trim();

        if (!name) {
            showMessage(getMessage('nameRequired', 'Please enter your name.'), 'error');
            return false;
        }
        if (!email) {
            showMessage(getMessage('emailRequired', 'Please enter your email address.'), 'error');
            return false;
        }
        if (!isValidEmail(email)) {
            showMessage(getMessage('invalidEmail', 'Please enter a valid email address.'), 'error');
            return false;
        }
        if (!message) {
            showMessage(getMessage('messageRequired', 'Please enter a message.'), 'error');
            return false;
        }
        return true;
    }

    function setSubmitting(submitting) {
        $submitBtn.prop('disabled', submitting);
        $form.data('submitting', submitting);
    }

    $form.on('submit', function (e) {
        e.preventDefault();

        if ($form.data('submitting')) {
            return false;
        }
        if (!validateForm()) {
            return false;
        }

        setSubmitting(true);
        $messageDiv.hide();
        showMessage(getMessage('submitting', 'Submitting your information...'), 'info');

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'kaicalls_ai_intake_submit',
                nonce: data.kaicalls_ai_intake_nonce,
                name: data.name,
                email: data.email,
                phone: data.phone,
                message: data.message
            },
            dataType: 'json',
            timeout: 30000,
            success: function (response) {
                if (response.success) {
                    showMessage(getMessage('success', 'Thank you for your submission!'), 'success');
                    $form[0].reset();
                } else {
                    const errorMsg = response.data && response.data.error
                        ? response.data.error
                        : getMessage('error', 'An error occurred. Please try again.');
                    showMessage(errorMsg, 'error');
                }
            },
            error: function (xhr, status) {
                let errorMsg = getMessage('networkError', 'A network error occurred. Please try again.');
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.error) {
                    errorMsg = xhr.responseJSON.data.error;
                } else if (status === 'timeout') {
                    errorMsg = getMessage('timeout', 'Request timed out. Please try again.');
                }
                showMessage(errorMsg, 'error');
            },
            complete: function () {
                setSubmitting(false);
            }
        });

        return false;
    });
});
