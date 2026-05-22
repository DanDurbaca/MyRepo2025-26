// Prevent horizontal scrolling
document.addEventListener('DOMContentLoaded', function() {
    // Remove any horizontal overflow
    document.body.style.overflowX = 'hidden';
    document.documentElement.style.overflowX = 'hidden';
    
    // Check for elements causing overflow
    function checkOverflow() {
        const elements = document.querySelectorAll('*');
        elements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                console.log('Overflow element:', el);
                el.style.maxWidth = '100%';
                el.style.overflowX = 'hidden';
            }
        });
    }
    
    // Check on load and resize
    checkOverflow();
    window.addEventListener('resize', checkOverflow);
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Theme is now handled by theme.js - no need for duplicate initialization here
});