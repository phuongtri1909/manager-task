document.addEventListener('DOMContentLoaded', function() {
    const toggleSidebarBtn = document.getElementById('toggle-sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const container = document.querySelector('.dashboard-container');
    const sidebar = document.getElementById('sidebar');

    // Check for saved state
    const sidebarState = localStorage.getItem('sidebarState');
    if (sidebarState === 'collapsed' && window.innerWidth > 768) {
        container.classList.add('sidebar-collapsed');
    }

    // Toggle sidebar
    toggleSidebarBtn.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('show');
        } else {
            container.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', 
                container.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        }
    });

    // Close sidebar on mobile
    closeSidebarBtn.addEventListener('click', function() {
        sidebar.classList.remove('show');
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
        }
    });
});