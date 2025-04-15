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

document.addEventListener("DOMContentLoaded", function() {
    // Get all the status spans
    const statusElements = document.querySelectorAll('.status-badge');

    statusElements.forEach(status => {
        // Add a click event listener
        status.addEventListener('click', function() {
            const taskId = status.getAttribute('data-task-id');
            const currentStatus = status.getAttribute('data-status');
            const newStatus = currentStatus === 'Ongoing' ? 'Done' : 'Ongoing'; // Toggle status

            // Send an AJAX request to update the status in the database
            updateTaskStatus(taskId, newStatus, status);
        });
    });
});

function updateTaskStatus(taskId, newStatus, statusElement) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "task.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // On success, update the status in the UI and change the data-status attribute
            statusElement.innerText = newStatus;
            statusElement.setAttribute('data-status', newStatus);

            // Optionally, change the class for styling
            statusElement.className = `status-badge status-${newStatus.toLowerCase()}`;
        }
    };

    // Sending taskId and newStatus in the request
    xhr.send(`update_status=true&task_id=${taskId}&status=${newStatus}`);
}
