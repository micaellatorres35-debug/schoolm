const defenseSchedulesApi = new URL('api/defense_schedules.php', window.location.href).toString();
let defenseEvents = [];
let defenseCalendarDate = new Date();
let defenseSelectedDate = '';

function escapeDefenseHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);
}

function defenseDetails(description) {
    const details = {};
    String(description || '').split(/\r?\n/).forEach(line => {
        const separator = line.indexOf(':');
        if (separator < 0) return;
        details[line.slice(0, separator).trim()] = line.slice(separator + 1).trim();
    });
    return details;
}

function defenseDateKey(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function renderDefenseCalendar() {
    const monthLabel = document.getElementById('defenseCalendarMonth');
    const daysContainer = document.getElementById('defenseCalendarDays');
    if (!monthLabel || !daysContainer) return;

    monthLabel.textContent = defenseCalendarDate.toLocaleDateString(undefined, {
        month: 'long',
        year: 'numeric'
    });

    const year = defenseCalendarDate.getFullYear();
    const month = defenseCalendarDate.getMonth();
    const firstDay = new Date(year, month, 1);
    const startOffset = (firstDay.getDay() + 6) % 7;
    const gridStart = new Date(year, month, 1 - startOffset);
    const todayKey = defenseDateKey(new Date());
    const eventCounts = defenseEvents.reduce((counts, event) => {
        const key = String(event.event_date || '').slice(0, 10);
        counts[key] = (counts[key] || 0) + 1;
        return counts;
    }, {});

    const dayButtons = [];
    for (let index = 0; index < 42; index += 1) {
        const date = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + index);
        const key = defenseDateKey(date);
        const isCurrentMonth = date.getMonth() === month;
        const classes = [
            'defense-calendar-day',
            isCurrentMonth ? '' : 'is-outside-month',
            key === todayKey ? 'is-today' : '',
            key === defenseSelectedDate ? 'is-selected' : '',
            eventCounts[key] ? 'has-defense' : ''
        ].filter(Boolean).join(' ');
        const countLabel = eventCounts[key] ? `${eventCounts[key]} defense schedule${eventCounts[key] === 1 ? '' : 's'}` : 'No defense schedules';
        dayButtons.push(`<button type="button" class="${classes}" data-defense-date="${key}" role="gridcell" aria-label="${escapeDefenseHtml(date.toLocaleDateString(undefined, { dateStyle: 'full' }))}, ${countLabel}" aria-pressed="${key === defenseSelectedDate ? 'true' : 'false'}">
            <span>${date.getDate()}</span>${eventCounts[key] ? '<i aria-hidden="true"></i>' : ''}
        </button>`);
    }

    daysContainer.innerHTML = dayButtons.join('');
    daysContainer.querySelectorAll('[data-defense-date]').forEach(button => {
        button.addEventListener('click', () => {
            defenseSelectedDate = button.dataset.defenseDate;
            const selectedDate = new Date(`${defenseSelectedDate}T00:00:00`);
            defenseCalendarDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
            renderDefenseCalendar();
            renderDefenseAgenda();
        });
    });
}

function renderDefenseAgenda() {
    const heading = document.getElementById('defenseCalendarSelectedDate');
    const agenda = document.getElementById('defenseCalendarAgenda');
    if (!heading || !agenda) return;

    const selectedEvents = defenseSelectedDate
        ? defenseEvents.filter(event => String(event.event_date || '').slice(0, 10) === defenseSelectedDate)
        : [];
    if (defenseSelectedDate) {
        const selectedDate = new Date(`${defenseSelectedDate}T00:00:00`);
        heading.textContent = selectedDate.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
    } else {
        heading.textContent = 'Upcoming Defenses';
    }

    if (!selectedEvents.length) {
        agenda.innerHTML = `<p class="defense-calendar-empty">${defenseSelectedDate ? 'No defenses scheduled for this date.' : 'Select a date with a marker to view its schedule.'}</p>`;
        return;
    }

    agenda.innerHTML = selectedEvents.map(event => {
        const details = defenseDetails(event.description);
        const title = String(event.event_title || '').replace(/^Research Defense:\s*/, '');
        return `<article class="defense-agenda-event">
            <span class="defense-agenda-time">${escapeDefenseHtml(String(event.start_time || '').slice(0, 5))}–${escapeDefenseHtml(String(event.end_time || '').slice(0, 5))}</span>
            <strong>${escapeDefenseHtml(title)}</strong>
            <small>${escapeDefenseHtml(details['Defense stage'] || 'Research Defense')} · ${escapeDefenseHtml(details.Candidate || 'Candidate not listed')}</small>
            <small><i class="fas fa-location-dot" aria-hidden="true"></i> ${escapeDefenseHtml(event.location || 'Venue not listed')}</small>
        </article>`;
    }).join('');
}

function showDefenseNotice(message, type) {
    const notice = document.getElementById('defenseScheduleNotice');
    if (!notice) return;
    notice.textContent = message;
    notice.className = `defense-notice defense-notice-${type}`;
    notice.hidden = false;
}

