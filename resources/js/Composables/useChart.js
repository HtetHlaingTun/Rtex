import { computed } from "vue";

export function useChartData(props, activePeriod, periods) {
    // Store raw data (all records)
    const rawData = computed(() => {
        return [...props.data];
    });

    // For chart display - aggregate by day (1 point per day)
    const chartPoints = computed(() => {
        if (!rawData.value.length) return [];

        // Group by date and average for chart display
        const grouped = {};
        rawData.value.forEach((item) => {
            const date = new Date(item.created_at).toISOString().split("T")[0];
            if (!grouped[date]) {
                grouped[date] = {
                    buy_rates: [],
                    sell_rates: [],
                    date: date,
                };
            }
            grouped[date].buy_rates.push(item.buy_rate);
            grouped[date].sell_rates.push(item.sell_rate);
        });

        return Object.values(grouped)
            .map((group) => ({
                buy_rate:
                    group.buy_rates.reduce((a, b) => a + b, 0) /
                    group.buy_rates.length,
                sell_rate:
                    group.sell_rates.reduce((a, b) => a + b, 0) /
                    group.sell_rates.length,
                created_at: group.date + "T00:00:00.000Z",
            }))
            .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    });

    const pointsCount = computed(() => chartPoints.value.length);

    // Calculate stats from RAW DATA (not aggregated chart points)
    const chartStats = computed(() => {
        // Use raw data for stats
        const points = rawData.value;
        if (!points.length)
            return { high: null, low: null, avg: null, volatility: null };

        // Get ALL sell rates from raw data
        const sellRates = points
            .map((p) => p.sell_rate)
            .filter((v) => v !== null && v !== undefined);

        if (sellRates.length === 0) {
            return { high: null, low: null, avg: null, volatility: null };
        }

        // Calculate from ALL raw records
        const high = Math.max(...sellRates);
        const low = Math.min(...sellRates);
        const avg = sellRates.reduce((a, b) => a + b, 0) / sellRates.length;

        let volatility = 0;
        if (sellRates.length > 1) {
            const variance =
                sellRates.reduce(
                    (acc, val) => acc + Math.pow(val - avg, 2),
                    0
                ) / sellRates.length;
            const stdDev = Math.sqrt(variance);
            volatility = (stdDev / avg) * 100;
        }

        return {
            high: Math.round(high * 100) / 100,
            low: Math.round(low * 100) / 100,
            avg: Math.round(avg * 100) / 100,
            volatility: Math.round(volatility * 100) / 100,
        };
    });

    const chartInfo = computed(() => {
        if (!chartPoints.value.length) return null;
        const period = periods.find((p) => p.value === activePeriod.value);
        if (period?.value === "all") {
            const firstDate = new Date(chartPoints.value[0].created_at);
            return `since ${firstDate.toLocaleDateString("en-GB", {
                month: "short",
                year: "numeric",
            })}`;
        }
        return `${pointsCount.value} data points`;
    });

    return {
        chartPoints,
        chartStats,
        pointsCount,
        chartInfo,
        filteredData: rawData,
    };
}
