function initAcademicActivities() {
    const root = document.getElementById('gcAcademicActivities');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';

    const tableBody = root.querySelector('#gcActivitiesTable tbody');
    const schoolYearSelect = root.querySelector('#gcActivitySchoolYear');
    const semesterSelect = root.querySelector('#gcActivitySemester');
    const activityModal = document.getElementById('gcActivityModal');
    const activityForm = document.getElementById('gcActivityForm');
    const categorySelect = document.getElementById('gcActivityCategory');
    const examPeriodSelect = document.getElementById('gcExamPeriod');
    const examPeriodGroup = document.getElementById('gcExamPeriodGroup');
    const state = { activities: [], canApprove: false, employeeId: 0, selectedCategory: '' };
    const apiUrl = new URL('api/academic_activities.php', window.location.href);

    function showMessage(message, isError = false) {
        const element = root.querySelector('#gcActivitiesMessage');
        element.textContent = message;
        element.classList.toggle('text-danger', isError);
        element.classList.toggle('text-muted', !isError);
    }

    async function request(payload = null, params = {}) {
        const url = new URL(apiUrl);
        Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));
        const options = payload ? {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        } : {};
        const response = await fetch(url, options);
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to load academic activities.');
        }
        return data;
    }

    function setSummary(summary = {}) {
        root.querySelector('#gcTotalActivities').textContent = summary.total ?? 0;
        root.querySelector('#gcPendingActivities').textContent = summary.pending ?? 0;
        root.querySelector('#gcUpcomingActivities').textContent = summary.upcoming ?? 0;
        root.querySelector('#gcCompletedActivities').textContent = summary.completed ?? 0;
    }

    function setEmpty(message) {
        tableBody.replaceChildren();
        const row = tableBody.insertRow();
        const cell = row.insertCell();
        cell.colSpan = 7;
        cell.className = 'text-center text-muted';
        cell.textContent = message;
    }

    function addTextCell(row, value) {
        const cell = row.insertCell();
        cell.textContent = value === null || value === undefined || value === '' ? '—' : String(value);
        return cell;
    }

    function addButton(container, label, action, className) {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.activityAction = action;
        button.className = className;
        button.textContent = label;
        container.appendChild(button);
    }

    function renderActivities() {
        const activities = state.activities.filter(activity =>
            !state.selectedCategory || activity.category === state.selectedCategory
        );
        if (!activities.length) {
            setEmpty(state.selectedCategory ? 'No activities in this category for the selected semester.' : 'No activities for the selected semester.');
            return;
        }

        tableBody.replaceChildren();
        activities.forEach(activity => {
            const row = tableBody.insertRow();
            row.dataset.activityId = activity.id;
            const titleCell = addTextCell(row, activity.title);
            if (activity.description) {
                const description = document.createElement('small');
                description.className = 'd-block text-muted';
                description.textContent = activity.description;
                titleCell.appendChild(description);
            }
            addTextCell(row, activity.category === 'Common Exams' && activity.exam_period
                ? `${activity.category} — ${activity.exam_period}`
                : activity.category);
            addTextCell(row, activity.activity_date);
            addTextCell(row, activity.submitted_by_name?.trim() || `Employee #${activity.submitted_by}`);
            const statusCell = row.insertCell();
            const statusBadge = document.createElement('span');
            const statusClass = activity.status.toLowerCase().replaceAll(' ', '-');
            statusBadge.className = `gc-activity-status gc-activity-status-${statusClass}`;
            statusBadge.textContent = activity.status;
            statusCell.appendChild(statusBadge);
            addTextCell(row, activity.decision_note);

            const actions = row.insertCell();
            actions.className = 'actions';
            if (state.canApprove && activity.status === 'Pending Approval') {
                addButton(actions, 'Approve', 'approve', 'btn btn-sm btn-success');
                addButton(actions, 'Reject', 'reject', 'btn btn-sm btn-outline-danger');
            } else if ((state.canApprove || Number(activity.submitted_by) === state.employeeId)
                && activity.status === 'Approved') {
                addButton(actions, 'Mark Completed', 'complete', 'btn btn-sm btn-primary');
            } else {
                actions.textContent = '—';
            }
        });
    }

    async function loadActivities() {
        setEmpty('Loading activities...');
        try {
            const params = {};
            if (schoolYearSelect.value) params.school_year_id = schoolYearSelect.value;
            if (semesterSelect.value) params.semester_id = semesterSelect.value;
            const data = await request(null, params);
            state.activities = data.activities || [];
            state.canApprove = Boolean(data.can_approve);
            state.employeeId = Number(data.current_employee_id || 0);
            document.getElementById('gcAddActivityButton').hidden = !data.can_create || !data.selected_period;
            setSummary(data.summary);

            const periods = data.periods || [];
            const selected = data.selected_period;
            if (!periods.length || !selected) {
                schoolYearSelect.replaceChildren(new Option('No active school years', ''));
                semesterSelect.replaceChildren(new Option('No active semesters', ''));
                schoolYearSelect.disabled = true;
                semesterSelect.disabled = true;
                renderActivities();
                return;
            }

            const schoolYears = [...new Map(periods.map(period => [
                String(period.school_year_id),
                { id: period.school_year_id, name: period.school_year }
            ])).values()];
            schoolYearSelect.replaceChildren(...schoolYears.map(year => new Option(year.name, year.id)));
            schoolYearSelect.value = String(selected.school_year_id);
            const semesters = periods.filter(period => String(period.school_year_id) === String(selected.school_year_id));
            semesterSelect.replaceChildren(...semesters.map(period => new Option(period.semester, period.semester_id)));
            semesterSelect.value = String(selected.semester_id);
            schoolYearSelect.disabled = false;
            semesterSelect.disabled = false;
            renderActivities();
            showMessage('');
        } catch (error) {
            setSummary();
            setEmpty(error.message);
            showMessage(error.message, true);
        }
    }

    function closeModal() {
        activityModal.classList.remove('show');
    }

    document.getElementById('gcAddActivityButton').addEventListener('click', () => {
        activityForm.reset();
        examPeriodGroup.hidden = true;
        examPeriodSelect.required = false;
        activityModal.classList.add('show');
    });

    activityModal.querySelectorAll('.modal-close').forEach(button => button.addEventListener('click', closeModal));
    activityModal.addEventListener('click', event => {
        if (event.target === activityModal) closeModal();
    });

    categorySelect.addEventListener('change', () => {
        const isCommonExam = categorySelect.value === 'Common Exams';
        examPeriodGroup.hidden = !isCommonExam;
        examPeriodSelect.required = isCommonExam;
        if (!isCommonExam) examPeriodSelect.value = '';
    });

    root.querySelectorAll('[data-activity-category]').forEach(button => {
        button.addEventListener('click', () => {
            state.selectedCategory = button.dataset.activityCategory || '';
            root.querySelectorAll('[data-activity-category]').forEach(tab => tab.classList.toggle('active', tab === button));
            renderActivities();
        });
    });

    schoolYearSelect.addEventListener('change', () => loadActivities());
    semesterSelect.addEventListener('change', () => loadActivities());

    activityForm.addEventListener('submit', async event => {
        event.preventDefault();
        const submitButton = activityForm.querySelector('[type="submit"]');
        if (!schoolYearSelect.value || !semesterSelect.value) {
            showMessage('Select an active school year and semester first.', true);
            closeModal();
            return;
        }

        submitButton.disabled = true;
        try {
            const result = await request({
                action: 'create',
                title: root.querySelector('#gcActivityTitle').value.trim(),
                category: categorySelect.value,
                exam_period: examPeriodSelect.value,
                activity_date: root.querySelector('#gcActivityDate').value,
                description: root.querySelector('#gcActivityDescription').value.trim(),
                school_year_id: schoolYearSelect.value,
                semester_id: semesterSelect.value
            });
            closeModal();
            showMessage(result.message);
            await loadActivities();
        } catch (error) {
            showMessage(error.message, true);
        } finally {
            submitButton.disabled = false;
        }
    });

    tableBody.addEventListener('click', async event => {
        const button = event.target.closest('[data-activity-action]');
        if (!button) return;
        const row = button.closest('[data-activity-id]');
        if (!row) return;

        const action = button.dataset.activityAction;
        let decisionNote = '';
        if (action === 'reject') {
            decisionNote = window.prompt('Reason for rejecting this activity:')?.trim() || '';
            if (!decisionNote) return;
        }
        if (action === 'approve' && !window.confirm('Approve this academic activity?')) return;
        if (action === 'complete' && !window.confirm('Mark this activity as completed?')) return;

        button.disabled = true;
        try {
            const result = await request({ action, id: row.dataset.activityId, decision_note: decisionNote });
            showMessage(result.message);
            await loadActivities();
        } catch (error) {
            showMessage(error.message, true);
            button.disabled = false;
        }
    });

    loadActivities();
}

document.addEventListener('DOMContentLoaded', initAcademicActivities);
window.addEventListener('page:loaded', event => {
    if (event.detail?.page === 'academic activities') initAcademicActivities();
});
