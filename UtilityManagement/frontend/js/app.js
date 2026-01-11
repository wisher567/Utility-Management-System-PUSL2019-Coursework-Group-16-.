document.addEventListener('DOMContentLoaded', () => {
    // ===== DARK MODE TOGGLE =====
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Apply saved theme
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark');
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark');
            localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
            showToast('Theme Changed', `Switched to ${body.classList.contains('dark') ? 'dark' : 'light'} mode`, 'success');
        });
    }

    // ===== SPOTLIGHT SEARCH =====
    const spotlightOverlay = document.getElementById('spotlightOverlay');
    const spotlightInput = document.getElementById('spotlightInput');
    const spotlightResults = document.getElementById('spotlightResults');

    function openSpotlight() {
        if (spotlightOverlay) {
            spotlightOverlay.classList.add('active');
            setTimeout(() => spotlightInput?.focus(), 100);
        }
    }

    function closeSpotlight() {
        if (spotlightOverlay) {
            spotlightOverlay.classList.remove('active');
            if (spotlightInput) spotlightInput.value = '';
        }
    }

    if (spotlightOverlay) {
        spotlightOverlay.addEventListener('click', (e) => {
            if (e.target === spotlightOverlay) closeSpotlight();
        });
    }

    if (spotlightInput) {
        spotlightInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const items = spotlightResults?.querySelectorAll('.spotlight-item');
            items?.forEach(item => {
                const title = item.querySelector('.spotlight-item-title')?.textContent.toLowerCase() || '';
                const desc = item.querySelector('.spotlight-item-desc')?.textContent.toLowerCase() || '';
                item.style.display = (title.includes(query) || desc.includes(query)) ? 'flex' : 'none';
            });
        });
    }

    // ===== KEYBOARD SHORTCUTS =====
    document.addEventListener('keydown', (e) => {
        // Skip if user is typing in an input
        if (e.target.matches('input, textarea, select')) return;

        // Cmd/Ctrl + K for spotlight
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            openSpotlight();
        }

        // ESC to close spotlight
        if (e.key === 'Escape') {
            closeSpotlight();
        }

        // D for dark mode toggle
        if (e.key === 'd' || e.key === 'D') {
            themeToggle?.click();
        }

        // Number keys 1-8 for navigation
        if (!spotlightOverlay?.classList.contains('active')) {
            const shortcutLinks = document.querySelectorAll('[data-shortcut]');
            shortcutLinks.forEach(link => {
                if (e.key === link.dataset.shortcut) {
                    e.preventDefault();
                    window.location.href = link.href;
                }
            });
        }
    });

    // ===== TOAST NOTIFICATIONS =====
    window.showToast = function (title, message, type = 'info', duration = 4000) {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.classList.add('hiding'); setTimeout(() => this.parentElement.remove(), 300);">×</button>
        `;

        container.appendChild(toast);

        // Auto dismiss
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    };

    // ===== CONFETTI EFFECT =====
    window.triggerConfetti = function (count = 100) {
        const container = document.getElementById('confettiContainer');
        if (!container) return;

        const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#22c55e', '#f59e0b', '#ef4444'];

        for (let i = 0; i < count; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 2 + 's';
            confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;

            // Random shapes
            if (Math.random() > 0.5) {
                confetti.style.borderRadius = '50%';
            } else {
                confetti.style.width = '8px';
                confetti.style.height = '16px';
            }

            container.appendChild(confetti);

            setTimeout(() => confetti.remove(), 5000);
        }
    };

    // ===== MAGNETIC CURSOR EFFECT =====
    const magneticBtns = document.querySelectorAll('.magnetic-btn');
    magneticBtns.forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            btn.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
        });

        btn.addEventListener('mouseleave', () => {
            btn.style.transform = 'translate(0, 0)';
        });
    });

    // ===== PARALLAX HERO =====
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.classList.add('parallax');
        const orbs = hero.querySelectorAll('.hero-orb');

        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            orbs.forEach((orb, index) => {
                const speed = (index + 1) * 0.1;
                orb.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Mouse parallax
        hero.addEventListener('mousemove', (e) => {
            const rect = hero.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            orbs.forEach((orb, index) => {
                const speed = (index + 1) * 20;
                orb.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    }

    // ===== NUMBER FLIP ODOMETER ANIMATION =====
    function animateValue(element, start, end, duration, prefix = '', suffix = '') {
        const range = end - start;
        const startTime = performance.now();
        const isDecimal = String(end).includes('.');
        const decimals = isDecimal ? (String(end).split('.')[1] || '').length : 0;

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = 1 - Math.pow(1 - progress, 3); // Ease out cubic
            const current = start + range * easeProgress;

            if (isDecimal) {
                element.textContent = prefix + current.toFixed(decimals) + suffix;
            } else {
                element.textContent = prefix + Math.floor(current).toLocaleString() + suffix;
            }

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    }

    // Apply to hero stat values
    document.querySelectorAll('.hero-stat-value').forEach(el => {
        const text = el.textContent;
        const match = text.match(/[\d,]+\.?\d*/);
        if (match) {
            const target = parseFloat(match[0].replace(/,/g, ''));
            const prefix = text.substring(0, text.indexOf(match[0]));
            const suffix = text.substring(text.indexOf(match[0]) + match[0].length);
            el.textContent = prefix + '0' + suffix;

            // Intersection observer to trigger on view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateValue(el, 0, target, 1500, prefix, suffix);
                        observer.disconnect();
                    }
                });
            });
            observer.observe(el);
        }
    });

    // ===== SMOOTH TABLE ROW HOVER =====
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        table.addEventListener('mouseover', e => {
            const row = e.target.closest('tr');
            if (row && row.parentElement.tagName === 'TBODY') {
                row.classList.add('hover');
            }
        });
        table.addEventListener('mouseout', e => {
            const row = e.target.closest('tr');
            if (row) row.classList.remove('hover');
        });
    });

    // ===== BUTTON RIPPLE EFFECT =====
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const rect = btn.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;

            btn.style.position = 'relative';
            btn.style.overflow = 'hidden';
            btn.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });

    // ===== FORM SUBMISSION WITH LOADING STATE =====
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', e => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('loading')) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.textContent;
                submitBtn.innerHTML = `
                    <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="30 70" stroke-linecap="round"/>
                        </svg>
                        Processing...
                    </span>
                `;
            }
        });
    });

    // ===== AUTO-DISMISS ALERTS =====
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
            margin-left: auto;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
            padding: 0;
            line-height: 1;
            color: inherit;
        `;
        closeBtn.addEventListener('click', () => dismissAlert(alert));
        alert.appendChild(closeBtn);

        if (alert.classList.contains('alert-success')) {
            setTimeout(() => dismissAlert(alert), 5000);
        }
    });

    function dismissAlert(alert) {
        alert.style.transition = 'all 0.4s ease-out';
        alert.style.opacity = '0';
        alert.style.transform = 'translateX(-20px)';
        setTimeout(() => alert.remove(), 400);
    }

    // ===== NAV LINK ACTIVE STATE =====
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.nav a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.style.color = '#fff';
            link.style.background = 'rgba(255, 255, 255, 0.1)';
        }
    });

    // ===== STAGGERED FADE-IN =====
    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.08}s`;
    });

    // ===== STICKY TABLE HEADERS =====
    document.querySelectorAll('.table-wrapper table').forEach(table => {
        table.classList.add('table-sticky');
    });

    // ===== REAL-TIME INDICATOR =====
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        const indicator = document.createElement('div');
        indicator.className = 'realtime-indicator';
        indicator.innerHTML = '<span class="realtime-dot"></span> Live data';
        indicator.style.position = 'absolute';
        indicator.style.top = '1rem';
        indicator.style.right = '1rem';
        const hero = document.querySelector('.hero');
        if (hero) {
            hero.style.position = 'relative';
            hero.appendChild(indicator);
        }
    }

    // ===== EXPORT BUTTON CLICK HANDLER =====
    document.querySelectorAll('.export-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (btn.classList.contains('export-btn-pdf')) {
                showToast('Export Started', 'Generating PDF report...', 'info');
            } else if (btn.classList.contains('export-btn-excel')) {
                showToast('Export Started', 'Generating Excel file...', 'info');
            }
        });
    });

    // ===== PAYMENT SUCCESS CONFETTI =====
    // Auto-trigger confetti if URL has success parameter
    if (window.location.search.includes('success=1')) {
        setTimeout(() => {
            triggerConfetti(150);
            showToast('Payment Recorded!', 'The payment was processed successfully.', 'success');
        }, 500);
    }

    // Add dynamic styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to { transform: scale(4); opacity: 0; }
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Welcome toast on first visit
    if (!sessionStorage.getItem('welcomed')) {
        setTimeout(() => {
            showToast('Welcome!', 'Press Ctrl+K for quick search, D for dark mode', 'info', 6000);
            sessionStorage.setItem('welcomed', '1');
        }, 1000);
    }
});
