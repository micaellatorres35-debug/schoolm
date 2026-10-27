document.addEventListener('DOMContentLoaded', function () {
    const sySemSelect = document.getElementById('sySemSelect');
    const tableBody = document.querySelector('#gradesTable tbody');

 
    fetch(`${BASE_URL}/enrollment/${Id}/semester`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            sySemSelect.innerHTML = '<option value="" disabled selected>Select School Year & Semester</option>';

            data.forEach((item, index) => {
                const option = document.createElement('option');
                option.value = item.semester_id; 
                option.textContent = `${item.name} - ${item.school_year_name}`;

               
                if (item.is_active || index === 0) {
                    option.selected = true;
                }

                sySemSelect.appendChild(option);
            });

            
            if (sySemSelect.value) {
                fetchGrades(sySemSelect.value);
            }
        })
        .catch(error => {
            console.error('Error fetching academic years:', error);
            sySemSelect.innerHTML = '<option value="" disabled>Failed to load data</option>';
        });

   
    sySemSelect.addEventListener('change', function () {
        const selectedSemesterId = this.value;
        // if (selectedSemesterId) {
        //     fetchGrades(selectedSemesterId);
        // }
        console.log(selectedSemesterId);
    });




     function fetchGrades(semesterId) {
    
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                    Loading grades...
                </td>
            </tr>
        `;

        fetch(`${BASE_URL}/grades/${Id}/subject?semester_id=${semesterId}`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch grades.');
                return response.json();
            })
            .then(grades => {
                tableBody.innerHTML = ''; 

                if (!grades || grades.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No grade records found for this semester.
                            </td>
                        </tr>
                    `;
                    return;
                }

                
                grades.forEach(item => {
                    const tr = document.createElement('tr');

               
                    const numGrade = parseFloat(item.grade);
                    let badgeClass = 'bg-secondary-subtle text-secondary';
                    let remarks = item.remarks || 'N/A';

                    if (item.grade === 'INC') {
                        badgeClass = 'bg-warning-subtle text-warning';
                        remarks = 'Incomplete';
                    } else if (!isNaN(numGrade) && numGrade <= 3.0) {
                        badgeClass = 'bg-success-subtle text-success';
                        remarks = 'Passed';
                    } else if (!isNaN(numGrade) && numGrade > 3.0) {
                        badgeClass = 'bg-danger-subtle text-danger';
                        remarks = 'Failed';
                    }

                    tr.innerHTML = `
                        <td class="ps-3 fw-bold text-dark">${escapeHtml(item.subject_code)}</td>
                        <td>${escapeHtml(item.subject_name)}</td>
                        <td class="text-center">${escapeHtml(item.subject_unit)}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-circle me-2 text-muted fs-5"></i>
                                <span>${escapeHtml(item.adviser_first_name || item.adviser_first_name || 'TBA')} ${escapeHtml(item.adviser_last_name || item.adviser_last_name || 'TBA')}</span>  
                            </div>
                        </td>
                        <td class="text-center fw-bold text-dark">${escapeHtml(item.grade || 'N/A')}</td>
                        <td class="text-center">
                            <span class="badge ${badgeClass} px-2 py-1 rounded">${escapeHtml(remarks)}</span>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            })
            .catch(error => {
                console.error('Error loading grades:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            Error loading grades. Please try again.
                        </td>
                    </tr>
                `;
            });
    }

  
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[m]);
    }


   
});


