/**
 * Events Management Module
 * Handles all client-side logic for event CRUD operations
 */

function exposeGlobal(name, value) {
    try {
        window[name] = value;
    } catch (e) {
        console.error(`Failed to expose "${name}" to window:`, e);
    }
}

// ===== GLOBAL STATE =====
const BASE_URL = '/schoolm/modules/college-coor/api';
const eventData = [];

function isEventsManagementPage() {
    return !!(
        document.getElementById('eventsTableBody') ||
        document.getElementById('eventForm') ||
        document.getElementById('addEventModal')
    );
}

// Modal functions
function openModal() {
    const modal = document.getElementById('addEventModal');
    if (!modal) return;
    modal.classList.add('show');
}

function closeModal() {
    const modal = document.getElementById('addEventModal');
    if (!modal) return;
    modal.classList.remove('show');
    resetEventForm();
}

// Initialize event manager (called both on page load and after dynamic page load)
function initializeEventManager() {
    if (!isEventsManagementPage()) {
        return;
    }

    // Get the form and reset its listener flag since it's a new element
    const form = document.getElementById('eventForm');
    if (form) {
        form.dataset.listenerAttached = 'false';
    }

    // Setup Add Event button
    const addBtn = document.getElementById('addEventBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            openAddEventModal();
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('addEventModal');
        if (modal && e.target === modal) {
            closeModal();
        }
    });

    attachFormListener();
    attachFilters();
    // Load templates for modal
    loadEventTemplates();

    // Always fetch fresh event data from API (important for dynamic page loads)
    refreshEventsData();
}

// Load templates via AJAX and populate the template dropdown
function loadEventTemplates() {
    const select = document.getElementById('templateSelect');
    if (!select) return;

    fetch(`${BASE_URL}/get_event_templates.php`)
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.templates)) {
                select.innerHTML = '<option value="">(none) - choose a template</option>';
                res.templates.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.template_id;
                    opt.textContent = (t.template_name || (`Template ${t.template_id}`)) + (t.event_type ? ` (${t.event_type})` : '');
                    opt.dataset.template = JSON.stringify(t);
                    select.appendChild(opt);
                });
            } else {
                select.innerHTML = '<option value="">(none) - no templates</option>';
            }
        })
        .catch(() => {
            select.innerHTML = '<option value="">(none) - no templates</option>';
        });
}

function applyTemplateToForm(t) {
    if (!t) return;
    if (t.default_title) document.getElementById('eventTitle').value = t.default_title;
    if (t.event_type) document.getElementById('eventType').value = t.event_type;
    if (t.default_description) document.getElementById('description').value = t.default_description;
    if (t.default_location) document.getElementById('location').value = t.default_location;
    if (t.default_target_audience) document.getElementById('targetAudience').value = t.default_target_audience;
    if (t.default_status) document.getElementById('eventStatus').value = t.default_status;
    if (typeof t.priority !== 'undefined' && document.getElementById('priority')) document.getElementById('priority').value = t.priority;
}

document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'templateSelect') {
        const v = e.target.value;
        if (!v) return;
        const opt = e.target.options[e.target.selectedIndex];
        if (!opt || !opt.dataset || !opt.dataset.template) return;
        try {
            const tmpl = JSON.parse(opt.dataset.template);
            applyTemplateToForm(tmpl);
        } catch (err) {
            console.error('Failed to parse template', err);
        }
    }
});

// ===============================
// MODAL CONTROL
// ===============================

// OPEN ADD MODAL (Clean State)
function openAddEventModal() {
    const form = document.getElementById('eventForm');

    if (!form) return;

    // Reset form
    form.reset();
    
    // Clear hidden ID
    document.getElementById('eventId').value = '';
    
    // Set title
    document.getElementById('eventModalTitle').textContent = 'Add New Event';

    // Reset submit button
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save me-2"></i> Save Event';

    // Show modal
    openModal();
}

