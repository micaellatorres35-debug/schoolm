const FM_ENDPOINT = '/schoolm/modules/college-coor-main/pages/faculty-management.php';

let fmView = 'overview';
let fmListData = [];
let fmDetailId = null;
let fmDetailData = null;
let fmDetailReq = 0;
let fmLastFocusedRow = null;
let fmSearchTimer = null;
let fmPendingFilter = 'all';

function fmEl(id) {
    return document.getElementById(id);
}

function fmEsc(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function fmBindOnce(el, flag, event, handler) {
    if (!el || el.dataset[flag] === '1') return;
    el.dataset[flag] = '1';
    el.addEventListener(event, handler);
}

function fmSetView(view) {
    fmView = view;
    const overview = fmEl('fmOverview');
    const detail = fmEl('fmDetail');
    if (overview) overview.hidden = view !== 'overview';
    if (detail) detail.hidden = view !== 'detail';
}

function fmFormatName(row) {
    const parts = [row && row.first_name, row && row.middle_name, row && row.last_name, row && row.suffix].filter(Boolean);
    return parts.length ? parts.join(' ') : 'Unknown faculty';
}

function fmInitials(name) {
    const text = String(name || '').trim();
    if (!text) return '?';
    const parts = text.split(/\s+/).filter(Boolean);
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function fmDepartmentName(value) {
    return value && String(value).trim() ? String(value).trim() : 'Not provided';
}

function fmFormatTime(value) {
    if (value === null || value === undefined || value === '') return '--';

    const timeText = String(value).trim();
    if (!timeText) return '--';

    const match = timeText.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/);
    if (!match) {
        return timeText;
    }

    let hours = Number(match[1]);
    const minutes = Number(match[2]);
    const suffix = hours >= 12 ? 'PM' : 'AM';

    if (hours === 0) hours = 12;
    else if (hours > 12) hours -= 12;

    return hours + ':' + (minutes < 10 ? '0' + minutes : minutes) + ' ' + suffix;
}

function fmRenderDirectory(list) {
    fmListData = Array.isArray(list) ? list : [];
    const tbody = fmEl('fmTableBody');
    const empty = fmEl('fmEmptyState');

    if (!tbody) return;

    if (!fmListData.length) {
        tbody.innerHTML = '<tr class="fm-empty-row"><td colspan="9">No faculty records found.</td></tr>';
        if (empty) empty.textContent = 'No faculty records found.';
        return;
    }

    tbody.innerHTML = fmListData.map((row) => {
        const employeeId = row.employee_id ?? 'N/A';
        const facultyName = fmEsc(fmFormatName(row));
        const department = fmEsc(fmDepartmentName(row.department));
        const position = fmEsc(row.position_name || row.position_id || 'Not provided');
        const employmentType = fmEsc(row.employment_type || 'N/A');
        const employmentStatus = fmEsc(row.employment_status || 'N/A');
        const degree = fmEsc(row.highest_degree || 'No record yet');
        const trainingsCount = Number(row.trainings_count || 0);

        return '<tr class="fm-row" tabindex="0" role="button" aria-label="Open faculty details for ' + facultyName + '" data-fm-row="1" data-employee-id="' + fmEsc(employeeId) + '" data-faculty-name="' + facultyName + '" data-department="' + department + '" data-position="' + fmEsc(row.position_name || '') + '" data-employment-type="' + employmentType + '" data-employment-status="' + employmentStatus + '">' +
            '<td>' + fmEsc(employeeId) + '</td>' +
            '<td>' + facultyName + '</td>' +
            '<td>' + department + '</td>' +
            '<td>' + position + '</td>' +
            '<td>' + employmentType + '</td>' +
            '<td>' + employmentStatus + '</td>' +
            '<td>' + degree + '</td>' +
            '<td>' + trainingsCount + '</td>' +
            '<td><div class="fm-row-actions"><button type="button" class="fm-btn fm-btn-secondary fm-row-view-btn" data-fm-action="view-faculty" data-employee-id="' + fmEsc(employeeId) + '">View</button><button type="button" class="fm-btn fm-btn-outline fm-row-schedule-btn" data-fm-action="shift-schedule" data-employee-id="' + fmEsc(employeeId) + '">Shift Schedule</button></div></td>' +
        '</tr>';
    }).join('');

    if (empty) empty.textContent = '';
}

function fmRequest(action, body) {
    return fetch(FM_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.assign({ action }, body || {}))
    }).then(async (response) => {
        const text = await response.text();
        let data = {};
        try {
            data = text ? JSON.parse(text) : {};
        } catch (error) {
            data = {};
        }
        return data;
    });
}

