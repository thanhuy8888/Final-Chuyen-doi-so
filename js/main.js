// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Initialize Charts
    initializeCharts();

    // Add fade-in animations
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
        card.classList.add('fade-in');
        if (index < 4) {
            card.classList.add(`fade-in-delay-${index + 1}`);
        }
    });
});

// Initialize Charts with Chart.js
function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu (triệu VNĐ)',
                    data: [120, 135, 128, 145, 152, 168, 175, 162, 178, 185, 192, 168],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 15, 35, 0.9)',
                        padding: 12,
                        borderColor: '#667eea',
                        borderWidth: 1,
                        titleColor: '#fff',
                        bodyColor: '#b8b8d1',
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b6b8c'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b6b8c'
                        }
                    }
                }
            }
        });
    }

    // Sales by Category Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Electronics', 'Audio', 'Accessories'],
                datasets: [{
                    data: [65, 20, 15],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(0, 242, 254, 0.8)',
                        'rgba(254, 225, 64, 0.8)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#b8b8d1',
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 15, 35, 0.9)',
                        padding: 12,
                        borderColor: '#667eea',
                        borderWidth: 1,
                        titleColor: '#fff',
                        bodyColor: '#b8b8d1'
                    }
                }
            }
        });
    }

    // Orders Chart
    const ordersCtx = document.getElementById('ordersChart');
    if (ordersCtx) {
        new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: ['Hà Nội', 'TP.HCM', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng'],
                datasets: [{
                    label: 'Đơn hàng',
                    data: [145, 189, 98, 67, 82],
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 15, 35, 0.9)',
                        padding: 12,
                        borderColor: '#667eea',
                        borderWidth: 1,
                        titleColor: '#fff',
                        bodyColor: '#b8b8d1',
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b6b8c'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b6b8c'
                        }
                    }
                }
            }
        });
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

// Update real-time stats (simulation)
function updateStats() {
    // This could be connected to real API endpoints
    console.log('Stats updated');
}

// Auto-update every 30 seconds
setInterval(updateStats, 30000);