// RESET FORM (on modal close)
function resetEventForm() {
    const form = document.getElementById('eventForm');
    if (!form) return;

    form.reset();
    document.getElementById('eventId').value = '';
    document.getElementById('eventModalTitle').textContent = 'Add New Event';

    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save me-2"></i> Save Event';

    // reset template and priority when closing
    const templateSelect = document.getElementById('templateSelect');
    if (templateSelect) templateSelect.value = '';
    const prioritySelect = document.getElementById('priority');
    if (prioritySelect) prioritySelect.value = '';
}

// ===============================
// FORM SUBMIT (ADD / UPDATE)
// ===============================
function attachFormListener() {
    const form = document.getElementById('eventForm');
    if (!form) {
        return;
    }

    // Prevent duplicate listener attachment
    if (form.dataset.listenerAttached === 'true') {
        return;
    }

    form.dataset.listenerAttached = 'true';

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        if (btn.disabled) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        // Get event ID - check it exists and is a valid number
        const eventIdElement = document.getElementById('eventId');
        
        if (!eventIdElement) {
            console.error('ERROR: eventId element not found!');
            throw new Error('Form element missing');
        }
        
        const eventIdValue = (eventIdElement.value || '').trim();
        const eventId = eventIdValue && eventIdValue !== '' ? parseInt(eventIdValue, 10) : 0;
        const isEdit = !isNaN(eventId) && eventId > 0;

        const payload = {
            event_title: document.getElementById('eventTitle').value,
            event_type: document.getElementById('eventType').value,
            event_date: document.getElementById('eventDate').value,
            start_time: document.getElementById('startTime').value,
            end_time: document.getElementById('endTime').value,
            location: document.getElementById('location').value,
            description: document.getElementById('description').value,
            target_audience: document.getElementById('targetAudience').value,
            status: document.getElementById('eventStatus').value,
            template_id: (document.getElementById('templateSelect') ? document.getElementById('templateSelect').value : null),
            priority: (document.getElementById('priority') ? document.getElementById('priority').value : null)
        };

        // Add event_id only for edits
        if (isEdit) {
            payload.event_id = eventId;
        }

        const url = isEdit
            ? `${BASE_URL}/update_event.php`
            : `${BASE_URL}/add_event.php`;

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showToast('Event saved successfully!', 'success');
                closeModal();
                notifyDashboardAlertsUpdate();
                refreshEventsData();
            } else {
                throw new Error(res.message || 'Unknown error occurred');
            }
        })
        .catch(err => {
            showToast('Error saving event: ' + err.message, 'danger');

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-2"></i> Save Event';
        });
    });
}

