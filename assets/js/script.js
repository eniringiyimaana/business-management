// In your scripts.js or before </body>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-active');
            sidebar.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.body.addEventListener('click', function(e) {
        if (window.innerWidth < 992 && 
            document.body.classList.contains('sidebar-active') &&
            !e.target.closest('.sidebar') && 
            !e.target.closest('.sidebar-toggle')) {
            document.body.classList.remove('sidebar-active');
            sidebar.classList.remove('active');
        }
    });
});