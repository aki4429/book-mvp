class BulkTimeSlotManager {
    constructor() {
        this.presets = [];
        this.previewData = null;
        this.timeslotCounter = 0;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadPresets();
        this.setDefaultDates();
    }

    bindEvents() {
        // 曜日選択
        document.querySelectorAll('[data-day]').forEach(label => {
            label.addEventListener('click', (e) => {
                const checkbox = label.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
                this.updateDaySelection(label, checkbox.checked);
            });
        });

        // プリセット選択
        document.getElementById('preset_select').addEventListener('change', (e) => {
            this.loadPreset(e.target.value);
        });

        document.getElementById('clear-preset-btn').addEventListener('click', () => {
            this.clearPreset();
        });

        // 時間枠管理
        document.getElementById('add-timeslot-btn').addEventListener('click', () => {
            this.addTimeslot();
        });

        // フォーム操作
        document.getElementById('preview-btn').addEventListener('click', () => {
            this.showPreview();
        });

        document.getElementById('bulk-timeslot-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.showPreview();
        });

        document.getElementById('reset-form-btn').addEventListener('click', () => {
            this.resetForm();
        });

        // モーダル操作
        document.getElementById('close-preview-modal').addEventListener('click', () => {
            this.closePreviewModal();
        });

        document.getElementById('cancel-preview').addEventListener('click', () => {
            this.closePreviewModal();
        });

        document.getElementById('confirm-create').addEventListener('click', () => {
            this.createTimeslots();
        });

        // モーダル外クリックで閉じる
        document.getElementById('preview-modal').addEventListener('click', (e) => {
            if (e.target.id === 'preview-modal') {
                this.closePreviewModal();
            }
        });
    }

    async loadPresets() {
        try {
            const response = await fetch('/admin/bulk-timeslots/presets', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.presets = data.data;
                this.renderPresetOptions();
            } else {
                this.showAlert('error', 'プリセットの読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading presets:', error);
            this.showAlert('error', 'プリセットの読み込み中にエラーが発生しました。');
        }
    }

    renderPresetOptions() {
        const select = document.getElementById('preset_select');
        const defaultOption = select.querySelector('option[value=""]');
        
        // 既存のオプションをクリア（デフォルトオプション以外）
        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }

        this.presets.forEach(preset => {
            const option = document.createElement('option');
            option.value = preset.id;
            option.textContent = preset.name;
            select.appendChild(option);
        });
    }

    async loadPreset(presetId) {
        if (!presetId) {
            this.clearPreset();
            return;
        }

        try {
            const response = await fetch(`/admin/bulk-timeslots/presets/${presetId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const preset = data.data;
                this.applyPreset(preset);
            } else {
                this.showAlert('error', 'プリセットの読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading preset:', error);
            this.showAlert('error', 'プリセットの読み込み中にエラーが発生しました。');
        }
    }

    applyPreset(preset) {
        // プリセット情報を表示
        const infoDiv = document.getElementById('selected-preset-info');
        infoDiv.innerHTML = `
            <div class="font-medium">${preset.name}</div>
            ${preset.description ? `<div class="text-gray-500 mt-1">${preset.description}</div>` : ''}
            <div class="text-sm text-blue-600 mt-1">${preset.time_slots.length}個の時間枠</div>
        `;

        // 既存の時間枠をクリア
        this.clearTimeslots();

        // プリセットの時間枠を適用
        preset.time_slots.forEach((slot, index) => {
            if (index === 0) {
                // 最初の行は既存のものを使用
                this.updateTimeslotRow(0, slot);
            } else {
                // 新しい行を追加
                this.addTimeslot(slot);
            }
        });
    }

    clearPreset() {
        document.getElementById('preset_select').value = '';
        document.getElementById('selected-preset-info').textContent = 'プリセットが選択されていません';
    }

    updateDaySelection(label, checked) {
        if (checked) {
            label.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
            label.classList.remove('border-gray-300');
        } else {
            label.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
            label.classList.add('border-gray-300');
        }
    }

    addTimeslot(slotData = null) {
        this.timeslotCounter++;
        const container = document.getElementById('timeslots-container');
        
        const timeslotRow = document.createElement('div');
        timeslotRow.className = 'timeslot-row border rounded-lg p-4 mb-3';
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
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="任意">
                </div>
                <div class="flex-shrink-0">
                    <button type="button" class="remove-timeslot-btn mt-6 text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(timeslotRow);

        // 削除ボタンのイベントリスナーを追加
        const removeBtn = timeslotRow.querySelector('.remove-timeslot-btn');
        removeBtn.addEventListener('click', () => {
            this.removeTimeslot(timeslotRow);
        });

        this.updateRemoveButtons();
    }

    updateTimeslotRow(index, slotData) {
        const container = document.getElementById('timeslots-container');
        const row = container.children[index];
        
        if (row) {
            row.querySelector('input[name$="[start_time]"]').value = slotData.start_time || '';
            row.querySelector('input[name$="[end_time]"]').value = slotData.end_time || '';
            row.querySelector('input[name$="[capacity]"]').value = slotData.capacity || 1;
            row.querySelector('input[name$="[service_id]"]').value = slotData.service_id || '';
        }
    }

    removeTimeslot(row) {
        row.remove();
        this.updateRemoveButtons();
        this.reindexTimeslots();
    }

    clearTimeslots() {
        const container = document.getElementById('timeslots-container');
        while (container.children.length > 1) {
            container.removeChild(container.lastChild);
        }
        this.updateRemoveButtons();
        this.timeslotCounter = 0;
    }

    updateRemoveButtons() {
        const container = document.getElementById('timeslots-container');
        const removeButtons = container.querySelectorAll('.remove-timeslot-btn');
        
        removeButtons.forEach(btn => {
            btn.disabled = container.children.length <= 1;
        });
    }

    reindexTimeslots() {
        const container = document.getElementById('timeslots-container');
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

    setDefaultDates() {
        const today = new Date();
        const nextWeek = new Date(today);
        nextWeek.setDate(today.getDate() + 7);

        document.getElementById('start_date').value = today.toISOString().split('T')[0];
        document.getElementById('end_date').value = nextWeek.toISOString().split('T')[0];
    }

    resetForm() {
        document.getElementById('bulk-timeslot-form').reset();
        this.clearPreset();
        this.clearTimeslots();
        this.addTimeslot(); // 初期の時間枠を再追加
        this.setDefaultDates();
        
        // 曜日選択をリセット
        document.querySelectorAll('[data-day]').forEach(label => {
            label.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
            label.classList.add('border-gray-300');
        });

        this.clearFormErrors();
        document.getElementById('create-btn').disabled = true;
    }

    async showPreview() {
        this.clearFormErrors();
        
        const formData = this.getFormData();
        if (!this.validateForm(formData)) {
            return;
        }

        this.showLoading(true);

        try {
            const response = await fetch('/admin/bulk-timeslots/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.previewData = data.data;
                this.renderPreview();
                this.showPreviewModal();
                document.getElementById('create-btn').disabled = false;
            } else {
                if (data.errors) {
                    this.showFormErrors(data.errors);
                } else {
                    this.showAlert('error', data.message || 'プレビューの生成に失敗しました。');
                }
            }
        } catch (error) {
            console.error('Error generating preview:', error);
            this.showAlert('error', 'プレビューの生成中にエラーが発生しました。');
        } finally {
            this.showLoading(false);
        }
    }

    getFormData() {
        const form = document.getElementById('bulk-timeslot-form');
        const formData = new FormData(form);
        
        const data = {
            days: formData.getAll('days[]'),
            start_date: formData.get('start_date'),
            end_date: formData.get('end_date'),
            overwrite_existing: formData.has('overwrite_existing'),
            time_slots: []
        };

        // 時間枠データを収集
        const container = document.getElementById('timeslots-container');
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

    validateForm(data) {
        let isValid = true;

        if (data.days.length === 0) {
            this.showFieldError('days', '対象曜日を選択してください。');
            isValid = false;
        }

        if (!data.start_date) {
            this.showFieldError('start_date', '開始日を入力してください。');
            isValid = false;
        }

        if (!data.end_date) {
            this.showFieldError('end_date', '終了日を入力してください。');
            isValid = false;
        }

        if (data.time_slots.length === 0) {
            this.showFieldError('time_slots', '少なくとも1つの時間枠を設定してください。');
            isValid = false;
        }

        return isValid;
    }

    renderPreview() {
        const container = document.getElementById('preview-content');
        
        let html = `
            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">作成サマリー</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>対象日数: <span class="font-medium">${this.previewData.total_dates}日</span></div>
                        <div>作成予定の時間枠: <span class="font-medium">${this.previewData.total_slots}個</span></div>
                    </div>
                </div>
        `;

        if (this.previewData.conflicts.length > 0) {
            html += `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-900 mb-2">⚠️ 重複する日付があります</h4>
                    <div class="text-sm text-yellow-800">
                        <p class="mb-2">以下の日付には既存の時間枠との重複があります：</p>
                        <div class="max-h-32 overflow-y-auto">
                            ${this.previewData.conflicts.map(date => `<div class="text-xs">${date}</div>`).join('')}
                        </div>
                        <p class="mt-2 text-xs">「既存の時間枠を上書きする」オプションが有効な場合、これらの日付の重複する時間枠は上書きされます。</p>
                    </div>
                </div>
            `;
        }

        html += `
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">設定される時間枠</h4>
                    <div class="space-y-2">
        `;

        this.previewData.time_slots.forEach(slot => {
            html += `
                <div class="flex justify-between items-center bg-white p-2 rounded border text-sm">
                    <span>${slot.start_time} - ${slot.end_time}</span>
                    <div class="flex space-x-4 text-xs text-gray-600">
                        <span>定員: ${slot.capacity}</span>
                        ${slot.service_id ? `<span>サービス: ${slot.service_id}</span>` : ''}
                    </div>
                </div>
            `;
        });

        html += `
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                    <h4 class="font-semibold text-gray-900 mb-3">対象日一覧</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
        `;

        this.previewData.dates.forEach(date => {
            const isConflict = this.previewData.conflicts.includes(date.date);
            html += `
                <div class="flex justify-between p-2 rounded ${isConflict ? 'bg-yellow-100 border border-yellow-300' : 'bg-white border'}">
                    <span>${date.day_name}</span>
                    <span class="text-gray-600">${date.slots_count}枠${isConflict ? ' ⚠️' : ''}</span>
                </div>
            `;
        });

        html += `
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    showPreviewModal() {
        document.getElementById('preview-modal').style.display = 'block';
    }

    closePreviewModal() {
        document.getElementById('preview-modal').style.display = 'none';
    }

    async createTimeslots() {
        this.showLoading(true);
        this.closePreviewModal();

        try {
            const formData = this.getFormData();
            
            const response = await fetch('/admin/bulk-timeslots/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.resetForm();
            } else {
                if (data.errors) {
                    this.showFormErrors(data.errors);
                } else {
                    this.showAlert('error', data.message || '時間枠の作成に失敗しました。');
                }
            }
        } catch (error) {
            console.error('Error creating timeslots:', error);
            this.showAlert('error', '時間枠の作成中にエラーが発生しました。');
        } finally {
            this.showLoading(false);
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
}

// グローバルインスタンス
const bulkTimeSlotManager = new BulkTimeSlotManager();
