<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    chartData: {
        type: Array,
        default: () => []
    },
    isUp: {
        type: Boolean,
        default: true
    }
});

const chartCanvas = ref(null);
let chartInstance = null;

const color = () => props.isUp ? '#10b981' : '#f43f5e';

const buildChart = () => {
    if (!chartCanvas.value || !props.chartData?.length) return;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: props.chartData.map(p => p.date),
            datasets: [{
                data: props.chartData.map(p => p.price),
                borderColor: color(),
                borderWidth: 2,
                backgroundColor: props.isUp
                    ? 'rgba(16,185,129,0.08)'
                    : 'rgba(244,63,94,0.08)',
                fill: true,
                tension: 0.3,
                pointRadius: props.chartData.length > 20 ? 0 : 3,
                pointHoverRadius: 5,
                pointBackgroundColor: color(),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + ctx.parsed.y.toLocaleString() + ' MMK'
                    },
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10, family: 'monospace' },
                        maxTicksLimit: 6,
                        maxRotation: 0,
                    },
                    border: { display: false }
                },
                y: {
                    position: 'right',
                    grid: {
                        color: 'rgba(148,163,184,0.1)',
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10, family: 'monospace' },
                        maxTicksLimit: 4,
                        callback: (v) => (v / 1000000).toFixed(2) + 'M'
                    },
                    border: { display: false }
                }
            },
            interaction: {
                mode: 'index',
                intersect: false,
            }
        }
    });
};

onMounted(async () => {
    await nextTick();
    buildChart();
});

watch(() => [props.chartData, props.isUp], async () => {
    await nextTick();
    buildChart();
}, { deep: true });

watch(
    [() => props.data, () => props.activePeriod], // Watch both data AND the period tab
    () => {
        updateChart();
    },
    { deep: true }
);

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>

<template>
    <div v-if="chartData.length > 1" style="position: relative; height: 220px;">
        <canvas ref="chartCanvas"></canvas>
    </div>

    <div v-else
        class="h-48 bg-slate-50 rounded-2xl border border-dashed border-slate-200 flex items-center justify-center">
        <p class="text-slate-400 text-xs italic">
            Not enough data yet — chart appears after 2+ records
        </p>
    </div>
</template>