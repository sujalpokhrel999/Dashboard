// Income Categories Chart
const incomeCtx = document.getElementById('incomeCategories').getContext('2d');
new Chart(incomeCtx, {
    type: 'doughnut',
    data: {
        labels: incomeCategoryData.map(data => data.category),
        datasets: [{
            data: incomeCategoryData.map(data => data.total),
            backgroundColor: ['#4caf50', '#ff9800', '#03a9f4', '#e91e63', '#9c27b0'],
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom', // Positions the legend at the bottom
                labels: {
                    color: '#000', // Legend text color
                    font: {
                        size: 14 // Legend font size
                    },
                    generateLabels: function (chart) {
                        const data = chart.data;
                        return data.labels.map((label, i) => ({
                            text: label,
                            fillStyle: data.datasets[0].backgroundColor[i],
                        }));
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        const label = context.label || ''; // Gets the category label
                        const value = context.raw || 0; // Gets the value (total) for that category
                        // Format the value to 2 decimal places using toFixed(2)
                        const formattedValue = value.toFixed(2); 
                        return `${label}: ${formattedValue}`; // Displays the label and formatted value in the tooltip
                    }
                }
            }            
        }
    }
});


// Expense Categories Chart
const expenseCtx = document.getElementById('expenseCategories').getContext('2d');
new Chart(expenseCtx, {
    type: 'doughnut',
    data: {
        labels: expenseCategoryData.map(data => data.category),
        datasets: [{
            data: expenseCategoryData.map(data => data.total),
            backgroundColor: ['#f44336', '#2196f3', '#ffc107', '#8bc34a', '#ff5722'],
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom', // Positions the legend at the bottom
                labels: {
                    color: '#000', // Legend text color
                    font: {
                        size: 14 // Legend font size
                    },
                    generateLabels: function (chart) {
                        const data = chart.data;
                        return data.labels.map((label, i) => ({
                            text: label,
                            fillStyle: data.datasets[0].backgroundColor[i],
                        }));
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        const label = context.label || ''; // Gets the category label
                        const value = context.raw || 0; // Gets the value (total) for that category
                        // Format the value to 2 decimal places using toFixed(2)
                        const formattedValue = value.toFixed(2); 
                        return `${label}: ${formattedValue}`; // Displays the label and formatted value in the tooltip
                    }
                }
            }
            
        }
    }
});