function fmLoadDirectory() {
    const searchInput = fmEl('fmSearch');
    const filterInput = fmEl('fmEmploymentFilter');
    const search = searchInput ? searchInput.value.trim() : '';
    const filter = filterInput ? filterInput.value : 'All';
    const empty = fmEl('fmEmptyState');

    if (empty) empty.textContent = 'Loading faculty directory…';

    return fmRequest('get_faculty_directory', {
        search,
        employment_type: filter
    }).then((data) => {
        if (!data || !data.success) {
            if (empty) empty.textContent = 'Unable to load faculty directory.';
            return;
        }
        fmRenderDirectory(data.data || []);
    }).catch(() => {
        if (empty) empty.textContent = 'Unable to load faculty directory.';
    });
}

function fmOpenDetail(employeeId) {
    const id = Number(employeeId);
    if (!id) return;

    fmLastFocusedRow = document.querySelector('tr[data-employee-id="' + id + '"]');
    fmDetailId = id;
    fmDetailReq += 1;
    const reqId = fmDetailReq;

    fmSetView('detail');
    const state = fmEl('fmDetailState');
    const content = fmEl('fmDetailContent');
    if (state) {
        state.hidden = false;
        state.innerHTML = 'Loading faculty details…';
    }
    if (content) content.hidden = true;

    fmRequest('get_faculty_full_profile', { employee_id: id }).then((data) => {
        if (reqId !== fmDetailReq || Number(fmDetailId) !== id) return;

        if (!data || !data.success || !data.data) {
            if (state) {
                state.hidden = false;
                state.innerHTML = 'Unable to load faculty details. <button type="button" class="fm-link-btn" data-fm-action="retry-detail">Try again</button>';
            }
            return;
        }

        fmDetailData = data.data;
        fmRenderDetail(data.data);
    }).catch(() => {
        if (reqId !== fmDetailReq || Number(fmDetailId) !== id) return;
        if (state) {
            state.hidden = false;
            state.innerHTML = 'Unable to load faculty details. <button type="button" class="fm-link-btn" data-fm-action="retry-detail">Try again</button>';
        }
    });
}

