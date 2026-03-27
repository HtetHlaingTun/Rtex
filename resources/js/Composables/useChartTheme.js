export function useChartTheme(chartPoints, activePeriod) {
    const getChartOptions = () => {
        const isDark = document.documentElement.classList.contains("dark");
        const points = chartPoints.value.length;

        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 300 },
            interaction: {
                mode: "index",
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    // ... existing tooltip config
                },
            },
            scales: {
                y: {
                    // ... existing y-axis config
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: isDark ? "#71717a" : "#C0C0BC",
                        font: { size: 11 },
                        maxTicksLimit: getMaxTicksLimit(),
                        maxRotation: activePeriod.value === "all" ? 45 : 0,
                        minRotation: 0,
                        autoSkip: true,
                        autoSkipPadding: 20,
                        // Custom callback to ensure labels are shown
                        callback: function (val, index) {
                            const label = this.getLabelForValue(val);
                            // Return empty string for empty labels
                            return label || "";
                        },
                    },
                },
            },
        };
    };

    const getMaxTicksLimit = () => {
        switch (activePeriod.value) {
            case "week":
                return 7;
            case "month":
                return 10;
            case "quarter":
                return 12;
            case "year":
                return 12;
            case "all":
                return 10;
            default:
                return 8;
        }
    };

    const formatPrice = (value) => {
        if (value == null || isNaN(value)) return "—";
        return value.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const handleThemeChange = (chartInstance, getOptions) => {
        if (chartInstance) {
            chartInstance.options = getOptions();
            chartInstance.update();
        }
    };

    return {
        getChartOptions,
        handleThemeChange,
    };
}
