// Password validation
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const passwordError = document.getElementById('passwordError');
    
    function validatePasswords() {
        if (newPassword && confirmPassword && newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                passwordError.textContent = 'Passwords do not match';
                return false;
            } else if (newPassword.value.length < 6) {
                confirmPassword.classList.add('is-invalid');
                passwordError.textContent = 'Password must be at least 6 characters';
                return false;
            } else {
                confirmPassword.classList.remove('is-invalid');
                return true;
            }
        }
        return true;
    }
    
    if (newPassword && confirmPassword) {
        newPassword.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
    }
    
    // Form submission
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            if (!validatePasswords()) {
                e.preventDefault();
                return false;
            }
            return true;
        });
    }
    
    // Account deletion confirmation
    const confirmUsername = document.getElementById('confirmUsername');
    const confirmDelete = document.getElementById('confirmDelete');
    
    if (confirmUsername && confirmDelete) {
        confirmUsername.addEventListener('input', function() {
            confirmDelete.disabled = this.value !== this.placeholder;
        });
        
        confirmDelete.addEventListener('click', function() {
            if (confirm('Are you absolutely sure? This cannot be undone!')) {
                // Redirect to deletion script
                window.location.href = 'delete_account.php';
            }
        });
    }
    
    // Auto-focus on current password field
    const currentPasswordField = document.querySelector('[name="current_password"]');
    if (currentPasswordField) {
        currentPasswordField.focus();
    }
});