// ===============================
// DISPLAY TABLE
// ===============================
function displayEventsData(data = eventData) {
    const tbody = document.getElementById('eventsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!data || !data.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-calendar-times me-2"></i> No events found
                </td>
            </tr>
        `;
        return;
    }

    // helper: convert 24-hour time string to 12-hour format
    function convertTo12Hour(timeStr) {
        if (!timeStr) return '';
        // handle 'HH:MM:SS' or 'HH:MM'
        const t = timeStr.split(' ')[0];
        const parts = t.split(':');
        if (parts.length < 2) return timeStr;
        let h = parseInt(parts[0], 10);
        const m = parts[1];
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) h = 12;
        return h + ':' + m + ' ' + ampm;
    }

    data.forEach(e => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td><strong>${escapeHtml(e.event_title || '')}</strong></td>
            <td>${escapeHtml(e.event_type || '-')}</td>
            <td>${formatDate(e.event_date)}<br><small class="text-muted">${convertTo12Hour(e.start_time) || ''} - ${convertTo12Hour(e.end_time) || ''}</small></td>
            <td>${escapeHtml(e.location || '-')}</td>
            <td>${escapeHtml(e.target_audience || '-')}</td>
            <td>${renderStatusBadge(e.status)}</td>
            <td>
                <button class="btn btn-info btn-sm" onclick="editEvent(${e.event_id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-warning btn-sm" onclick="printEvent(${e.event_id})">
                    <i class="fas fa-print"></i> Print
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// EDIT EVENT
function editEvent(id) {
    const event = eventData.find(e => e.event_id == id);
    
    if (!event) {
        console.error('Event not found in eventData for ID:', id);
        return;
    }

    const eventIdInput = document.getElementById('eventId');
    
    if (!eventIdInput) {
        console.error('ERROR: eventId input not found!');
        return;
    }

    eventIdInput.value = event.event_id;
    
    document.getElementById('eventTitle').value = event.event_title || '';
    document.getElementById('eventType').value = event.event_type || '';
    document.getElementById('eventDate').value = event.event_date || '';
    document.getElementById('startTime').value = event.start_time || '';
    document.getElementById('endTime').value = event.end_time || '';
    document.getElementById('location').value = event.location || '';
    document.getElementById('description').value = event.description || '';
    document.getElementById('targetAudience').value = event.target_audience || '';
    document.getElementById('eventStatus').value = event.status || '';
    const templateSelect = document.getElementById('templateSelect');
    if (templateSelect) templateSelect.value = event.template_id || '';
    const prioritySelect = document.getElementById('priority');
    if (prioritySelect) prioritySelect.value = event.priority || '';

    document.getElementById('eventModalTitle').textContent = 'Edit Event';

    openModal();
}

// DELETE EVENT
function deleteEvent(id) {
    if (!confirm('Are you sure you want to delete this event?')) return;

    fetch(`${BASE_URL}/delete_event.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ event_id: id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            showToast('Event deleted successfully!', 'success');
            notifyDashboardAlertsUpdate();
            refreshEventsData();
        } else {
            throw new Error(res.message || 'Delete failed');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error deleting event', 'danger');
    });
}

// ===============================
// FILTERS
// ===============================
function attachFilters() {
    const searchInput = document.getElementById('searchInput');
    const filterType = document.getElementById('filterType');
    const filterStatus = document.getElementById('filterStatus');
    
    if (searchInput) searchInput.addEventListener('keyup', filterEvents);
    if (filterType) filterType.addEventListener('change', filterEvents);
    if (filterStatus) filterStatus.addEventListener('change', filterEvents);
}

function filterEvents() {
    const searchInput = document.getElementById('searchInput');
    const filterType = document.getElementById('filterType');
    const filterStatus = document.getElementById('filterStatus');
    
    const search = searchInput ? searchInput.value.toLowerCase() : '';
    const type = filterType ? filterType.value : '';
    const status = filterStatus ? filterStatus.value : '';

    const filtered = eventData.filter(e =>
        (!search || (e.event_title && e.event_title.toLowerCase().includes(search))) &&
        (!type || e.event_type === type) &&
        (!status || e.status === status)
    );

    displayEventsData(filtered);
}

// ===============================
// REFRESH DATA
// ===============================
function refreshEventsData() {
    fetch(`${BASE_URL}/get_events.php`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                eventData.length = 0;
                eventData.push(...(res.events || []));
                displayEventsData();
                updateEventStatistics();
            }
        })
        .catch(err => {
            showToast('Failed to refresh events', 'danger');
        });
}

// ===============================
// STATISTICS
// ===============================
function updateEventStatistics() {
    const totalEl = document.getElementById('totalEvents');
    const upcomingEl = document.getElementById('upcomingEvents');
    const ongoingEl = document.getElementById('ongoingEvents');
    const completedEl = document.getElementById('completedEvents');
    
    if (!eventData) return;
    
    const total = eventData.length;
    const scheduled = eventData.filter(e => e.status === 'upcoming').length;
    const ongoing = eventData.filter(e => e.status === 'ongoing').length;
    const completed = eventData.filter(e => e.status === 'completed').length;

    if (totalEl) totalEl.textContent = total;
    if (upcomingEl) upcomingEl.textContent = scheduled;
    if (ongoingEl) ongoingEl.textContent = ongoing;
    if (completedEl) completedEl.textContent = completed;
}

