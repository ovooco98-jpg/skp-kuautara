// Chart.js helper untuk dashboard
import { Chart, registerables } from 'chart.js';

// Register all Chart.js components
Chart.register(...registerables);

// Export Chart untuk penggunaan global
window.Chart = Chart;

// Helper function untuk membuat chart dengan konfigurasi default
export function createChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        console.error(`Canvas dengan id "${canvasId}" tidak ditemukan`);
        return null;
    }

    return new Chart(ctx, {
        ...config,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: config.showLegend !== false,
                    position: config.legendPosition || 'top',
                },
                tooltip: {
                    enabled: true,
                },
            },
            ...config.options,
        },
    });
}

// Helper untuk chart line
export function createLineChart(canvasId, labels, datasets, options = {}) {
    return createChart(canvasId, {
        type: 'line',
        data: {
            labels,
            datasets: datasets.map(ds => ({
                fill: false,
                tension: 0.4,
                ...ds,
            })),
        },
        ...options,
    });
}

// Helper untuk chart bar
export function createBarChart(canvasId, labels, datasets, options = {}) {
    return createChart(canvasId, {
        type: 'bar',
        data: {
            labels,
            datasets,
        },
        ...options,
    });
}

// Helper untuk chart pie/doughnut
export function createPieChart(canvasId, labels, data, options = {}) {
    return createChart(canvasId, {
        type: options.type || 'pie',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: options.backgroundColor || [
                    'rgba(59, 130, 246, 0.8)', // blue
                    'rgba(16, 185, 129, 0.8)', // green
                    'rgba(245, 158, 11, 0.8)', // yellow
                    'rgba(239, 68, 68, 0.8)',  // red
                    'rgba(139, 92, 246, 0.8)', // purple
                    'rgba(236, 72, 153, 0.8)', // pink
                ],
                borderColor: options.borderColor || '#fff',
                borderWidth: options.borderWidth || 2,
            }],
        },
        ...options,
    });
}