async function loadDefenseSchedules() {
    const rows = document.getElementById('defenseScheduleRows');
    if (!rows) return;
    rows.innerHTML = '<tr><td class="defense-table-message" colspan="5">Loading scheduled defenses...</td></tr>';

    try {
        const response = await fetch(defenseSchedulesApi, { credentials: 'same-origin' });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to load scheduled defenses.');
        }

        defenseEvents = result.events;
        if (!defenseSelectedDate) {
            const today = defenseDateKey(new Date());
            const upcomingEvent = defenseEvents.find(event => String(event.event_date || '').slice(0, 10) >= today);
            defenseSelectedDate = upcomingEvent ? String(upcomingEvent.event_date).slice(0, 10) : today;
            if (upcomingEvent) {
                const eventDate = new Date(`${defenseSelectedDate}T00:00:00`);
                defenseCalendarDate = new Date(eventDate.getFullYear(), eventDate.getMonth(), 1);
            }
        }
        renderDefenseCalendar();
        renderDefenseAgenda();

        if (!result.events.length) {
            rows.innerHTML = '<tr><td class="defense-table-message" colspan="5">No research defense schedules have been assigned yet.</td></tr>';
            return;
        }

        rows.innerHTML = result.events.map(event => {
            const details = defenseDetails(event.description);
            const title = String(event.event_title || '').replace(/^Research Defense:\s*/, '');
            const eventDate = new Date(`${event.event_date}T00:00:00`);
            const dateLabel = Number.isNaN(eventDate.getTime())
                ? escapeDefenseHtml(event.event_date)
                : escapeDefenseHtml(eventDate.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
            const timeLabel = `${escapeDefenseHtml(String(event.start_time || '').slice(0, 5))} – ${escapeDefenseHtml(String(event.end_time || '').slice(0, 5))}`;
            const status = String(event.status || 'upcoming');
            return `<tr>
                <td><strong>${escapeDefenseHtml(title)}</strong><small>${escapeDefenseHtml(details['Defense stage'] || 'Research Defense')}</small></td>
                <td>${escapeDefenseHtml(details.Candidate || '—')}</td>
                <td>${dateLabel}<small>${timeLabel}</small></td>
                <td>${escapeDefenseHtml(event.location || '—')}</td>
                <td><span class="defense-status defense-status-${escapeDefenseHtml(status.toLowerCase())}">${escapeDefenseHtml(status)}</span></td>
            </tr>`;
        }).join('');
    } catch (error) {
        rows.innerHTML = '<tr><td class="defense-table-message defense-error-text" colspan="5">Could not load schedules. Use the refresh button to try again.</td></tr>';
        console.error('Defense schedule loading failed:', error);
    }
}

function initializeDefenseScheduling() {
    const form = document.getElementById('defenseScheduleForm');
    if (!form) return;

    if (form.dataset.listenerAttached !== 'true') {
        form.dataset.listenerAttached = 'true';
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = form.querySelector('[type="submit"]');
            const originalLabel = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Saving...';

            const payload = Object.fromEntries(new FormData(form).entries());
            try {
                const response = await fetch(defenseSchedulesApi, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Unable to save the defense schedule.');
                }

                form.reset();
                defenseSelectedDate = payload.defense_date;
                const submittedDate = new Date(`${defenseSelectedDate}T00:00:00`);
                defenseCalendarDate = new Date(submittedDate.getFullYear(), submittedDate.getMonth(), 1);
                showDefenseNotice('Defense schedule assigned and added to Events & Calendar.', 'success');
                await loadDefenseSchedules();
            } catch (error) {
                showDefenseNotice(error.message || 'Unable to save the defense schedule.', 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = originalLabel;
            }
        });
    }

    const refreshButton = document.getElementById('refreshDefenseSchedules');
    if (refreshButton && refreshButton.dataset.listenerAttached !== 'true') {
        refreshButton.dataset.listenerAttached = 'true';
        refreshButton.addEventListener('click', loadDefenseSchedules);
    }

    const previousMonth = document.getElementById('defenseCalendarPrevious');
    if (previousMonth && previousMonth.dataset.listenerAttached !== 'true') {
        previousMonth.dataset.listenerAttached = 'true';
        previousMonth.addEventListener('click', () => {
            defenseCalendarDate = new Date(defenseCalendarDate.getFullYear(), defenseCalendarDate.getMonth() - 1, 1);
            defenseSelectedDate = defenseDateKey(defenseCalendarDate);
            renderDefenseCalendar();
            renderDefenseAgenda();
        });
    }

    const nextMonth = document.getElementById('defenseCalendarNext');
    if (nextMonth && nextMonth.dataset.listenerAttached !== 'true') {
        nextMonth.dataset.listenerAttached = 'true';
        nextMonth.addEventListener('click', () => {
            defenseCalendarDate = new Date(defenseCalendarDate.getFullYear(), defenseCalendarDate.getMonth() + 1, 1);
            defenseSelectedDate = defenseDateKey(defenseCalendarDate);
            renderDefenseCalendar();
            renderDefenseAgenda();
        });
    }

    loadDefenseSchedules();
}

document.addEventListener('DOMContentLoaded', initializeDefenseScheduling);
window.addEventListener('page:loaded', event => {
    if (event.detail?.page === 'defense-scheduling') initializeDefenseScheduling();
});
