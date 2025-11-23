export default function initDashboard(dashboardData) {

    function animateCounter(id, target, symbol = '', isCurrency = false) {
        let count = 0;
        const el = document.getElementById(id);
        if (!el) return;

        const speed = Math.max(1, Math.ceil(target / 100));

        const interval = setInterval(() => {
            count += speed;
            if (count >= target) count = target;

            const formatted = count.toLocaleString();
            el.innerText = isCurrency ? symbol + formatted : formatted;

            if (count >= target) clearInterval(interval);
        }, 20);
    }

    animateCounter("salesCounter", dashboardData.todaySales, dashboardData.currencySymbol, true);
    animateCounter("profitCounter", dashboardData.todayProfit, dashboardData.currencySymbol, true);
    animateCounter("lowStockCounter", dashboardData.lowStockCount, "", false);

    // ----------------- Chart -------------------
    if (dashboardData.isAdmin) {
        const canvas = document.getElementById("salesChart");
        if (canvas) {
            const ctx = canvas.getContext("2d");

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dashboardData.chartDays,
                    datasets: [{
                        label: 'Sales',
                        data: dashboardData.chartTotals,
                        backgroundColor: 'rgba(37,99,235,0.2)',
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                }
            });
        }
    }
}
