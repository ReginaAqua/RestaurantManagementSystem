    // Sidebar toggle functionality
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    toggleBtn.addEventListener('click', function(e) {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('collapsed');
    });
    
    // Profile dropdown functionality
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    
    profileBtn.addEventListener('click', function(e) {
      e.stopPropagation(); // Prevent click event from bubbling up
      profileDropdown.style.display = profileDropdown.style.display === 'flex' ? 'none' : 'flex';
    });
    
    // Close dropdown when clicking anywhere else
    document.addEventListener('click', function() {
      profileDropdown.style.display = 'none';
    });
    
    // Add ripple effect to dashboard boxes
    const boxes = document.querySelectorAll('.dashboard-box');
    
    boxes.forEach(box => {
      box.addEventListener('click', function(e) {
        // Remove any existing ripple element
        const existingRipple = box.querySelector('.ripple');
        if (existingRipple) {
          existingRipple.remove();
        }
        // Create a new ripple element
        const ripple = document.createElement('span');
        ripple.classList.add('ripple');
        // Get click coordinates relative to the box
        const rect = box.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.width = ripple.style.height = Math.max(rect.width, rect.height) + 'px';
        box.appendChild(ripple);
        // Remove the ripple after the animation ends
        ripple.addEventListener('animationend', () => {
          ripple.remove();
        });
      });
    });