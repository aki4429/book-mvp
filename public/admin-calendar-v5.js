// === ADMIN CALENDAR - CUSTOMER SEARCH ===

// Alert function for user feedback
function showAlert(type, message) {
  // Create alert element
  const alert = document.createElement('div');
  alert.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-lg transition-all duration-300 ${
    type === 'error' 
      ? 'bg-red-100 border border-red-400 text-red-700' 
      : type === 'success'
      ? 'bg-green-100 border border-green-400 text-green-700'
      : 'bg-blue-100 border border-blue-400 text-blue-700'
  }`;
  alert.innerHTML = `
    <div class="flex items-center">
      <span>${message}</span>
      <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-lg font-bold">&times;</button>
    </div>
  `;
  
  document.body.appendChild(alert);
  
  // Auto-remove after 5 seconds
  setTimeout(() => {
    if (alert.parentNode) {
      alert.remove();
    }
  }, 5000);
}

let currentYear;
let currentMonth;
let selectedDate = null;
let tooltipTimeout;
let isTooltipPinned = false;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Get current year and month from PHP variables (will be set by inline script)
  if (typeof window.calendarData !== 'undefined') {
    currentYear = window.calendarData.currentYear;
    currentMonth = window.calendarData.currentMonth;
  }
  
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  
  // ESC key handlers
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeTimeslotModal();
      closeReservationModal();
      closeAddReservationModal();
      if (isTooltipPinned) {
        hideTooltip(true);
      }
    }
  });

  // Modal outside click handlers
  document.addEventListener('click', function(e) {
    const timeslotModal = document.getElementById('timeslot-modal');
    const reservationModal = document.getElementById('reservation-modal');
    const addReservationModal = document.getElementById('add-reservation-modal');
    const tooltip = document.getElementById('tooltip');
    
    if (e.target === timeslotModal) {
      closeTimeslotModal();
    }
    if (e.target === reservationModal) {
      closeReservationModal();
    }
    if (e.target === addReservationModal) {
      closeAddReservationModal();
    }
    
    const isTooltipClick = tooltip.contains(e.target);
    const isCalendarDayClick = e.target.closest('.calendar-day');
    
    if (isTooltipPinned && !isTooltipClick && !isCalendarDayClick) {
      hideTooltip(true);
    }
  });

  // Tooltip mouse events
  document.getElementById('tooltip').addEventListener('mouseenter', function() {
    if (!isTooltipPinned) {
      clearTimeout(tooltipTimeout);
    }
  });

  document.getElementById('tooltip').addEventListener('mouseleave', function() {
    if (!isTooltipPinned) {
      hideTooltip();
    }
  });
});

function changeMonth(direction) {
  currentMonth += direction;
  
  if (currentMonth > 12) {
    currentMonth = 1;
    currentYear++;
  } else if (currentMonth < 1) {
    currentMonth = 12;
    currentYear--;
  }

  const changeMonthUrl = window.routes ? window.routes.changeMonth : '/admin/calendar/change-month';
  
  fetch(`${changeMonthUrl}?year=${currentYear}&month=${currentMonth}`, {
    method: 'GET',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('calendar-container').innerHTML = data.html;
      document.getElementById('calendar-title').textContent = `${data.year}年${data.month}月`;
      document.getElementById('day-details-container').style.display = 'none';
      hideTooltip();
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showMessage('エラーが発生しました。', 'error');
  });
}

function selectDate(date) {
  selectedDate = date;
  
  const daySlotsUrl = window.routes ? window.routes.daySlots : '/admin/calendar/day-slots';
  
  fetch(`${daySlotsUrl}?date=${date}`, {
    method: 'GET',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('day-details-content').innerHTML = data.html;
      document.getElementById('day-details-container').style.display = 'block';
      
      const selectedDateObj = new Date(date + 'T00:00:00');
      const month = selectedDateObj.getMonth() + 1;
      const day = selectedDateObj.getDate();
      const title = `${month}月${day}日 の管理 (時間枠: ${data.slots_count}件)`;
      document.getElementById('day-details-title').textContent = title;
      
      // Update selected date cell
      document.querySelectorAll('.calendar-day').forEach(dayEl => {
        dayEl.classList.remove('selected-date');
      });
      
      const selectedDateCell = document.querySelector(`[data-date="${date}"]`);
      if (selectedDateCell) {
        selectedDateCell.classList.add('selected-date');
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showMessage('エラーが発生しました。', 'error');
  });
}

function handleTimeslotFormSubmit() {
  const form = document.getElementById('timeslot-form');
  const formData = new FormData(form);
  const id = formData.get('timeslot_id');
  const isEdit = id && id !== '';
  
  const url = window.routes ? 
    (isEdit ? `${window.routes.timeslotBase}/${id}` : window.routes.timeslotCreate) :
    (isEdit ? `/admin/timeslots/${id}` : '/admin/timeslots');
    
  const method = isEdit ? 'PUT' : 'POST';
  
  const data = {
    date: formData.get('date'),
    start_time: formData.get('start_time'),
    end_time: formData.get('end_time'),
    capacity: formData.get('capacity'),
    service_id: formData.get('service_id') || '',
    available: document.getElementById('available').checked
  };
  
  fetch(url, {
    method: method,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(data)
  })
  .then(response => {
    return response.text().then(text => {
      try {
        const jsonData = JSON.parse(text);
        return jsonData;
      } catch (parseError) {
        throw new Error(`Invalid JSON response: ${parseError.message}`);
      }
    });
  })
  .then(data => {
    if (data.success) {
      showMessage(data.message, 'success');
      closeTimeslotModal();
      selectDate(selectedDate);
      changeMonth(0);
    } else {
      showMessage(data.message || 'エラーが発生しました。', 'error');
    }
  })
  .catch(error => {
    showMessage(`エラーが発生しました: ${error.message}`, 'error');
  });
}

function handleTimeslotSubmit(e) {
  e.preventDefault();
  e.stopPropagation();
  
  const formData = new FormData(e.target);
  const id = formData.get('timeslot_id');
  const isEdit = id && id !== '';
  
  const baseUrl = window.routes ? window.routes.timeslotBase : '/admin/timeslots';
  const createUrl = window.routes ? window.routes.timeslotCreate : '/admin/timeslots/create';
  
  const url = isEdit ? `${baseUrl}/${id}` : createUrl;
  const method = isEdit ? 'PUT' : 'POST';
  
  const data = {
    date: formData.get('date'),
    start_time: formData.get('start_time'),
    end_time: formData.get('end_time'),
    capacity: formData.get('capacity'),
    service_id: formData.get('service_id') || '',
    available: document.getElementById('available').checked
  };
  
  fetch(url, {
    method: method,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(data)
  })
  .then(response => {
    return response.text().then(text => {
      try {
        const jsonData = JSON.parse(text);
        return jsonData;
      } catch (parseError) {
        throw new Error(`Invalid JSON response: ${parseError.message}`);
      }
    });
  })
  .then(data => {
    if (data.success) {
      showMessage(data.message, 'success');
      closeTimeslotModal();
      selectDate(selectedDate);
      changeMonth(0);
    } else {
      showMessage(data.message || 'エラーが発生しました。', 'error');
    }
  })
  .catch(error => {
    showMessage(`エラーが発生しました: ${error.message}`, 'error');
  });
}

function handleReservationSubmit(e) {
  e.preventDefault();
  e.stopPropagation();
  
  const formData = new FormData(e.target);
  const id = formData.get('reservation_id');
  
  const data = {
    customer_name: document.getElementById('customer-name').value,
    customer_email: document.getElementById('customer-email').value,  
    customer_phone: document.getElementById('customer-phone').value,
    status: document.getElementById('reservation-status').value
  };
  
  const baseUrl = window.routes ? window.routes.reservationBase : '/admin/reservations';
  
  fetch(`${baseUrl}/${id}`, {
    method: 'PUT',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(data)
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(errorData => {
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showMessage(data.message, 'success');
      closeReservationModal();
      selectDate(selectedDate);
      changeMonth(0);
    } else {
      showMessage(data.message || 'エラーが発生しました。', 'error');
    }
  })
  .catch(error => {
    showMessage(`エラーが発生しました: ${error.message}`, 'error');
  });
}

// All other functions remain the same...
function showTooltip(event, date, isPinned = false) {
  clearTimeout(tooltipTimeout);
  
  const element = event.currentTarget;
  const slotsData = element.getAttribute('data-admin-slots');
  
  if (!slotsData) return;
  
  let adminData;
  try {
    adminData = JSON.parse(slotsData);
  } catch (e) {
    console.error('Failed to parse admin slots data:', e);
    return;
  }

  isTooltipPinned = isPinned;

  if (isPinned) {
    document.querySelectorAll('.calendar-day').forEach(day => {
      day.classList.remove('clicked-date');
    });
    
    const clickedDateCell = document.querySelector(`[data-date="${date}"]`);
    if (clickedDateCell) {
      clickedDateCell.classList.add('clicked-date');
    }
  }

  const tooltip = document.getElementById('tooltip');
  const tooltipContent = document.getElementById('tooltip-content');
  
  let content = `<div class="font-semibold mb-2 flex justify-between items-center">
    <span>${formatDate(date)}</span>
    ${isPinned ? '<button onclick="hideTooltip(true)" class="text-gray-400 hover:text-white ml-2">✕</button>' : ''}
  </div>`;
  
  content += `<div class="text-xs text-gray-300 mb-2">
    時間枠: ${adminData.totalSlots}件 | 予約: ${adminData.totalReservations}件
  </div>`;
  
  if (adminData.slots && adminData.slots.length > 0) {
    adminData.slots.forEach(slot => {
      const startTime = slot.start_time ? slot.start_time.substring(0, 5) : '';
      const endTime = slot.end_time ? slot.end_time.substring(0, 5) : '';
      const reservationCount = slot.reservations ? slot.reservations.length : 0;
      const capacity = slot.capacity || 1;
      
      const statusClass = slot.available ? 'text-green-300' : 'text-red-300';
      const statusText = slot.available ? '受付中' : '停止';
      
      content += `
        <div class="border-b border-gray-700 last:border-b-0 py-2">
          <div class="flex justify-between items-center">
            <div class="font-medium">${startTime} - ${endTime}</div>
            <div class="text-xs ${statusClass}">${statusText}</div>
          </div>
          <div class="text-xs text-gray-300">
            予約: ${reservationCount}/${capacity}
            ${slot.service_id ? ` | ID: ${slot.service_id}` : ''}
          </div>
        </div>
      `;
    });
  } else {
    content += '<div class="text-gray-400 text-xs">時間枠が設定されていません</div>';
  }
  
  tooltipContent.innerHTML = content;

  const rect = element.getBoundingClientRect();
  let left = rect.left + (rect.width / 2) - 100;
  let top = rect.top - 10;
  
  if (left < 10) left = 10;
  if (left + 200 > window.innerWidth - 10) {
    left = window.innerWidth - 210;
  }
  if (top < 10) {
    top = rect.bottom + 10;
  }

  tooltip.style.left = `${left}px`;
  tooltip.style.top = `${top}px`;
  tooltip.style.display = 'block';
  
  setTimeout(() => {
    tooltip.classList.remove('opacity-0', 'scale-95');
    tooltip.classList.add('opacity-100', 'scale-100');
    tooltip.style.pointerEvents = 'auto';
  }, 10);
}

function hideTooltip(force = false) {
  const tooltip = document.getElementById('tooltip');
  
  if (isTooltipPinned && !force) {
    return;
  }
  
  tooltipTimeout = setTimeout(() => {
    tooltip.classList.remove('opacity-100', 'scale-100');
    tooltip.classList.add('opacity-0', 'scale-95');
    tooltip.style.pointerEvents = 'none';
    isTooltipPinned = false;
    
    document.querySelectorAll('.calendar-day').forEach(day => {
      day.classList.remove('clicked-date');
    });
    
    setTimeout(() => {
      tooltip.style.display = 'none';
    }, 200);
  }, force ? 0 : 300);
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
  const days = ['日', '月', '火', '水', '木', '金', '土'];
  
  return `${date.getMonth() + 1}月${date.getDate()}日 (${days[date.getDay()]})`;
}

function editTimeSlot(id, startTime, endTime, capacity, serviceId, available) {
  document.getElementById('timeslot-modal-title').textContent = '時間枠編集';
  document.getElementById('timeslot-id').value = id;
  document.getElementById('selected-date').value = selectedDate;
  document.getElementById('start-time').value = startTime;
  document.getElementById('end-time').value = endTime;
  document.getElementById('capacity').value = capacity;
  document.getElementById('service-id').value = serviceId || '';
  document.getElementById('available').checked = available;
  
  document.getElementById('timeslot-modal').classList.add('show');
}

function addTimeSlot() {
  if (!selectedDate) {
    showMessage('日付を選択してください。', 'error');
    return;
  }
  
  document.getElementById('timeslot-modal-title').textContent = '時間枠追加';
  document.getElementById('timeslot-form').reset();
  document.getElementById('timeslot-id').value = '';
  document.getElementById('selected-date').value = selectedDate;
  document.getElementById('available').checked = true;
  
  document.getElementById('timeslot-modal').classList.add('show');
}

function deleteTimeSlot(id) {
  if (!confirm('この時間枠を削除しますか？')) {
    return;
  }
  
  const baseUrl = window.routes ? window.routes.timeslotBase : '/admin/timeslots';
  
  fetch(`${baseUrl}/${id}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showMessage(data.message, 'success');
      selectDate(selectedDate);
    } else {
      showMessage(data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showMessage('エラーが発生しました。', 'error');
  });
}