function fmRenderDetail(data) {
    const employee = data && data.employee ? data.employee : {};
    const education = Array.isArray(data.education_records) ? data.education_records : [];
    const requirements = Array.isArray(data.requirements) ? data.requirements : [];
    const trainingRecords = Array.isArray(data.training_records) ? data.training_records : [];
    const engagementRecords = Array.isArray(data.engagement_records) ? data.engagement_records : [];
    const profileName = fmFormatName(employee);

    const state = fmEl('fmDetailState');
    const content = fmEl('fmDetailContent');
    if (state) state.hidden = true;
    if (content) content.hidden = false;

    const avatar = fmEl('fmDetailAvatar');
    if (avatar) avatar.textContent = fmInitials(profileName);

    const nameEl = fmEl('fmDetailName');
    if (nameEl) nameEl.textContent = profileName;

    const statusEl = fmEl('fmDetailStatus');
    if (statusEl) statusEl.textContent = employee.employment_status || 'N/A';

    const subline = fmEl('fmDetailSubline');
    if (subline) {
        subline.textContent = [employee.employee_code || 'Employee', employee.position_name || 'Position', employee.department || 'Department'].join(' · ');
    }

    const employeeIdEl = fmEl('fmDetailEmployeeId');
    if (employeeIdEl) employeeIdEl.textContent = employee.employee_id || 'N/A';

    const positionEl = fmEl('fmDetailPosition');
    if (positionEl) positionEl.textContent = employee.position_name || 'N/A';

    const departmentEl = fmEl('fmDetailDepartment');
    if (departmentEl) departmentEl.textContent = employee.department || 'N/A';

    const infoEmail = fmEl('fmInfoEmail');
    if (infoEmail) infoEmail.textContent = employee.email || 'Not provided';

    const infoCreated = fmEl('fmInfoCreated');
    if (infoCreated) infoCreated.textContent = employee.created_at || 'Not provided';

    const infoType = fmEl('fmInfoEmploymentType');
    if (infoType) infoType.textContent = employee.employment_type || 'Not provided';

    const infoStatus = fmEl('fmInfoEmploymentStatus');
    if (infoStatus) infoStatus.textContent = employee.employment_status || 'Not provided';

    const profileEmployeeId = fmEl('fmProfileEmployeeId');
    if (profileEmployeeId) profileEmployeeId.textContent = employee.employee_id || 'N/A';

    const profileNameEl = fmEl('fmProfileName');
    if (profileNameEl) profileNameEl.textContent = profileName;

    const profileDepartment = fmEl('fmProfileDepartment');
    if (profileDepartment) profileDepartment.textContent = employee.department || 'N/A';

    const profileEmail = fmEl('fmProfileEmail');
    if (profileEmail) profileEmail.textContent = employee.email || 'Not provided';

    const profilePosition = fmEl('fmProfilePosition');
    if (profilePosition) profilePosition.textContent = employee.position_name || 'N/A';

    const profileEmploymentType = fmEl('fmProfileEmploymentType');
    if (profileEmploymentType) profileEmploymentType.textContent = employee.employment_type || 'Not provided';

    const profileEmploymentStatus = fmEl('fmProfileEmploymentStatus');
    if (profileEmploymentStatus) profileEmploymentStatus.textContent = employee.employment_status || 'Not provided';

    const educationBody = fmEl('fmEducationTableBody');
    if (educationBody) {
        if (!education.length) {
            educationBody.innerHTML = '<tr><td colspan="4">No education records found.</td></tr>';
        } else {
            educationBody.innerHTML = education.map((row) => {
                const level = fmEsc(row.level || 'N/A');
                const school = fmEsc(row.school_name || 'N/A');
                const course = fmEsc(row.course || 'N/A');
                const year = fmEsc(row.year_graduated || 'N/A');
                return '<tr><td>' + level + '</td><td>' + school + '</td><td>' + course + '</td><td>' + year + '</td></tr>';
            }).join('');
        }
    }

    const requirementBody = fmEl('fmRequirementsTableBody');
    if (requirementBody) {
        if (!requirements.length) {
            requirementBody.innerHTML = '<tr><td colspan="5">No faculty requirements found.</td></tr>';
        } else {
            requirementBody.innerHTML = requirements.map((row) => {
                const name = fmEsc(row.requirement_name || 'N/A');
                const status = fmRequirementBadge(row.status || 'N/A');
                const remarks = fmEsc(row.remarks || 'N/A');
                const submitted = fmEsc(row.submitted_date || 'N/A');
                const followUp = fmEsc(row.follow_up_date || 'N/A');
                return '<tr><td>' + name + '</td><td>' + status + '</td><td>' + remarks + '</td><td>' + submitted + '</td><td>' + followUp + '</td></tr>';
            }).join('');
        }
    }

    const trainingBody = fmEl('fmTrainingTableBody');
    if (trainingBody) {
        const combined = [...trainingRecords, ...engagementRecords].map((row) => ({
            type: row.engagement_type || row.cert_name || row.type || 'Training / Certification',
            title: row.title || row.cert_name || 'N/A',
            organization: row.organization || row.issuing_organization || 'N/A',
            date: row.date_period || row.date_issued || [row.start_date, row.end_date].filter(Boolean).join(' to ') || 'N/A',
            status: row.status || 'N/A',
            outcome: row.outcome || 'Not Applicable',
            id: row.id || row.cert_id || '',
            source: row.source || 'training',
            employee_id: row.employee_id || employee.employee_id
        }));

        let filtered = combined;
        const filter = fmPendingFilter || 'all';
        if (filter === 'active') filtered = combined.filter((r) => ['Pending', 'Approved', 'Ongoing', 'For Review'].includes(String(r.status)));
        if (filter === 'completed') filtered = combined.filter((r) => String(r.status).toLowerCase() === 'completed');
        if (filter === 'archived') filtered = combined.filter((r) => String(r.status).toLowerCase() === 'archived');
        if (filter === 'all') filtered = combined.filter((r) => String(r.status).toLowerCase() !== 'archived');

        if (!filtered.length) {
            trainingBody.innerHTML = '<tr><td colspan="7">No training records match the selected status.</td></tr>';
        } else {
            trainingBody.innerHTML = filtered.map((row) => {
                const id = Number(row.id || 0);
                return '<tr data-fm-engagement-id="' + fmEsc(id) + '" data-fm-status="' + fmEsc(row.status) + '">' +
                    '<td>' + fmEsc(row.type) + '</td>' +
                    '<td>' + fmEsc(row.title) + '</td>' +
                    '<td>' + fmEsc(row.organization) + '</td>' +
                    '<td>' + fmEsc(row.date) + '</td>' +
                    '<td>' + fmEsc(row.status) + '</td>' +
                    '<td>' + fmEsc(row.outcome) + '</td>' +
                    '<td>' + fmRenderTrainingActions(row) + '</td>' +
                    '</tr>';
            }).join('');
        }
    }

    const detailShift = fmEl('fmDetailShiftScheduleBtn');
    const detailAdd = fmEl('fmDetailAddEngagementBtn');
    const isPartTimeActive = employee.employment_type === 'Part-time' && String(employee.employment_status || '').toLowerCase() === 'active';
    if (detailShift) detailShift.hidden = !isPartTimeActive;
    if (detailAdd) detailAdd.hidden = !isPartTimeActive;
}

