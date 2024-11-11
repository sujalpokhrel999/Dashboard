// script.js

const ctx = document.getElementById('lineChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            'Feb 1', 'Feb 7', 'Feb 15', 'Feb 22', 'Mar 1', 'Mar 8', 'Mar 15', 'Mar 22', 'Apr 1', 'Apr 8', 'Apr 15', 'Apr 22', 'May 1'
        ],
        datasets: [{
            label: 'Price',
            data: [150, 180, 200, 175, 165, 140, 135, 155, 120, 110, 100, 90, 105],
            borderColor: '#007bff',
            fill: false,
            tension: 0.4, // Smoothing effect
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                min: 20,
                max: 250,
                title: {
                    display: true,
                    text: 'Price',
                },
            },
            x: {
                title: {
                    display: true,
                    text: 'Date',
                },
            }
        },
        plugins: {
            legend: {
                display: false
            },
            annotation: {
                annotations: {
                    box1: {
                        type: 'box',
                        xMin: 8, // Start of shaded area (e.g., 'Apr 1')
                        xMax: 11, // End of shaded area (e.g., 'Apr 22')
                        backgroundColor: 'rgba(200, 200, 200, 0.2)',
                    }
                }
            }
        }
    }
});