function closeTimeslotModal() {
  document.getElementById('timeslot-modal').classList.remove('show');
}

function editReservation(id, customerName, customerEmail, customerPhone, status) {
  document.getElementById('reservation-id').value = id;
  document.getElementById('customer-name').value = customerName;
  document.getElementById('customer-email').value = customerEmail;
  document.getElementById('customer-phone').value = customerPhone || '';
  document.getElementById('reservation-status').value = status;
  
  document.getElementById('reservation-modal').classList.add('show');
}

function deleteReservation(id) {
  if (!confirm('この予約を削除しますか？')) {
    return;
  }
  
  const baseUrl = window.routes ? window.routes.reservationBase : '/admin/reservations';
  
  fetch(`${baseUrl}/${id}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showMessage(data.message, 'success');
      selectDate(selectedDate);
      changeMonth(0);
    } else {
      showMessage(data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showMessage('エラーが発生しました。', 'error');
  });
}

function closeReservationModal() {
  document.getElementById('reservation-modal').classList.remove('show');
}

function showMessage(message, type) {
  const container = document.getElementById('message-container');
  
  const alertClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700';
  const iconColor = type === 'success' ? 'text-green-400' : 'text-red-400';
  const icon = type === 'success' 
    ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
    : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
  
  container.innerHTML = `
    <div class="${alertClass} border rounded-lg p-4 mx-auto max-w-md">
      <div class="flex items-center">
        <div class="${iconColor} mr-3">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            ${icon}
          </svg>
        </div>
        <p class="font-medium">${message}</p>
      </div>
    </div>
  `;
  
  setTimeout(() => {
    container.innerHTML = '';
  }, 3000);
}

// === RESERVATION MANAGEMENT ===

// 予約追加
function addReservation(timeSlotId) {
  const modal = document.getElementById('add-reservation-modal');
  
  if (!modal) {
    return;
  }
  
  // フォーム要素の設定
  const timeSlotIdField = document.getElementById('add-reservation-time-slot-id');
  const customerNameField = document.getElementById('add-customer-name');
  const customerEmailField = document.getElementById('add-customer-email');
  const customerPhoneField = document.getElementById('add-customer-phone');
  const statusField = document.getElementById('add-reservation-status');
  
  // Set the time slot ID in the hidden field
  if (timeSlotIdField) {
    timeSlotIdField.value = timeSlotId;
  }
  
  // Clear form fields
  if (customerNameField) customerNameField.value = '';
  if (customerEmailField) customerEmailField.value = '';
  if (customerPhoneField) customerPhoneField.value = '';
  if (statusField) statusField.value = 'confirmed';
  
  // Reset customer selection
  document.getElementById('new-customer').checked = true;
  document.getElementById('existing-customer').checked = false;
  toggleCustomerInput();
  
  const customerSearchResults = document.getElementById('customer-search-results');
  if (customerSearchResults) {
    customerSearchResults.style.display = 'none';
    customerSearchResults.innerHTML = '';
  }
  
  const customerSearch = document.getElementById('customer-search');
  if (customerSearch) customerSearch.value = '';
  
  const selectedCustomerId = document.getElementById('selected-customer-id');
  if (selectedCustomerId) selectedCustomerId.value = '';
  
  // Show modal
  modal.style.display = 'block';
  modal.style.visibility = 'visible';
  modal.style.opacity = '1';
  modal.classList.add('show');
}

// 予約追加モーダルを閉じる
function closeAddReservationModal() {
  const modal = document.getElementById('add-reservation-modal');
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('show');
    
    // フォームをリセット
    const form = modal.querySelector('form');
    if (form) {
      form.reset();
    }
    
    // パスワードフィールドを個別でクリア
    const passwordField = document.getElementById('add-customer-password');
    const passwordConfirmField = document.getElementById('add-customer-password-confirmation');
    if (passwordField) passwordField.value = '';
    if (passwordConfirmField) passwordConfirmField.value = '';
    
    // 顧客検索結果をクリア
    const searchResults = document.getElementById('customer-search-results');
    if (searchResults) {
      searchResults.style.display = 'none';
      searchResults.innerHTML = '';
    }
    
    // 選択された顧客IDをクリア
    const selectedCustomerId = document.getElementById('selected-customer-id');
    if (selectedCustomerId) selectedCustomerId.value = '';
  }
}

// 予約追加フォーム送信
async function handleAddReservationSubmit(event) {
  event.preventDefault();
  
  const formData = new FormData(event.target);
  const existingCustomer = document.getElementById('existing-customer').checked;
  const submitButton = event.target.querySelector('button[type="submit"]');
  const originalButtonText = submitButton ? submitButton.textContent : '';
  
  // ボタンを無効化してローディング状態にする
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.textContent = '処理中...';
  }
  
  let data;
  
  if (existingCustomer) {
    const selectedCustomerId = document.getElementById('selected-customer-id').value;
    if (!selectedCustomerId) {
      showAlert('error', '顧客を選択してください');
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
      return;
    }
    
    data = {
      time_slot_id: formData.get('time_slot_id'),
      customer_id: selectedCustomerId,
      status: formData.get('status') || 'confirmed',
      use_existing_customer: true
    };
  } else {
    // 新規顧客の場合、パスワード確認
    const password = formData.get('customer_password');
    const passwordConfirmation = formData.get('customer_password_confirmation');
    
    if (!password || password.length < 6) {
      showAlert('error', 'パスワードは6文字以上で入力してください');
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
      return;
    }
    
    if (password !== passwordConfirmation) {
      showAlert('error', 'パスワードが一致しません');
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
      return;
    }
    
    data = {
      time_slot_id: formData.get('time_slot_id'),
      customer_name: formData.get('customer_name'),
      customer_email: formData.get('customer_email'),
      customer_phone: formData.get('customer_phone'),
      customer_password: password,
      customer_password_confirmation: passwordConfirmation,
      status: formData.get('status') || 'confirmed',
      use_existing_customer: false
    };
  }
  
  try {
    // タイムアウト設定（10秒）
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000);
    
    const response = await fetch(window.routes.reservationCreate, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify(data),
      signal: controller.signal
    });
    
    clearTimeout(timeoutId); // リクエスト成功時はタイムアウトをクリア
    
    if (!response.ok) {
      const errorText = await response.text();
      showAlert('error', `サーバーエラー: ${response.status} ${response.statusText}`);
      
      // ボタンを元の状態に戻す
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
      return;
    }
    
    const responseText = await response.text();
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      showAlert('error', 'サーバーからの応答形式が不正です');
      
      // ボタンを元の状態に戻す
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
      return;
    }
    
    if (result.success) {
      showAlert('success', result.message);
      
      // ボタンを元の状態に戻す
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
      
      closeAddReservationModal();
      
      // 少し待ってから画面を更新（UI の応答性向上）
      setTimeout(() => {
        // Refresh day slots if we have a selected date
        if (selectedDate) {
          selectDate(selectedDate); // 既存の selectDate 関数を使用してページを更新
        }
        
        // カレンダー全体も更新
        changeMonth(0);
      }, 500);
    } else {
      showAlert('error', result.message || 'エラーが発生しました');
      
      // ボタンを元の状態に戻す
      if (submitButton) {
        submitButton.disabled = false; 
        submitButton.textContent = originalButtonText;
      }
    }
  } catch (error) {
    if (error.name === 'TypeError' && error.message.includes('fetch')) {
      showAlert('error', 'ネットワーク接続エラー: サーバーに接続できません');
    } else if (error.name === 'AbortError') {
      showAlert('error', 'リクエストがタイムアウトしました');
    } else {
      showAlert('error', `エラーが発生しました: ${error.message}`);
    }
  } finally {
    // ボタンを元の状態に戻す
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.textContent = originalButtonText;
    }
  }
}

// === CUSTOMER MANAGEMENT ===

// 顧客入力方法の切り替え
function toggleCustomerInput() {
  const existingCustomerRadio = document.getElementById('existing-customer');
  const newCustomerRadio = document.getElementById('new-customer');
  const existingSection = document.getElementById('existing-customer-section');
  const newSection = document.getElementById('new-customer-section');
  
  if (existingCustomerRadio && existingCustomerRadio.checked) {
    existingSection.style.display = 'block';
    newSection.style.display = 'none';
    
    // 新規顧客フィールドの必須を解除
    const newFields = newSection.querySelectorAll('[required]');
    newFields.forEach(field => field.removeAttribute('required'));
  } else {
    existingSection.style.display = 'none';
    newSection.style.display = 'block';
    
    // 新規顧客フィールドに必須を設定
    document.getElementById('add-customer-name').setAttribute('required', 'required');
    document.getElementById('add-customer-email').setAttribute('required', 'required');
  }
}

// 顧客検索
let searchTimeout;
async function searchCustomers(query) {
  clearTimeout(searchTimeout);
  
  const resultsContainer = document.getElementById('customer-search-results');
  
  if (query.length < 2) {
    resultsContainer.style.display = 'none';
    return;
  }
  
  searchTimeout = setTimeout(async () => {
    try {
      const response = await fetch(`${window.routes.searchCustomers}?query=${encodeURIComponent(query)}`, {
        method: 'GET',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      });
      
      const result = await response.json();
      
      if (result.success && result.customers) {
        displayCustomerResults(result.customers);
      } else {
        resultsContainer.style.display = 'none';
      }
    } catch (error) {
      console.error('Customer search error:', error);
      resultsContainer.style.display = 'none';
    }
  }, 300);
}

// 顧客検索結果を表示
function displayCustomerResults(customers) {
  const resultsContainer = document.getElementById('customer-search-results');
  
  if (customers.length === 0) {
    resultsContainer.innerHTML = '<div class="p-2 text-sm text-gray-500">該当する顧客が見つかりません</div>';
    resultsContainer.style.display = 'block';
    return;
  }
  
  const html = customers.map(customer => `
    <div class="p-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
         onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.email}', '${customer.phone || ''}')">
      <div class="font-medium text-sm">${customer.name}</div>
      <div class="text-xs text-gray-500">${customer.email}</div>
      ${customer.phone ? `<div class="text-xs text-gray-500">${customer.phone}</div>` : ''}
    </div>
  `).join('');
  
  resultsContainer.innerHTML = html;
  resultsContainer.style.display = 'block';
}

// 顧客を選択
function selectCustomer(id, name, email, phone) {
  document.getElementById('selected-customer-id').value = id;
  document.getElementById('customer-search').value = `${name} (${email})`;
  document.getElementById('customer-search-results').style.display = 'none';
  
  // 新規顧客フィールドに値を設定（表示用）
  document.getElementById('add-customer-name').value = name;
  document.getElementById('add-customer-email').value = email;
  document.getElementById('add-customer-phone').value = phone || '';
}
