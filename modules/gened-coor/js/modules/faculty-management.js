function initFacultyManagementPage() {
    console.log('');
    console.log('===================================================================');
    console.log('=== initFacultyManagementPage() INITIALIZATION STARTED ===');
    console.log('===================================================================');
    console.log('');
    
    // DEBUG: Check CSS display rules for modal.show
    const styleSheets = document.styleSheets;
    console.log('🔍 CHECKING CSS RULES FOR MODAL DISPLAY...');
    console.log('Total stylesheets found:', styleSheets.length);
    console.log('');
    
    let modalShowRules = [];
    let modalRules = [];
    
    for (let sheetIdx = 0; sheetIdx < styleSheets.length; sheetIdx++) {
        const sheet = styleSheets[sheetIdx];
        let sheetName = sheet.href || 'inline';
        try {
            const rules = sheet.cssRules || sheet.rules;
            if (!rules) continue;
            
            for (let ruleIdx = 0; ruleIdx < rules.length; ruleIdx++) {
                const rule = rules[ruleIdx];
                if (!rule.selectorText) continue;
                
                // Check for .modal.show
                if (rule.selectorText.includes('.modal.show')) {
                    console.log(`✓ Found .modal.show rule in ${sheetName}:`);
                    console.log(`    Selector: ${rule.selectorText}`);
                    console.log(`    Display: ${rule.style.display || 'NOT SET'}`);
                    console.log(`    Full CSS Text: ${rule.style.cssText}`);
                    modalShowRules.push({sheet: sheetName, selector: rule.selectorText, display: rule.style.display});
                }
                
                // Also check for just .modal
                if (rule.selectorText === '.modal') {
                    console.log(`✓ Found .modal rule in ${sheetName}:`);
                    console.log(`    Display: ${rule.style.display || 'NOT SET'}`);
                    console.log(`    Visibility: ${rule.style.visibility || 'NOT SET'}`);
                    console.log(`    Full CSS Text: ${rule.style.cssText}`);
                    modalRules.push({sheet: sheetName, display: rule.style.display});
                }
            }
        } catch (e) {
            // CORS errors on external stylesheets are expected
        }
    }
    
    console.log('');
    if (modalShowRules.length === 0) {
        console.warn('⚠️ WARNING: No CSS rule for .modal.show found!');
        console.warn('    The modal will not display when .show class is added!');
        console.warn('    Check CSS files or add: .modal.show { display: flex; }');
    } else {
        console.log(`✓ Found ${modalShowRules.length} .modal.show CSS rule(s)`);
    }
    
    if (modalRules.length === 0) {
        console.warn('⚠️ WARNING: No CSS rule for .modal found!');
    } else {
        console.log(`✓ Found ${modalRules.length} .modal CSS rule(s)`);
    }
    
    // Test: Check actual computed style of modal element
    console.log('');
    console.log('🔍 CHECKING ACTUAL MODAL ELEMENT...');
    const testModal = document.getElementById('fmEngagementDetailsModal');
    if (testModal) {
        console.log('✓ Modal element (#fmEngagementDetailsModal) exists in DOM');
        console.log('  Current classes:', testModal.className);
        console.log('  Computed display (without .show):', window.getComputedStyle(testModal).display);
        
        testModal.classList.add('show');
        console.log('  Added .show class temporarily...');
        console.log('  Computed display (with .show):', window.getComputedStyle(testModal).display);
        testModal.classList.remove('show');
        console.log('  Removed .show class');
    } else {
        console.error('✗ Modal element (#fmEngagementDetailsModal) NOT FOUND in DOM!');
    }
    
    console.log('');
    console.log('===================================================================');
    
    const tabButtons = document.querySelectorAll('[data-fm-tab]');
    const panels = document.querySelectorAll('.fm-panel');

    function bindAddEngagementEmployeeTypeListener() {
        if (document.body.dataset.addEngagementSyncBound === 'true') {
            return;
        }

        const syncEngagementType = (employeeSelect) => {
            const aeTypeInput = document.getElementById('aeType');
            if (!employeeSelect || !aeTypeInput) {
                return;
            }

            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            const employmentType = (selectedOption && selectedOption.value)
                ? (selectedOption.dataset.employmentType || '')
                : '';

            aeTypeInput.value = employmentType;
            aeTypeInput.setAttribute('value', employmentType);
            aeTypeInput.readOnly = true;
            aeTypeInput.disabled = true;
        };

        document.addEventListener('change', function (event) {
            const target = event.target;
            if (!target || target.id !== 'aeEmployee') {
                return;
            }

            syncEngagementType(target);
        });

        const initialEmployeeSelect = document.getElementById('aeEmployee');
        const initialTypeInput = document.getElementById('aeType');
        if (initialEmployeeSelect && initialTypeInput) {
            syncEngagementType(initialEmployeeSelect);
        }

        document.body.dataset.addEngagementSyncBound = 'true';
    }

    bindAddEngagementEmployeeTypeListener();

    function activateTab(tabName) {
        tabButtons.forEach(button => {
            button.classList.toggle('active', button.dataset.fmTab === tabName);
        });
        panels.forEach(panel => {
            panel.classList.toggle('active', panel.id === `fm-panel-${tabName}`);
        });
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            activateTab(this.dataset.fmTab);
        });
    });

    function initializeSearch(inputId, tableId, rowFilter) {
        const searchInput = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!searchInput || !table) return;

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const originalRows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.classList.contains('no-data'));

        function performSearch() {
            const term = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            tbody.innerHTML = '';
            originalRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                const matches = (!term || rowText.includes(term)) && (!rowFilter || rowFilter(row));
                if (matches) {
                    tbody.appendChild(row);
                    visibleCount += 1;
                }
            });

            if (visibleCount === 0) {
                const noDataRow = document.createElement('tr');
                noDataRow.className = 'no-data';
                const colCount = table.querySelectorAll('thead th').length || 1;
                noDataRow.innerHTML = `<td colspan="${colCount}" class="text-center text-muted" style="padding: 20px;">No records match the search criteria.</td>`;
                tbody.appendChild(noDataRow);
            }
        }

        searchInput.addEventListener('input', performSearch);
        searchInput.addEventListener('change', performSearch);
    }

    initializeSearch('searchCredentials', 'credentialsTable');
    initializeSearch('searchAttainment', 'attainmentTable');
    let trainingFilter = 'all';
    const trainingTable = document.getElementById('trainingsTable');
    const trainingFilterButtons = document.querySelectorAll('[data-training-filter]');
    const trainingTbody = trainingTable?.querySelector('tbody');
    const trainingRows = trainingTbody
        ? Array.from(trainingTbody.querySelectorAll('tr')).filter(row => !row.classList.contains('no-data'))
        : [];

    function applyTrainingFilter() {
        if (!trainingTbody) return;
        const matchingRows = trainingRows.filter(row => {
            const status = (row.dataset.recordStatus || '').toLowerCase();
            if (trainingFilter === 'archived') return status === 'archived';
            if (trainingFilter === 'completed') return status === 'completed';
            if (trainingFilter === 'active') return ['pending', 'ongoing', 'for review', 'approved'].includes(status);
            return status !== 'archived';
        });
        trainingTbody.replaceChildren(...matchingRows);
        if (!matchingRows.length) {
            const noDataRow = document.createElement('tr');
            noDataRow.className = 'no-data';
            noDataRow.innerHTML = `<td colspan="${trainingTable.querySelectorAll('thead th').length}" class="text-center text-muted" style="padding: 20px;">No trainings match this filter.</td>`;
            trainingTbody.appendChild(noDataRow);
        }
    }

    if (trainingTable && trainingFilterButtons.length) {
        trainingFilterButtons.forEach(button => {
            button.addEventListener('click', function () {
                trainingFilter = this.dataset.trainingFilter || 'all';
                trainingFilterButtons.forEach(filterButton => filterButton.classList.toggle('active', filterButton === this));
                applyTrainingFilter();
            });
        });
        applyTrainingFilter();
    }

    function openProfileModal(data) {
        const modal = document.getElementById('fmProfileModal');
        if (!modal) return;

        document.getElementById('fmModalEmployeeId').textContent = data.employeeId || 'N/A';
        document.getElementById('fmModalFacultyName').textContent = data.facultyName || 'N/A';
        document.getElementById('fmModalDepartment').textContent = data.department || 'Not provided';
        document.getElementById('fmModalEmail').textContent = data.email || 'Not provided';
        document.getElementById('fmModalCreatedAt').textContent = data.createdAt || 'Not provided';
        document.getElementById('fmModalPosition').textContent = data.position || 'Not provided';
        document.getElementById('fmModalEmploymentType').textContent = data.employmentType || 'Not provided';
        document.getElementById('fmModalStatus').textContent = data.employmentStatus || 'Not provided';
        modal.classList.add('show');
    }

    function closeProfileModal() {
        const modal = document.getElementById('fmProfileModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    function openEducationModal(employeeCode, facultyName, department, records, requirements) {
        const modal = document.getElementById('fmEducationModal');
        if (!modal) return;

        const employeeIdElement = document.getElementById('fmEducationEmployeeId');
        const facultyNameElement = document.getElementById('fmEducationFacultyName');
        const departmentElement = document.getElementById('fmEducationDepartment');
        const historyBody = document.getElementById('fmEducationHistoryBody');
        const requirementsBody = document.getElementById('fmEducationRequirementsBody');

        employeeIdElement.textContent = employeeCode || 'N/A';
        facultyNameElement.textContent = facultyName || 'N/A';
        departmentElement.textContent = department || 'Not provided';

        if (!records || records.length === 0) {
            historyBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted" style="padding: 18px;">No educational attainment records found.</td></tr>';
        } else {
            historyBody.innerHTML = records.map(record => {
                const degree = record.level || 'N/A';
                const school = record.school_name || 'N/A';
                const year = record.year_graduated || 'N/A';
                const course = record.course || 'N/A';

                return `
                    <tr>
                        <td>${course || degree}</td>
                        <td>${school}</td>
                        <td>${year}</td>
                        <td>${degree}</td>
                    </tr>
                `;
            }).join('');
        }

        if (!requirements || requirements.length === 0) {
            requirementsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding: 18px;">No faculty requirement records found.</td></tr>';
        } else {
            requirementsBody.innerHTML = requirements.map(requirement => {
                const requirementName = requirement.requirement_name || 'N/A';
                const status = requirement.status || 'N/A';
                const remarks = requirement.remarks || 'N/A';
                const submittedDate = requirement.submitted_date || 'N/A';
                const followUpDate = requirement.follow_up_date || 'N/A';

                return `
                    <tr>
                        <td>${requirementName}</td>
                        <td>${renderRequirementStatus(status)}</td>
                        <td>${remarks}</td>
                        <td>${submittedDate}</td>
                        <td>${followUpDate}</td>
                    </tr>
                `;
            }).join('');
        }

        modal.classList.add('show');
    }

    function closeEducationModal() {
        const modal = document.getElementById('fmEducationModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    function openTrainingModal(employeeCode, facultyName, employmentType, employmentStatus, records, recordSource, recordId) {
        const modal = document.getElementById('fmTrainingModal');
        if (!modal) return;

        const employeeIdElement = document.getElementById('fmTrainingEmployeeId');
        const facultyNameElement = document.getElementById('fmTrainingFacultyName');
        const employmentTypeElement = document.getElementById('fmTrainingEmploymentType');
        const employmentStatusElement = document.getElementById('fmTrainingEmploymentStatus');
        const historyBody = document.getElementById('fmTrainingHistoryBody');

        employeeIdElement.textContent = employeeCode || 'N/A';
        facultyNameElement.textContent = facultyName || 'N/A';
        employmentTypeElement.textContent = employmentType || 'N/A';
        employmentStatusElement.textContent = employmentStatus || 'N/A';

        const certificateContainer = document.getElementById('fmTrainingCertificate');
        if (certificateContainer) {
            certificateContainer.replaceChildren();
            certificateContainer.className = 'mt-3 text-muted';
        }

        if (!records || records.length === 0) {
            historyBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding: 18px;">No training or certification records found.</td></tr>';
            modal.classList.add('show');
            return;
        }

        historyBody.innerHTML = records.map(record => {
            const type = record.type || record.engagement_type || 'Training / Certification';
            const title = record.title || record.cert_name || 'N/A';
            const organization = record.organization || record.issuing_organization || 'N/A';
            const datePeriod = record.date_period || (record.start_date && record.end_date ? `${record.start_date} to ${record.end_date}` : record.date_issued || record.start_date || 'N/A');
            const status = record.status || 'Completed';
            const outcome = record.outcome || 'N/A';

            return `
                <tr>
                    <td>${type}</td>
                    <td>${title}</td>
                    <td>${organization}</td>
                    <td>${datePeriod}</td>
                    <td>${status}</td>
                    <td>${outcome}</td>
                </tr>
            `;
        }).join('');

        if (certificateContainer) {
            const certificate = records.find(record => record.certificate_path);
            if (recordSource === 'cc_certification_engagements' && recordId) {
                const link = document.createElement('a');
                const url = new URL('/schoolm/modules/gened-coor/api/download_training_certificate.php', window.location.origin);
                url.searchParams.set('source', recordSource);
                url.searchParams.set('record_id', recordId);
                link.href = url.toString();
                link.className = 'btn btn-sm btn-primary';
                link.textContent = 'Download Certificate';
                certificateContainer.appendChild(link);
            } else if (certificate && recordSource && recordId) {
                const link = document.createElement('a');
                const url = new URL('/schoolm/modules/gened-coor/api/download_training_certificate.php', window.location.origin);
                url.searchParams.set('source', recordSource);
                url.searchParams.set('record_id', recordId);
                link.href = url.toString();
                link.className = 'btn btn-sm btn-primary';
                link.textContent = 'Download Certificate';
                certificateContainer.appendChild(link);
            } else {
                certificateContainer.textContent = 'No certificate uploaded.';
                certificateContainer.className = 'mt-3 text-muted';
            }
        }

        modal.classList.add('show');
    }

    function closeTrainingModal() {
        const modal = document.getElementById('fmTrainingModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    function formatBreakDuration(value) {
        if (!value || value === '--') return 'N/A';
        const numericValue = Number(value);
        if (!Number.isNaN(numericValue) && numericValue > 0) {
            return `${numericValue} minutes`;
        }
        return String(value);
    }

    function formatTimeDisplay(value) {
        if (!value || value === '--') return '--';

        const str = String(value).trim();
        if (!/^\d{1,2}:\d{2}(:\d{2})?$/.test(str)) {
            return str;
        }

        const [hoursPart, minutesPart] = str.split(':');
        const hours = Number(hoursPart);
        const minutes = Number(minutesPart || 0);
        const period = hours >= 12 ? 'PM' : 'AM';
        const normalizedHours = hours % 12 === 0 ? 12 : hours % 12;
        return `${normalizedHours}:${String(minutes).padStart(2, '0')} ${period}`;
    }

    function openShiftScheduleModal(scheduleData) {
        const modal = document.getElementById('fmShiftScheduleModal');
        const content = document.getElementById('fmShiftScheduleContent');
        const emptyState = document.getElementById('fmShiftScheduleEmpty');
        if (!modal) return;

        if (!scheduleData || !scheduleData.shift_name || scheduleData.shift_name === 'N/A') {
            if (content) content.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
            modal.classList.add('show');
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        if (content) content.style.display = 'block';

        document.getElementById('fmShiftFacultyName').textContent = scheduleData.faculty_name || 'N/A';
        document.getElementById('fmShiftEmployeeId').textContent = scheduleData.employee_id || 'N/A';
        document.getElementById('fmShiftDepartment').textContent = scheduleData.department || 'Not provided';
        document.getElementById('fmShiftName').textContent = scheduleData.shift_name || 'N/A';
        document.getElementById('fmShiftBreakDuration').textContent = formatBreakDuration(scheduleData.break_duration || 'N/A');
        document.getElementById('fmShiftEffectiveFrom').textContent = scheduleData.effective_from || 'N/A';
        document.getElementById('fmShiftEffectiveTo').textContent = scheduleData.effective_to || 'Ongoing';
        document.getElementById('fmShiftStatus').textContent = scheduleData.status || 'Active';

        const weeklyBody = document.getElementById('fmShiftWeeklyBody');
        if (weeklyBody) {
            const days = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const weeklySchedule = scheduleData.weekly_schedule || {};
            const hasWeeklyRows = Object.keys(weeklySchedule).length > 0;

            if (!hasWeeklyRows) {
                weeklyBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding: 18px;">No weekly day-specific schedule configured.</td></tr>';
            } else {
                weeklyBody.innerHTML = days.map(day => {
                    const row = weeklySchedule[day] || { start_time: '--', break_start: '--', break_end: '--', end_time: '--' };
                    return `
                        <tr>
                            <td>${day}</td>
                            <td>${formatTimeDisplay(row.start_time)}</td>
                            <td>${formatTimeDisplay(row.break_start)}</td>
                            <td>${formatTimeDisplay(row.break_end)}</td>
                            <td>${formatTimeDisplay(row.end_time)}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        modal.classList.add('show');
    }

    function closeShiftScheduleModal() {
        const modal = document.getElementById('fmShiftScheduleModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    function openEngagementDetailsModal(engagementData, employeeData) {
        console.log('');
        console.log('=== openEngagementDetailsModal() CALLED ===');
        console.log('  engagementData:', engagementData);
        console.log('  employeeData:', employeeData);
        
        const modal = document.getElementById('fmEngagementDetailsModal');
        console.log('Modal element (#fmEngagementDetailsModal) found:', !!modal);
        if (!modal) {
            console.log('✗✗✗ CRITICAL ERROR: Modal #fmEngagementDetailsModal NOT FOUND in DOM!');
            console.log('Check if modal HTML was added to faculty-management.php');
            return;
        }

        const emptyState = document.getElementById('fmEngagementDetailsEmpty');
        const content = document.getElementById('fmEngagementDetailsContent');
        console.log('  emptyState (#fmEngagementDetailsEmpty) found:', !!emptyState);
        console.log('  content (#fmEngagementDetailsContent) found:', !!content);
        
        if (!engagementData || !employeeData) {
            console.log('WARNING: No data provided, showing empty state');
            if (emptyState) emptyState.style.display = 'block';
            if (content) content.style.display = 'none';
            modal.classList.add('show');
            console.log('Modal classes after add:', modal.className);
            console.log('Computed display (should be flex/block):', window.getComputedStyle(modal).display);
            console.log('Computed visibility:', window.getComputedStyle(modal).visibility);
            console.log('Computed opacity:', window.getComputedStyle(modal).opacity);
            return;
        }

        const facultyName = `${employeeData.first_name || ''} ${employeeData.middle_name || ''} ${employeeData.last_name || ''}`.trim();
        console.log('Faculty name constructed:', facultyName);
        console.log('Setting content fields...');
        
        try {
            document.getElementById('fmEngagementDetailsEmployeeId').textContent = employeeData.employee_id || 'N/A';
            document.getElementById('fmEngagementDetailsFacultyName').textContent = facultyName || 'N/A';
            document.getElementById('fmEngagementDetailsType').textContent = engagementData.engagement_type || 'N/A';
            document.getElementById('fmEngagementDetailsStatus').textContent = engagementData.status || 'N/A';
            document.getElementById('fmEngagementDetailsTitle').textContent = engagementData.title || 'N/A';
            document.getElementById('fmEngagementDetailsOrganization').textContent = engagementData.organization || 'N/A';
            document.getElementById('fmEngagementDetailsStartDate').textContent = engagementData.start_date || 'N/A';
            document.getElementById('fmEngagementDetailsEndDate').textContent = engagementData.end_date || 'N/A';
            console.log('✓ All text content fields set successfully');
        } catch (error) {
            console.log('✗ Error setting text content:', error.message);
        }

        const certGenContainer = document.getElementById('fmEngagementDetailsCertGenContainer');
        if (engagementData.certificate_generated_at && engagementData.certificate_generated_at !== 'N/A' && engagementData.certificate_generated_at !== '') {
            if (certGenContainer) certGenContainer.style.display = 'block';
            document.getElementById('fmEngagementDetailsCertGenDate').textContent = engagementData.certificate_generated_at;
            console.log('✓ Certificate section shown with date:', engagementData.certificate_generated_at);
        } else {
            if (certGenContainer) certGenContainer.style.display = 'none';
            console.log('Certificate section hidden (no date)');
        }

        if (emptyState) emptyState.style.display = 'none';
        if (content) content.style.display = 'block';
        
        console.log('');
        console.log('=== ADDING "show" CLASS TO MODAL ===');
        console.log('Modal classes BEFORE:', modal.className);
        console.log('Modal display BEFORE:', window.getComputedStyle(modal).display);
        
        modal.classList.add('show');
        
        console.log('Modal classes AFTER:', modal.className);
        console.log('Modal display AFTER:', window.getComputedStyle(modal).display);
        console.log('Modal visibility AFTER:', window.getComputedStyle(modal).visibility);
        console.log('Modal opacity AFTER:', window.getComputedStyle(modal).opacity);
        console.log('Modal position AFTER:', window.getComputedStyle(modal).position);
        console.log('Modal z-index AFTER:', window.getComputedStyle(modal).zIndex);
        console.log('');
        console.log('✓✓✓ Modal should now be visible ✓✓✓');
    }

    function closeEngagementDetailsModal() {
        const modal = document.getElementById('fmEngagementDetailsModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    // Use event delegation for table action buttons so listeners survive DOM re-renders
    // Credentials table: handle profile view
    const credentialsTable = document.getElementById('credentialsTable');
    if (credentialsTable) {
        credentialsTable.addEventListener('click', function (e) {
            const btn = e.target.closest('.view-profile-btn');
            if (!btn) return;
            const row = btn.closest('tr');
            if (!row) return;

            openProfileModal({
                employeeId: row.dataset.employeeCode || row.cells[0]?.textContent || '',
                facultyName: row.dataset.facultyName || row.cells[1]?.textContent || '',
                department: row.dataset.department || row.cells[2]?.textContent || '',
                email: row.dataset.email || '',
                createdAt: row.dataset.createdAt || '',
                position: row.dataset.position || row.cells[3]?.textContent || '',
                employmentType: row.dataset.employmentType || row.cells[4]?.textContent || '',
                employmentStatus: row.dataset.employmentStatus || row.cells[5]?.textContent || ''
            });
        });
    }

    // Attainment table: handle attainment view
    const attainmentTable = document.getElementById('attainmentTable');
    if (attainmentTable) {
        attainmentTable.addEventListener('click', function (e) {
            const editButton = e.target.closest('.edit-attainment-btn');
            if (editButton) {
                const row = editButton.closest('tr');
                if (!row) return;
                let records = [];
                try {
                    records = JSON.parse(row.dataset.records || '[]');
                } catch (error) {
                    records = [];
                }
                openEditEducationModal({
                    employeeId: row.dataset.employeeId,
                    facultyName: row.dataset.facultyName || row.cells[1]?.textContent || 'N/A'
                }, records[0] || null);
                return;
            }

            const btn = e.target.closest('.view-attainment-btn');
            if (!btn) return;
            const row = btn.closest('tr');
            if (!row) return;

            const employeeId = row.dataset.employeeId || row.dataset.employeeCode || row.cells[0]?.textContent || '';

            let records = [];
            try {
                records = JSON.parse(row.dataset.records || '[]');
            } catch (error) {
                records = [];
            }

            const fallbackOpen = () => openEducationModal(
                row.dataset.employeeCode || row.cells[0]?.textContent || 'N/A',
                row.dataset.facultyName || row.cells[1]?.textContent || 'N/A',
                row.dataset.department || 'Not provided',
                records,
                []
            );

            if (!employeeId) {
                fallbackOpen();
                return;
            }

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({
                    action: 'get_faculty_education',
                    employee_id: String(employeeId)
                })
            })
            .then(response => response.json())
            .then(data => {
                const responseRecords = Array.isArray(data && data.records) ? data.records : records;
                const responseRequirements = Array.isArray(data && data.requirements) ? data.requirements : [];

                openEducationModal(
                    row.dataset.employeeCode || row.cells[0]?.textContent || 'N/A',
                    row.dataset.facultyName || row.cells[1]?.textContent || 'N/A',
                    row.dataset.department || 'Not provided',
                    responseRecords,
                    responseRequirements
                );
            })
            .catch(() => fallbackOpen());
        });
    }

    function openEditEducationModal(faculty, record) {
        const modal = document.getElementById('fmEditEducationModal');
        const form = document.getElementById('fmEditEducationForm');
        if (!modal || !form) return;

        form.reset();
        document.getElementById('fmEditEducationEmployeeId').value = faculty.employeeId || '';
        document.getElementById('fmEditEducationRecordId').value = record?.education_id || '';
        document.getElementById('fmEditEducationFacultyName').value = faculty.facultyName || 'N/A';
        document.getElementById('fmEditEducationTitle').textContent = record ? 'Edit Educational Attainment' : 'Add Educational Attainment';
        document.getElementById('fmSaveEducationButton').textContent = record ? 'Save Changes' : 'Add Record';
        document.getElementById('fmEditEducationLevel').value = record?.level || '';
        document.getElementById('fmEditEducationCourse').value = record?.course || '';
        document.getElementById('fmEditEducationSchool').value = record?.school_name || '';
        document.getElementById('fmEditEducationYear').value = record?.year_graduated || '';
        document.getElementById('fmEditEducationHonors').value = record?.honors || '';
        document.getElementById('fmEditEducationMessage').textContent = '';
        modal.classList.add('show');
    }

    const editEducationModal = document.getElementById('fmEditEducationModal');
    const editEducationForm = document.getElementById('fmEditEducationForm');
    if (editEducationModal && !editEducationModal.dataset.handlersBound) {
        editEducationModal.dataset.handlersBound = 'true';
        editEducationModal.querySelectorAll('.modal-close').forEach(button => {
            button.addEventListener('click', () => editEducationModal.classList.remove('show'));
        });
        editEducationModal.addEventListener('click', event => {
            if (event.target === editEducationModal) editEducationModal.classList.remove('show');
        });
    }

    if (editEducationForm && !editEducationForm.dataset.submitBound) {
        editEducationForm.dataset.submitBound = 'true';
        editEducationForm.addEventListener('submit', async event => {
            event.preventDefault();
            const saveButton = document.getElementById('fmSaveEducationButton');
            const message = document.getElementById('fmEditEducationMessage');
            const formData = new URLSearchParams(new FormData(editEducationForm));
            formData.set('action', 'save_faculty_education');
            saveButton.disabled = true;
            message.textContent = 'Saving educational attainment...';
            message.classList.remove('text-danger');
            message.classList.add('text-muted');

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: formData
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Unable to save educational attainment.');
                }

                editEducationModal.classList.remove('show');
                const searchTerm = document.getElementById('searchAttainment')?.value || '';
                const refreshResponse = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams({ action: 'search_records', table: 'attainmentTable', term: searchTerm })
                });
                const refreshed = await refreshResponse.json();
                const tbody = document.querySelector('#attainmentTable tbody');
                if (refreshed.success && tbody) tbody.innerHTML = refreshed.html;
            } catch (error) {
                message.textContent = error.message || 'Unable to save educational attainment.';
                message.classList.remove('text-muted');
                message.classList.add('text-danger');
            } finally {
                saveButton.disabled = false;
            }
        });
    }

    // Trainings table: delegate all training-related action buttons
    const trainingsTable = document.getElementById('trainingsTable');
    if (trainingsTable) {
        trainingsTable.addEventListener('click', function (e) {
            const btn = e.target.closest('button, a');
            if (!btn) return;
            const row = btn.closest('tr');
            if (!row) return;

            // View training / archived engagement details
            if (btn.classList.contains('view-training-btn')) {
                console.log('=== VIEW TRAINING (delegated) CLICKED ===');
                const recordSource = String(row.dataset.recordSource || '');
                let records = [];
                try { records = JSON.parse(row.dataset.records || '[]'); } catch (err) { records = []; }

                openTrainingModal(
                    row.dataset.employeeCode || row.cells[0]?.textContent || 'N/A',
                    row.dataset.facultyName || row.cells[1]?.textContent || 'N/A',
                    row.dataset.employmentType || 'N/A',
                    row.dataset.employmentStatus || 'N/A',
                    records,
                    recordSource,
                    row.dataset.recordId
                );
                return;
            }

            if (btn.classList.contains('edit-training-btn') || btn.classList.contains('upload-training-certificate-btn')) {
                let records = [];
                try { records = JSON.parse(row.dataset.records || '[]'); } catch (err) { records = []; }
                const record = records[0] || {};
                const source = row.dataset.recordSource || '';
                const isCertification = source === 'em_certifications';
                const form = document.getElementById('fmEditTrainingForm');
                form.reset();
                document.getElementById('fmTrainingRecordId').value = row.dataset.recordId || '';
                document.getElementById('fmTrainingRecordSource').value = source;
                document.getElementById('fmEditTrainingFaculty').value = row.dataset.facultyName || '';
                document.getElementById('fmEditTrainingTitle').value = record.cert_name || record.title || '';
                document.getElementById('fmEditTrainingOrganization').value = record.issuing_organization || record.organization || '';
                const engagementTypeGroup = document.getElementById('fmEditTrainingTypeGroup');
                const engagementTypeSelect = document.getElementById('fmEditTrainingType');
                engagementTypeGroup.hidden = isCertification;
                engagementTypeSelect.disabled = isCertification;
                engagementTypeSelect.required = !isCertification;
                engagementTypeSelect.value = record.engagement_type || '';
                const startInput = document.getElementById('fmEditTrainingStart');
                const endInput = document.getElementById('fmEditTrainingEnd');
                startInput.name = isCertification ? 'date_issued' : 'start_date';
                endInput.name = isCertification ? 'expiry_date' : 'end_date';
                startInput.value = record.date_issued || record.start_date || '';
                endInput.value = record.expiry_date || record.end_date || '';
                document.getElementById('fmEditTrainingStartLabel').textContent = isCertification ? 'Date Issued' : 'Start Date';
                document.getElementById('fmEditTrainingEndLabel').textContent = isCertification ? 'Expiry Date' : 'End Date';
                document.getElementById('fmEditTrainingMessage').textContent = '';
                document.getElementById('fmEditTrainingModal').classList.add('show');
                return;
            }

            if (btn.classList.contains('archive-training-btn')) {
                if (!window.confirm('Archive this training record? It will be hidden from the active list but not deleted.')) return;
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'archive_training_record',
                        record_source: row.dataset.recordSource,
                        record_id: Number(row.dataset.recordId || 0)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Unable to archive record.');
                    window.location.reload();
                })
                .catch(error => window.alert(error.message || 'Unable to archive record.'));
                return;
            }

            // Approve engagement
            if (btn.classList.contains('approve-engagement-btn')) {
                const engagementId = btn.dataset.engagementId;
                if (!engagementId) return;
                const confirmed = window.confirm('Approve this engagement record?');
                if (!confirmed) return;
                performEngagementWorkflow('approve_engagement', engagementId);
                return;
            }

            // Complete engagement
            if (btn.classList.contains('complete-engagement-btn')) {
                const engagementId = btn.dataset.engagementId;
                if (!engagementId) return;
                const allowedOutcomes = ['Continue', 'Regularize', 'End Engagement', 'Not Applicable'];
                const outcomeInput = window.prompt('Enter the engagement outcome: Continue, Regularize, End Engagement, or Not Applicable', 'Continue');
                if (outcomeInput === null) return;
                const outcome = outcomeInput.trim();
                if (!allowedOutcomes.includes(outcome)) {
                    alert('Invalid outcome. Please use one of: Continue, Regularize, End Engagement, Not Applicable.');
                    return;
                }
                const confirmed = window.confirm('Mark this engagement as completed?');
                if (!confirmed) return;
                performEngagementWorkflow('mark_completed_engagement', engagementId, outcome);
                return;
            }

            // Archive engagement
            if (btn.classList.contains('archive-engagement-btn')) {
                const engagementId = btn.dataset.engagementId;
                if (!engagementId) return;
                const confirmed = window.confirm('Archive this engagement record? This will not delete the record.');
                if (!confirmed) return;
                performEngagementWorkflow('archive_engagement', engagementId);
                return;
            }

            // Shift schedule
            if (btn.classList.contains('shift-schedule-btn')) {
                const employeeId = Number(btn.dataset.employeeId || 0);
                const facultyName = row.dataset.facultyName || 'N/A';
                const department = row.dataset.department || 'Not provided';
                if (!employeeId) { alert('Invalid faculty selected.'); return; }

                fetch('/modules/college-coor/pages/faculty-management.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'get_faculty_shift_schedule', employee_id: employeeId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success && data.schedule) {
                        openShiftScheduleModal({
                            faculty_name: data.schedule.faculty_name || facultyName,
                            employee_id: data.schedule.employee_id || employeeId,
                            department: data.schedule.department || department,
                            shift_name: data.schedule.shift_name || 'N/A',
                            shift_start_time: data.schedule.shift_start_time || 'N/A',
                            shift_end_time: data.schedule.shift_end_time || 'N/A',
                            break_duration: data.schedule.break_duration || 'N/A',
                            effective_from: data.schedule.effective_from || 'N/A',
                            effective_to: data.schedule.effective_to || 'Ongoing',
                            status: data.schedule.status || 'Active',
                            weekly_schedule: data.schedule.weekly_schedule || {}
                        });
                        return;
                    }

                    openShiftScheduleModal({ faculty_name: facultyName, employee_id: employeeId, department: department, shift_name: 'N/A' });
                })
                .catch(() => {
                    openShiftScheduleModal({ faculty_name: facultyName, employee_id: employeeId, department: department, shift_name: 'N/A' });
                });
                return;
            }

            // Generate certificate
            if (btn.classList.contains('generate-certificate-btn')) {
                const engagementId = btn.dataset.engagementId;
                if (!engagementId) return;
                const origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
                const url = origin + '/modules/college-coor/pages/engagement_certificate.php?engagement_id=' + encodeURIComponent(engagementId);
                window.open(url, '_blank');
                return;
            }
        });
    }

    const editTrainingModal = document.getElementById('fmEditTrainingModal');
    const editTrainingForm = document.getElementById('fmEditTrainingForm');
    if (editTrainingModal) {
        editTrainingModal.querySelectorAll('.modal-close').forEach(button => {
            button.addEventListener('click', () => editTrainingModal.classList.remove('show'));
        });
        editTrainingModal.addEventListener('click', event => {
            if (event.target === editTrainingModal) editTrainingModal.classList.remove('show');
        });
    }

    if (editTrainingForm) {
        editTrainingForm.addEventListener('submit', async event => {
            event.preventDefault();
            const button = document.getElementById('fmSaveTrainingButton');
            const message = document.getElementById('fmEditTrainingMessage');
            button.disabled = true;
            message.textContent = 'Saving training record...';
            message.classList.remove('text-danger');
            message.classList.add('text-muted');
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: new FormData(editTrainingForm)
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Unable to save training record.');
                window.location.reload();
            } catch (error) {
                message.textContent = error.message || 'Unable to save training record.';
                message.classList.remove('text-muted');
                message.classList.add('text-danger');
            } finally {
                button.disabled = false;
            }
        });
    }

    document.querySelectorAll('#fmProfileModal .modal-close').forEach(button => {
        button.addEventListener('click', closeProfileModal);
    });

    document.querySelectorAll('#fmEducationModal .modal-close').forEach(button => {
        button.addEventListener('click', closeEducationModal);
    });

    document.querySelectorAll('#fmTrainingModal .modal-close').forEach(button => {
        button.addEventListener('click', closeTrainingModal);
    });

    document.querySelectorAll('#fmShiftScheduleModal .modal-close').forEach(button => {
        button.addEventListener('click', closeShiftScheduleModal);
    });

    document.querySelectorAll('#fmEngagementDetailsModal .modal-close').forEach(button => {
        button.addEventListener('click', closeEngagementDetailsModal);
    });

    const engagementDetailsModal = document.getElementById('fmEngagementDetailsModal');
    if (engagementDetailsModal) {
        engagementDetailsModal.addEventListener('click', function (event) {
            if (event.target === engagementDetailsModal) {
                closeEngagementDetailsModal();
            }
        });
    }

    // Add Engagement modal handlers
    const addEngagementBtn = document.getElementById('addEngagementBtn');
    const addEngagementModal = document.getElementById('fmAddEngagementModal');
    const addEngagementForm = document.getElementById('addEngagementForm');

    function openAddEngagementModal() {
        if (!addEngagementModal) return;
        addEngagementModal.classList.add('show');
    }

    function closeAddEngagementModal() {
        if (!addEngagementModal) return;
        addEngagementModal.classList.remove('show');
    }

    if (addEngagementBtn) {
        addEngagementBtn.addEventListener('click', openAddEngagementModal);
    }

    document.querySelectorAll('#fmAddEngagementModal .modal-close').forEach(button => {
        button.addEventListener('click', closeAddEngagementModal);
    });

    if (addEngagementModal) {
        addEngagementModal.addEventListener('click', function (event) {
            if (event.target === addEngagementModal) {
                closeAddEngagementModal();
            }
        });
    }

    if (addEngagementForm) {
        addEngagementForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(addEngagementForm);
            const employeeSelect = document.getElementById('aeEmployee');
            const selectedOption = employeeSelect && employeeSelect.selectedIndex >= 0
                ? employeeSelect.options[employeeSelect.selectedIndex]
                : null;
            const employmentType = (selectedOption && selectedOption.dataset.employmentType)
                ? selectedOption.dataset.employmentType
                : '';

            const payload = {
                action: 'add_engagement',
                employee_id: Number(formData.get('employee_id')) || 0,
                engagement_type: employmentType,
                title: (formData.get('title') || '').trim(),
                organization: (formData.get('organization') || '').trim(),
                start_date: formData.get('start_date') || null,
                end_date: formData.get('end_date') || null
            };

            if (!payload.employee_id || !payload.engagement_type || !payload.title || !payload.organization || !payload.start_date || !payload.end_date) {
                alert('Please fill required fields.');
                return;
            }

            if (!confirm('Create this engagement record with status Pending?')) return;

            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    alert(data.message || 'Engagement created');
                    window.location.reload();
                    return;
                }
                alert((data && data.message) || 'Unable to create engagement');
            })
            .catch((err) => {
                console.error('Engagement fetch error:', err);
                alert('Unable to create engagement.');
            });
        });
    }

    const profileModal = document.getElementById('fmProfileModal');
    if (profileModal) {
        profileModal.addEventListener('click', function (event) {
            if (event.target === profileModal) {
                closeProfileModal();
            }
        });
    }

    const educationModal = document.getElementById('fmEducationModal');
    if (educationModal) {
        educationModal.addEventListener('click', function (event) {
            if (event.target === educationModal) {
                closeEducationModal();
            }
        });
    }

    const trainingModal = document.getElementById('fmTrainingModal');
    if (trainingModal) {
        trainingModal.addEventListener('click', function (event) {
            if (event.target === trainingModal) {
                closeTrainingModal();
            }
        });
    }

    function performEngagementWorkflow(action, engagementId, outcome) {
        if (!engagementId) {
            alert('Invalid engagement record.');
            return;
        }

        const payload = {
            action: action,
            engagement_id: Number(engagementId)
        };

        if (action === 'approve_engagement') {
            payload.approved_by = 0;
        }

        if (action === 'mark_completed_engagement') {
            payload.outcome = outcome || 'Not Applicable';
        }

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                alert(data.message || 'Action completed successfully.');
                window.location.reload();
                return;
            }

            alert((data && data.message) || 'The action could not be completed.');
        })
        .catch(() => {
            alert('The action could not be completed. Please try again.');
        });
    }

    document.querySelectorAll('.approve-engagement-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const confirmed = window.confirm('Approve this engagement record?');
            if (!confirmed) return;

            performEngagementWorkflow('approve_engagement', engagementId);
        });
    });

    document.querySelectorAll('.complete-engagement-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const allowedOutcomes = ['Continue', 'Regularize', 'End Engagement', 'Not Applicable'];
            const outcomeInput = window.prompt('Enter the engagement outcome: Continue, Regularize, End Engagement, or Not Applicable', 'Continue');
            if (outcomeInput === null) return;

            const outcome = outcomeInput.trim();
            if (!allowedOutcomes.includes(outcome)) {
                alert('Invalid outcome. Please use one of: Continue, Regularize, End Engagement, Not Applicable.');
                return;
            }

            const confirmed = window.confirm('Mark this engagement as completed?');
            if (!confirmed) return;

            performEngagementWorkflow('mark_completed_engagement', engagementId, outcome);
        });
    });

    document.querySelectorAll('.archive-engagement-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const confirmed = window.confirm('Archive this engagement record? This will not delete the record.');
            if (!confirmed) return;

            performEngagementWorkflow('archive_engagement', engagementId);
        });
    });

    document.querySelectorAll('.shift-schedule-btn').forEach(button => {
        button.addEventListener('click', function () {
            const employeeId = Number(this.dataset.employeeId || 0);
            const row = this.closest('tr');
            const facultyName = row && row.dataset.facultyName ? row.dataset.facultyName : 'N/A';
            const department = row && row.dataset.department ? row.dataset.department : 'Not provided';

            if (!employeeId) {
                alert('Invalid faculty selected.');
                return;
            }

            fetch('/modules/college-coor/pages/faculty-management.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'get_faculty_shift_schedule',
                    employee_id: employeeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success && data.schedule) {
                    openShiftScheduleModal({
                        faculty_name: data.schedule.faculty_name || facultyName,
                        employee_id: data.schedule.employee_id || employeeId,
                        department: data.schedule.department || department,
                        shift_name: data.schedule.shift_name || 'N/A',
                        shift_start_time: data.schedule.shift_start_time || 'N/A',
                        shift_end_time: data.schedule.shift_end_time || 'N/A',
                        break_duration: data.schedule.break_duration || 'N/A',
                        effective_from: data.schedule.effective_from || 'N/A',
                        effective_to: data.schedule.effective_to || 'Ongoing',
                        status: data.schedule.status || 'Active',
                        weekly_schedule: data.schedule.weekly_schedule || {}
                    });
                    return;
                }

                openShiftScheduleModal({
                    faculty_name: facultyName,
                    employee_id: employeeId,
                    department: department,
                    shift_name: 'N/A'
                });
            })
            .catch(() => {
                openShiftScheduleModal({
                    faculty_name: facultyName,
                    employee_id: employeeId,
                    department: department,
                    shift_name: 'N/A'
                });
            });
        });
    });

    document.querySelectorAll('.generate-certificate-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
            const url = origin + '/modules/college-coor/pages/engagement_certificate.php?engagement_id=' + encodeURIComponent(engagementId);
            window.open(url, '_blank');
        });
    });

    const assignmentTable = document.getElementById('facultyAssignmentsTable');
    const sectionsModal = document.getElementById('fmAssignSectionsModal');
    const subjectModal = document.getElementById('fmAssignSubjectModal');
    const sectionsForm = document.getElementById('fmAssignSectionsForm');
    const subjectForm = document.getElementById('fmAssignSubjectForm');
    const assignmentApiUrl = endpoint => new URL(`api/${endpoint}`, window.location.href).toString();

    async function requestAssignmentData(endpoint, options = {}) {
        const response = await fetch(assignmentApiUrl(endpoint), options);
        const data = await response.json();
        if (!response.ok || data.success === false || data.error) {
            throw new Error(data.message || data.error || 'Unable to complete faculty assignment request.');
        }
        return data;
    }

    function renderFacultyAssignmentRows(facultyLoads) {
        const tbody = assignmentTable?.querySelector('tbody');
        if (!tbody) return;
        tbody.replaceChildren();

        if (!Array.isArray(facultyLoads) || facultyLoads.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="7" class="text-center text-muted">No faculty records available for assignment.</td>';
            tbody.appendChild(row);
            return;
        }

        facultyLoads.forEach(faculty => {
            const row = document.createElement('tr');
            const values = [
                faculty.faculty_name || 'N/A',
                faculty.department || 'Department not assigned',
                faculty.assigned_sections ?? 0,
                faculty.assigned_subjects ?? 0,
                `${faculty.teaching_units ?? 0} / ${faculty.max_load ?? 15}`,
                faculty.load_status || 'Underloaded'
            ];
            values.forEach(value => {
                const cell = document.createElement('td');
                cell.textContent = String(value);
                row.appendChild(cell);
            });

            const actions = document.createElement('td');
            actions.className = 'actions';
            [
                ['Assign Sections', 'fmAssignSections', 'btn btn-sm btn-outline-primary'],
                ['Assign Subject', 'fmAssignSubject', 'btn btn-sm btn-outline-secondary']
            ].forEach(([label, action, className]) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = className;
                button.dataset[action] = 'true';
                button.dataset.facultyId = String(faculty.id);
                button.dataset.facultyName = faculty.faculty_name || '';
                button.textContent = label;
                actions.appendChild(button);
            });
            row.appendChild(actions);
            tbody.appendChild(row);
        });
    }

    async function reloadFacultyAssignments() {
        if (!assignmentTable) return;
        const tbody = assignmentTable.querySelector('tbody');
        if (tbody) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="7" class="text-center text-muted">Refreshing assignments...</td>';
            tbody.replaceChildren(row);
        }
        try {
            const facultyLoads = await requestAssignmentData('get_faculty_load.php');
            renderFacultyAssignmentRows(facultyLoads);
        } catch (error) {
            renderFacultyAssignmentRows([]);
            if (tbody?.firstElementChild) {
                tbody.firstElementChild.firstElementChild.textContent = error.message;
                tbody.firstElementChild.firstElementChild.classList.add('text-danger');
            }
        }
    }

    function setAssignmentMessage(elementId, message, isError = false) {
        const element = document.getElementById(elementId);
        if (!element) return;
        element.textContent = message;
        element.classList.toggle('text-danger', isError);
        element.classList.toggle('text-muted', !isError);
    }

    function closeAssignmentModal(modal) {
        modal?.classList.remove('show');
    }

    async function loadAssignmentSections() {
        const schoolYearId = document.getElementById('fmAssignSectionsSchoolYear')?.value;
        const semesterId = document.getElementById('fmAssignSectionsSemester')?.value;
        const facultyId = document.getElementById('fmAssignSectionsFacultyId')?.value;
        const sectionSelect = document.getElementById('fmAssignSectionsList');
        if (!sectionSelect) return;
        sectionSelect.replaceChildren(new Option('Loading sections...', ''));
        sectionSelect.disabled = true;
        if (!schoolYearId || !semesterId || !facultyId) return;

        try {
            const query = new URLSearchParams({ school_year_id: schoolYearId, semester_id: semesterId, faculty_id: facultyId });
            const data = await requestAssignmentData(`get_adviser_sections.php?${query}`);
            sectionSelect.replaceChildren();
            if (!data.sections?.length) {
                sectionSelect.add(new Option('No unassigned sections for this period', ''));
                return;
            }
            data.sections.forEach(section => {
                const label = [section.section_code, section.program_code, section.grade_level].filter(Boolean).join(' | ');
                sectionSelect.add(new Option(label, section.id));
            });
            sectionSelect.disabled = false;
            setAssignmentMessage('fmAssignSectionsMessage', '');
        } catch (error) {
            sectionSelect.replaceChildren(new Option('Unable to load sections', ''));
            setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
        }
    }

    async function loadAssignmentSemesters() {
        const schoolYearId = document.getElementById('fmAssignSectionsSchoolYear')?.value;
        const semesterSelect = document.getElementById('fmAssignSectionsSemester');
        const sectionSelect = document.getElementById('fmAssignSectionsList');
        if (!semesterSelect || !sectionSelect) return;
        semesterSelect.replaceChildren(new Option('Loading semesters...', ''));
        semesterSelect.disabled = true;
        sectionSelect.replaceChildren(new Option('Select semester first', ''));
        sectionSelect.disabled = true;
        if (!schoolYearId) return;

        try {
            const data = await requestAssignmentData(`get_semesters.php?school_year_id=${encodeURIComponent(schoolYearId)}`);
            semesterSelect.replaceChildren(new Option('Select semester', ''));
            (data.semesters || []).forEach(semester => semesterSelect.add(new Option(semester.name, semester.id)));
            if (!data.semesters?.length) {
                semesterSelect.replaceChildren(new Option('No active semesters', ''));
                return;
            }
            semesterSelect.disabled = false;
        } catch (error) {
            semesterSelect.replaceChildren(new Option('Unable to load semesters', ''));
            setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
        }
    }

    async function openAssignSections(button) {
        if (!sectionsModal) return;
        document.getElementById('fmAssignSectionsFacultyId').value = button.dataset.facultyId || '';
        document.getElementById('fmAssignSectionsFacultyName').value = button.dataset.facultyName || '';
        setAssignmentMessage('fmAssignSectionsMessage', '');
        const yearSelect = document.getElementById('fmAssignSectionsSchoolYear');
        const semesterSelect = document.getElementById('fmAssignSectionsSemester');
        const sectionSelect = document.getElementById('fmAssignSectionsList');
        yearSelect.replaceChildren(new Option('Loading school years...', ''));
        yearSelect.disabled = true;
        semesterSelect.replaceChildren(new Option('Select school year first', ''));
        semesterSelect.disabled = true;
        sectionSelect.replaceChildren(new Option('Select semester first', ''));
        sectionSelect.disabled = true;
        sectionsModal.classList.add('show');

        try {
            const data = await requestAssignmentData('get_school_years.php');
            yearSelect.replaceChildren(new Option('Select school year', ''));
            (data.school_years || []).forEach(year => yearSelect.add(new Option(year.name, year.id)));
            if (!data.school_years?.length) {
                yearSelect.replaceChildren(new Option('No active school years', ''));
                return;
            }
            yearSelect.disabled = false;
            yearSelect.value = String(data.school_years[0].id);
            await loadAssignmentSemesters();
        } catch (error) {
            yearSelect.replaceChildren(new Option('Unable to load school years', ''));
            setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
        }
    }

    async function loadSubjectOptions(sectionId) {
        const subjectSelect = document.getElementById('fmAssignSubjectSubject');
        const submitButton = subjectForm?.querySelector('button[type="submit"]');
        if (!subjectSelect) return;
        subjectSelect.replaceChildren(new Option('Loading subjects...', ''));
        subjectSelect.disabled = true;
        if (submitButton) submitButton.disabled = true;
        if (!sectionId) return;

        try {
            const data = await requestAssignmentData(`get_section_subjects.php?section_id=${encodeURIComponent(sectionId)}`);
            subjectSelect.replaceChildren(new Option('Select subject', ''));
            (data.subjects || []).forEach(subject => {
                subjectSelect.add(new Option(`${subject.code} - ${subject.name} (${subject.units} units)`, subject.id));
            });
            if (!data.subjects?.length) {
                subjectSelect.replaceChildren(new Option(data.message || 'No curriculum subjects found', ''));
                setAssignmentMessage('fmAssignSubjectMessage', data.message || 'No curriculum subjects found for this section.');
                return;
            }
            subjectSelect.disabled = false;
            if (submitButton) submitButton.disabled = false;
            setAssignmentMessage('fmAssignSubjectMessage', '');
        } catch (error) {
            subjectSelect.replaceChildren(new Option('Unable to load subjects', ''));
            setAssignmentMessage('fmAssignSubjectMessage', error.message, true);
        }
    }

    async function openAssignSubject(button) {
        if (!subjectModal) return;
        document.getElementById('fmAssignSubjectFacultyId').value = button.dataset.facultyId || '';
        document.getElementById('fmAssignSubjectFacultyName').value = button.dataset.facultyName || '';
        setAssignmentMessage('fmAssignSubjectMessage', '');
        const sectionSelect = document.getElementById('fmAssignSubjectSection');
        const subjectSelect = document.getElementById('fmAssignSubjectSubject');
        const submitButton = subjectForm?.querySelector('button[type="submit"]');
        sectionSelect.replaceChildren(new Option('Loading assigned sections...', ''));
        sectionSelect.disabled = true;
        subjectSelect.replaceChildren(new Option('Select a section first', ''));
        subjectSelect.disabled = true;
        if (submitButton) submitButton.disabled = true;
        subjectModal.classList.add('show');

        try {
            const data = await requestAssignmentData(`get_instructor_sections.php?faculty_id=${encodeURIComponent(button.dataset.facultyId || '')}`);
            sectionSelect.replaceChildren(new Option('Select section', ''));
            (data.sections || []).forEach(section => {
                const label = [section.section_code, section.program_code, section.semester, section.school_year].filter(Boolean).join(' | ');
                sectionSelect.add(new Option(label, section.section_id));
            });
            if (!data.sections?.length) {
                sectionSelect.replaceChildren(new Option('Assign faculty to a section first', ''));
                setAssignmentMessage('fmAssignSubjectMessage', 'Assign this faculty member to a section before assigning subjects.');
                return;
            }
            sectionSelect.disabled = false;
        } catch (error) {
            sectionSelect.replaceChildren(new Option('Unable to load sections', ''));
            setAssignmentMessage('fmAssignSubjectMessage', error.message, true);
        }
    }

    if (assignmentTable && !assignmentTable.dataset.handlersBound) {
        assignmentTable.dataset.handlersBound = 'true';
        assignmentTable.addEventListener('click', event => {
            const sectionsButton = event.target.closest('[data-fm-assign-sections]');
            const subjectButton = event.target.closest('[data-fm-assign-subject]');
            if (sectionsButton) openAssignSections(sectionsButton);
            if (subjectButton) openAssignSubject(subjectButton);
        });
    }

    if (sectionsForm && !sectionsForm.dataset.assignmentBound) {
        sectionsForm.dataset.assignmentBound = 'true';
        document.getElementById('fmAssignSectionsSchoolYear').addEventListener('change', loadAssignmentSemesters);
        document.getElementById('fmAssignSectionsSemester').addEventListener('change', loadAssignmentSections);
        sectionsForm.addEventListener('submit', async event => {
            event.preventDefault();
            const sectionIds = Array.from(document.getElementById('fmAssignSectionsList').selectedOptions).map(option => option.value).filter(Boolean);
            const facultyId = document.getElementById('fmAssignSectionsFacultyId').value;
            const submitButton = sectionsForm.querySelector('button[type="submit"]');
            if (!facultyId || !sectionIds.length) {
                setAssignmentMessage('fmAssignSectionsMessage', 'Select at least one section.', true);
                return;
            }
            submitButton.disabled = true;
            try {
                const result = await requestAssignmentData('add_section_faculty.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ faculty_id: facultyId, section_ids: sectionIds })
                });
                setAssignmentMessage('fmAssignSectionsMessage', result.message || 'Section assignments saved.');
                closeAssignmentModal(sectionsModal);
                await reloadFacultyAssignments();
            } catch (error) {
                setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
            } finally {
                submitButton.disabled = false;
            }
        });
    }

    if (subjectForm && !subjectForm.dataset.assignmentBound) {
        subjectForm.dataset.assignmentBound = 'true';
        document.getElementById('fmAssignSubjectSection').addEventListener('change', event => loadSubjectOptions(event.target.value));
        subjectForm.addEventListener('submit', async event => {
            event.preventDefault();
            const facultyId = document.getElementById('fmAssignSubjectFacultyId').value;
            const sectionId = document.getElementById('fmAssignSubjectSection').value;
            const subjectId = document.getElementById('fmAssignSubjectSubject').value;
            const submitButton = subjectForm.querySelector('button[type="submit"]');
            if (!facultyId || !sectionId || !subjectId) {
                setAssignmentMessage('fmAssignSubjectMessage', 'Select an assigned section and curriculum subject.', true);
                return;
            }
            submitButton.disabled = true;
            try {
                const result = await requestAssignmentData('assign_subject_to_faculty.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ faculty_id: facultyId, assignments: [{ section_id: sectionId, subject_id: subjectId }] })
                });
                setAssignmentMessage('fmAssignSubjectMessage', result.message || 'Subject assignment saved.');
                closeAssignmentModal(subjectModal);
                await reloadFacultyAssignments();
            } catch (error) {
                setAssignmentMessage('fmAssignSubjectMessage', error.message, true);
            } finally {
                submitButton.disabled = false;
            }
        });
    }

    [sectionsModal, subjectModal].forEach(modal => {
        if (!modal || modal.dataset.closeBound) return;
        modal.dataset.closeBound = 'true';
        modal.querySelectorAll('.modal-close').forEach(button => {
            button.addEventListener('click', () => closeAssignmentModal(modal));
        });
        modal.addEventListener('click', event => {
            if (event.target === modal) closeAssignmentModal(modal);
        });
    });

    reloadFacultyAssignments();
}

function initFacultyManagementWhenReady() {
    if (document.querySelector('[data-fm-tab]')) {
        initFacultyManagementPage();
    }
}

document.addEventListener('DOMContentLoaded', initFacultyManagementWhenReady);
window.addEventListener('page:loaded', function (event) {
    if (event.detail && event.detail.page === 'faculty-management') {
        setTimeout(initFacultyManagementWhenReady, 100);
    }
});

function getRequirementBadgeClass(status) {
        const normalized = String(status || '').trim().toLowerCase();
        if (normalized === 'submitted') return 'badge badge-success';
        if (normalized === 'missing') return 'badge badge-danger';
        if (normalized === 'for follow-up' || normalized === 'for_follow-up') return 'badge badge-warning';
        return 'badge badge-secondary';
    }

    function renderRequirementStatus(status) {
        const safeStatus = status || 'N/A';
        return `<span class="${getRequirementBadgeClass(safeStatus)}">${safeStatus}</span>`;
    }
