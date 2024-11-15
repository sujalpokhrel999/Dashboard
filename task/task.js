// Get all status badge elements
const statusBadges = document.querySelectorAll('.status-badge');

// Add event listener to each status badge
statusBadges.forEach(statusBadge => {
    statusBadge.addEventListener('click', function() {
        // Toggle the classes and text content
        if (statusBadge.classList.contains('status-ongoing')) {
            statusBadge.classList.remove('status-ongoing');
            statusBadge.classList.add('status-done');
            statusBadge.textContent = 'Done'; // Change text to 'Done'
        } else {
            statusBadge.classList.remove('status-done');
            statusBadge.classList.add('status-ongoing');
            statusBadge.textContent = 'Ongoing'; // Change text to 'Ongoing'
        }
    });
});



  // Function to close the toast
  function closeToast() {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 10000);
    }
}

// Automatically close toast after 3 seconds
window.onload = () => {
    setTimeout(closeToast, 10000);
};
