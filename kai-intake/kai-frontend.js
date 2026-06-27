jQuery(document).ready(function ($) {
    'use strict';

    // Prevent multiple initializations
    if (window.kaiIntakeInitialized) {
        return;
    }
    window.kaiIntakeInitialized = true;

    const $form = $('#kai-intake-form');
    const $messageDiv = $('#kai-form-message');
    const $submitBtn = $form.find('.kai-submit-btn');

    if (!$form.length) {
        return;
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
        const name = $form.find('#kai_name').val().trim();
        const email = $form.find('#kai_email').val().trim();
        const message = $form.find('#kai_message').val().trim();

        if (!name) {
            showMessage('Please enter your name.', 'error');
            return false;
        }
        if (!email) {
            showMessage('Please enter your email address.', 'error');
            return false;
        }
        if (!isValidEmail(email)) {
            showMessage('Please enter a valid email address.', 'error');
            return false;
        }
        if (!message) {
            showMessage('Please enter a message.', 'error');
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
        showMessage('Submitting your information...', 'info');

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        $.ajax({
            url: kai_intake_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'kai_intake_submit',
                nonce: data.kai_nonce,
                name: data.name,
                email: data.email,
                phone: data.phone,
                message: data.message
            },
            dataType: 'json',
            timeout: 30000,
            success: function (response) {
                if (response.success) {
                    showMessage(kai_intake_ajax.messages.success, 'success');
                    $form[0].reset();
                } else {
                    const errorMsg = response.data && response.data.error
                        ? response.data.error
                        : kai_intake_ajax.messages.error;
                    showMessage(errorMsg, 'error');
                }
            },
            error: function (xhr, status) {
                let errorMsg = kai_intake_ajax.messages.network_error;
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.error) {
                    errorMsg = xhr.responseJSON.data.error;
                } else if (status === 'timeout') {
                    errorMsg = 'Request timed out. Please try again.';
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
