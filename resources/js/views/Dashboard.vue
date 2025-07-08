<template>
    <div class="dashboard__container">
        <h2 class="dashboard__title">Dashboard Overview</h2>
        <div v-if="stats">
            <h3>Status Counts</h3>
            <div class="dashboard__cards">
                <div
                    class="dashboard__card"
                    v-for="(count, status, i) in stats.by_status"
                    :key="status"
                    :class="colorClass(i)"
                >
                    <div class="dashboard__card-value">{{ count }}</div>
                    <div class="dashboard__card-label">{{ status.toUpperCase() }}</div>
                </div>
            </div>

            <h3>Category Chart</h3>
            <div class="dashboard__chart-card">
                <canvas ref="categoryChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</template>

<script>
import { getStats } from '../services/api';
import {
    Chart,
    BarElement,
    BarController,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(
    BarElement,
    BarController,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
);

export default {
    data() {
        return {
            stats: null,
            chartInstance: null,
        };
    },
    async mounted() {
        this.stats = await getStats();

        this.$nextTick(() => {
            this.renderChart(this.stats.by_category);
        });
    },
    methods: {
        renderChart(categoryCounts) {
            const ctx = this.$refs.categoryChart;

            if (!ctx || !(ctx instanceof HTMLCanvasElement)) {
                console.error('Canvas ref not found or invalid');
                return;
            }

            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            this.chartInstance = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(categoryCounts),
                    datasets: [
                        {
                            label: 'Tickets per Category',
                            data: Object.values(categoryCounts),
                            backgroundColor: '#4CAF50',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            });
        },
        colorClass(index) {
            const colors = ['teal', 'blue', 'purple'];
            return colors[index % colors.length];
        }
    },
};
</script>

<style src="../assets/styles/dashboard.css" scoped></style>
