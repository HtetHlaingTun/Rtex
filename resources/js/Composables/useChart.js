import { computed } from "vue";

export function useChartData(props, activePeriod, periods) {
    const filteredData = computed(() => {
        if (!props.data.length) return [];

        const period = periods.find((p) => p.value === activePeriod.value);
        if (!period || !period.days) return [...props.data];

        const cutoffDate = new Date();
        cutoffDate.setDate(cutoffDate.getDate() - period.days);

        return props.data.filter(
            (item) => new Date(item.created_at) >= cutoffDate,
        );
    });

    const chartPoints = computed(() => {
        if (!filteredData.value.length) return [];

        // Sort by date ascending
        return [...filteredData.value].sort(
            (a, b) => new Date(a.created_at) - new Date(b.created_at),
        );
    });

    const chartStats = computed(() => {
        if (!chartPoints.value.length) return null;

        const buyRates = chartPoints.value
            .map((p) => Number(p.buy_rate))
            .filter((v) => !isNaN(v));
        const sellRates = chartPoints.value
            .map((p) => Number(p.sell_rate))
            .filter((v) => !isNaN(v));

        if (!buyRates.length || !sellRates.length) return null;

        const avgSell = sellRates.reduce((a, b) => a + b, 0) / sellRates.length;

        return {
            high: formatPrice(Math.max(...sellRates)),
            low: formatPrice(Math.min(...sellRates)),
            avg: formatPrice(avgSell),
        };
    });

    const chartInfo = computed(() => {
        if (!chartPoints.value.length) return null;

        const period = periods.find((p) => p.value === activePeriod.value);
        if (period?.value === "all") {
            const firstDate = new Date(chartPoints.value[0].created_at);
            return `since ${firstDate.toLocaleDateString("en-GB", { month: "short", year: "numeric" })}`;
        }

        return `${chartPoints.value.length} data points`;
    });

    const formatPrice = (value) => {
        if (value == null || isNaN(value)) return "—";
        return value.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    return {
        filteredData,
        chartPoints,
        chartStats,
        chartInfo,
    };
}
