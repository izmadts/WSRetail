<!DOCTYPE html>
<html>
<head>
    <title>Test Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div style="width: 600px; margin: 50px auto;">
        <h2>Test Chart</h2>
        <canvas id="testChart" style="height: 400px;"></canvas>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            console.log('Chart.js version:', Chart.version);

            var ctx = document.getElementById('testChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Test Data',
                            data: [10, 20, 30, 40, 50, 60],
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                });
                console.log('Chart created');
            } else {
                console.error('Canvas element not found');
            }
        });
    </script>
</body>
</html>