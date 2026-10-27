document.addEventListener('DOMContentLoaded', function () {
    loadOfferedScholarships();
    bindModalEvents();
});

async function loadOfferedScholarships() {
    const grid = document.getElementById('schoffGrid');

    try {
        const response = await fetch(`${BASE_URL}/scholarship-offered/list`);
        const result = await response.json();

        if (!result || result.success === false || !result.data || result.data.length === 0) {
            grid.innerHTML = `<div class="schoff-empty">No Data Found</div>`;
            return;
        }

        grid.innerHTML = '';
        result.data.forEach(type => {
            const amount = Number(type.fixed_amount) || 0;
            const formattedAmount = `₱${amount.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;

            grid.innerHTML += `
                <div class="schoff-card">
                    <div class="schoff-name">${type.type_name ?? '-'}</div>
                    <div class="schoff-sponsor">${type.sponsor ?? ''}</div>
                    <div class="schoff-amount">${formattedAmount}</div>
                    <div class="schoff-meta">
                        <span class="schoff-tag">${type.coverage_type ?? '-'}</span>
                        <span class="schoff-tag">${type.eligibility_basis ?? '-'}</span>
                    </div>
                    <div class="schoff-desc">${type.description ?? ''}</div>
                    <button type="button" class="schoff-apply-btn"
                        data-type-id="${type.scholarship_type_id ?? ''}"
                        data-type-name="${type.type_name ?? ''}">Apply</button>
                </div>
            `;
        });

        bindApplyButtons();

    } catch (error) {
        console.error('Error loading scholarship offers:', error);
        grid.innerHTML = `<div class="schoff-empty">Unable to load data.</div>`;
    }
}

function bindApplyButtons() {
    document.querySelectorAll('.schoff-apply-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            openApplyModal(this.dataset.typeId, this.dataset.typeName);
        });
    });
}

function openApplyModal(typeId, typeName) {
    document.getElementById('schoffTypeId').value = typeId;
    document.getElementById('schoffModalTypeName').value = typeName;
    document.getElementById('schoffAttachment').value = '';
    document.getElementById('schoffModalError').classList.remove('active');
    document.getElementById('schoffModalOverlay').classList.add('active');
}

function closeApplyModal() {
    document.getElementById('schoffModalOverlay').classList.remove('active');
}

function bindModalEvents() {
    document.getElementById('schoffModalClose').addEventListener('click', closeApplyModal);
    document.getElementById('schoffModalCancel').addEventListener('click', closeApplyModal);

    document.getElementById('schoffModalOverlay').addEventListener('click', function (e) {
        if (e.target === this) closeApplyModal();
    });

    document.getElementById('schoffApplyForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('schoffModalSubmit');
        const errorEl = document.getElementById('schoffModalError');
        errorEl.classList.remove('active');

        const fileInput = document.getElementById('schoffAttachment');
        if (!fileInput.files.length) {
            errorEl.textContent = 'Please attach a supporting document.';
            errorEl.classList.add('active');
            return;
        }

        const formData = new FormData(this);
        const typeId = document.getElementById('schoffTypeId').value;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        try {
            const response = await fetch(`${BASE_URL}/scholarship-offered/apply`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log('Apply response:', result);

            if (!result || result.success === false) {
                errorEl.textContent = result?.message ?? 'Something went wrong. Please try again.';
                errorEl.classList.add('active');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Application';
                return;
            }

            closeApplyModal();

            const btn = document.querySelector(`.schoff-apply-btn[data-type-id="${typeId}"]`);
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Applied';
            }

        } catch (error) {
            console.error('Error submitting application:', error);
            errorEl.textContent = 'Something went wrong. Please try again.';
            errorEl.classList.add('active');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Application';
        }
    });
}