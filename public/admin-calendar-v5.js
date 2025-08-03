// === ADMIN CALENDAR v5.4 STANDALONE - ROUTE FIX ===
console.log('🚀🚀🚀 ADMIN CALENDAR STANDALONE LOADED - VERSION 5.4 🚀🚀🚀');
console.log('🔥 CACHE COMPLETELY BYPASSED - NEW STANDALONE FILE! 🔥');
console.log('⚡ TIMESTAMP:', new Date().toISOString());

let currentYear;
let currentMonth;
let selectedDate = null;
let tooltipTimeout;
let isTooltipPinned = false;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  console.log('🎯 DOM LOADED - INITIALIZING ADMIN CALENDAR');
  
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
      if (isTooltipPinned) {
        hideTooltip(true);
      }
    }
  });

  // Modal outside click handlers
  document.addEventListener('click', function(e) {
    const timeslotModal = document.getElementById('timeslot-modal');
    const reservationModal = document.getElementById('reservation-modal');
    const tooltip = document.getElementById('tooltip');
    
    if (e.target === timeslotModal) {
      closeTimeslotModal();
    }
    if (e.target === reservationModal) {
      closeReservationModal();
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
  console.log('🚀 TIMESLOT FORM SUBMIT - BUTTON CLICK - VERSION 5.2');
  
  const form = document.getElementById('timeslot-form');
  const formData = new FormData(form);
  const id = formData.get('timeslot_id');
  const isEdit = id && id !== '';
  
  console.log('📋 BUTTON Form data check:', {
    timeslot_id: formData.get('timeslot_id'),
    date: formData.get('date'),
    start_time: formData.get('start_time'),
    end_time: formData.get('end_time'),
    capacity: formData.get('capacity'),
    service_id: formData.get('service_id'),
    available: document.getElementById('available').checked
  });
  
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
  
  console.log('🎯 BUTTON Submitting timeslot data:', { url, method, data });
  
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
    console.log('📡 BUTTON Response received:', response);
    console.log('🔍 BUTTON Response details:', {
      status: response.status,
      statusText: response.statusText,
      redirected: response.redirected,
      url: response.url,
      headers: Array.from(response.headers.entries())
    });
    
    return response.text().then(text => {
      console.log('📄 BUTTON Response text:', text.substring(0, 500) + '...');
      
      try {
        const jsonData = JSON.parse(text);
        console.log('✅ BUTTON Valid JSON response:', jsonData);
        return jsonData;
      } catch (parseError) {
        console.error('❌ BUTTON JSON parse error:', parseError);
        console.log('🔥 BUTTON Full response text:', text);
        throw new Error(`Invalid JSON response: ${parseError.message}`);
      }
    });
  })
  .then(data => {
    console.log('✅ BUTTON Success response:', data);
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
    console.error('💥 BUTTON Error:', error);
    showMessage(`エラーが発生しました: ${error.message}`, 'error');
  });
}

function handleTimeslotSubmit(e) {
  e.preventDefault();
  e.stopPropagation();
  console.log('🚀 TIMESLOT SUBMIT FROM STANDALONE - VERSION 5.2 - FORM PREVENT ENHANCED');
  
  const formData = new FormData(e.target);
  const id = formData.get('timeslot_id');
  const isEdit = id && id !== '';
  
  console.log('📋 STANDALONE Form data check:', {
    timeslot_id: formData.get('timeslot_id'),
    date: formData.get('date'),
    start_time: formData.get('start_time'),
    end_time: formData.get('end_time'),
    capacity: formData.get('capacity'),
    service_id: formData.get('service_id')
  });
  
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
  
  console.log('🎯 STANDALONE Submitting timeslot data:', { url, method, data });
  
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
    console.log('📡 STANDALONE Response received:', response);
    console.log('🔍 STANDALONE Response details:', {
      status: response.status,
      statusText: response.statusText,
      redirected: response.redirected,
      url: response.url,
      headers: Array.from(response.headers.entries())
    });
    
    // レスポンス内容をテキストとして読み取って確認
    return response.text().then(text => {
      console.log('📄 STANDALONE Response text:', text.substring(0, 500) + '...');
      
      // JSONかどうか確認してパース
      try {
        const jsonData = JSON.parse(text);
        console.log('✅ STANDALONE Valid JSON response:', jsonData);
        return jsonData;
      } catch (parseError) {
        console.error('❌ STANDALONE JSON parse error:', parseError);
        console.log('🔥 STANDALONE Full response text:', text);
        throw new Error(`Invalid JSON response: ${parseError.message}`);
      }
    });
  })
  .then(data => {
    console.log('✅ STANDALONE Success response:', data);
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
    console.error('💥 STANDALONE Error:', error);
    showMessage(`エラーが発生しました: ${error.message}`, 'error');
  });
}

function handleReservationSubmit(e) {
  e.preventDefault();
  e.stopPropagation();
  console.log('🚀 RESERVATION SUBMIT FROM STANDALONE - VERSION 5.2 - FORM PREVENT ENHANCED');
  
  const formData = new FormData(e.target);
  const id = formData.get('reservation_id');
  
  console.log('📋 STANDALONE Reservation form data check:', {
    reservation_id: formData.get('reservation_id'),
    customer_name: formData.get('customer_name'),
    customer_email: formData.get('customer_email'),
    customer_phone: formData.get('customer_phone'),
    status: formData.get('status')
  });
  
  const data = {
    customer_name: document.getElementById('customer-name').value,
    customer_email: document.getElementById('customer-email').value,  
    customer_phone: document.getElementById('customer-phone').value,
    status: document.getElementById('reservation-status').value
  };
  
  const baseUrl = window.routes ? window.routes.reservationBase : '/admin/reservations';
  
  console.log('🎯 STANDALONE Submitting reservation data:', { id, data });
  
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
    console.log('📡 STANDALONE Reservation response received:', response);
    if (!response.ok) {
      return response.json().then(errorData => {
        console.error('❌ STANDALONE Reservation server error response:', errorData);
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
      });
    }
    return response.json();
  })
  .then(data => {
    console.log('✅ STANDALONE Reservation success response:', data);
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
    console.error('💥 STANDALONE Reservation error:', error);
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
