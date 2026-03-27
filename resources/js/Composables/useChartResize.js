import { computed } from "vue";

export function useChartResize(chartPoints, activePeriod) {
    const isScrollable = computed(() => {
        const pointsCount = chartPoints.value.length;
        return (
            (activePeriod.value === "all" || activePeriod.value === "year") &&
            pointsCount > 60
        );
    });

    const canvasWidth = computed(() => {
        if (!isScrollable.value) return "100%";
        const points = chartPoints.value.length;
        const pointWidth = activePeriod.value === "all" ? 12 : 15;
        return Math.max(points * pointWidth, 800) + "px";
    });

    return {
        isScrollable,
        canvasWidth,
    };
}
