// resources/js/composables/useFuelTrends.js

export function useFuelTrends() {
    const calculateTrend = (newer, older) => {
        if (older === undefined || older === null) return null;
        const diff = newer - older;
        if (diff === 0) return null;
        return {
            diff: Math.abs(diff),
            icon: diff > 0 ? "▲" : "▼",
            color:
                diff > 0
                    ? "text-emerald-600 dark:text-emerald-400"
                    : "text-rose-600 dark:text-rose-400",
        };
    };

    const addTrendsToHistory = (records) => {
        if (!records || records.length === 0) return [];

        return records.map((record, idx) => {
            const previousRecord = records[idx - 1];

            return {
                ...record,
                trend_92: previousRecord
                    ? calculateTrend(record.octane_92, previousRecord.octane_92)
                    : null,
                trend_95: previousRecord
                    ? calculateTrend(record.octane_95, previousRecord.octane_95)
                    : null,
                trend_diesel: previousRecord
                    ? calculateTrend(record.diesel, previousRecord.diesel)
                    : null,
                trend_premium_diesel: previousRecord
                    ? calculateTrend(
                          record.premium_diesel,
                          previousRecord.premium_diesel
                      )
                    : null,
            };
        });
    };

    /**
     * Get today's trend compared to yesterday
     * CRITICAL: today is records[0], yesterday is records[1]
     */
    const getTodayTrends = (records) => {
        if (!records || records.length < 2) {
            return {
                trend92: null,
                trend95: null,
                trendDiesel: null,
                trendPremium: null,
            };
        }

        const today = records[0]; // Newest record
        const yesterday = records[1]; // Previous day's record

        // ✅ FIXED: Pass TODAY first (newer), YESTERDAY second (older)
        return {
            trend92: calculateTrend(today.octane_92, yesterday.octane_92),
            trend95: calculateTrend(today.octane_95, yesterday.octane_95),
            trendDiesel: calculateTrend(today.diesel, yesterday.diesel),
            trendPremium: calculateTrend(
                today.premium_diesel,
                yesterday.premium_diesel
            ),
        };
    };

    return { addTrendsToHistory, calculateTrend, getTodayTrends };
}