// ===============================
// HELPERS
// ===============================
function notifyDashboardAlertsUpdate() {
    try {
        if (window.localStorage) {
            localStorage.setItem('ccDashboardAlertsRefresh', String(Date.now()));
        }
        window.dispatchEvent(new CustomEvent('cc:dashboard-alerts-refresh', {
            detail: { source: 'events-management' }
        }));
    } catch (error) {
        console.warn('Unable to notify dashboard alerts:', error);
    }
}

function renderStatusBadge(status) {
    const map = {
        'upcoming': 'secondary',
        'ongoing': 'primary',
        'completed': 'success',
        'cancelled': 'danger'
    };

    const color = map[status] || 'secondary';
    return `<span class="badge bg-${color}">${escapeHtml(status || 'Unknown')}</span>`;
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        return new Date(dateStr).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        return dateStr;
    }
}

function formatTimeForDisplay(timeStr) {
    if (!timeStr) return 'TBA';

    const normalized = timeStr.toString().trim();
    const timeParts = normalized.split(' ')[0].split(':');
    if (timeParts.length < 2) return normalized;

    let hours = parseInt(timeParts[0], 10);
    const minutes = timeParts[1];
    const suffix = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    if (hours === 0) hours = 12;

    return `${hours}:${minutes} ${suffix}`;
}

function getStatusClassName(status) {
    const normalized = (status || 'upcoming').toLowerCase();
    if (normalized === 'ongoing') return 'status-ongoing';
    if (normalized === 'completed') return 'status-completed';
    if (normalized === 'cancelled') return 'status-cancelled';
    return 'status-upcoming';
}

function cleanupPrintView() {
    const wrapper = document.getElementById('eventPrintWrapper');
    if (wrapper && wrapper.parentNode) {
        wrapper.parentNode.removeChild(wrapper);
    }
}