function fmRequirementBadge(status) {
    const text = String(status || 'N/A').trim();
    const normalized = text.toLowerCase();

    let className = 'fm-badge fm-badge-muted';
    if (normalized === 'submitted') className = 'fm-badge fm-badge-success';
    else if (normalized === 'missing') className = 'fm-badge fm-badge-danger';
    else if (normalized === 'for follow-up' || normalized === 'for follow up') className = 'fm-badge fm-badge-warning';

    return '<span class="' + className + '">' + fmEsc(text || 'N/A') + '</span>';
}

function fmRenderTrainingActions(row) {
    const status = String(row.status || '');
    const id = Number(row.id || 0);
    if (!id) return '<span class="fm-muted">—</span>';

    const actions = [];
    if (status === 'Pending') {
        actions.push('<button type="button" class="fm-btn fm-btn-success fm-workflow-btn" data-fm-action="approve-engagement" data-fm-engagement-id="' + id + '">Approve</button>');
    }
    if (['Approved', 'Ongoing'].includes(status)) {
        actions.push('<button type="button" class="fm-btn fm-btn-warning fm-workflow-btn" data-fm-action="complete-engagement" data-fm-engagement-id="' + id + '">Mark Completed</button>');
    }
    if (status === 'Completed') {
        actions.push('<button type="button" class="fm-btn fm-btn-primary fm-workflow-btn" data-fm-action="generate-certificate" data-fm-engagement-id="' + id + '">Generate Certificate</button>');
        actions.push('<button type="button" class="fm-btn fm-btn-outline fm-workflow-btn" data-fm-action="archive-engagement" data-fm-engagement-id="' + id + '">Archive</button>');
    }
    if (status === 'Archived') {
        actions.push('<span class="fm-muted">Archived</span>');
    }
    return actions.join(' ');
}

