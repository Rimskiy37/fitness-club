/**
 * FitLife — Основной JavaScript
 * Содержит AJAX/Fetch функции и интерактивность
 *
 * Запуск: php -S localhost:8000 router.php
 */

// ============================================================
// 1. AJAX: Поиск занятий по названию
// ============================================================
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
let searchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            if (searchResults) searchResults.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(APP_URL + '/api/search-classes.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (data.success && searchResults) {
                        searchResults.innerHTML = renderClassCards(data.classes);
                    }
                })
                .catch(err => console.error('Ошибка поиска:', err));
        }, 300);
    });
}

// ============================================================
// 2. AJAX: Фильтрация занятий по категории
// ============================================================
function filterByCategory(category, btnEl) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    const container = document.getElementById('classCards');
    if (!container) return;

    container.innerHTML = '<div class="spinner"></div>';

    let url = APP_URL + '/api/filter-classes.php';
    if (category && category !== 'all') {
        url += '?category=' + encodeURIComponent(category);
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = renderClassCards(data.classes);
            }
        })
        .catch(err => {
            console.error('Ошибка фильтрации:', err);
            container.innerHTML = '<p class="alert alert-danger">Ошибка загрузки данных</p>';
        });
}

// ============================================================
// 3. AJAX: Запись на занятие
// ============================================================
function bookClass(classId, date) {
    if (!confirm('Записаться на это занятие?')) return;

    fetch(APP_URL + '/api/book-class.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ class_id: classId, date: date })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Вы успешно записались на занятие!', 'success');
                const btn = document.querySelector(`[data-class-id="${classId}"]`);
                if (btn) {
                    btn.textContent = 'Вы записаны';
                    btn.disabled = true;
                    btn.classList.remove('btn-primary', 'pulse-btn');
                    btn.classList.add('btn-success');
                }
            } else {
                showToast(data.error || 'Ошибка записи', 'danger');
            }
        })
        .catch(err => {
            console.error('Ошибка записи:', err);
            showToast('Произошла ошибка при записи', 'danger');
        });
}

// ============================================================
// 4. AJAX: Отмена записи
// ============================================================
function cancelBooking(bookingId, btnEl) {
    if (!confirm('Отменить запись на занятие?')) return;

    fetch(APP_URL + '/api/cancel-booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ booking_id: bookingId })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Запись отменена', 'info');
                const row = document.querySelector(`[data-booking-id="${bookingId}"]`);
                if (row) {
                    const statusEl = row.querySelector('.status');
                    if (statusEl) {
                        statusEl.textContent = 'Отменена';
                        statusEl.className = 'status status-cancelled';
                    }
                    if (btnEl) btnEl.remove();
                }
            } else {
                showToast(data.error || 'Ошибка отмены', 'danger');
            }
        })
        .catch(err => {
            console.error('Ошибка отмены:', err);
            showToast('Произошла ошибка', 'danger');
        });
}

// ============================================================
// 5. AJAX: Проверка email при регистрации
// ============================================================
const regEmailInput = document.getElementById('regEmail');
if (regEmailInput) {
    regEmailInput.addEventListener('blur', function () {
        const email = this.value.trim();
        if (!email) return;

        fetch(APP_URL + '/api/check-email.php?email=' + encodeURIComponent(email))
            .then(res => res.json())
            .then(data => {
                const feedback = document.getElementById('emailFeedback');
                if (feedback) {
                    if (data.exists) {
                        feedback.textContent = 'Этот email уже зарегистрирован';
                        feedback.style.color = '#E76F51';
                        regEmailInput.classList.add('is-invalid');
                    } else {
                        feedback.textContent = 'Email доступен';
                        feedback.style.color = '#2A9D8F';
                        regEmailInput.classList.remove('is-invalid');
                    }
                }
            })
            .catch(err => console.error('Ошибка проверки email:', err));
    });
}

