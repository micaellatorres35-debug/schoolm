(function () {
    'use strict';

    let currentDate = new Date();
    let readNotificationIds = [];
    let dashboardTimer = null;
    const urgentToastShownKey = 'ccUrgentToastShownIds';
    const readNotificationKey = 'ccReadNotificationIds';
    const researchProjectsStorageKey = 'researchCoor_projects';
    const researchProposalsStorageKey = 'researchCoor_proposals';
    const researchDocumentsStorageKey = 'researchCoor_documents';
    const researchEthicsStorageKey = 'researchCoor_ethics';
    const researchProjectStatuses = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Ongoing', 'Completed', 'Rejected'];

    const dashboard = () => ({
        eventsByDate: window.eventsByDate || {},
        notificationEvents: window.notificationEvents || [],
        serverTimestamp: window.serverTimestamp || new Date().toISOString()
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'};
        return String(text).replace(/[&<>"']/g, character => map[character]);
    }

    function readEmbeddedDashboardData(id, fallback) {
        const element = document.getElementById(id);
        if (!element) return fallback;
        try {
            return JSON.parse(element.textContent || '');
        } catch (error) {
            console.error(`Unable to parse ${id}:`, error);
            return fallback;
        }
    }

    function initializeEmbeddedDashboardData() {
        window.eventsByDate = readEmbeddedDashboardData('researchDashboardEventsData', {});
        window.notificationEvents = readEmbeddedDashboardData('researchDashboardNotificationsData', []);
        window.researchDashboardData = readEmbeddedDashboardData('researchDashboardServerData', {});
        window.serverTimestamp = readEmbeddedDashboardData('researchDashboardTimestampData', new Date().toISOString());
    }

    function getResearchProjects() {
        try {
            const storedProjects = localStorage.getItem(researchProjectsStorageKey);
            if (storedProjects === null) return {projects: [], error: null};
            const projects = JSON.parse(storedProjects);
            if (!Array.isArray(projects)) throw new Error('Stored research project data must be a list.');
            if (!projects.every(project => project && typeof project === 'object' && !Array.isArray(project))) {
                throw new Error('Stored research project data contains an invalid record.');
            }

            return {projects, error: null};
        } catch (error) {
            console.error('Unable to load research dashboard project data:', error);
            return {projects: [], error: 'Research project totals could not be loaded from this browser.'};
        }
    }

    function getResearchProposals() {
        try {
            const storedProposals = localStorage.getItem(researchProposalsStorageKey);
            if (storedProposals === null) return {proposals: null, error: null};
            const proposals = JSON.parse(storedProposals);
            if (!Array.isArray(proposals)) throw new Error('Stored research proposal data must be a list.');
            return {proposals, error: null};
        } catch (error) {
            console.error('Unable to load research dashboard proposal data:', error);
            return {proposals: null, error: 'Research proposal totals could not be loaded from this browser.'};
        }
    }

    function getResearchDocumentCount() {
        try {
            const storedDocuments = localStorage.getItem(researchDocumentsStorageKey);
            if (storedDocuments === null) return {count: 0, error: null};
            const documents = JSON.parse(storedDocuments);
            if (!Array.isArray(documents)) throw new Error('Stored research document data must be a list.');
            return {
                count: documents.filter(document => document && document.archived !== true).length,
                error: null
            };
        } catch (error) {
            console.error('Unable to load research dashboard document data:', error);
            return {count: null, error: 'Research document totals could not be loaded from this browser.'};
        }
    }

    function getResearchEthicsCount() {
        try {
            const storedRecords = localStorage.getItem(researchEthicsStorageKey);
            if (storedRecords === null) return {count: 0, error: null};
            const records = JSON.parse(storedRecords);
            if (!Array.isArray(records)) throw new Error('Stored ethics records must be a list.');
            return {
                count: records.filter(record => record && record.archived !== true).length,
                error: null
            };
        } catch (error) {
            console.error('Unable to load research dashboard ethics data:', error);
            return {count: null, error: 'Ethics and compliance totals could not be loaded from this browser.'};
        }
    }

    function localDateValue(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(String(value || ''))) return null;
        const date = new Date(`${value}T00:00:00`);
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function dateOnly(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    function dateLabel(value) {
        const date = localDateValue(value);
        return date ? date.toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'}) : 'Date not set';
    }

    function normalizedProjectStatus(project) {
        const status = researchProjectStatuses.find(value => value.toLowerCase() === String(project.status || '').trim().toLowerCase());
        return status || 'Draft';
    }

    function getStatusPercentages(countByStatus, totalProjects) {
        const percentages = Object.fromEntries(researchProjectStatuses.map(status => [status, 0]));
        if (!totalProjects) return percentages;

        const remainders = researchProjectStatuses.map((status, index) => {
            const exactPercent = (countByStatus[status] / totalProjects) * 100;
            percentages[status] = Math.floor(exactPercent);
            return {status, index, remainder: exactPercent - percentages[status]};
        });
        let remainingPercent = 100 - Object.values(percentages).reduce((sum, percent) => sum + percent, 0);
        remainders.sort((left, right) => right.remainder - left.remainder || left.index - right.index);
        for (let index = 0; index < remainingPercent; index++) {
            percentages[remainders[index].status]++;
        }
        return percentages;
    }

    function getResearchDeadlines(projects) {
        const today = dateOnly(new Date());
        const lastUpcomingDate = new Date(today);
        lastUpcomingDate.setDate(lastUpcomingDate.getDate() + 30);
        const deadlines = [];
        const delayedProjectIds = new Set();
        let upcomingActivities = 0;

        projects.forEach(project => {
            const status = normalizedProjectStatus(project);
            if (status === 'Completed' || status === 'Rejected') return;

            const milestones = (Array.isArray(project.milestones) ? project.milestones : [])
                .filter(milestone => milestone && typeof milestone === 'object');
            milestones.forEach(milestone => {
                const deadline = localDateValue(milestone.date);
                if (!deadline) return;
                const milestoneStatus = String(milestone.status || 'Pending').toLowerCase();
                if (milestoneStatus === 'completed') return;
                if (deadline < today || milestoneStatus === 'delayed') {
                    delayedProjectIds.add(String(project.id));
                    deadlines.push({
                        title: milestone.title || 'Research milestone',
                        project: project.title || 'Untitled project',
                        date: milestone.date,
                        overdue: deadline < today,
                        delayed: milestoneStatus === 'delayed'
                    });
                } else if (deadline <= lastUpcomingDate) {
                    upcomingActivities++;
                    deadlines.push({
                        title: milestone.title || 'Research milestone',
                        project: project.title || 'Untitled project',
                        date: milestone.date,
                        overdue: false
                    });
                }
            });

            const targetDate = localDateValue(project.targetDate);
            if (!targetDate) return;
            const hasTargetMilestone = milestones.some(milestone =>
                milestone.date === project.targetDate &&
                String(milestone.status || '').toLowerCase() !== 'completed'
            );
            if (targetDate < today) {
                delayedProjectIds.add(String(project.id));
                if (!hasTargetMilestone) {
                    deadlines.push({
                        title: 'Project completion deadline',
                        project: project.title || 'Untitled project',
                        date: project.targetDate,
                        overdue: true
                    });
                }
            } else if (targetDate <= lastUpcomingDate && !hasTargetMilestone) {
                upcomingActivities++;
                deadlines.push({
                    title: 'Project completion deadline',
                    project: project.title || 'Untitled project',
                    date: project.targetDate,
                    overdue: false
                });
            }
        });

        Object.entries(dashboard().eventsByDate).forEach(([dateValue, events]) => {
            const eventDate = localDateValue(dateValue);
            if (!eventDate || eventDate < today || eventDate > lastUpcomingDate) return;
            (Array.isArray(events) ? events : []).forEach(event => {
                const eventText = typeof event === 'string'
                    ? event
                    : `${event.title || ''} ${event.type || ''} ${event.description || ''}`;
                if (/\b(research|proposal|thesis|dissertation|manuscript|defense)\b/i.test(eventText)) {
                    upcomingActivities++;
                }
            });
        });

        deadlines.sort((left, right) => left.date.localeCompare(right.date));
        return {deadlines, delayedProjectIds, upcomingActivities};
    }

    function renderResearchOverview() {
        const notice = document.getElementById('researchDashboardNotice');
        if (!notice) return;
        const {projects, error: storageError} = getResearchProjects();
        const {proposals, error: proposalStorageError} = getResearchProposals();
        const {count: researchDocuments, error: documentStorageError} = getResearchDocumentCount();
        const {count: ethicsRecords, error: ethicsStorageError} = getResearchEthicsCount();
        const activeProjects = projects.filter(project => project.archived !== true);
        const workflowData = window.researchDashboardData || {};
        const {deadlines, delayedProjectIds, upcomingActivities} = getResearchDeadlines(activeProjects);
        const countByStatus = researchProjectStatuses.reduce((counts, status) => {
            counts[status] = activeProjects.filter(project => normalizedProjectStatus(project) === status).length;
            return counts;
        }, {});
        const countValues = {
            total: activeProjects.length,
            ongoing: countByStatus.Ongoing,
            completed: countByStatus.Completed,
            pendingProposals: proposals
                ? proposals.filter(proposal => ['Submitted', 'Under Review', 'Revision Requested', 'Resubmitted'].includes(proposal.status)).length
                : countByStatus.Submitted + countByStatus['Under Review'],
            approvedProposals: proposals
                ? proposals.filter(proposal => proposal.status === 'Approved').length
                : countByStatus.Approved,
            pendingReports: workflowData.pendingReports,
            researchDocuments,
            ethicsRecords,
            upcomingActivities,
            delayed: delayedProjectIds.size,
            pendingApprovals: workflowData.pendingApprovals
        };

        document.querySelectorAll('[data-research-count]').forEach(element => {
            const value = countValues[element.dataset.researchCount];
            element.textContent = value === null || value === undefined ? '—' : value;
        });

        const statusOverview = document.getElementById('researchStatusOverview');
        if (statusOverview) {
            const statusColors = {
                Draft: '#64748b',
                Submitted: '#0284c7',
                'Under Review': '#d97706',
                Approved: '#4f46e5',
                Ongoing: '#0d9488',
                Completed: '#16a34a',
                Rejected: '#dc2626'
            };
            const totalProjects = activeProjects.length;
            const statusPercentages = getStatusPercentages(countByStatus, totalProjects);
            let cumulativePercent = 0;
            const gradientStops = researchProjectStatuses.map(status => {
                const count = countByStatus[status];
                const start = cumulativePercent;
                cumulativePercent += totalProjects ? (count / totalProjects) * 100 : 0;
                return `${statusColors[status]} ${start}% ${cumulativePercent}%`;
            });
            const donutStyle = totalProjects
                ? `background:conic-gradient(${gradientStops.join(',')})`
                : 'background:conic-gradient(#e2e8f0 0% 100%)';
            const legendItems = researchProjectStatuses.map(status => {
                const count = countByStatus[status];
                const percent = statusPercentages[status];
                return `<li class="research-status-legend-item" data-status="${escapeHtml(status)}">
                    <span class="research-status-swatch" aria-hidden="true"></span>
                    <span class="research-status-legend-label">${escapeHtml(status)}</span>
                    <strong>${count}</strong>
                    <small>${percent}%</small>
                </li>`;
            }).join('');
            statusOverview.innerHTML = `<div class="research-status-donut" role="img" aria-label="${totalProjects} research projects, grouped by status" style="${donutStyle}">
                    <div><strong>${totalProjects}</strong><span>Projects</span></div>
                </div>
                <ul class="research-status-legend">${legendItems}</ul>`;
        }

        const deadlineList = document.getElementById('researchDeadlineList');
        if (deadlineList) {
            const visibleDeadlines = deadlines.slice(0, 8);
            deadlineList.innerHTML = visibleDeadlines.length
                ? visibleDeadlines.map(deadline => `<li class="research-deadline-item ${deadline.overdue || deadline.delayed ? 'is-overdue' : ''}">
                    <span class="research-deadline-dot" aria-hidden="true"></span>
                    <span><span class="research-deadline-title">${escapeHtml(deadline.title)}</span><span class="research-deadline-project">${escapeHtml(deadline.project)}</span></span>
                    <time class="research-deadline-date" datetime="${escapeHtml(deadline.date)}">${deadline.overdue ? 'Overdue · ' : deadline.delayed ? 'Delayed · ' : ''}${escapeHtml(dateLabel(deadline.date))}</time>
                </li>`).join('')
                : '<li class="research-empty-state">No upcoming deadlines or overdue research activities.</li>';
        }

        const recentProjects = document.getElementById('researchRecentProjects');
        if (recentProjects) {
            const latestProjects = [...activeProjects]
                .sort((left, right) => String(right.registeredAt || '').localeCompare(String(left.registeredAt || '')))
                .slice(0, 5);
            recentProjects.innerHTML = latestProjects.length
                ? `<div class="research-recent-table-wrap"><table class="research-recent-table">
                    <thead><tr><th>Project title</th><th>PI / Lead</th><th>Status</th><th>Progress</th><th>Deadline</th></tr></thead>
                    <tbody>${latestProjects.map(project => {
                        const status = normalizedProjectStatus(project);
                        const progress = Math.round(Math.min(100, Math.max(0, Number(project.progress) || 0)));
                        return `<tr>
                            <td><a href="?page=research-project" data-page="research-project" class="research-project-title-link">${escapeHtml(project.title || 'Untitled project')}</a><small>${escapeHtml(project.registrationNo || 'Research project')}</small></td>
                            <td>${escapeHtml(project.investigator || 'Not assigned')}</td>
                            <td><span class="research-project-status" data-status="${escapeHtml(status)}">${escapeHtml(status)}</span></td>
                            <td><div class="research-recent-progress" role="img" aria-label="${progress}% complete"><span><i style="width:${progress}%"></i></span><small>${progress}%</small></div></td>
                            <td><time datetime="${escapeHtml(project.targetDate || '')}">${escapeHtml(dateLabel(project.targetDate))}</time></td>
                        </tr>`;
                    }).join('')}</tbody>
                </table></div>`
                : '<p class="research-empty-state">No research projects have been registered yet.</p>';
        }

        const errors = [storageError, proposalStorageError, documentStorageError, ethicsStorageError, ...(workflowData.workflowErrors || [])].filter(Boolean);
        if (errors.length) {
            notice.textContent = errors.join(' ');
            notice.hidden = false;
        } else {
            notice.hidden = true;
            notice.textContent = '';
        }
    }

    function getResearchCalendarEvents() {
        const eventsByDate = Object.fromEntries(
            Object.entries(dashboard().eventsByDate).map(([date, events]) => [date, [...events]])
        );
        const {projects} = getResearchProjects();
        projects.forEach(project => {
            const projectStatus = normalizedProjectStatus(project);
            if (projectStatus === 'Rejected' || projectStatus === 'Completed') return;

            const milestones = (Array.isArray(project.milestones) ? project.milestones : [])
                .filter(milestone => milestone && typeof milestone === 'object');
            const entries = milestones
                .filter(milestone => milestone.date && String(milestone.status || '').toLowerCase() !== 'completed')
                .map(milestone => ({
                    date: milestone.date,
                    title: milestone.title || 'Research milestone',
                    type: 'Research Milestone',
                    time: '',
                    location: '',
                    description: `Project: ${project.title || 'Untitled project'}`,
                    status: 'upcoming'
                }));

            const hasTargetMilestone = milestones.some(milestone =>
                milestone.date === project.targetDate &&
                String(milestone.status || '').toLowerCase() !== 'completed'
            );
            if (project.targetDate && !hasTargetMilestone) {
                entries.push({
                    date: project.targetDate,
                    title: 'Project deadline',
                    type: 'Research Deadline',
                    time: '',
                    location: '',
                    description: project.title || 'Untitled project',
                    status: 'upcoming'
                });
            }

            entries.forEach(entry => {
                if (!entry.date) return;
                const date = entry.date;
                delete entry.date;
                eventsByDate[date] = eventsByDate[date] || [];
                eventsByDate[date].unshift(entry);
            });
        });
        return eventsByDate;
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
        const events = getResearchCalendarEvents();
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
            const dayEvents = events[date] || [];
            let html = `<div class="calendar-day-number">${day}</div>`;
            el.setAttribute('role', 'button');
            el.tabIndex = 0;
            if (dayEvents.length) {
                const hasResearchEvent = dayEvents.some(event =>
                    typeof event === 'object' && String(event.type || '').toLowerCase().includes('research')
                );
                html += `<span class="research-calendar-markers${hasResearchEvent ? ' has-research' : ''}" aria-hidden="true"><i></i>${dayEvents.length > 1 ? '<i></i>' : ''}</span>`;
                el.setAttribute('aria-label', `${date}, ${dayEvents.length} scheduled ${dayEvents.length === 1 ? 'activity' : 'activities'}`);
                el.title = `${dayEvents.length} scheduled ${dayEvents.length === 1 ? 'activity' : 'activities'}`;
            } else {
                el.setAttribute('aria-label', date);
            }
            el.innerHTML = html;
            el.onclick = () => showEventsModal(date, day);
            el.onkeydown = event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    showEventsModal(date, day);
                }
            };
            days.appendChild(el);
        }
        const trailingDays = 42 - days.children.length;
        for (let day = 1; day <= trailingDays; day++) {
            const el = document.createElement('div');
            el.className = 'calendar-day other-month';
            el.innerHTML = `<div class="calendar-day-number">${day}</div>`;
            days.appendChild(el);
        }
    }

    function previousMonth() {
        currentDate.setDate(1);
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    }

    function nextMonth() {
        currentDate.setDate(1);
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    }

    function showEventsModal(dateStr) {
        const modal = document.getElementById('eventsModal');
        const title = document.getElementById('modalDateTitle');
        const list = document.getElementById('modalEventsList');
        if (!modal || !title || !list) return;
        const date = new Date(dateStr + 'T00:00:00');
        title.textContent = date.toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'});
        const events = getResearchCalendarEvents()[dateStr] || [];
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

    function initializeDashboard() {
        if (!document.getElementById('calendarDays')) return;
        initializeEmbeddedDashboardData();
        window.dashboardClientLoadTs = window.dashboardClientLoadTs || Date.now();
        readNotificationIds = getStoredIds(readNotificationKey);
        renderResearchOverview();
        renderCalendar();
        renderNotificationBell();
        updatePriorityAlertTimes();
        updateNotificationTimes();
        updateToastCountdowns();
        showUrgentToasts();
        if (!dashboardTimer) dashboardTimer = setInterval(() => { updatePriorityAlertTimes(); updateNotificationTimes(); updateToastCountdowns(); }, 60000);
    }

    window.previousMonth = previousMonth;
    window.nextMonth = nextMonth;
    window.showEventsModal = showEventsModal;
    window.closeEventsModal = closeEventsModal;
    window.viewAlertDetails = viewAlertDetails;
    window.viewAllPriorityAlerts = viewAllPriorityAlerts;
    window.reinitializeDashboard = function () {
        initializeEmbeddedDashboardData();
        renderResearchOverview();
        renderCalendar();
        renderNotificationBell();
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeDashboard, {once:true});
    else initializeDashboard();

    window.addEventListener('page:loaded', event => {
        if (!event.detail || event.detail.page === 'dashboard-overview') initializeDashboard();
    });

    window.addEventListener('storage', event => {
        if ([researchProjectsStorageKey, researchProposalsStorageKey, researchDocumentsStorageKey, researchEthicsStorageKey].includes(event.key) &&
            document.getElementById('calendarDays')) {
            renderResearchOverview();
        }
        if (event.key === researchProjectsStorageKey && document.getElementById('calendarDays')) {
            renderCalendar();
        }
        if (event.key === readNotificationKey) {
            readNotificationIds = getStoredIds(readNotificationKey);
            renderNotificationBell();
        }
    });

    window.addEventListener('research-projects-updated', () => {
        if (document.getElementById('calendarDays')) {
            renderResearchOverview();
            renderCalendar();
        }
    });

    window.addEventListener('research-proposals-updated', () => {
        if (document.getElementById('calendarDays')) renderResearchOverview();
    });

    window.addEventListener('research-documents-updated', () => {
        if (document.getElementById('calendarDays')) renderResearchOverview();
    });

    window.addEventListener('research-ethics-updated', () => {
        if (document.getElementById('calendarDays')) renderResearchOverview();
    });

    document.addEventListener('click', event => {
        if (event.target === document.getElementById('eventsModal')) closeEventsModal();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeEventsModal();
    });
})();
