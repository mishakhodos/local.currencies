<canvas id="currencyChart" width="800" height="400"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('currencyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($arResult['DATA'], 'date')) ?>,
            datasets: [{
                label: 'Курс <?= htmlspecialcharsbx($arParams['CURRENCY']) ?>',
                data: <?= json_encode(array_column($arResult['DATA'], 'rate')) ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });
</script>