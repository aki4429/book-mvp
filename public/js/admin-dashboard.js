class AdminDashboard {
    constructor() {
        this.chart = null;
        this.currentStatsData = {};
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initChart();
        this.loadInitialData();
    }

    bindEvents() {
        // 期間別統計の変更
        document.getElementById('stats-period').addEventListener('change', (e) => {
            this.loadPeriodStats(e.target.value);
        });

        // チャートタイプ・期間の変更
        document.getElementById('chart-type').addEventListener('change', () => {
            this.updateChart();
        });

        document.getElementById('chart-period').addEventListener('change', () => {
            this.updateChart();
        });
    }

    loadInitialData() {
        this.loadPeriodStats('today');
        this.updateChart();
    }

    async loadPeriodStats(period) {
        this.showLoading(true);
        
        try {
            const response = await fetch(`/admin/admin-dashboard/stats?period=${period}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.currentStatsData = data.data;
                this.renderPeriodStats(data.data, period);
            } else {
                this.showAlert('error', '統計データの読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading period stats:', error);
            this.showAlert('error', '統計データの読み込み中にエラーが発生しました。');
        } finally {
            this.showLoading(false);
        }
    }

    renderPeriodStats(stats, period) {
        const periodLabels = {
            today: '今日',
            week: '今週',
            month: '今月',
            year: '今年'
        };

        const periodKey = period === 'today' ? 'today' : 
                         period === 'week' ? 'week' :
                         period === 'month' ? 'month' : 'year';

        document.getElementById('period-reservations').textContent = 
            stats[`${periodKey}_reservations`] || 0;
        document.getElementById('period-timeslots').textContent = 
            stats[`${periodKey}_timeslots`] || 0;
        document.getElementById('period-available-slots').textContent = 
            stats[`${periodKey}_available_slots`] || 0;
        document.getElementById('period-customers').textContent = 
            stats[`${periodKey}_customers`] || 0;
    }

    async loadRecentActivity(limit = 10) {
        try {
            const response = await fetch(`/admin/admin-dashboard/recent-activity?limit=${limit}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.renderRecentActivity(data.data);
            } else {
                this.showAlert('error', '最近のアクティビティの読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
            this.showAlert('error', '最近のアクティビティの読み込み中にエラーが発生しました。');
        }
    }

    renderRecentActivity(activities) {
        const container = document.getElementById('recent-activity');
        
        if (activities.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    最近のアクティビティはありません
                </div>
            `;
            return;
        }

        container.innerHTML = activities.map(activity => `
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">${this.escapeHtml(activity.customer_name)}</p>
                    <p class="text-xs text-gray-500">${activity.time_slot_date} ${activity.time_slot_start.substring(0, 5)}-${activity.time_slot_end.substring(0, 5)}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        activity.status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    }">
                        ${activity.status === 'confirmed' ? '確定' : '保留'}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">${activity.formatted_date}</p>
                </div>
            </div>
        `).join('');
    }

    initChart() {
        const ctx = document.getElementById('dashboard-chart').getContext('2d');
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: '予約数',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    async updateChart() {
        const chartType = document.getElementById('chart-type').value;
        const chartPeriod = document.getElementById('chart-period').value;
        
        try {
            const response = await fetch(`/admin/admin-dashboard/chart-data?type=${chartType}&period=${chartPeriod}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                const data = result.data;
                
                // チャートの色を変更
                const colors = {
                    reservations: { border: 'rgb(59, 130, 246)', background: 'rgba(59, 130, 246, 0.1)' },
                    customers: { border: 'rgb(34, 197, 94)', background: 'rgba(34, 197, 94, 0.1)' },
                    timeslots: { border: 'rgb(168, 85, 247)', background: 'rgba(168, 85, 247, 0.1)' }
                };

                const typeLabels = {
                    reservations: '予約数',
                    customers: '顧客数',
                    timeslots: '時間枠数'
                };

                this.chart.data.labels = data.map(item => item.label);
                this.chart.data.datasets[0].data = data.map(item => item.value);
                this.chart.data.datasets[0].label = typeLabels[chartType];
                this.chart.data.datasets[0].borderColor = colors[chartType].border;
                this.chart.data.datasets[0].backgroundColor = colors[chartType].background;
                
                this.chart.update();
            } else {
                this.showAlert('error', 'チャートデータの読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error updating chart:', error);
            this.showAlert('error', 'チャートデータの読み込み中にエラーが発生しました。');
        }
    }

    showAlert(type, message) {
        const container = document.getElementById('alert-container');
        const alert = document.getElementById('alert');
        const icon = document.getElementById('alert-icon');
        const messageEl = document.getElementById('alert-message');
        
        // リセット
        alert.className = 'rounded-lg p-4';
        icon.innerHTML = '';
        
        if (type === 'success') {
            alert.classList.add('bg-green-50', 'border', 'border-green-200');
            messageEl.className = 'text-sm font-medium text-green-700';
            icon.innerHTML = `
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            `;
        } else {
            alert.classList.add('bg-red-50', 'border', 'border-red-200');
            messageEl.className = 'text-sm font-medium text-red-700';
            icon.innerHTML = `
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            `;
        }
        
        messageEl.textContent = message;
        container.style.display = 'block';
        
        // 5秒後に自動で隠す
        setTimeout(() => {
            container.style.display = 'none';
        }, 5000);
    }

    showLoading(show) {
        document.getElementById('loading').style.display = show ? 'block' : 'none';
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// グローバルインスタンス
const adminDashboard = new AdminDashboard();
