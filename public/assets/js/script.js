document.addEventListener('DOMContentLoaded', function() {
    const toggleSidebarBtn = document.getElementById('toggle-sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const container = document.querySelector('.dashboard-container');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarHeaderText = document.querySelector('.sidebar-header span');
    const menuTextElements = document.querySelectorAll('.sidebar-menu .menu-text');

    // Function to update text visibility based on sidebar state
    function updateTextVisibility(isCollapsed) {
        // Toggle visibility of the header text
        if (sidebarHeaderText) {
            sidebarHeaderText.style.display = isCollapsed ? 'none' : '';
        }
        
        // Toggle visibility of menu text items
        menuTextElements.forEach(element => {
            element.style.display = isCollapsed ? 'none' : '';
        });
    }
    
    // Check for saved state
    const sidebarState = localStorage.getItem('sidebarState');
    if (sidebarState === 'collapsed' && window.innerWidth > 768) {
        container.classList.add('sidebar-collapsed');
        updateTextVisibility(true);
    }

    // Toggle sidebar
    toggleSidebarBtn.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('show');
            const isMobileCollapsed = !sidebar.classList.contains('show');
            updateTextVisibility(isMobileCollapsed);
        } else {
            container.classList.toggle('sidebar-collapsed');
            const isCollapsed = container.classList.contains('sidebar-collapsed');
            updateTextVisibility(isCollapsed);
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        }
    });

    // Close sidebar on mobile
    closeSidebarBtn.addEventListener('click', function() {
        sidebar.classList.remove('show');
        updateTextVisibility(true);
        
        // On mobile, also update the collapsed state in localStorage
        if (window.innerWidth <= 768) {
            localStorage.setItem('sidebarState', 'collapsed');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            // When switching from mobile to desktop, respect the saved state
            const isCollapsed = container.classList.contains('sidebar-collapsed');
            updateTextVisibility(isCollapsed);
        }
    });
});