<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role(['Administrator', 'Manager']);

$range = sanitize($_GET['range'] ?? 'monthly');
$revenue = get_revenue_report($range);
$defaulters = get_defaulters();
$consumption = get_consumption_analysis();

// Prepare chart data
$revenueLabels = array_reverse(array_column($revenue, 'bucket'));
$revenueData = array_reverse(array_column($revenue, 'total'));
$consumptionLabels = array_column($consumption, 'utility_name');
$consumptionData = array_column($consumption, 'total_consumption');

require_once __DIR__ . '/../includes/header.php';
?>

<section class="grid two-columns">
    <!-- Revenue Chart -->
    <article class="card">
        <div class="card-header">
            <h2>Revenue Overview</h2>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <form method="GET" class="inline-form">
                    <select name="range">
                        <option value="daily" <?= $range === 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $range === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="yearly" <?= $range === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                    </select>
                    <button class="btn btn-secondary" type="submit">Apply</button>
                </form>
                <button class="export-btn export-btn-pdf" onclick="showToast('Export', 'Generating PDF report...', 'info')">📄 PDF</button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </article>

    <!-- Consumption Chart -->
    <article class="card">
        <div class="card-header">
            <h2>Consumption by Utility</h2>
        </div>
        <div class="chart-container">
            <canvas id="consumptionChart"></canvas>
        </div>
    </article>
</section>

<section class="grid two-columns">
    <!-- Revenue Table -->
    <article class="card">
        <h3>Revenue Details</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenue as $row): ?>
                        <tr>
                            <td><?= e($row['bucket']) ?></td>
                            <td>$<?= number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- Defaulters List -->
    <article class="card">
        <div class="card-header">
            <h3>Defaulters</h3>
            <?php if ($defaulters): ?>
                <span class="status status-overdue"><?= count($defaulters) ?> overdue</span>
            <?php endif; ?>
        </div>
        <?php if (!$defaulters): ?>
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <p>All accounts are current!</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($defaulters as $defaulter): ?>
                            <tr>
                                <td><?= htmlspecialchars($defaulter['first_name'] . ' ' . $defaulter['last_name']) ?></td>
                                <td><?= $defaulter['due_date']->format('Y-m-d') ?></td>
                                <td class="text-danger">$<?= number_format($defaulter['total_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js global defaults
    Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, sans-serif";
    Chart.defaults.color = '#64748b';
    
    // Revenue Chart - Gradient Line
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
    revenueGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    revenueGradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($revenueLabels) ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?= json_encode($revenueData) ?>,
                borderColor: '#3b82f6',
                backgroundColor: revenueGradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#fff',
                    bodyColor: '#94a3b8',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Consumption Chart - Doughnut
    const consumptionCtx = document.getElementById('consumptionChart').getContext('2d');
    const consumptionColors = [
        '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899'
    ];
    
    new Chart(consumptionCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($consumptionLabels) ?>,
            datasets: [{
                data: <?= json_encode($consumptionData) ?>,
                backgroundColor: consumptionColors,
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#fff',
                    bodyColor: '#94a3b8',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.parsed.toLocaleString()} units (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