// ============================================================
// 6. AJAX: Изменение статуса записи (админ-панель)
// ============================================================
function changeBookingStatus(bookingId, newStatus) {
    fetch(APP_URL + '/admin/api/update-booking-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ booking_id: bookingId, status: newStatus })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Статус обновлён', 'success');
                const row = document.querySelector(`[data-booking-id="${bookingId}"]`);
                if (row) {
                    const statusEl = row.querySelector('.status');
                    if (statusEl) {
                        statusEl.textContent = data.status_label;
                        statusEl.className = 'status status-' + newStatus;
                    }
                }
            } else {
                showToast(data.error || 'Ошибка', 'danger');
            }
        })
        .catch(err => console.error('Ошибка:', err));
}

// ============================================================
// 7. AJAX: Получение статистики для админ-панели
// ============================================================
function loadDashboardStats() {
    const statsContainer = document.getElementById('dashboardStats');
    if (!statsContainer) return;

    fetch(APP_URL + '/admin/api/stats.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statsContainer.innerHTML = `
                    <div class="stat-card">
                        <div class="label">Всего клиентов</div>
                        <div class="value">${data.stats.total_members}</div>
                    </div>
                    <div class="stat-card success">
                        <div class="label">Активных карт</div>
                        <div class="value">${data.stats.active_cards}</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="label">Записей сегодня</div>
                        <div class="value">${data.stats.today_bookings}</div>
                    </div>
                    <div class="stat-card info">
                        <div class="label">Посещений за неделю</div>
                        <div class="value">${data.stats.week_visits}</div>
                    </div>
                `;
            }
        })
        .catch(err => console.error('Ошибка загрузки статистики:', err));
}

// ============================================================
// Вспомогательные функции
// ============================================================

function renderClassCards(classes) {
    if (!classes || classes.length === 0) {
        return '<p style="text-align:center;color:#6C757D;">Занятия не найдены</p>';
    }

    const categoryLabels = {
        yoga:'Йога', pilates:'Пилатес', cardio:'Кардио', strength:'Силовая',
        dance:'Танцы', crossfit:'CrossFit', boxing:'Бокс', stretching:'Стретчинг'
    };
    const dayNames = { 1:'Пн',2:'Вт',3:'Ср',4:'Чт',5:'Пт',6:'Сб',7:'Вс' };

    return classes.map(c => `
        <div class="card fade-in">
            <img class="card-img" src="${APP_URL}/assets/images/${c.image || 'class_default.png'}" alt="${c.name}">
            <div class="card-body">
                <span class="card-category">${categoryLabels[c.category] || c.category}</span>
                <h3 class="card-title">${c.name}</h3>
                <p class="card-text">${c.description ? c.description.substring(0, 80) + '...' : ''}</p>
                <div class="card-meta">
                    <span>${dayNames[c.day_of_week] || ''} ${c.start_time?.substring(0,5) || ''}</span>
                    <span>${c.trainer_name || ''}</span>
                </div>
                <button class="btn btn-primary btn-sm pulse-btn" style="margin-top:12px;width:100%"
                    data-class-id="${c.id}"
                    onclick="bookClass(${c.id}, '${getNextDate(c.day_of_week)}')">
                    Записаться
                </button>
            </div>
        </div>
    `).join('');
}

function getNextDate(dayOfWeek) {
    const today = new Date();
    const diff = ((dayOfWeek - today.getDay() + 7) % 7) || 7;
    const next = new Date(today);
    next.setDate(today.getDate() + diff);
    return next.toISOString().split('T')[0];
}

function showToast(message, type = 'info') {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:16px 24px;border-radius:8px;color:#fff;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.2);transition:all 0.3s ease;opacity:0;transform:translateY(-10px);';
        document.body.appendChild(toast);
    }
    const colors = { success:'#2A9D8F', danger:'#E76F51', info:'#457B9D', warning:'#E9C46A' };
    toast.style.background = colors[type] || colors.info;
    toast.textContent = message;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
    }, 3000);
}

// Инициализация
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('dashboardStats')) {
        loadDashboardStats();
    }
});
