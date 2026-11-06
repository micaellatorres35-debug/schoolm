function initGenEdEventsDashboard() {
    const root = document.getElementById('genedEventsDashboard');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';

    const eventsApiUrl = new URL('api/events_dashboard.php', window.location.href);
    const activitiesApiUrl = new URL('api/academic_activities.php', window.location.href);
    const modal = root.querySelector('#addEventModal');
    const detailModal = root.querySelector('#eventDetailsModal');
    const form = root.querySelector('#eventForm');
    const addButton = root.querySelector('#addEventBtn');
    const tableBody = root.querySelector('#genedEventsTableBody');
    const state = { dashboard: null, events: [], category: '' };

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);

    const showToast = (message, isError = false) => {
        const container = root.querySelector('#toastContainer');
        const toast = document.createElement('div');
        toast.className = `ge-toast${isError ? ' ge-toast-error' : ''}`;
        toast.textContent = message;
        container.appendChild(toast);
        window.setTimeout(() => toast.remove(), 4500);
    };

    const request = async (url, options = {}) => {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                ...(options.headers || {}),
                ...(options.body ? { 'Content-Type': 'application/json' } : {})
            }
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            throw new Error(payload.message || `Request failed (${response.status}).`);
        }
        return payload;
    };

    const formatDate = value => {
        if (!value) return 'Date not set';
        const date = new Date(`${value}T00:00:00`);
        return Number.isNaN(date.getTime())
            ? value
            : date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const formatTime = value => {
        if (!value) return '';
        const [hour, minute] = value.slice(0, 5).split(':').map(Number);
        const date = new Date();
        date.setHours(hour, minute, 0, 0);
        return date.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
    };

    const statusClass = status => {
        if (status === 'Pending Approval' || status === 'Pending') return 'ge-status-pending';
        if (status === 'Approved') return 'ge-status-approved';
        if (status === 'Completed') return 'ge-status-completed';
        if (status === 'Rejected' || status === 'Returned') return 'ge-status-returned';
        return 'ge-status-neutral';
    };

    const displayStatus = event => {
        if (event.status === 'Pending Approval') return 'Pending';
        if (event.status === 'Rejected') return 'Returned';
        if (event.status === 'Draft') return 'Draft';
        if (event.status === 'Completed' || event.event_status === 'completed') return 'Completed';
        if (event.status === 'Approved') return 'Approved';
        return event.status || event.event_status || 'Unknown';
    };

    const eventCategory = event => {
        if (event.event_category) return event.event_category;
        const text = `${event.title || ''} ${event.event_type || ''} ${event.description || ''}`.toLowerCase();
        if (/buwan ng wika|filipin|language|literary|literature|balagtasan|poetry|essay|writing|slogan/.test(text)) return 'Language & Literature';
        if (/rizal|history|historical|culture|cultural|art appreciation/.test(text)) return 'History & Culture';
        if (/science|technology|sts|math|nutrition/.test(text)) return 'Science & Math';
        if (/ethic|value|integrity|citizenship/.test(text)) return 'Values & Ethics';
        if (/sports|sport|pe event|intramural|dance competition/.test(text)) return 'Sports';
        if (/nstp|community|outreach|clean-up|tree planting|service/.test(text)) return 'Community';
        return 'Academic Support';
    };

    const actionButtons = item => {
        const canApprove = state.dashboard?.can_approve;
        const isPending = item.status === 'Pending Approval';
        const isApproved = item.status === 'Approved';
        const canManage = state.dashboard?.can_create;
        const isOwner = Number(item.submitted_by) === Number(state.dashboard?.current_employee_id);
        const actions = [];
        if ((item.status === 'Draft' || item.status === 'Rejected') && (isOwner || canManage)) {
            actions.push(`<button type="button" class="ge-action-button ge-action-submit" data-source="event" data-action="submit" data-id="${Number(item.id)}">Submit</button>`);
        }
        if (isPending && canApprove) {
            actions.push(`<button type="button" class="ge-action-button ge-action-approve" data-source="event" data-action="approve" data-id="${Number(item.id)}">Approve</button>`);
            actions.push(`<button type="button" class="ge-action-button ge-action-reject" data-source="event" data-action="reject" data-id="${Number(item.id)}">Reject</button>`);
        } else if (isApproved && (isOwner || canApprove)) {
            actions.push(`<button type="button" class="ge-action-button ge-action-complete" data-source="event" data-action="complete" data-id="${Number(item.id)}">Complete</button>`);
        }
        if (isOwner || canManage) {
            actions.push(`<button type="button" class="ge-action-button ge-action-archive" data-source="event" data-action="archive" data-id="${Number(item.id)}">Archive</button>`);
        }
        return `<div class="ge-event-row-actions">${actions.join('')}</div>`;
    };

    const renderEvents = () => {
        const events = state.events.filter(event => {
            return !state.category || eventCategory(event) === state.category;
        });

        if (!events.length) {
            tableBody.innerHTML = `<tr><td colspan="7" class="ge-event-empty">${state.events.length ? 'No events in this category.' : 'No events have been added yet.'}</td></tr>`;
            return;
        }

        tableBody.innerHTML = events.map(event => {
            const eventDate = new Date(`${event.event_date}T00:00:00`);
            const formattedDate = Number.isNaN(eventDate.getTime()) ? escapeHtml(event.event_date || '—') : eventDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
            const submittedBy = event.submitted_by_name || '—';
            const status = displayStatus(event);
            return `
                <tr>
                    <td><strong>${escapeHtml(event.title)}</strong><small>${escapeHtml(event.event_type || 'GenEd event')}</small></td>
                    <td>${escapeHtml(eventCategory(event))}</td>
                    <td><span>${formattedDate}${event.start_time ? `, ${escapeHtml(formatTime(event.start_time))}` : ''}</span><small>${escapeHtml(event.location || '—')}</small></td>
                    <td>${escapeHtml(submittedBy)}</td>
                    <td><span class="ge-status ${statusClass(status)}">${escapeHtml(status)}</span></td>
                    <td>${escapeHtml(event.decision_note || '—')}</td>
                    <td>${actionButtons(event)}</td>
                </tr>`;
        }).join('');
    };

    const renderDashboard = data => {
        state.dashboard = data;
        state.events = data.event_records || [];
        root.querySelector('#eventSchoolYear').value = data.period?.school_year || 'No active school year';
        root.querySelector('#eventSemester').value = data.period?.semester || 'No active semester';
        addButton.hidden = !data.can_create;
        const summary = data.event_summary || {};
        root.querySelector('#eventTotal').textContent = summary.total ?? 0;
        root.querySelector('#eventPending').textContent = summary.pending ?? 0;
        root.querySelector('#eventUpcoming').textContent = summary.upcoming ?? 0;
        root.querySelector('#eventCompleted').textContent = summary.completed ?? 0;
        renderEvents();
    };

    const loadDashboard = async () => {
        try {
            const data = await request(eventsApiUrl);
            renderDashboard(data);
        } catch (error) {
            root.querySelector('#eventSchoolYear').value = 'Unable to load';
            root.querySelector('#eventSemester').value = 'Unable to load';
            tableBody.innerHTML = `<tr><td colspan="7" class="ge-event-empty ge-error-text">${escapeHtml(error.message)}</td></tr>`;
            showToast(error.message, true);
        }
    };

    const openModal = () => {
        form.reset();
        modal.hidden = false;
        root.querySelector('#eventTitle').focus();
    };

    const closeModal = () => {
        modal.hidden = true;
    };

    addButton.addEventListener('click', openModal);
    root.querySelectorAll('[data-close-event-modal]').forEach(button => {
        button.addEventListener('click', closeModal);
    });
    root.querySelectorAll('[data-close-event-details]').forEach(button => {
        button.addEventListener('click', () => {
            detailModal.hidden = true;
        });
    });

    root.querySelectorAll('[data-event-category]').forEach(button => {
        button.addEventListener('click', () => {
            state.category = button.dataset.eventCategory || '';
            root.querySelectorAll('[data-event-category]').forEach(tab => {
                tab.classList.toggle('active', tab === button);
            });
            renderEvents();
        });
    });

    root.addEventListener('click', async event => {
        const suggestion = event.target.closest('.use-gened-event-suggestion');
        if (suggestion) {
            openModal();
            root.querySelector('#eventTitle').value = suggestion.dataset.title || '';
            root.querySelector('#eventType').value = suggestion.dataset.eventType || '';
            root.querySelector('#eventCategory').value = eventCategory({
                title: suggestion.dataset.title,
                description: suggestion.dataset.description
            });
            root.querySelector('#description').value = suggestion.dataset.description || '';
            root.querySelector('#eventScope').value = 'Department-level';
            return;
        }

        const viewButton = event.target.closest('[data-view-event]');
        if (viewButton) {
            const item = state.events.find(record => Number(record.id) === Number(viewButton.dataset.viewEvent));
            if (!item) return;
            const setDetail = (id, value) => {
                root.querySelector(`#${id}`).textContent = value || '—';
            };
            root.querySelector('#eventDetailsTitle').textContent = item.title || 'Event details';
            setDetail('eventDetailsType', item.event_type);
            setDetail('eventDetailsDate', `${formatDate(item.event_date)} · ${formatTime(item.start_time)}${item.end_time ? ` – ${formatTime(item.end_time)}` : ''}`);
            setDetail('eventDetailsVenue', item.location);
            setDetail('eventDetailsScope', item.event_scope);
            setDetail('eventDetailsStatus', displayStatus(item));
            setDetail('eventDetailsInCharge', item.submitted_by_name);
            setDetail('eventDetailsAudience', item.target_audience);
            setDetail('eventDetailsDescription', item.description);
            detailModal.hidden = false;
            return;
        }

        const button = event.target.closest('.ge-action-button');
        if (!button) return;
        const action = button.dataset.action;
        let decisionNote = '';
        if (action === 'reject') {
            const rejectionReason = window.prompt('Reason for rejecting this item:');
            if (rejectionReason === null) return;
            decisionNote = rejectionReason.trim();
            if (!decisionNote) {
                showToast('A rejection reason is required.', true);
                return;
            }
        } else if (action === 'complete' && !window.confirm('Mark this item as completed?')) {
            return;
        } else if (action === 'archive' && !window.confirm('Archive this event? It will be removed from the active events list.')) {
            return;
        } else if (action === 'submit' && !window.confirm('Submit this event for approval?')) {
            return;
        }

        button.disabled = true;
        const api = button.dataset.source === 'activity' ? activitiesApiUrl : eventsApiUrl;
        try {
            const result = await request(api, {
                method: 'POST',
                body: JSON.stringify({ action, id: Number(button.dataset.id), decision_note: decisionNote })
            });
            showToast(result.message);
            await loadDashboard();
        } catch (error) {
            button.disabled = false;
            showToast(error.message, true);
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const submitButton = form.querySelector('[type="submit"]');
        submitButton.disabled = true;
        const payload = {
            action: 'create',
            event_title: root.querySelector('#eventTitle').value.trim(),
            event_type: root.querySelector('#eventType').value,
            event_category: root.querySelector('#eventCategory').value,
            event_scope: root.querySelector('#eventScope').value,
            event_date: root.querySelector('#eventDate').value,
            start_time: root.querySelector('#startTime').value,
            end_time: root.querySelector('#endTime').value,
            location: root.querySelector('#location').value.trim(),
            target_audience: root.querySelector('#targetAudience').value.trim(),
            description: root.querySelector('#description').value.trim()
        };
        try {
            const result = await request(eventsApiUrl, {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            closeModal();
            showToast(result.message);
            await loadDashboard();
        } catch (error) {
            showToast(error.message, true);
        } finally {
            submitButton.disabled = false;
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
        if (event.key === 'Escape' && !detailModal.hidden) detailModal.hidden = true;
    });

    loadDashboard();
}

document.addEventListener('DOMContentLoaded', initGenEdEventsDashboard);
window.addEventListener('page:loaded', event => {
    if (event.detail?.page === 'events-management') initGenEdEventsDashboard();
});
