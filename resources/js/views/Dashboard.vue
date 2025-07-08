<template>
    <div class="dashboard__container">

        <div v-if="stats">
            <h3>Status Counts</h3>
            <ul>
                <li v-for="(count, status) in stats.by_status" :key="status">
                    {{ status }}: {{ count }}
                </li>
            </ul>

            <h3>Category Chart</h3>
            <canvas ref="categoryChart" width="400" height="200"></canvas>
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
    Legend
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
    },
};
</script>

<style src="../assets/styles/dashboard.css" scoped></style>