function printEvent(id) {
    const event = eventData.find(e => Number(e.event_id) === Number(id));

    if (!event) {
        showToast('Event not found', 'danger');
        return;
    }

    cleanupPrintView();

    const eventTitle = escapeHtml(event.event_title || 'Untitled Event');
    const eventDate = escapeHtml(new Date(event.event_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }));
    const venue = escapeHtml(event.location || 'To be announced');
    const timeRange = escapeHtml(
        `${formatTimeForDisplay(event.start_time || '')} - ${formatTimeForDisplay(event.end_time || '')}`
            .replace('TBA - TBA', 'TBA')
            .replace(/^-|-$|\s+-\s+/g, '')
    );
    const audience = escapeHtml(event.target_audience || 'General Public');
    const description = (event.description || 'No description provided.').replace(/\n/g, '<br><br>');
    const printedDate = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const wrapper = document.createElement('div');
    wrapper.id = 'eventPrintWrapper';
    wrapper.className = 'event-print-wrapper';
    wrapper.innerHTML = `
        <div class="event-print-sheet">
            <div class="event-print-header">
                <div class="event-print-brand">
                    <div class="event-print-logo">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="event-print-brand-text">
                        <div class="event-print-institution">BESTLINK COLLEGE OF THE PHILIPPINES</div>
                        <div class="event-print-subtitle">College Coordinator Office</div>
                    </div>
                </div>
            </div>

            <div class="event-print-banner">
                <i class="fas fa-calendar-alt"></i>
                <span>EVENT NOTICE</span>
            </div>

            <div class="event-print-card">
                <div class="event-print-info-grid">
                    <div class="event-print-info-item">
                        <div class="event-print-icon-wrap">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="event-print-info-text">
                            <div class="event-print-label">Event Title</div>
                            <div class="event-print-value event-print-title-value">${eventTitle}</div>
                        </div>
                    </div>

                    <div class="event-print-info-item">
                        <div class="event-print-icon-wrap">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="event-print-info-text">
                            <div class="event-print-label">Time</div>
                            <div class="event-print-value">${timeRange}</div>
                        </div>
                    </div>

                    <div class="event-print-info-item">
                        <div class="event-print-icon-wrap">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="event-print-info-text">
                            <div class="event-print-label">Date</div>
                            <div class="event-print-value">${eventDate}</div>
                        </div>
                    </div>

                    <div class="event-print-info-item">
                        <div class="event-print-icon-wrap">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="event-print-info-text">
                            <div class="event-print-label">Target Audience</div>
                            <div class="event-print-value">${audience}</div>
                        </div>
                    </div>

                    <div class="event-print-info-item full-width-item">
                        <div class="event-print-icon-wrap">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="event-print-info-text">
                            <div class="event-print-label">Venue</div>
                            <div class="event-print-value">${venue}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="event-print-description-block">
                <div class="event-print-description-banner">
                    <i class="fas fa-file-alt"></i>
                    <span>EVENT DESCRIPTION</span>
                </div>
                <div class="event-print-description">${description}</div>
            </div>

            <div class="event-print-footer">
                <div class="event-print-footer-signature">
                    <div class="event-print-footer-label">Prepared by:</div>
                    <div class="event-print-signature-line"></div>
                    <div class="event-print-footer-name">College Coordinator</div>
                </div>

                <div class="event-print-footer-date">
                    <div class="event-print-date-row">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Date Printed:</span>
                    </div>
                    <div class="event-print-date-value">${printedDate}</div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(wrapper);

    setTimeout(() => {
        window.print();
    }, 150);
}

window.addEventListener('afterprint', cleanupPrintView);

// ===============================
// TOAST NOTIFICATION
// ===============================
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toastContainer');
    if (!container) {
        return;
    }
    
    const toastId = 'toast-' + Date.now();
    
    const html = `<div id="${toastId}" class="toast ${type}">${message}</div>`;
    
    container.insertAdjacentHTML('beforeend', html);
    
    // Auto remove after specified duration
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.style.animation = 'slideOutTop 0.3s ease-out';
            setTimeout(() => {
                if (toast && toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, duration);
}

// ===============================
// PAGE LOAD & INITIALIZATION
// ===============================

// Load eventData from preload blob when page loads dynamically
window.addEventListener('page:loaded', function(e) {
    if (e.detail?.page !== 'events-management') return;

    try {
        const blob = document.getElementById('preload-eventData');
        if (blob && blob.textContent) {
            try {
                eventData.length = 0;
                eventData.push(...(JSON.parse(blob.textContent) || []));
                console.log('Loaded preload eventData:', eventData);
            } catch (err) {
                console.error('Failed to parse preload-eventData JSON', err);
            }
        }
    } catch (e) {
        // ignore
    }

    initializeEventManager();
});

// Load eventData from preload blob on initial page load
document.addEventListener('DOMContentLoaded', function() {
    if (!isEventsManagementPage()) {
        return;
    }

    try {
        const blob = document.getElementById('preload-eventData');
        if (blob && blob.textContent) {
            try {
                eventData.length = 0;
                eventData.push(...(JSON.parse(blob.textContent) || []));
            } catch (err) {
                console.error('Failed to parse preload-eventData JSON', err);
            }
        }
    } catch (e) {
        // ignore
    }

    initializeEventManager();
});

// ===============================
// EXPOSE FUNCTIONS TO WINDOW
// ===============================
// Required for inline onclick handlers in HTML
exposeGlobal('openModal', openModal);
exposeGlobal('closeModal', closeModal);
exposeGlobal('resetEventForm', resetEventForm);
exposeGlobal('openAddEventModal', openAddEventModal);
exposeGlobal('editEvent', editEvent);
exposeGlobal('deleteEvent', deleteEvent);
exposeGlobal('printEvent', printEvent);
exposeGlobal('filterEvents', filterEvents);
exposeGlobal('showToast', showToast);
exposeGlobal('initializeEventManager', initializeEventManager);
