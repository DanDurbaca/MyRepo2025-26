// app.js - Common JavaScript utilities for AJAX and UI enhancements

// Configure global AJAX headers to include CSRF token for security
$.ajaxSetup({
    headers: {
        'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') || ''
    }
});

// Display a toast notification (custom, not Bootstrap)
function showToast(message, type = 'info') {
    // Ensure toast container exists in DOM
    if (!$('#toast-container').length) {
        $('body').append('<div id="toast-container"></div>');
    }

    // Create and append toast element to container
    const toast = $('<div class="toast toast-' + type + '">' + message + '</div>');
    $('#toast-container').append(toast);

    // Remove toast after 4 seconds (auto-dismiss)
    setTimeout(function() {
        toast.fadeOut(300, function() {
            toast.remove();
        });
    }, 4000);
}

// Submit a form via AJAX, with success and error callbacks
function ajaxSubmitForm(form, successCallback, errorCallback) {
    // Accepts HTMLFormElement, jQuery object, or selector
    const $form = $(form);
    const formEl = ($form && $form.length) ? $form[0] : form;
    if (!(formEl instanceof HTMLFormElement)) {
        throw new TypeError("ajaxSubmitForm: 'form' must be a form element, selector, or jQuery form object");
    }
    const submitBtn = $form.find('button[type="submit"]');
    const originalText = submitBtn.html();

    // Disable submit button and show loading state
    submitBtn.prop('disabled', true).text('Loading...');

    const formData = new FormData(formEl); // Collect form data

    // Perform AJAX request for form submission
    $.ajax({
        url: $form.attr('action') || window.location.pathname,
        method: $form.attr('method') || 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalText);
            if (response.success || response.ok) {
                showToast(response.message || 'Success!', 'success');
                if (successCallback) successCallback(response);
            } else {
                showToast(response.error || response.message || 'An error occurred', 'error');
                if (errorCallback) errorCallback(response);
            }
        },
        error: function(xhr, status, error) {
            submitBtn.prop('disabled', false).html(originalText);
            // Show SweetAlert for network errors
            Swal.fire({
                title: 'Network Error',
                text: 'Please check your connection and try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            if (errorCallback) errorCallback(xhr.responseJSON);
        }
    });
}

// Show a SweetAlert confirmation dialog for actions
function confirmAction(title, text, confirmButtonText, cancelButtonText) {
    confirmButtonText = confirmButtonText || 'Yes';
    cancelButtonText = cancelButtonText || 'Cancel';
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText
    });
}

// Dynamically load content into a target element via AJAX
function loadContent(url, target, callback) {
    $(target).html('<p>Loading...</p>');
    $.get(url, function(data) {
        $(target).html(data);
        if (callback) callback();
    }).fail(function() {
        $(target).html('<div class="alert alert-danger">Failed to load content.</div>');
    });
}

// On document ready, set CSRF token meta tag for AJAX
$(document).ready(function() {
    var csrfToken = $('input[name="csrf_token"]').val();
    if (csrfToken) {
        $('meta[name="csrf-token"]').attr('content', csrfToken);
    }
});