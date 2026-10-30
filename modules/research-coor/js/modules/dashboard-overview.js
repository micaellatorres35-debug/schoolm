(function () {
    'use strict';

    let currentDate = new Date();
    let readNotificationIds = [];
    let facultyLoadChart = null;
    let activityTimer = null;
    let dashboardTimer = null;
    const urgentToastShownKey = 'ccUrgentToastShownIds';
    const readNotificationKey = 'ccReadNotificationIds';

    const dashboard = () => ({
        eventsByDate: window.eventsByDate || {},
        notificationEvents: window.notificationEvents || [],
        chartData: window.chartData || {},
        serverTimestamp: window.serverTimestamp || new Date().toISOString(),
        allActivities: window.allActivities || []
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'};
        return String(text).replace(/[&<>"']/g, character => map[character]);
    }

    function convertTo12Hour(timeStr) {
        if (!timeStr) return '';
        const [hours, minutes] = String(timeStr).substring(0, 5).split(':');
        let hour = parseInt(hours, 10);
        const suffix = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        return `${hour}:${minutes || '00'} ${suffix}`;
    }

    function renderCalendar() {
        const header = document.getElementById('monthYear');
        const days = document.getElementById('calendarDays');
        if (!header || !days) return;
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const events = dashboard().eventsByDate;
        header.textContent = `${names[month]} ${year}`;
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const previousDays = new Date(year, month, 0).getDate();
        days.innerHTML = '';
        for (let i = firstDay - 1; i >= 0; i--) {
            const el = document.createElement('div');
            el.className = 'calendar-day other-month';
            el.innerHTML = `<div class="calendar-day-number">${previousDays - i}</div>`;
            days.appendChild(el);
        }
        for (let day = 1; day <= daysInMonth; day++) {
            const el = document.createElement('div');
            const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            el.className = 'calendar-day';
            const today = new Date();
            if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) el.classList.add('today');
            let html = `<div class="calendar-day-number">${day}</div>`;
            if (events[date]) {
                const event = events[date][0];
                const title = typeof event === 'string' ? event : (event.title || 'Event');
                html += `<div class="calendar-events"><span class="calendar-event-dot"></span> ${escapeHtml(title)}</div>`;
            }
            el.innerHTML = html;
            el.onclick = () => showEventsModal(date, day);
            days.appendChild(el);
        }
        for (let day = 1; day <= 42 - days.children.length; day++) {
            const el = document.createElement('div');
            el.className = 'calendar-day other-month';
            el.innerHTML = `<div class="calendar-day-number">${day}</div>`;
            days.appendChild(el);
        }
    }

    function previousMonth() { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); }
    function nextMonth() { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); }

    function showEventsModal(dateStr) {
        const modal = document.getElementById('eventsModal');
        const title = document.getElementById('modalDateTitle');
        const list = document.getElementById('modalEventsList');
        if (!modal || !title || !list) return;
        const date = new Date(dateStr + 'T00:00:00');
        title.textContent = date.toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'});
        const events = dashboard().eventsByDate[dateStr] || [];
        list.innerHTML = events.length ? events.map(event => {
            if (typeof event === 'string') {
                const parts = event.split(' - ');
                event = {title: parts[0], time: parts[1] || '', type: 'Academic', status: 'upcoming'};
            }
            const status = event.status || 'upcoming';
            const colors = {completed:'#10b981', cancelled:'#ef4444', ongoing:'#f59e0b'};
            const color = colors[status] || '#3b82f6';
            let html = `<div class="modal-event-item"><div style="display:flex;justify-content:space-between;align-items:start;gap:10px;"><div style="flex:1;"><div class="modal-event-title">${escapeHtml(event.title)}</div><div style="font-size:12px;color:#9ca3af;margin-top:2px;"><span style="background:${color};color:white;padding:2px 8px;border-radius:3px;font-size:11px;">${escapeHtml(event.type || 'Academic')}</span></div></div><span style="background:${color};color:white;padding:4px 8px;border-radius:3px;font-size:11px;white-space:nowrap;">${escapeHtml(status)}</span></div>`;
            if (event.time) html += `<div class="modal-event-desc"><strong>Time:</strong> ${escapeHtml(convertTo12Hour(event.time))}${event.endTime ? ' - ' + escapeHtml(convertTo12Hour(event.endTime)) : ''}</div>`;
            if (event.location) html += `<div class="modal-event-desc"><strong>Location:</strong> ${escapeHtml(event.location)}</div>`;
            if (event.description) html += `<div class="modal-event-desc"><strong>Description:</strong> ${escapeHtml(event.description)}</div>`;
            if (event.audience) html += `<div class="modal-event-desc"><strong>Target Audience:</strong> ${escapeHtml(event.audience)}</div>`;
            return html + '</div>';
        }).join('') : '<div class="modal-event-empty">No events scheduled for this day</div>';
        modal.style.display = 'flex';
    }

    function closeEventsModal() {
        const modal = document.getElementById('eventsModal');
        if (modal) modal.style.display = 'none';
    }

    function viewAlertDetails(button) {
        if (button && button.dataset && button.dataset.date) showEventsModal(button.dataset.date, button.dataset.day);
    }

    function getStoredIds(key) {
        try {
            const value = JSON.parse(localStorage.getItem(key)) || [];
            return Array.isArray(value) ? value.map(String) : [];
        } catch (error) { return []; }
    }

    function setStoredIds(key, ids) {
        localStorage.setItem(key, JSON.stringify([...new Set(ids.map(String))]));
    }

    function closeNotificationDropdown() {
        document.getElementById('bellDropdown')?.classList.remove('open');
    }

    function updateBellBadge() {
        const badge = document.getElementById('notifBadge');
        if (!badge) return;
        const unread = document.querySelectorAll('#bellDropdown .notif-item.unread').length;
        badge.textContent = unread;
        badge.classList.toggle('hidden', unread === 0);
    }

    function markNotificationRead(id) {
        id = String(id);
        if (!readNotificationIds.includes(id)) {
            readNotificationIds.push(id);
            setStoredIds(readNotificationKey, readNotificationIds);
        }
        document.querySelectorAll('#bellDropdown .notif-item').forEach(item => {
            if (String(item.dataset.eventId) === id) {
                item.classList.remove('unread');
                item.querySelector('.notif-unread-dot')?.remove();
            }
        });
        updateBellBadge();
    }

    function renderNotificationBell() {
        const list = document.querySelector('#bellDropdown .notif-list');
        if (!list) return;
        const events = [...dashboard().notificationEvents].sort((a, b) => (b.notification_created_iso || '').localeCompare(a.notification_created_iso || ''));
        if (!events.length) {
            list.innerHTML = '<li class="notif-empty">No notifications yet</li>';
            updateBellBadge();
            return;
        }
        list.innerHTML = events.map(event => {
            const unread = !readNotificationIds.includes(String(event.id));
            return `<li class="notif-item${unread ? ' unread' : ''}" data-event-id="${escapeHtml(event.id)}" data-date="${escapeHtml(event.date)}" data-day="${escapeHtml(event.day)}" data-priority="${escapeHtml(event.priority)}" data-start="${escapeHtml(event.start_iso || '')}" data-end="${escapeHtml(event.end_iso || '')}" data-created="${escapeHtml(event.notification_created_iso || '')}"><button type="button" class="notif-link" data-event-id="${escapeHtml(event.id)}" data-date="${escapeHtml(event.date)}" data-day="${escapeHtml(event.day)}"><div class="notif-left"><span class="notif-icon">${event.icon || '🔔'}</span><div class="notif-text"><span class="notif-title">${escapeHtml(event.title)}</span><span class="notif-status">${escapeHtml(event.remaining)}</span></div></div><div class="notif-right"><span class="notif-time">${escapeHtml(event.notification || '')}</span>${unread ? '<span class="notif-unread-dot"></span>' : ''}</div></button></li>`;
        }).join('');
        list.querySelectorAll('.notif-link').forEach(link => link.addEventListener('click', function () {
            markNotificationRead(this.dataset.eventId);
            closeNotificationDropdown();
            showEventsModal(this.dataset.date, this.dataset.day);
        }));
        updateBellBadge();
    }

    function hasToastBeenShown(id) { return getStoredIds(urgentToastShownKey).includes(String(id)); }

    function markToastShown(id) {
        const ids = getStoredIds(urgentToastShownKey);
        if (!ids.includes(String(id))) {
            ids.push(String(id));
            setStoredIds(urgentToastShownKey, ids);
        }
    }

    async function showUrgentToasts() {
        for (const event of dashboard().notificationEvents.filter(item => item.priority === 'urgent')) {
            if (hasToastBeenShown(event.id)) continue;
            markToastShown(event.id);
            createToast(event);
            await new Promise(resolve => setTimeout(resolve, 4400));
        }
    }

    function createToast(event) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'cc-toast show urgent';
        toast.dataset.start = event.start_iso || '';
        toast.dataset.end = event.end_iso || '';
        toast.innerHTML = `<div class="toast-content"><div class="toast-title">Urgent Event</div><div class="toast-message">${escapeHtml(event.title)}</div><div class="toast-countdown">${escapeHtml(event.remaining)}</div></div><button type="button" class="toast-close" aria-label="Close">&times;</button>`;
        toast.addEventListener('click', e => {
            if (e.target.closest('.toast-close')) return;
            markNotificationRead(event.id);
            viewAlertDetails({dataset: {date: event.date, day: event.day}});
        });
        toast.querySelector('.toast-close').addEventListener('click', e => {
            e.stopPropagation();
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function viewAllPriorityAlerts() {
        document.querySelector('.priority-alerts-section')?.scrollIntoView({behavior:'smooth', block:'start'});
    }

    function getServerNow() {
        const serverMs = new Date(dashboard().serverTimestamp).getTime();
        const loadTime = window.dashboardClientLoadTs || Date.now();
        return new Date(serverMs + Date.now() - loadTime);
    }

    function getRemainingText(startIso, endIso) {
        if (!startIso) return '';
        const start = new Date(startIso);
        const end = endIso ? new Date(endIso) : null;
        const now = getServerNow();
        if (now < start) {
            const minutes = Math.floor((start - now) / 60000);
            if (minutes < 1) return 'Starts within 1 Minute';
            if (minutes < 60) return `Starts in ${minutes} Minute${minutes === 1 ? '' : 's'}`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `Starts in ${hours} Hour${hours === 1 ? '' : 's'}${minutes % 60 ? ' ' + minutes % 60 + ' Min' : ''}`;
            const days = Math.ceil(minutes / 1440);
            return `Starts in ${days} Day${days === 1 ? '' : 's'}`;
        }
        if (end && now < end) return '🟢 Ongoing';
        return 'Completed';
    }

    function updatePriorityAlertTimes() {
        document.querySelectorAll('.alert-item').forEach(element => {
            const start = element.dataset.start;
            if (!start) return;
            const text = getRemainingText(start, element.dataset.end);
            const time = element.querySelector('.alert-time');
            if (time) time.textContent = text;
            element.style.opacity = text === 'Completed' ? '0.6' : '1';
        });
    }

    function formatExactTime(date) {
        const hour = date.getHours();
        return `${hour % 12 || 12}:${String(date.getMinutes()).padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
    }

    function getNotificationTimeText(createdIso) {
        if (!createdIso) return '';
        const created = new Date(createdIso);
        const difference = getServerNow() - created;
        if (difference < 0) return formatExactTime(created);
        const minutes = Math.floor(difference / 60000);
        if (minutes < 1) return formatExactTime(created);
        if (minutes < 60) return `${minutes}m ago`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours}h ago`;
        const days = Math.floor(hours / 24);
        return days < 7 ? `${days}d ago` : `${Math.floor(days / 7)}w ago`;
    }

    function updateNotificationTimes() {
        document.querySelectorAll('.notif-item').forEach(element => {
            const time = element.querySelector('.notif-time');
            if (element.dataset.created && time) time.textContent = getNotificationTimeText(element.dataset.created);
        });
    }

    function updateToastCountdowns() {
        document.querySelectorAll('.cc-toast').forEach(element => {
            const countdown = element.querySelector('.toast-countdown');
            if (countdown) countdown.textContent = getRemainingText(element.dataset.start, element.dataset.end);
        });
    }

    function formatActivityElapsedTime(value) {
        const timestamp = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(timestamp.getTime())) return 'Unknown time';
        const seconds = Math.max(0, Math.floor((Date.now() - timestamp.getTime()) / 1000));
        if (seconds < 60) return Math.max(1, seconds) + ' seconds ago';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
        return timestamp.toLocaleString();
    }

    function updateRecentActivityTimes() {
        document.querySelectorAll('.activity-time').forEach(element => {
            element.textContent = formatActivityElapsedTime(element.dataset.timestamp);
        });
    }

    function openActivitiesModal() {
        const modal = document.getElementById('activitiesModal');
        const list = document.getElementById('allActivitiesList');
         if (modal.parentElement !== document.body) { document.body.appendChild(modal); } 
        if (!modal || !list) return;
        const activities = dashboard().allActivities;
        list.innerHTML = activities.length ? activities.map(activity => `<div style="padding:12px;border-radius:5px;background-color:#f0f9ff;border-left:4px solid #3b82f6;margin-bottom:8px;"><div style="font-weight:600;color:#1f2937;font-size:14px;">${escapeHtml(activity.type)}</div><div style="color:#4b5563;font-size:13px;margin:4px 0;">${escapeHtml(activity.description)}</div><div><span style="font-size:11px;color:#6b7280;">${escapeHtml(activity.module)}</span> <span style="font-size:11px;color:#9ca3af;">${formatActivityElapsedTime(activity.timestamp)}</span></div></div>`).join('') : '<div style="padding:20px;text-align:center;color:#999;">No recent activities yet</div>';
        modal.style.display = 'flex';
    }

    function closeActivitiesModal() {
        const modal = document.getElementById('activitiesModal');
        if (modal) modal.style.display = 'none';
    }

    function computeLoadCounts(units, maxLoad = 15) {
        return units.reduce((counts, value) => {
            const total = Number(value) || 0;
            if (total < maxLoad) counts.under++;
            else if (total === maxLoad) counts.full++;
            else counts.over++;
            return counts;
        }, {under:0, full:0, over:0});
    }

    function updateFaultyLoadSummary(counts) {
        const element = document.getElementById('faultyLoadSummary');
        if (element) element.textContent = `Faculty Load: ${counts.under + counts.full + counts.over}`;
    }

    function chart(id, config) {
        const canvas = document.getElementById(id);
        if (!canvas || typeof Chart === 'undefined') return null;
        const existing = Chart.getChart(canvas);
        if (existing) existing.destroy();
        return new Chart(canvas.getContext('2d'), config);
    }

    function initStudentsPerProgramChart() {
        const data = dashboard().chartData;
        chart('studentsPerProgramChart', {type:'line', data:{labels:data.programLabels || [], datasets:[{label:'Number of Students',data:data.programData || [],borderColor:'#3498db',backgroundColor:'rgba(52,152,219,.1)',borderWidth:3,fill:true,tension:.4,pointBackgroundColor:'#3498db',pointBorderColor:'#fff',pointBorderWidth:2,pointRadius:5,pointHoverRadius:7}]}, options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:true,position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
    }

    function initFacultyLoadChart(initialUnits) {
        const counts = computeLoadCounts(initialUnits || dashboard().chartData.facultyUnits || []);
        updateFaultyLoadSummary(counts);
        facultyLoadChart = chart('facultyLoadChart', {type:'bar',data:{labels:['Underloaded','Fully Loaded','Overloaded'],datasets:[{label:'Faculty Count',data:[counts.under,counts.full,counts.over],backgroundColor:['#f59e0b','#10b981','#ef4444'],borderColor:['#d97706','#059669','#dc2626'],borderWidth:2}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:true,position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
    }

    function initStudentStatusChart() {
        const data = dashboard().chartData;
        chart('studentStatusChart', {type:'pie',data:{labels:data.statusLabels || [],datasets:[{data:data.statusCounts || [],backgroundColor:['#2ecc71','#e74c3c','#95a5a6'],borderColor:'#fff',borderWidth:2}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:true,position:'bottom'}}}});
    }

    async function refreshFacultyLoadChart() {
        try {
            // Use optimized API endpoint for distribution data
            const response = await fetch('/schoolm/modules/college-coor/api/get_faculty_load_distribution.php', {credentials:'same-origin'});
            if (!response.ok) throw new Error('Failed to load faculty load distribution');
            const distribution = await response.json();
            
            // Handle error response from API
            if (distribution.error) {
                throw new Error(distribution.error);
            }
            
            // Update chart with distribution data
            const counts = {
                under: distribution.underloaded || 0,
                full: distribution.fully_loaded || 0,
                over: distribution.overloaded || 0
            };
            
            updateFaultyLoadSummary(counts);
            
            // Initialize chart if it doesn't exist
            if (!facultyLoadChart) {
                initFacultyLoadChart([]);
                facultyLoadChart.data.datasets[0].data = [counts.under, counts.full, counts.over];
                facultyLoadChart.update();
                return;
            }
            
            // Update chart data
            facultyLoadChart.data.datasets[0].data = [counts.under, counts.full, counts.over];
            facultyLoadChart.update();
        } catch (error) {
            console.error('Error refreshing faculty load chart:', error);
        }
    }

    function initDashboardCharts() {
        const data = dashboard().chartData;
        initStudentsPerProgramChart();
        initFacultyLoadChart(data.facultyUnits || []);
        initStudentStatusChart();
        refreshFacultyLoadChart();
    }

    function initializeDashboard() {
        if (!document.getElementById('calendarDays')) return;
        window.dashboardClientLoadTs = window.dashboardClientLoadTs || Date.now();
        readNotificationIds = getStoredIds(readNotificationKey);
        renderCalendar();
        renderNotificationBell();
        updatePriorityAlertTimes();
        updateNotificationTimes();
        updateToastCountdowns();
        updateRecentActivityTimes();
        showUrgentToasts();
        initDashboardCharts();
        if (!activityTimer) activityTimer = setInterval(updateRecentActivityTimes, 1000);
        if (!dashboardTimer) dashboardTimer = setInterval(() => { updatePriorityAlertTimes(); updateNotificationTimes(); updateToastCountdowns(); }, 60000);
        if (!window.facultyLoadRefreshTimer) window.facultyLoadRefreshTimer = window.setInterval(refreshFacultyLoadChart, 30000);
        if (sessionStorage.getItem('refreshFacultyLoad') === 'true') {
            sessionStorage.removeItem('refreshFacultyLoad');
            refreshFacultyLoadChart();
        }
    }

    window.previousMonth = previousMonth;
    window.nextMonth = nextMonth;
    window.showEventsModal = showEventsModal;
    window.closeEventsModal = closeEventsModal;
    window.viewAlertDetails = viewAlertDetails;
    window.openActivitiesModal = openActivitiesModal;
    window.closeActivitiesModal = closeActivitiesModal;
    window.viewAllPriorityAlerts = viewAllPriorityAlerts;
    window.refreshFacultyLoadChart = refreshFacultyLoadChart;
    window.initDashboardCharts = initDashboardCharts;
    window.reinitializeDashboard = function () {
        initDashboardCharts();
        renderCalendar();
        renderNotificationBell();
        updateRecentActivityTimes();
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeDashboard, {once:true});
    else initializeDashboard();

    window.addEventListener('page:loaded', event => {
        if (!event.detail || event.detail.page === 'dashboard-overview') initializeDashboard();
    });

    window.addEventListener('storage', event => {
        if (event.key === 'refreshFacultyLoad' && event.newValue === 'true') refreshFacultyLoadChart();
        if (event.key === readNotificationKey) {
            readNotificationIds = getStoredIds(readNotificationKey);
            renderNotificationBell();
        }
    });

    document.addEventListener('click', event => {
        if (event.target === document.getElementById('eventsModal')) closeEventsModal();
        if (event.target === document.getElementById('activitiesModal')) closeActivitiesModal();
    });
})();
