class PresetManager {
    constructor() {
        this.presets = [];
        this.pagination = {};
        this.filters = {
            search: '',
            status: '',
            sort: 'sort_order',
            direction: 'asc',
            per_page: 10
        };
        this.selectedPresets = new Set();
        this.currentPreset = null;
        this.isEditing = false;
        this.timeslotCounter = 0;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadPresets();
    }

    bindEvents() {
        // 検索とフィルター
        document.getElementById('search-input').addEventListener('input', this.debounce((e) => {
            this.filters.search = e.target.value;
            this.loadPresets();
        }, 500));

        document.getElementById('status-filter').addEventListener('change', (e) => {
            this.filters.status = e.target.value;
            this.loadPresets();
        });

        document.getElementById('sort-field').addEventListener('change', (e) => {
            this.filters.sort = e.target.value;
            this.loadPresets();
        });

        document.getElementById('sort-direction').addEventListener('change', (e) => {
            this.filters.direction = e.target.value;
            this.loadPresets();
        });

        document.getElementById('per-page').addEventListener('change', (e) => {
            this.filters.per_page = e.target.value;
            this.loadPresets();
        });

        // ボタンイベント
        document.getElementById('create-preset-btn').addEventListener('click', () => this.openCreateModal());
        document.getElementById('clear-filters-btn').addEventListener('click', () => this.clearFilters());
        document.getElementById('bulk-delete-btn').addEventListener('click', () => this.bulkDelete());

        // モーダル関連
        document.getElementById('close-modal').addEventListener('click', () => this.closeModal());
        document.getElementById('cancel-btn').addEventListener('click', () => this.closeModal());
        document.getElementById('preset-form').addEventListener('submit', (e) => this.handleFormSubmit(e));

        // 削除確認モーダル
        document.getElementById('cancel-delete').addEventListener('click', () => this.closeDeleteModal());
        document.getElementById('confirm-delete').addEventListener('click', () => this.confirmDelete());

        // 全選択チェックボックス
        document.getElementById('select-all').addEventListener('change', (e) => this.toggleSelectAll(e.target.checked));

        // モーダル時間枠管理
        document.getElementById('add-timeslot-modal-btn').addEventListener('click', () => this.addModalTimeslot());

        // モーダル外クリックで閉じる
        document.getElementById('preset-modal').addEventListener('click', (e) => {
            if (e.target.id === 'preset-modal') this.closeModal();
        });

        document.getElementById('delete-modal').addEventListener('click', (e) => {
            if (e.target.id === 'delete-modal') this.closeDeleteModal();
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

    async loadPresets(page = 1) {
        this.showLoading(true);
        
        try {
            const params = new URLSearchParams({
                ...this.filters,
                page: page
            });

            const response = await fetch(`/admin/preset-manager/presets?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.presets = data.data;
                this.pagination = data.pagination;
                this.renderPresets();
                this.renderPagination();
                this.selectedPresets.clear();
                this.updateBulkDeleteButton();
                this.updateSelectAllCheckbox();
            } else {
                this.showAlert('error', 'プリセットの読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading presets:', error);
            this.showAlert('error', 'プリセットの読み込み中にエラーが発生しました。');
        } finally {
            this.showLoading(false);
        }
    }

    renderPresets() {
        const tbody = document.getElementById('presets-table-body');
        
        if (this.presets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        プリセットが見つかりませんでした。
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.presets.map(preset => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="preset-checkbox rounded border-gray-300" 
                           value="${preset.id}" ${this.selectedPresets.has(preset.id) ? 'checked' : ''}>
                </td>
                <td class="px-6 py-4">
                    <div>
                        <div class="text-sm font-medium text-gray-900">${this.escapeHtml(preset.name)}</div>
                        ${preset.description ? `<div class="text-sm text-gray-500 mt-1">${this.escapeHtml(preset.description)}</div>` : ''}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${preset.time_slots.length}個
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${preset.is_active ? 
                        '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">アクティブ</span>' : 
                        '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">非アクティブ</span>'
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${preset.sort_order}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${this.formatDate(preset.created_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        <button onclick="presetManager.openEditModal(${preset.id})" class="text-blue-600 hover:text-blue-900">
                            編集
                        </button>
                        <button onclick="presetManager.toggleStatus(${preset.id})" class="text-yellow-600 hover:text-yellow-900">
                            ${preset.is_active ? '無効化' : '有効化'}
                        </button>
                        <button onclick="presetManager.duplicate(${preset.id})" class="text-green-600 hover:text-green-900">
                            複製
                        </button>
                        <button onclick="presetManager.openDeleteModal(${preset.id})" class="text-red-600 hover:text-red-900">
                            削除
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // チェックボックスイベントを再バインド
        document.querySelectorAll('.preset-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const presetId = parseInt(e.target.value);
                if (e.target.checked) {
                    this.selectedPresets.add(presetId);
                } else {
                    this.selectedPresets.delete(presetId);
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
                <button onclick="presetManager.loadPresets(${this.pagination.current_page - 1})" 
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
                <button onclick="presetManager.loadPresets(${i})" 
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
                <button onclick="presetManager.loadPresets(${this.pagination.current_page + 1})" 
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
        document.getElementById('status-filter').value = '';
        document.getElementById('sort-field').value = 'sort_order';
        document.getElementById('sort-direction').value = 'asc';
        document.getElementById('per-page').value = '10';
        
        this.filters = {
            search: '',
            status: '',
            sort: 'sort_order',
            direction: 'asc',
            per_page: 10
        };
        
        this.loadPresets();
    }

    openCreateModal() {
        this.isEditing = false;
        this.currentPreset = null;
        
        document.getElementById('modal-title').textContent = 'プリセット作成';
        document.getElementById('submit-btn').textContent = '作成';
        
        this.resetForm();
        this.showModal();
    }

    async openEditModal(presetId) {
        this.isEditing = true;
        this.currentPreset = presetId;
        
        try {
            const response = await fetch(`/admin/preset-manager/presets/${presetId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const preset = data.data;
                
                document.getElementById('modal-title').textContent = 'プリセット編集';
                document.getElementById('submit-btn').textContent = '更新';
                
                document.getElementById('preset-name').value = preset.name;
                document.getElementById('preset-description').value = preset.description || '';
                document.getElementById('preset-is-active').checked = preset.is_active;
                
                // 時間枠を設定
                this.clearModalTimeslots();
                preset.time_slots.forEach((slot, index) => {
                    if (index === 0) {
                        this.updateModalTimeslotRow(0, slot);
                    } else {
                        this.addModalTimeslot(slot);
                    }
                });
                
                this.clearFormErrors();
                this.showModal();
            } else {
                this.showAlert('error', 'プリセット情報の取得に失敗しました。');
            }
        } catch (error) {
            console.error('Error loading preset:', error);
            this.showAlert('error', 'プリセット情報の取得中にエラーが発生しました。');
        }
    }

    showModal() {
        document.getElementById('preset-modal').style.display = 'block';
    }

    closeModal() {
        document.getElementById('preset-modal').style.display = 'none';
        this.resetForm();
    }

    resetForm() {
        document.getElementById('preset-form').reset();
        document.getElementById('preset-is-active').checked = true;
        this.clearModalTimeslots();
        this.addModalTimeslot(); // 初期の時間枠を再追加
        this.clearFormErrors();
    }

    addModalTimeslot(slotData = null) {
        this.timeslotCounter++;
        const container = document.getElementById('modal-timeslots-container');
        
        const timeslotRow = document.createElement('div');
        timeslotRow.className = 'modal-timeslot-row border rounded-lg p-4 mb-3';
        timeslotRow.innerHTML = `
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">開始時間</label>
                    <input type="time" name="time_slots[${this.timeslotCounter}][start_time]" 
                           value="${slotData?.start_time || ''}"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">終了時間</label>
                    <input type="time" name="time_slots[${this.timeslotCounter}][end_time]" 
                           value="${slotData?.end_time || ''}"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="w-24">
                    <label class="block text-sm font-medium mb-1">定員</label>
                    <input type="number" name="time_slots[${this.timeslotCounter}][capacity]" 
                           value="${slotData?.capacity || 1}" min="1" max="100" 
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="w-32">
                    <label class="block text-sm font-medium mb-1">サービスID</label>
                    <input type="text" name="time_slots[${this.timeslotCounter}][service_id]" 
                           value="${slotData?.service_id || ''}"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="任意">
                </div>
                <div class="flex-shrink-0">
                    <button type="button" class="remove-modal-timeslot-btn mt-6 text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(timeslotRow);

        // 削除ボタンのイベントリスナーを追加
        const removeBtn = timeslotRow.querySelector('.remove-modal-timeslot-btn');
        removeBtn.addEventListener('click', () => {
            this.removeModalTimeslot(timeslotRow);
        });

        this.updateModalRemoveButtons();
    }

    updateModalTimeslotRow(index, slotData) {
        const container = document.getElementById('modal-timeslots-container');
        const row = container.children[index];
        
        if (row) {
            row.querySelector('input[name$="[start_time]"]').value = slotData.start_time || '';
            row.querySelector('input[name$="[end_time]"]').value = slotData.end_time || '';
            row.querySelector('input[name$="[capacity]"]').value = slotData.capacity || 1;
            row.querySelector('input[name$="[service_id]"]').value = slotData.service_id || '';
        }
    }

    removeModalTimeslot(row) {
        row.remove();
        this.updateModalRemoveButtons();
        this.reindexModalTimeslots();
    }

    clearModalTimeslots() {
        const container = document.getElementById('modal-timeslots-container');
        while (container.children.length > 1) {
            container.removeChild(container.lastChild);
        }
        this.updateModalRemoveButtons();
        this.timeslotCounter = 0;
    }

    updateModalRemoveButtons() {
        const container = document.getElementById('modal-timeslots-container');
        const removeButtons = container.querySelectorAll('.remove-modal-timeslot-btn');
        
        removeButtons.forEach(btn => {
            btn.disabled = container.children.length <= 1;
        });
    }

    reindexModalTimeslots() {
        const container = document.getElementById('modal-timeslots-container');
        Array.from(container.children).forEach((row, index) => {
            row.querySelectorAll('input').forEach(input => {
                const name = input.name;
                if (name && name.includes('time_slots[')) {
                    const fieldName = name.match(/\[([^\[\]]*)\]$/)[1];
                    input.name = `time_slots[${index}][${fieldName}]`;
                }
            });
        });
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        this.clearFormErrors();
        
        const formData = this.getModalFormData();
        if (!this.validateModalForm(formData)) {
            return;
        }

        try {
            const url = this.isEditing ? 
                `/admin/preset-manager/presets/${this.currentPreset}` : 
                '/admin/preset-manager/presets';
            
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
                this.loadPresets();
            } else {
                if (result.errors) {
                    this.showFormErrors(result.errors);
                } else {
                    this.showAlert('error', result.message || 'エラーが発生しました。');
                }
            }
        } catch (error) {
            console.error('Error saving preset:', error);
            this.showAlert('error', '保存中にエラーが発生しました。');
        }
    }

    getModalFormData() {
        const data = {
            name: document.getElementById('preset-name').value,
            description: document.getElementById('preset-description').value,
            is_active: document.getElementById('preset-is-active').checked,
            time_slots: []
        };

        // 時間枠データを収集
        const container = document.getElementById('modal-timeslots-container');
        Array.from(container.children).forEach((row, index) => {
            const startTime = row.querySelector(`input[name$="[start_time]"]`).value;
            const endTime = row.querySelector(`input[name$="[end_time]"]`).value;
            const capacity = row.querySelector(`input[name$="[capacity]"]`).value;
            const serviceId = row.querySelector(`input[name$="[service_id]"]`).value;

            if (startTime && endTime) {
                data.time_slots.push({
                    start_time: startTime,
                    end_time: endTime,
                    capacity: parseInt(capacity) || 1,
                    service_id: serviceId || null
                });
            }
        });

        return data;
    }

    validateModalForm(data) {
        let isValid = true;

        if (!data.name.trim()) {
            this.showFieldError('name', 'プリセット名を入力してください。');
            isValid = false;
        }

        if (data.time_slots.length === 0) {
            this.showFieldError('time_slots', '少なくとも1つの時間枠を設定してください。');
            isValid = false;
        }

        return isValid;
    }

    openDeleteModal(presetId) {
        const preset = this.presets.find(p => p.id === presetId);
        if (!preset) return;
        
        this.currentPreset = presetId;
        document.getElementById('delete-message').textContent = 
            `「${preset.name}」を削除してもよろしいですか？この操作は元に戻すことができません。`;
        
        document.getElementById('delete-modal').style.display = 'block';
    }

    closeDeleteModal() {
        document.getElementById('delete-modal').style.display = 'none';
        this.currentPreset = null;
    }

    async confirmDelete() {
        if (!this.currentPreset) return;
        
        try {
            const response = await fetch(`/admin/preset-manager/presets/${this.currentPreset}`, {
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
                this.loadPresets();
            } else {
                this.showAlert('error', data.message || '削除に失敗しました。');
            }
        } catch (error) {
            console.error('Error deleting preset:', error);
            this.showAlert('error', '削除中にエラーが発生しました。');
        }
    }

    async bulkDelete() {
        if (this.selectedPresets.size === 0) return;
        
        if (!confirm(`選択した${this.selectedPresets.size}個のプリセットを削除してもよろしいですか？`)) {
            return;
        }
        
        try {
            const response = await fetch('/admin/preset-manager/presets/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    preset_ids: Array.from(this.selectedPresets)
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.selectedPresets.clear();
                this.updateBulkDeleteButton();
                this.loadPresets();
            } else {
                this.showAlert('error', data.message || '一括削除に失敗しました。');
            }
        } catch (error) {
            console.error('Error bulk deleting presets:', error);
            this.showAlert('error', '一括削除中にエラーが発生しました。');
        }
    }

    async toggleStatus(presetId) {
        const preset = this.presets.find(p => p.id === presetId);
        if (!preset) return;
        
        try {
            const response = await fetch(`/admin/preset-manager/presets/${presetId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.loadPresets();
            } else {
                this.showAlert('error', data.message || 'ステータス変更に失敗しました。');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            this.showAlert('error', 'ステータス変更中にエラーが発生しました。');
        }
    }

    async duplicate(presetId) {
        try {
            const response = await fetch(`/admin/preset-manager/presets/${presetId}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.loadPresets();
            } else {
                this.showAlert('error', data.message || '複製に失敗しました。');
            }
        } catch (error) {
            console.error('Error duplicating preset:', error);
            this.showAlert('error', '複製中にエラーが発生しました。');
        }
    }

    toggleSelectAll(checked) {
        this.selectedPresets.clear();
        
        if (checked) {
            this.presets.forEach(preset => {
                this.selectedPresets.add(preset.id);
            });
        }
        
        document.querySelectorAll('.preset-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
        });
        
        this.updateBulkDeleteButton();
    }

    updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all');
        const totalPresets = this.presets.length;
        const selectedCount = this.selectedPresets.size;
        
        if (selectedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (selectedCount === totalPresets) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    updateBulkDeleteButton() {
        const button = document.getElementById('bulk-delete-btn');
        button.disabled = this.selectedPresets.size === 0;
        
        if (this.selectedPresets.size > 0) {
            button.textContent = `選択した${this.selectedPresets.size}個のプリセットを削除`;
        } else {
            button.textContent = '選択したプリセットを削除';
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

    showFormErrors(errors) {
        this.clearFormErrors();
        
        Object.keys(errors).forEach(field => {
            const fieldKey = field.includes('.') ? field.split('.')[0] : field;
            this.showFieldError(fieldKey, errors[field][0]);
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
            day: 'numeric'
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
const presetManager = new PresetManager();
