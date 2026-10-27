  
 
 document.addEventListener('DOMContentLoaded', function () {

    const editBtn = document.getElementById('editBtn');
    const form = document.getElementById('updateStudentForm');
    const insertDocuments = document.getElementById('insertDocuments');
    const insertStudentDocumentForm = document.getElementById('insertStudentDocumentForm');


    editBtn.addEventListener('click',function(){

    const editStudentInfoModal = new bootstrap.Modal(document.getElementById('editStudentInfo'));
 
    editStudentInfoModal.show();

   });

   insertDocuments.addEventListener('click',function(){

     const insertDocumentModal = new bootstrap.Modal(document.getElementById('insertDocumentModal'));
     insertDocumentModal.show();

     loadStudentRequirements(student_id);
     

   });

  

  loadDocuments(student_id);




    if (!form) {
        return;
    }

    form.addEventListener('submit', async function (e) {

        e.preventDefault();

        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);

        try {

            const response = await fetch(`${BASE_URL}/students/update`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if(result.status == 'success')
            {

                
              const modalElement = document.getElementById('editStudentInfo');
               const modal = bootstrap.Modal.getInstance(modalElement);

                if (modal) {
                    modal.hide();
                }

                Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: result.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                location.reload();
            }); 

            }else{

                 Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Unable to update student information.',
                confirmButtonColor: '#dc3545'
            });
            }

        } catch (error) {

            console.error('Update student error:', error);

            alert(error.message || 'Something went wrong.');

        }

    });


    insertStudentDocumentForm.addEventListener('submit',async function (e) {

         e.preventDefault();

        const formData = new FormData(insertStudentDocumentForm);



        try {

            const response = await fetch(`${BASE_URL}/students/document/store`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            console.log(result);

            if(result.status == 'success')
            {

              const modalElement = document.getElementById('insertDocumentModal');
               const modal = bootstrap.Modal.getInstance(modalElement);

                if (modal) {
                    modal.hide();
                }

                Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: result.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                location.reload();
            }); 

            }else{

                 Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Unable to update student information.',
                confirmButtonColor: '#dc3545'
            });
            }

        } catch (error) {

            console.error('Update student error:', error);

            alert(error.message || 'Something went wrong.');

        }

    })



   });





function loadDocuments(student_id) {
    const tbody = document.getElementById("students-documents-table-body");
    const countBadge = document.getElementById("doc-count-badge");

    
    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading requirements...
            </td>
        </tr>
    `;

    fetch(`${BASE_URL}/enrollees/${student_id}/allDocs`)
        .then(response => {
            if (!response.ok) throw new Error("Failed to load records");
            return response.json();
        })
        .then(documents => {
            if (countBadge) countBadge.textContent = `${documents.length} Requirements`;

            if (!documents || documents.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            No requirements found.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = documents.map(doc => {
                
                const isSubmitted = Number(doc.is_submitted) === 1;

                const badgeClass = isSubmitted 
                    ? "bg-success-subtle text-success-emphasis border border-success-subtle" 
                    : "bg-warning-subtle text-warning-emphasis border border-warning-subtle";
                
                const badgeLabel = isSubmitted ? "Submitted" : "Pending";
                const badgeIcon = isSubmitted ? "bi-check-circle" : "bi-clock-history";

                const disabledAttr = !isSubmitted ? 'disabled tabindex="-1" aria-disabled="true"' : '';
                const btnDisabledClass = !isSubmitted ? 'disabled opacity-50' : '';

                
                const requirementTitle = doc.requirement_name || `Requirement ID: ${doc.requirement_id}`;

                return `
                    <tr>
                        <!-- Requirement Info -->
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">${requirementTitle}</div>
                            ${doc.notes ? `<small class="text-muted d-block mt-1">${doc.notes}</small>` : ''}
                        </td>

                        <!-- Submission Status -->
                        <td>
                            <span class="badge ${badgeClass} rounded-pill px-2 py-1">
                                <i class="bi ${badgeIcon} me-1"></i>${badgeLabel}
                            </span>
                        </td>

                        <!-- Date Submitted -->
                        <td class="text-muted small">
                            ${isSubmitted && doc.submitted_date ? doc.submitted_date : '<span class="text-body-tertiary">—</span>'}
                        </td>

                        <!-- Actions using student_requirement_id -->
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="${doc.doc_path || '#'}"
                                   data-lightbox="doc-${doc.student_requirement_id}"
                                   data-title="${requirementTitle}"
                                   class="btn btn-outline-primary ${btnDisabledClass}"
                                   ${disabledAttr}>
                                    <i class="bi bi-eye me-1"></i>View
                                </a>

                           

                            
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        })
        .catch(error => {
            console.error("Error loading documents:", error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-octagon me-1"></i> Error loading requirements.
                    </td>
                </tr>
            `;
        });
}


async function loadStudentRequirements(student_id) {

    const tbody = document.getElementById('studentRequirementsTable');

    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success me-2"></div>
                Loading requirements...
            </td>
        </tr>
    `;

    try {

        const response = await fetch(
            `${BASE_URL}/enrollees/${student_id}/Docs`
        );

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const requirements = await response.json();


        renderRequirements(requirements);
        populateMissingRequirements(requirements);

    } catch (error) {

        console.error('Error loading requirements:', error);

        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger py-4">
                    Failed to load requirements.
                </td>
            </tr>
        `;
    }
}


function renderRequirements(requirements) {

    const tbody = document.getElementById('studentRequirementsTable');

    tbody.innerHTML = '';

    if (!requirements || requirements.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    No requirements found.
                </td>
            </tr>
        `;

        return;
    }

    requirements.forEach(requirement => {

        const row = document.createElement('tr');

        const status = Number(requirement.is_submitted) === 1
            ? `
                <span class="badge bg-success-subtle text-success">
                    <i class="bi bi-check-circle me-1"></i>
                    Submitted
                </span>
              `
            : `
                <span class="badge bg-danger-subtle text-danger">
                    <i class="bi bi-x-circle me-1"></i>
                    Missing
                </span>
              `;

        const submittedDate = requirement.submitted_date
            ? requirement.submitted_date
            : '—';

        row.innerHTML = `

            <td>
                <div class="fw-semibold">
                    ${requirement.requirement_name}
                </div>
            </td>

            <td>
                <span class="badge bg-primary-subtle text-primary text-capitalize">
                    ${requirement.requirement_category}
                </span>
            </td>

            <td>
                ${
                    Number(requirement.is_mandatory) === 1
                    ? `
                        <span class="badge bg-danger-subtle text-danger">
                            Required
                        </span>
                    `
                    : `
                        <span class="badge bg-secondary-subtle text-secondary">
                            Optional
                        </span>
                    `
                }
            </td>

            <td>
                ${status}
            </td>

            <td>
                ${submittedDate}
            </td>

        `;

        tbody.appendChild(row);

    });
}


function populateMissingRequirements(requirements) {

    const select = document.getElementById('requirement_id');

    // Reset dropdown
    select.innerHTML = `
        <option value="" selected disabled>
            Select requirement
        </option>
    `;

    // Only get missing requirements
    const missingRequirements = requirements.filter(requirement =>
        Number(requirement.is_submitted) !== 1
    );

    // No missing requirements
    if (missingRequirements.length === 0) {

        select.innerHTML = `
            <option value="" selected disabled>
                No missing requirements
            </option>
        `;

        return;
    }

    // Populate dropdown
    missingRequirements.forEach(requirement => {

        const option = document.createElement('option');

        option.value = requirement.requirement_id;
        option.textContent = requirement.requirement_name;

        select.appendChild(option);

    });
}

