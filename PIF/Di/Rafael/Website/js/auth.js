// Form validation for registration
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('reg_password').value;
            const confirmPassword = document.getElementById('reg_confirmPassword').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            return true;
        });
    }
    
    // Auto-switch to register tab if there's a registration error
    if (window.location.search.includes('register_error')) {
        const registerTab = new bootstrap.Tab(document.querySelector('#authTabs a[href="#register"]'));
        registerTab.show();
    }
});