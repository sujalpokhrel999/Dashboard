// Dark mode toggle functionality
const body = document.querySelector('body');
const modeSwitch = document.querySelector(".toggle-switch");
const modeText = document.querySelector(".mode-text");

if (modeSwitch) {
    modeSwitch.addEventListener("click", () => {
        body.classList.toggle("dark");
        
        if (body.classList.contains("dark")) {
            modeText.innerText = "Light mode";
        } else {
            modeText.innerText = "Dark mode";
        }
    });
}

// Date validation
document.querySelector('form').addEventListener('submit', function(e) {
    const startDate = new Date(document.querySelector('input[name="start_date"]').value);
    const endDate = new Date(document.querySelector('input[name="end_date"]').value);
    
    if (endDate < startDate) {
        e.preventDefault();
        alert('End date cannot be earlier than start date');
    }
});

// Initialize date inputs if empty
window.addEventListener('load', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (!startDate.value) {
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        startDate.value = thirtyDaysAgo.toISOString().split('T')[0];
    }
    
    if (!endDate.value) {
        const today = new Date();
        endDate.value = today.toISOString().split('T')[0];
    }
});