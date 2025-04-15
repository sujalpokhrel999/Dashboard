function renderExpenseChart(data) {
    if (!data || data.length === 0) return;
    
    const chartContainer = document.getElementById('incomeChart');
    chartContainer.innerHTML = '';
    
    // Find maximum value for scaling
    const maxAmount = Math.max(...data.map(item => item.total));
    
    data.forEach((item, index) => {
        // Create bar group
        const barGroup = document.createElement('div');
        barGroup.className = 'bar-group';
        
        // Create bar
        const bar = document.createElement('div');
        bar.className = `bar ${item.category}`;
        console.log(bar.className);
        console.log(item.total);
        const heightPercent = (item.total/maxAmount)*100;
        bar.style.height = heightPercent; // Start at 0 for animation
        
        // Format currency for display
        const formattedAmount = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(item.total);
        
        bar.setAttribute('data-value', formattedAmount);
        
        // Create label
        const label = document.createElement('div');
        label.className = 'bar-label';
        label.textContent = item.category;
        
        // Add to DOM
        barGroup.appendChild(bar);
        barGroup.appendChild(label);
        chartContainer.appendChild(barGroup);
        
        // Animate height after a small delay
        setTimeout(() => {
            bar.style.height = heightPercent + 'px';
        }, 100 * index);
    });
}

// Initialize chart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (typeof categoryData !== 'undefined') {
        renderExpenseChart(categoryData);
    }
});

// Update chart after adding new expense
const expenseForm = document.querySelector('form[method="post"]');
if (expenseForm) {
    expenseForm.addEventListener('submit', function() {
        setTimeout(() => {
            location.reload();
        }, 500);
    });
}