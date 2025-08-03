class SettingsManager {
    constructor() {
        this.settings = [];
        this.pagination = {};
        this.filters = {
            search: '',
            type: '',
            sort: 'key',
            direction: 'asc',
            per_page: 10
        };
        this.selectedSettings = new Set();
        this.currentSetting = null;
        this.isEditing = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadSettings();
    }

    bindEvents() {
        // 検索とフィルター
        document.getElementById('search-input').addEventListener('input', this.debounce((e) => {
            this.filters.search = e.target.value;
            this.loadSettings();
        }, 500));

        document.getElementById('type-filter').addEventListener('change', (e) => {
            this.filters.type = e.target.value;
            this.loadSettings();
        });

        document.getElementById('sort-field').addEventListener('change', (e) => {
            this.filters.sort = e.target.value;
            this.loadSettings();
        });

        document.getElementById('sort-direction').addEventListener('change', (e) => {
            this.filters.direction = e.target.value;
            this.loadSettings();
        });

        document.getElementById('per-page').addEventListener('change', (e) => {
            this.filters.per_page = e.target.value;
            this.loadSettings();
        });

        // ボタンイベント
        document.getElementById('create-setting-btn').addEventListener('click', () => this.openCreateModal());
        document.getElementById('clear-filters-btn').addEventListener('click', () => this.clearFilters());
        document.getElementById('bulk-delete-btn').addEventListener('click', () => this.bulkDelete());
        document.getElementById('system-info-btn').addEventListener('click', () => this.openSystemInfoModal());

        // モーダル関連
        document.getElementById('close-modal').addEventListener('click', () => this.closeModal());
        document.getElementById('cancel-btn').addEventListener('click', () => this.closeModal());
        document.getElementById('setting-form').addEventListener('submit', (e) => this.handleFormSubmit(e));

        // 削除確認モーダル
        document.getElementById('cancel-delete').addEventListener('click', () => this.closeDeleteModal());
        document.getElementById('confirm-delete').addEventListener('click', () => this.confirmDelete());

        // システム情報モーダル
        document.getElementById('close-system-info-modal').addEventListener('click', () => this.closeSystemInfoModal());

        // 全選択チェックボックス
        document.getElementById('select-all').addEventListener('change', (e) => this.toggleSelectAll(e.target.checked));

        // データ型変更時のヘルプテキスト更新
        document.getElementById('setting-type').addEventListener('change', (e) => this.updateValueHelp(e.target.value));

        // モーダル外クリックで閉じる
        document.getElementById('setting-modal').addEventListener('click', (e) => {
            if (e.target.id === 'setting-modal') this.closeModal();
        });

        document.getElementById('delete-modal').addEventListener('click', (e) => {
            if (e.target.id === 'delete-modal') this.closeDeleteModal();
        });

        document.getElementById('system-info-modal').addEventListener('click', (e) => {
            if (e.target.id === 'system-info-modal') this.closeSystemInfoModal();
        });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    async loadSettings(page = 1) {
        this.showLoading(true);
        
        try {
            const params = new URLSearchParams({
                ...this.filters,
                page: page
            });

            const response = await fetch(`/admin/settings-manager/settings?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.settings = data.data;
                this.pagination = data.pagination;
                this.renderSettings();
                this.renderPagination();
                this.selectedSettings.clear();
                this.updateBulkDeleteButton();
                this.updateSelectAllCheckbox();
            } else {
                this.showAlert('error', '設定の読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            this.showAlert('error', '設定の読み込み中にエラーが発生しました。');
        } finally {
            this.showLoading(false);
        }
    }

    renderSettings() {
        const tbody = document.getElementById('settings-table-body');
        
        if (this.settings.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        設定が見つかりませんでした。
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.settings.map(setting => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="setting-checkbox rounded border-gray-300" 
                           value="${setting.id}" ${this.selectedSettings.has(setting.id) ? 'checked' : ''}>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 font-mono">${this.escapeHtml(setting.key)}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 max-w-xs truncate" title="${this.escapeHtml(setting.value)}">
                        ${this.formatValue(setting.value, setting.type)}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${this.getTypeColor(setting.type)}">
                        ${this.getTypeLabel(setting.type)}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 max-w-xs truncate" title="${this.escapeHtml(setting.description || '')}">
                        ${this.escapeHtml(setting.description || '-')}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${this.formatDate(setting.updated_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        <button onclick="settingsManager.openEditModal(${setting.id})" class="text-blue-600 hover:text-blue-900">
                            編集
                        </button>
                        <button onclick="settingsManager.resetToDefault(${setting.id})" class="text-yellow-600 hover:text-yellow-900">
                            リセット
                        </button>
                        <button onclick="settingsManager.openDeleteModal(${setting.id})" class="text-red-600 hover:text-red-900">
                            削除
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // チェックボックスイベントを再バインド
        document.querySelectorAll('.setting-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const settingId = parseInt(e.target.value);
                if (e.target.checked) {
                    this.selectedSettings.add(settingId);
                } else {
                    this.selectedSettings.delete(settingId);
                }
                this.updateBulkDeleteButton();
                this.updateSelectAllCheckbox();
            });
        });
    }

    renderPagination() {
        const container = document.getElementById('pagination-container');
        
        if (this.pagination.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let paginationHtml = `
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    ${this.pagination.from || 0} - ${this.pagination.to || 0} / ${this.pagination.total} 件
                </div>
                <div class="flex space-x-1">
        `;

        // 前へボタン
        if (this.pagination.current_page > 1) {
            paginationHtml += `
                <button onclick="settingsManager.loadSettings(${this.pagination.current_page - 1})" 
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                    前へ
                </button>
            `;
        }

        // ページ番号ボタン
        const startPage = Math.max(1, this.pagination.current_page - 2);
        const endPage = Math.min(this.pagination.last_page, this.pagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === this.pagination.current_page;
            paginationHtml += `
                <button onclick="settingsManager.loadSettings(${i})" 
                        class="px-3 py-2 text-sm ${isActive ? 
                            'bg-blue-500 text-white' : 
                            'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                        } rounded">
                    ${i}
                </button>
            `;
        }

        // 次へボタン
        if (this.pagination.current_page < this.pagination.last_page) {
            paginationHtml += `
                <button onclick="settingsManager.loadSettings(${this.pagination.current_page + 1})" 
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                    次へ
                </button>
            `;
        }

        paginationHtml += `
                </div>
            </div>
        `;

        container.innerHTML = paginationHtml;
    }

    clearFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('type-filter').value = '';
        document.getElementById('sort-field').value = 'key';
        document.getElementById('sort-direction').value = 'asc';
        document.getElementById('per-page').value = '10';
        
        this.filters = {
            search: '',
            type: '',
            sort: 'key',
            direction: 'asc',
            per_page: 10
        };
        
        this.loadSettings();
    }

    openCreateModal() {
        this.isEditing = false;
        this.currentSetting = null;
        
        document.getElementById('modal-title').textContent = '設定作成';
        document.getElementById('submit-btn').textContent = '作成';
        
        this.resetForm();
        this.showModal();
    }

    async openEditModal(settingId) {
        this.isEditing = true;
        this.currentSetting = settingId;
        
        try {
            const response = await fetch(`/admin/settings-manager/settings/${settingId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const setting = data.data;
                
                document.getElementById('modal-title').textContent = '設定編集';
                document.getElementById('submit-btn').textContent = '更新';
                
                document.getElementById('setting-key').value = setting.key;
                document.getElementById('setting-type').value = setting.type;
                document.getElementById('setting-value').value = setting.value;
                document.getElementById('setting-description').value = setting.description || '';
                
                this.updateValueHelp(setting.type);
                this.clearFormErrors();
                this.showModal();
            } else {
                this.showAlert('error', '設定情報の取得に失敗しました。');
            }
        } catch (error) {
            console.error('Error loading setting:', error);
            this.showAlert('error', '設定情報の取得中にエラーが発生しました。');
        }
    }

    showModal() {
        document.getElementById('setting-modal').style.display = 'block';
    }

    closeModal() {
        document.getElementById('setting-modal').style.display = 'none';
        this.resetForm();
    }

    resetForm() {
        document.getElementById('setting-form').reset();
        document.getElementById('setting-type').value = 'string';
        this.updateValueHelp('string');
        this.clearFormErrors();
    }

    updateValueHelp(type) {
        const helpElement = document.getElementById('value-help');
        const helpTexts = {
            'string': '任意の文字列を入力してください',
            'integer': '整数値を入力してください（例: 123）',
            'boolean': 'true/false または 1/0 を入力してください',
            'json': '有効なJSON形式で入力してください（例: {"key": "value"}）'
        };
        
        helpElement.textContent = helpTexts[type] || '';
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        this.clearFormErrors();
        
        const formData = {
            key: document.getElementById('setting-key').value,
            type: document.getElementById('setting-type').value,
            value: document.getElementById('setting-value').value,
            description: document.getElementById('setting-description').value
        };

        try {
            const url = this.isEditing ? 
                `/admin/settings-manager/settings/${this.currentSetting}` : 
                '/admin/settings-manager/settings';
            
            const method = this.isEditing ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', result.message);
                this.closeModal();
                this.loadSettings();
            } else {
                if (result.errors) {
                    this.showFormErrors(result.errors);
                } else {
                    this.showAlert('error', result.message || 'エラーが発生しました。');
                }
            }
        } catch (error) {
            console.error('Error saving setting:', error);
            this.showAlert('error', '保存中にエラーが発生しました。');
        }
    }

    openDeleteModal(settingId) {
        const setting = this.settings.find(s => s.id === settingId);
        if (!setting) return;
        
        this.currentSetting = settingId;
        document.getElementById('delete-message').textContent = 
            `「${setting.key}」を削除してもよろしいですか？この操作は元に戻すことができません。`;
        
        document.getElementById('delete-modal').style.display = 'block';
    }

    closeDeleteModal() {
        document.getElementById('delete-modal').style.display = 'none';
        this.currentSetting = null;
    }

    async confirmDelete() {
        if (!this.currentSetting) return;
        
        try {
            const response = await fetch(`/admin/settings-manager/settings/${this.currentSetting}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.closeDeleteModal();
                this.loadSettings();
            } else {
                this.showAlert('error', data.message || '削除に失敗しました。');
            }
        } catch (error) {
            console.error('Error deleting setting:', error);
            this.showAlert('error', '削除中にエラーが発生しました。');
        }
    }

    async bulkDelete() {
        if (this.selectedSettings.size === 0) return;
        
        if (!confirm(`選択した${this.selectedSettings.size}個の設定を削除してもよろしいですか？`)) {
            return;
        }
        
        try {
            const response = await fetch('/admin/settings-manager/settings/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    setting_ids: Array.from(this.selectedSettings)
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.selectedSettings.clear();
                this.updateBulkDeleteButton();
                this.loadSettings();
            } else {
                this.showAlert('error', data.message || '一括削除に失敗しました。');
            }
        } catch (error) {
            console.error('Error bulk deleting settings:', error);
            this.showAlert('error', '一括削除中にエラーが発生しました。');
        }
    }

    async resetToDefault(settingId) {
        if (!confirm('この設定をデフォルト値にリセットしてもよろしいですか？')) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/settings-manager/settings/${settingId}/reset-default`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.loadSettings();
            } else {
                this.showAlert('error', data.message || 'リセットに失敗しました。');
            }
        } catch (error) {
            console.error('Error resetting setting:', error);
            this.showAlert('error', 'リセット中にエラーが発生しました。');
        }
    }

    async openSystemInfoModal() {
        try {
            const response = await fetch('/admin/settings-manager/system-info', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.renderSystemInfo(data.data);
                document.getElementById('system-info-modal').style.display = 'block';
            } else {
                this.showAlert('error', 'システム情報の取得に失敗しました。');
            }
        } catch (error) {
            console.error('Error loading system info:', error);
            this.showAlert('error', 'システム情報の取得中にエラーが発生しました。');
        }
    }

    closeSystemInfoModal() {
        document.getElementById('system-info-modal').style.display = 'none';
    }

    renderSystemInfo(info) {
        const container = document.getElementById('system-info-content');
        
        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">設定情報</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">総設定数:</dt>
                            <dd class="text-sm font-medium text-gray-900">${info.total_settings}個</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">データ型:</dt>
                            <dd class="text-sm font-medium text-gray-900">${info.setting_types.join(', ')}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">最終更新:</dt>
                            <dd class="text-sm font-medium text-gray-900">${info.last_updated ? this.formatDate(info.last_updated) : 'なし'}</dd>
                        </div>
                    </dl>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">システム情報</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">PHP:</dt>
                            <dd class="text-sm font-medium text-gray-900">${info.php_version}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Laravel:</dt>
                            <dd class="text-sm font-medium text-gray-900">${info.laravel_version}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">データベース:</dt>
                            <dd class="text-sm font-medium text-gray-900">${info.database_connection}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        `;
    }

    toggleSelectAll(checked) {
        this.selectedSettings.clear();
        
        if (checked) {
            this.settings.forEach(setting => {
                this.selectedSettings.add(setting.id);
            });
        }
        
        document.querySelectorAll('.setting-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
        });
        
        this.updateBulkDeleteButton();
    }

    updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all');
        const totalSettings = this.settings.length;
        const selectedCount = this.selectedSettings.size;
        
        if (selectedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (selectedCount === totalSettings) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    updateBulkDeleteButton() {
        const button = document.getElementById('bulk-delete-btn');
        button.disabled = this.selectedSettings.size === 0;
        
        if (this.selectedSettings.size > 0) {
            button.textContent = `選択した${this.selectedSettings.size}個の設定を削除`;
        } else {
            button.textContent = '選択した設定を削除';
        }
    }

    formatValue(value, type) {
        if (!value) return '-';
        
        switch (type) {
            case 'boolean':
                return value === 'true' || value === '1' ? 'true' : 'false';
            case 'json':
                try {
                    return JSON.stringify(JSON.parse(value), null, 2);
                } catch (e) {
                    return value;
                }
            default:
                return this.escapeHtml(value.length > 50 ? value.substring(0, 50) + '...' : value);
        }
    }

    getTypeColor(type) {
        const colors = {
            'string': 'bg-blue-100 text-blue-800',
            'integer': 'bg-green-100 text-green-800',
            'boolean': 'bg-yellow-100 text-yellow-800',
            'json': 'bg-purple-100 text-purple-800'
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    }

    getTypeLabel(type) {
        const labels = {
            'string': '文字列',
            'integer': '整数',
            'boolean': '真偽値',
            'json': 'JSON'
        };
        return labels[type] || type;
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

    showFormErrors(errors) {
        this.clearFormErrors();
        
        Object.keys(errors).forEach(field => {
            this.showFieldError(field, errors[field][0]);
        });
    }

    showFieldError(field, message) {
        const errorEl = document.getElementById(`error-${field}`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
    }

    clearFormErrors() {
        document.querySelectorAll('[id^="error-"]').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
    }

    showLoading(show) {
        document.getElementById('loading').style.display = show ? 'block' : 'none';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
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
const settingsManager = new SettingsManager();
