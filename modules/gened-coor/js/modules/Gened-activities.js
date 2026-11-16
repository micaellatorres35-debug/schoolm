(() => {
    const statuses = ['Pending Approval', 'Approved', 'Rejected', 'Completed'];

    function initialize() {
        const root = document.getElementById('gcAcademicActivities');
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';

        const apiUrl = new URL('api/gened_subject_activities.php', window.location.href);
        const fields = {
            year: root.querySelector('#gcActivitySchoolYear'),
            semester: root.querySelector('#gcActivitySemester'),
            department: root.querySelector('#gcActivityDepartment'),
            section: root.querySelector('#gcActivitySection'),
            subject: root.querySelector('#gcActivitySubject'),
            faculty: root.querySelector('#gcActivityFaculty'),
            category: root.querySelector('#gcActivityCategoryFilter'),
            status: root.querySelector('#gcActivityStatusFilter'),
            search: root.querySelector('#gcActivitySearch')
        };
        const body = root.querySelector('#gcActivitiesTable tbody');
        const notice = root.querySelector('#gcActivityNotice');
        const addButton = document.querySelector('#gcActivityAddButton');
        const modal = root.querySelector('#gcActivityModal');
        const form = root.querySelector('#gcActivityForm');
        const assignmentSelect = root.querySelector('#gcActivityAssignment');
        const teacherSelect = root.querySelector('#gcActivityTeachers');
        const categorySelect = root.querySelector('#gcActivityCategory');
        const examPeriodGroup = root.querySelector('#gcExamPeriodGroup');
        const examPeriodSelect = root.querySelector('#gcExamPeriod');
        const formMessage = root.querySelector('#gcActivityFormMessage');
        const state = {
            activities: [],
            assignmentOptions: [],
            periods: [],
            categories: [],
            examPeriods: [],
            currentEmployeeId: 0,
            canCreate: false,
            canApprove: false
        };

        const request = async (options = {}) => {
            const response = await fetch(apiUrl, {
                credentials: 'same-origin',
                ...options,
                headers: {
                    ...(options.headers || {}),
                    ...(options.body ? {'Content-Type': 'application/json'} : {})
                }
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Request failed (${response.status}).`);
            }
            return data;
        };

        const setNotice = (message, isError = false) => {
            notice.textContent = message;
            notice.classList.toggle('is-error', isError);
        };

        const uniqueOptions = (items, valueKey, labelKey) => {
            const options = new Map();
            items.forEach(item => {
                const value = String(item[valueKey] ?? '');
                const label = String(item[labelKey] ?? '');
                if (value && label && !options.has(value)) options.set(value, label);
            });
            return [...options].map(([value, label]) => ({value, label}));
        };

        const fillSelect = (select, placeholder, options, selectedValue = select.value) => {
            select.replaceChildren(new Option(placeholder, ''));
            options.forEach(option => select.add(new Option(option.label, option.value)));
            select.value = options.some(option => String(option.value) === String(selectedValue))
                ? String(selectedValue)
                : '';
        };

        const setFormMessage = (message, isError = false) => {
            formMessage.textContent = message;
            formMessage.classList.toggle('text-danger', isError);
            formMessage.classList.toggle('text-muted', !isError);
        };

        const selectedActivities = () => {
            const query = fields.search.value.trim().toLocaleLowerCase();
            return state.activities.filter(activity => {
                const teacherIds = activity.teachers.map(teacher => String(teacher.id));
                const searchable = [
                    activity.title, activity.description, activity.category,
                    activity.department_code, activity.department_name,
                    activity.section_code, activity.subject_code, activity.subject_name,
                    ...activity.teachers.map(teacher => teacher.name)
                ].join(' ').toLocaleLowerCase();
                return (!fields.year.value || String(activity.school_year_id) === fields.year.value)
                    && (!fields.semester.value || String(activity.semester_id) === fields.semester.value)
                    && (!fields.department.value || String(activity.department_id) === fields.department.value)
                    && (!fields.section.value || String(activity.section_id) === fields.section.value)
                    && (!fields.subject.value || String(activity.subject_id) === fields.subject.value)
                    && (!fields.faculty.value || teacherIds.includes(fields.faculty.value))
                    && (!fields.category.value || activity.category === fields.category.value)
                    && (!fields.status.value || activity.status === fields.status.value)
                    && (!query || searchable.includes(query));
            });
        };

        const addCell = (row, value) => {
            const cell = row.insertCell();
            cell.textContent = value === null || value === undefined || value === '' ? '—' : String(value);
            return cell;
        };

        const statusClass = status => ({
            'Pending Approval': 'gc-activity-status-pending',
            Approved: 'gc-activity-status-approved',
            Rejected: 'gc-activity-status-rejected',
            Completed: 'gc-activity-status-completed'
        })[status] || 'gc-activity-status-neutral';

        const appendAction = (cell, label, action, id, className) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `gc-activity-action ${className}`;
            button.dataset.activityAction = action;
            button.dataset.activityId = String(id);
            button.textContent = label;
            cell.appendChild(button);
        };

        const render = () => {
            const shown = selectedActivities();
            body.replaceChildren();

            if (!shown.length) {
                const row = body.insertRow();
                const cell = row.insertCell();
                cell.colSpan = 10;
                cell.className = 'gc-activity-empty';
                cell.textContent = state.activities.length
                    ? 'No activities match the selected filters.'
                    : 'No activities have been added for the available subject assignments.';
            } else {
                shown.forEach(activity => {
                    const row = body.insertRow();
                    const titleCell = row.insertCell();
                    const title = document.createElement('strong');
                    title.textContent = activity.title;
                    titleCell.appendChild(title);
                    if (activity.description) {
                        const description = document.createElement('small');
                        description.textContent = activity.description;
                        titleCell.appendChild(description);
                    }

                    addCell(row, `${activity.subject_code} — ${activity.subject_name}`);
                    addCell(row, `${activity.department_code} — ${activity.department_name}`);
                    addCell(row, activity.section_code);
                    addCell(row, activity.teachers.map(teacher => teacher.name).join(', '));
                    addCell(row, activity.category === 'Common Exams' && activity.exam_period
                        ? `${activity.category} — ${activity.exam_period}`
                        : activity.category);
                    const date = new Date(`${activity.activity_date}T00:00:00`);
                    addCell(row, Number.isNaN(date.getTime())
                        ? activity.activity_date
                        : date.toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'}));
                    addCell(row, `${activity.semester}, AY ${activity.school_year}`);

                    const statusCell = row.insertCell();
                    const badge = document.createElement('span');
                    badge.className = `gc-activity-status ${statusClass(activity.status)}`;
                    badge.textContent = activity.status;
                    statusCell.appendChild(badge);

                    const actionsCell = row.insertCell();
                    actionsCell.className = 'gc-activity-actions';
                    const isOwner = Number(activity.submitted_by) === state.currentEmployeeId;
                    if (activity.status === 'Pending Approval' && state.canApprove) {
                        appendAction(actionsCell, 'Approve', 'approve', activity.id, 'is-approve');
                        appendAction(actionsCell, 'Reject', 'reject', activity.id, 'is-reject');
                    } else if (activity.status === 'Approved' && (isOwner || state.canApprove)) {
                        appendAction(actionsCell, 'Complete', 'complete', activity.id, 'is-complete');
                    } else if (activity.status === 'Rejected' && activity.decision_note) {
                        actionsCell.title = activity.decision_note;
                        actionsCell.textContent = 'See rejection note';
                    } else {
                        actionsCell.textContent = '—';
                    }
                });
            }

            root.querySelector('#gcActivityTotal').textContent = String(shown.length);
            root.querySelector('#gcActivityPending').textContent = String(shown.filter(item => item.status === 'Pending Approval').length);
            root.querySelector('#gcActivityApproved').textContent = String(shown.filter(item => item.status === 'Approved').length);
            root.querySelector('#gcActivityCompleted').textContent = String(shown.filter(item => item.status === 'Completed').length);
            root.querySelector('#gcActivityResultCount').textContent = `${shown.length} ${shown.length === 1 ? 'record' : 'records'}`;
        };

        const loadFilters = () => {
            const selectedValues = Object.fromEntries(
                Object.entries(fields).map(([key, select]) => [key, select.value])
            );
            const activeYear = uniqueOptions(state.periods, 'school_year_id', 'school_year');
            fields.year.replaceChildren(
                activeYear.length
                    ? new Option(activeYear[0].label, activeYear[0].value, true, true)
                    : new Option('No active academic year', '')
            );
            fillSelect(fields.semester, 'All Semesters', uniqueOptions(state.periods, 'semester_id', 'semester'), selectedValues.semester);
            fillSelect(fields.department, 'All Departments', uniqueOptions(state.assignmentOptions, 'department_id', 'department_name'), selectedValues.department);
            fillSelect(fields.section, 'All Sections', uniqueOptions(state.assignmentOptions, 'section_id', 'section_code'), selectedValues.section);
            fillSelect(fields.subject, 'All Subjects', uniqueOptions(state.assignmentOptions, 'subject_id', 'subject_name'), selectedValues.subject);

            const teachers = new Map();
            state.assignmentOptions.forEach(assignment => assignment.faculty.forEach(faculty => {
                teachers.set(String(faculty.id), faculty.name);
            }));
            fillSelect(fields.faculty, 'All Teachers', [...teachers].map(([value, label]) => ({value, label})), selectedValues.faculty);
            fillSelect(fields.category, 'All Categories', state.categories.map(value => ({value, label: value})), selectedValues.category);
            fillSelect(fields.status, 'All Statuses', statuses.map(value => ({value, label: value})), selectedValues.status);
        };

        const populateAssignmentSelect = () => {
            assignmentSelect.replaceChildren(new Option('Select subject and section', ''));
            state.assignmentOptions.forEach(option => {
                const label = `${option.subject_code} — ${option.subject_name} | ${option.section_code} | ${option.department_code} | ${option.semester}, AY ${option.school_year}`;
                assignmentSelect.add(new Option(label, option.key));
            });
            assignmentSelect.disabled = state.assignmentOptions.length === 0;
            if (state.assignmentOptions.length === 0) {
                assignmentSelect.replaceChildren(new Option('No active subject assignments available', ''));
            }
        };

        const populateTeacherSelect = () => {
            const assignment = state.assignmentOptions.find(option => option.key === assignmentSelect.value);
            teacherSelect.replaceChildren();
            if (!assignment || !assignment.faculty.length) {
                teacherSelect.add(new Option('No eligible teachers', ''));
                teacherSelect.disabled = true;
                return;
            }
            assignment.faculty.forEach(teacher => {
                teacherSelect.add(new Option(teacher.name, String(teacher.id)));
            });
            teacherSelect.disabled = false;
        };

        const loadData = async (successMessage = '') => {
            setNotice('Loading GenEd activities...');
            try {
                const data = await request();
                state.activities = Array.isArray(data.activities) ? data.activities : [];
                state.assignmentOptions = Array.isArray(data.assignment_options) ? data.assignment_options : [];
                state.periods = Array.isArray(data.periods) ? data.periods : [];
                state.categories = Array.isArray(data.categories) ? data.categories : [];
                state.examPeriods = Array.isArray(data.exam_periods) ? data.exam_periods : [];
                state.currentEmployeeId = Number(data.current_employee_id) || 0;
                state.canCreate = Boolean(data.can_create);
                state.canApprove = Boolean(data.can_approve);
                addButton.hidden = !state.canCreate;
                loadFilters();
                populateAssignmentSelect();
                categorySelect.replaceChildren(new Option('Select category', ''));
                state.categories.forEach(category => categorySelect.add(new Option(category, category)));
                examPeriodSelect.replaceChildren(new Option('Select exam period', ''));
                state.examPeriods.forEach(period => examPeriodSelect.add(new Option(period, period)));
                setNotice(successMessage || 'Activities are linked to a subject and section. Teachers are limited to active faculty assigned to that subject and section.');
                render();
            } catch (error) {
                state.activities = [];
                state.assignmentOptions = [];
                state.periods = [];
                addButton.hidden = true;
                loadFilters();
                populateAssignmentSelect();
                setNotice(error.message || 'Unable to load GenEd activities.', true);
                render();
            }
        };

        const openModal = () => {
            if (!state.assignmentOptions.length) {
                setNotice('No active subject assignments with eligible teachers are available.', true);
                return;
            }
            form.reset();
            setFormMessage('');
            populateAssignmentSelect();
            populateTeacherSelect();
            examPeriodGroup.hidden = true;
            examPeriodSelect.required = false;
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            root.querySelector('#gcActivityTitle').focus();
        };

        const closeModal = () => {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        };

        addButton.addEventListener('click', openModal);
        assignmentSelect.addEventListener('change', populateTeacherSelect);
        categorySelect.addEventListener('change', () => {
            const isCommonExam = categorySelect.value === 'Common Exams';
            examPeriodGroup.hidden = !isCommonExam;
            examPeriodSelect.required = isCommonExam;
            if (!isCommonExam) examPeriodSelect.value = '';
        });
        root.querySelectorAll('[data-close-activity]').forEach(button => button.addEventListener('click', closeModal));
        modal.addEventListener('click', event => {
            if (event.target === modal) closeModal();
        });
        Object.values(fields).forEach(field => {
            field.addEventListener(field === fields.search ? 'input' : 'change', render);
        });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            const assignment = state.assignmentOptions.find(option => option.key === assignmentSelect.value);
            const facultyIds = Array.from(teacherSelect.selectedOptions).map(option => Number(option.value)).filter(Boolean);
            if (!assignment || !facultyIds.length) {
                setFormMessage('Select a subject assignment and at least one eligible teacher.', true);
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            setFormMessage('Submitting activity...');
            try {
                const data = await request({
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'create',
                        title: root.querySelector('#gcActivityTitle').value.trim(),
                        description: root.querySelector('#gcActivityDescription').value.trim(),
                        category: categorySelect.value,
                        exam_period: examPeriodSelect.value,
                        activity_date: root.querySelector('#gcActivityDate').value,
                        school_year_id: assignment.school_year_id,
                        semester_id: assignment.semester_id,
                        section_id: assignment.section_id,
                        subject_id: assignment.subject_id,
                        faculty_ids: facultyIds
                    })
                });
                closeModal();
                await loadData(data.message || 'Activity submitted for approval.');
            } catch (error) {
                setFormMessage(error.message || 'Unable to submit the activity.', true);
            } finally {
                submitButton.disabled = false;
            }
        });

        root.addEventListener('click', async event => {
            const button = event.target.closest('[data-activity-action]');
            if (!button) return;

            const action = button.dataset.activityAction;
            let decisionNote = '';
            if (action === 'reject') {
                const note = window.prompt('Reason for rejecting this activity:');
                if (note === null) return;
                decisionNote = note.trim();
                if (!decisionNote) {
                    setNotice('A rejection reason is required.', true);
                    return;
                }
            } else if (action === 'complete' && !window.confirm('Mark this activity as completed?')) {
                return;
            }

            button.disabled = true;
            try {
                const data = await request({
                    method: 'POST',
                    body: JSON.stringify({
                        action,
                        id: Number(button.dataset.activityId),
                        decision_note: decisionNote
                    })
                });
                await loadData(data.message || 'Activity status updated.');
            } catch (error) {
                button.disabled = false;
                setNotice(error.message || 'Unable to update the activity status.', true);
            }
        });

        loadData();
    }

    document.addEventListener('DOMContentLoaded', initialize);
    window.addEventListener('page:loaded', event => {
        if (['academic activities', 'GenEducation activities'].includes(event.detail?.page)) initialize();
    });
})();
