<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$metrics = get_dashboard_metrics();
$recentBills = get_recent_bills(6);
$currentUser = ums_current_user();
$role = $currentUser['role'];

// Get monthly revenue for chart (last 6 months)
$revenueData = get_revenue_report('monthly');
$chartLabels = array_slice(array_reverse(array_column($revenueData, 'bucket')), -6);
$chartData = array_slice(array_reverse(array_column($revenueData, 'total')), -6);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
        <div class="hero-grid"></div>
    </div>
    <div class="hero-content">
        <div class="hero-text">
            <p class="hero-greeting animate-fade-up">Welcome back,</p>
            <h1 class="hero-title animate-fade-up delay-1"><?= htmlspecialchars($currentUser['name']) ?></h1>
            <p class="hero-subtitle animate-fade-up delay-2">Here's what's happening with your utility management today.</p>
        </div>
        <div class="hero-stats">
            <div class="hero-stat animate-scale-in delay-1">
                <span class="hero-stat-value"><?= $metrics['customers'] ?></span>
                <span class="hero-stat-label">Customers</span>
            </div>
            <div class="hero-stat animate-scale-in delay-2">
                <span class="hero-stat-value"><?= $metrics['active_meters'] ?></span>
                <span class="hero-stat-label">Active Meters</span>
            </div>
            <div class="hero-stat animate-scale-in delay-3">
                <span class="hero-stat-value">$<?= number_format($metrics['monthly_revenue'], 0) ?></span>
                <span class="hero-stat-label">This Month</span>
            </div>
            <div class="hero-stat animate-scale-in delay-4 <?= $metrics['pending_bills'] > 0 ? 'hero-stat-warning' : '' ?>">
                <span class="hero-stat-value"><?= $metrics['pending_bills'] ?></span>
                <span class="hero-stat-label">Pending Bills</span>
            </div>
        </div>
    </div>
</section>

<section class="grid two-columns">
    <!-- Revenue Trend Chart -->
    <article class="card hover-lift">
        <div class="card-header">
            <h2>Revenue Trend</h2>
            <a href="reports.php" class="link hover-arrow">View details <span class="arrow">→</span></a>
        </div>
        <div class="chart-container" style="height: 250px;">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </article>

    <!-- Quick Actions -->
    <article class="card hover-lift">
        <h2>Quick Actions</h2>
        <div class="action-grid">
            <a class="action-card" href="customers.php">
                <span class="action-icon">👥</span>
                <span class="action-label">Customers</span>
            </a>
            <a class="action-card" href="meters.php">
                <span class="action-icon">📟</span>
                <span class="action-label">Meters</span>
            </a>
            <a class="action-card" href="readings.php">
                <span class="action-icon">📊</span>
                <span class="action-label">Readings</span>
            </a>
            <a class="action-card" href="billing.php">
                <span class="action-icon">📄</span>
                <span class="action-label">Billing</span>
            </a>
            <a class="action-card" href="payments.php">
                <span class="action-icon">💳</span>
                <span class="action-label">Payments</span>
            </a>
            <a class="action-card" href="reports.php">
                <span class="action-icon">📈</span>
                <span class="action-label">Reports</span>
            </a>
            <?php if (in_array($role, ['Administrator', 'Manager'])): ?>
            <a class="action-card action-card-alt" href="staff.php">
                <span class="action-icon">🔐</span>
                <span class="action-label">Staff</span>
            </a>
            <?php endif; ?>
        </div>
    </article>
</section>

<section class="card hover-lift">
    <div class="card-header">
        <h2>Recent Bills</h2>
        <a href="billing.php" class="link hover-arrow">View all <span class="arrow">→</span></a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Customer</th>
                    <th>Utility</th>
                    <th>Consumption</th>
                    <th>Status</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentBills as $index => $bill): ?>
                    <tr class="animate-fade-in" style="animation-delay: <?= $index * 0.05 ?>s">
                        <td><strong>#<?= $bill['bill_id'] ?></strong></td>
                        <td><?= htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) ?></td>
                        <td><?= htmlspecialchars($bill['utility_name']) ?></td>
                        <td><?= $bill['consumption'] ?> units</td>
                        <td><span class="status status-<?= strtolower($bill['status']) ?>"><?= e($bill['status']) ?></span></td>
                        <td><?= $bill['due_date']->format('M d, Y') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($role === 'Field Officer'): ?>
    <section class="card hover-lift tip-card">
        <div class="tip-icon">📋</div>
        <div>
            <h3>Field Officer Tasks</h3>
            <p>Review your assigned meters and schedule on the Readings page.</p>
        </div>
    </section>
<?php elseif ($role === 'Billing Clerk'): ?>
    <section class="card hover-lift tip-card">
        <div class="tip-icon">💡</div>
        <div>
            <h3>Billing Clerk Tips</h3>
            <p>Use the Billing module to run the automated generation process once readings are confirmed.</p>
        </div>
    </section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trend Chart - Bar Chart with gradient
    const ctx = document.getElementById('revenueTrendChart');
    if (ctx) {
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 250);
        gradient.addColorStop(0, '#3b82f6');
        gradient.addColorStop(1, '#1d4ed8');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: gradient,
                    borderRadius: 8,
                    borderSkipped: false,
                    barThickness: 40,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
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
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.5)' },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                animation: { duration: 1000, easing: 'easeOutQuart' }
            }
        });
    }

    // Animate hero stat values
    document.querySelectorAll('.hero-stat-value').forEach(el => {
        const text = el.textContent;
        const match = text.match(/[\d,]+/);
        if (match) {
            const target = parseInt(match[0].replace(/,/g, ''));
            const prefix = text.substring(0, text.indexOf(match[0]));
            const suffix = text.substring(text.indexOf(match[0]) + match[0].length);
            let current = 0;
            const duration = 1500;
            const steps = 40;
            const increment = target / steps;
            
            const counter = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(counter);
                }
                el.textContent = prefix + Math.floor(current).toLocaleString() + suffix;
            }, duration / steps);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