function fmSetStatusFilter(filter) {
    fmPendingFilter = filter || 'all';
    const toggles = document.querySelectorAll('[data-fm-status-filter]');
    toggles.forEach((button) => {
        const active = button.dataset.fmStatusFilter === fmPendingFilter;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    if (fmDetailData && fmDetailId) {
        fmRenderDetail(fmDetailData);
    }
}

function fmHandleRootClick(event) {
    const statusToggle = event.target.closest('[data-fm-status-filter]');
    if (statusToggle) {
        fmSetStatusFilter(statusToggle.dataset.fmStatusFilter);
        return;
    }

    const button = event.target.closest('[data-fm-action]');
    if (!button) {
        const row = event.target.closest('tr[data-employee-id]');
        if (row && !event.target.closest('button')) {
            fmOpenDetail(row.dataset.employeeId);
        }
        return;
    }

    const action = button.dataset.fmAction;
    const employeeId = button.dataset.employeeId || button.dataset.fmEmployeeId;

    if (action === 'view-faculty') {
        fmOpenDetail(employeeId);
        return;
    }

    if (action === 'retry-detail') {
        if (fmDetailId) fmOpenDetail(fmDetailId);
        return;
    }

    if (action === 'back') {
        fmSetView('overview');
        if (fmLastFocusedRow) {
            fmLastFocusedRow.focus();
        }
        return;
    }

    if (action === 'shift-schedule') {
        const id = employeeId || fmDetailId;
        if (!id) return;
        fmOpenShiftSchedule(id);
        return;
    }

    if (action === 'add-engagement') {
        const employee = fmDetailData && fmDetailData.employee ? fmDetailData.employee : null;
        if (!employee) return;
        fmOpenAddEngagementModal(employee);
        return;
    }

    if (action === 'approve-engagement') {
        const engagementId = button.dataset.fmEngagementId;
        if (!engagementId) return;
        fmRunWorkflow('approve_engagement', Number(engagementId));
        return;
    }

    if (action === 'complete-engagement') {
        const engagementId = button.dataset.fmEngagementId;
        if (!engagementId) return;
        fmOpenOutcomeModal(engagementId, 'mark_completed_engagement');
        return;
    }

    if (action === 'archive-engagement') {
        const engagementId = button.dataset.fmEngagementId;
        if (!engagementId) return;
        fmRunWorkflow('archive_engagement', Number(engagementId));
        return;
    }

    if (action === 'generate-certificate') {
        const engagementId = button.dataset.fmEngagementId;
        if (!engagementId) return;
        const origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
        const url = origin + '/schoolm/modules/college-coor-main/pages/engagement_certificate.php?engagement_id=' + encodeURIComponent(engagementId);
        window.open(url, '_blank');
        return;
    }

    if (action === 'close-add-engagement') {
        fmCloseModal('fmAddEngagementModal');
        return;
    }

    if (action === 'close-shift-schedule') {
        fmCloseModal('fmShiftScheduleModal');
        return;
    }

    if (action === 'close-outcome-modal') {
        fmCloseModal('fmOutcomeModal');
        return;
    }
}

function fmHandleRootKeydown(event) {
    const row = event.target.closest('tr[data-employee-id]');
    if (!row) return;
    if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('button')) {
        event.preventDefault();
        fmOpenDetail(row.dataset.employeeId);
    }
}

function fmOpenShiftSchedule(employeeId) {
    const modal = fmEl('fmShiftScheduleModal');
    const content = fmEl('fmShiftScheduleContent');
    if (!modal || !content) return;

    content.innerHTML = '<p class="fm-empty-state-inline">Loading shift schedule…</p>';
    modal.hidden = false;

    fmRequest('get_faculty_shift_schedule', { employee_id: Number(employeeId) }).then((data) => {
        if (!data || !data.success || !data.schedule) {
            content.innerHTML = '<p class="fm-empty-state-inline">No active shift schedule found.</p>';
            modal.hidden = false;
            return;
        }

        const schedule = data.schedule;
        const weekly = schedule.weekly_schedule || {};
        const dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const rows = dayOrder.map((day) => {
            const daySchedule = weekly[day] || {};
            return '<tr>' +
                '<td>' + fmEsc(day) + '</td>' +
                '<td>' + fmEsc(fmFormatTime(daySchedule.start_time)) + '</td>' +
                '<td>' + fmEsc(fmFormatTime(daySchedule.break_start)) + '</td>' +
                '<td>' + fmEsc(fmFormatTime(daySchedule.break_end)) + '</td>' +
                '<td>' + fmEsc(fmFormatTime(daySchedule.end_time)) + '</td>' +
            '</tr>';
        }).join('');

        const breakDuration = schedule.break_duration;
        let breakLabel = '--';
        if (breakDuration !== null && breakDuration !== undefined && breakDuration !== '') {
            const value = Number(breakDuration);
            breakLabel = Number.isFinite(value) ? value + ' minutes' : String(breakDuration);
        }

        content.innerHTML = '<div class="fm-shift-grid">' +
            '<div><strong>Faculty Name</strong><span>' + fmEsc(schedule.faculty_name || 'N/A') + '</span></div>' +
            '<div><strong>Employee ID</strong><span>' + fmEsc(schedule.employee_id || 'N/A') + '</span></div>' +
            '<div><strong>Department</strong><span>' + fmEsc(schedule.department || 'Not provided') + '</span></div>' +
            '<div><strong>Shift Name</strong><span>' + fmEsc(schedule.shift_name || 'N/A') + '</span></div>' +
            '<div><strong>Break Duration</strong><span>' + fmEsc(breakLabel) + '</span></div>' +
            '<div><strong>Status</strong><span>' + fmEsc(schedule.status || 'Active') + '</span></div>' +
            '<div><strong>Effective From</strong><span>' + fmEsc(schedule.effective_from || 'N/A') + '</span></div>' +
            '<div><strong>Effective To</strong><span>' + fmEsc(schedule.effective_to || 'Ongoing') + '</span></div>' +
        '</div>' +
        '<div class="fm-shift-table-wrap">' +
            '<table class="fm-shift-table">' +
                '<thead><tr><th>Day</th><th>Start</th><th>Break Start</th><th>Break End</th><th>End</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>' +
        '</div>';
        modal.hidden = false;
    }).catch(() => {
        content.innerHTML = '<p class="fm-empty-state-inline">No active shift schedule found.</p>';
        modal.hidden = false;
    });
}

function fmOpenAddEngagementModal(employee) {
    const modal = fmEl('fmAddEngagementModal');
    const nameEl = fmEl('fmAddEngagementEmployee');
    const hiddenId = fmEl('fmAddEngagementEmployeeId');
    const titleEl = fmEl('fmAddEngagementTitle');
    const orgEl = fmEl('fmAddEngagementOrganization');
    const startEl = fmEl('fmAddEngagementStartDate');
    const endEl = fmEl('fmAddEngagementEndDate');
    if (!modal || !nameEl || !hiddenId) return;

    nameEl.value = (employee.employee_code || employee.employee_id || '') + ' - ' + fmFormatName(employee);
    hiddenId.value = employee.employee_id || '';
    if (titleEl) titleEl.value = '';
    if (orgEl) orgEl.value = '';
    if (startEl) startEl.value = '';
    if (endEl) endEl.value = '';
    modal.hidden = false;
}

function fmCloseModal(id) {
    const el = fmEl(id);
    if (el) el.hidden = true;
}

function fmModalConfirm(action, engagementId, outcome) {
    const payload = { action, engagement_id: Number(engagementId) };
    if (action === 'mark_completed_engagement') payload.outcome = outcome || 'Not Applicable';
    fmRequest(action, payload).then((data) => {
        if (data && data.success) {
            if (fmDetailId) fmOpenDetail(fmDetailId);
            fmLoadDirectory();
            return;
        }
        alert(data && data.message ? data.message : 'The action could not be completed.');
    }).catch(() => {
        alert('The action could not be completed.');
    });
}

function fmRunWorkflow(action, engagementId, outcome) {
    if (!engagementId) return;
    if (action === 'archive_engagement' && !window.confirm('Archive this engagement?')) return;
    if (action === 'approve_engagement' && !window.confirm('Approve this engagement?')) return;
    fmModalConfirm(action, Number(engagementId), outcome);
}

function fmOpenOutcomeModal(engagementId, action) {
    const modal = fmEl('fmOutcomeModal');
    const select = fmEl('fmOutcomeSelect');
    const confirmBtn = fmEl('fmOutcomeConfirmBtn');
    if (!modal || !select || !confirmBtn) return;

    modal.dataset.fmActionTarget = action;
    modal.dataset.fmEngagementId = engagementId;
    select.value = 'Continue';
    confirmBtn.onclick = function () {
        const outcome = select.value;
        fmCloseModal('fmOutcomeModal');
        fmRunWorkflow(action, Number(engagementId), outcome);
    };
    modal.hidden = false;
}

function fmInitAddEngagementForm() {
    const form = fmEl('fmAddEngagementForm');
    if (!form || form.dataset.fmBound === '1') return;
    form.dataset.fmBound = '1';
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const employeeId = fmEl('fmAddEngagementEmployeeId')?.value;
        const title = fmEl('fmAddEngagementTitle')?.value.trim();
        const organization = fmEl('fmAddEngagementOrganization')?.value.trim();
        const startDate = fmEl('fmAddEngagementStartDate')?.value;
        const endDate = fmEl('fmAddEngagementEndDate')?.value;

        if (!employeeId || !title || !organization || !startDate || !endDate) {
            alert('Please complete all engagement fields.');
            return;
        }

        if (!window.confirm('Create this engagement record?')) return;

        fmRequest('add_engagement', {
            employee_id: Number(employeeId),
            title,
            organization,
            start_date: startDate,
            end_date: endDate
        }).then((data) => {
            if (data && data.success) {
                fmCloseModal('fmAddEngagementModal');
                form.reset();
                if (fmDetailId) fmOpenDetail(fmDetailId);
                fmLoadDirectory();
                return;
            }
            alert((data && data.message) || 'Unable to create engagement.');
        }).catch(() => {
            alert('Unable to create engagement.');
        });
    });
}

function fmHandleFilters() {
    const searchInput = fmEl('fmSearch');
    const filterInput = fmEl('fmEmploymentFilter');
    if (!searchInput || !filterInput) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(fmSearchTimer);
        fmSearchTimer = setTimeout(() => fmLoadDirectory(), 250);
    });

    filterInput.addEventListener('change', function () {
        fmLoadDirectory();
    });
}

function fmInit() {
    const root = fmEl('fmRoot');
    if (!root) return;

    fmBindOnce(root, 'fmBoundClick', 'click', fmHandleRootClick);
    fmBindOnce(root, 'fmBoundKey', 'keydown', fmHandleRootKeydown);
    fmHandleFilters();
    fmInitAddEngagementForm();

    if (!root.dataset.fmLoaded) {
        root.dataset.fmLoaded = '1';
        fmSetView('overview');
        fmLoadDirectory();
    }
}

window.addEventListener('page:loaded', function (event) {
    if (event.detail && event.detail.page === 'faculty-management') {
        setTimeout(fmInit, 50);
    }
});

document.addEventListener('DOMContentLoaded', fmInit);
