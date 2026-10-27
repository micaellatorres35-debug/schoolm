document.addEventListener('DOMContentLoaded', function () {

    const torProcessing = document.getElementById('torProcessing');
    const corProcessing = document.getElementById('corProcessing');
    const purpose = document.getElementById('purpose');
    const otherPurposeBox = document.getElementById('otherPurposeBox');
    const otherTorPurposeBox = document.getElementById('otherTorPurposeBox');
    const school_year = document.getElementById('school_year');
    const semester = document.getElementById('semester');
    const closeBtn = document.getElementById('closeBtn');
    const toRcloseBtn = document.getElementById('toRcloseBtn');
    const toRpurpose = document.getElementById('toRpurpose');
    const corProcessingModal = new bootstrap.Modal(document.getElementById('corProcessingModal'));
    const torProcessingModal = new bootstrap.Modal(document.getElementById('torProcessingModal'));
    
  

    //  ---- COR  ----- //

    closeBtn.addEventListener('click', function(){
 
         resetForm('corRequestForm');

    });

  
    document.getElementById('CorRequestSubmitBtn').addEventListener('click', function() {

    document.getElementById('corRequestForm').requestSubmit();
       
    });


    document.getElementById('corRequestForm').addEventListener('submit', function(e) {
    e.preventDefault(); 

        const formData = new FormData(this);

        fetch(this.action, { 
            method: 'POST',    
            body: formData,
        })
        .then(res => res.json())
        .then(data => {

               
        document.querySelectorAll('.error').forEach(el => el.innerText = '');

        document.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('is-invalid');
        });

        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');



        if (data.status === 'error') {
            for (let field in data.errors) {

                const input = document.getElementById(field);
                const feedback = document.getElementById('error-' + field);
                
                input.classList.add('is-invalid');        
                feedback.innerText = data.errors[field]; 
            }

        } else if (data.status === 'success') {

            const form = document.getElementById('corRequestForm'); 
            
            form.reset();
            document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

            corProcessingModal.hide();

            console.log(data);


            Swal.fire({
                title: "Success!",
                text: data.message,
                icon: "success"
             });

        }

        })
        .catch(err => console.log(err));
    });


    purpose.addEventListener("change", function(){

        if(this.value === "Others"){
            otherPurposeBox.classList.remove("d-none");
        }
        else{
            otherPurposeBox.classList.add("d-none");
        }

    });


    corProcessing.addEventListener('click', function(){

        corProcessingModal.show();

    });



    school_year.addEventListener('change',function(){


         const schoolYearId = this.value;

        if (!schoolYearId) {

            semester.innerHTML = `
                <option value="">
                    Select School Year first
                </option>
            `;

            semester.disabled = true;

            return;
        }

        loadSemesters(schoolYearId);


    });

    loadSchoolYears();



    //----- TOR -----//

    torProcessing.addEventListener('click', function(){

    torProcessingModal.show();

    });


    toRcloseBtn.addEventListener('click', function () {
        resetForm('torRequestForm');
        otherTorPurposeBox.classList.add('d-none');
    });

    toRpurpose.addEventListener("change", function () {

        if (this.value === "Others") {
            otherTorPurposeBox.classList.remove("d-none");
        } else {
            otherTorPurposeBox.classList.add("d-none");
        }

    });

    

    document.getElementById('TorRequestSubmitBtn').addEventListener('click', function() {

    document.getElementById('torRequestForm').requestSubmit();
       
    });

    document.getElementById('torRequestForm').addEventListener('submit', function(e) {
    e.preventDefault(); 

        const formData = new FormData(this);

        fetch(this.action, { 
            method: 'POST',    
            body: formData,
        })
        .then(res => res.json())
        .then(data => {

               
        document.querySelectorAll('.error').forEach(el => el.innerText = '');

        document.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('is-invalid');
        });

        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');



        if (data.status === 'error') {
            for (let field in data.errors) {

                const input = document.getElementById(field);
                const feedback = document.getElementById('error-' + field);
                
                input.classList.add('is-invalid');        
                feedback.innerText = data.errors[field]; 
            }

        } else if (data.status === 'success') {

            const form = document.getElementById('torRequestForm'); 
            
            form.reset();
            document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

            torProcessingModal.hide();
            

            Swal.fire({
                title: "Success!",
                text: data.message,
                icon: "success"
             });

        }

        })
        .catch(err => console.log(err));
    });

});



async function loadSchoolYears()
{
    try {

        const response = await fetch(
            `${BASE_URL}/allSchoolYear`
        );


        const years = await response.json();


        let html = `
            <option value="">
                Select School Year
            </option>
        `;

        years.forEach(year => {

            html += `
                <option value="${year.id}">
                    ${year.name}
                </option>
            `;

        });


        document.getElementById("school_year").innerHTML = html;


    } catch(error) {

        console.error(
            "Failed loading school years:",
            error
        );

    }
}

async function loadSemesters(schoolYearId)
{
    const semester = document.getElementById("semester");

    try {

        semester.disabled = true;

        semester.innerHTML = `
            <option value="">
                Loading semesters...
            </option>
        `;


        const response = await fetch(
            `${BASE_URL}/allSemester?school_year_id=${schoolYearId}`
        );


        const semesters = await response.json();


        let html = `
            <option value="">
                Select Semester
            </option>
        `;


        semesters.forEach(item => {

            html += `
                <option value="${item.id}">
                    ${item.semester}
                </option>
            `;

        });


        semester.innerHTML = html;

        semester.disabled = false;

    }
    catch (error) {

        console.error(
            "Failed to load semesters:",
            error
        );

        semester.innerHTML = `
            <option value="">
                Failed to load semesters
            </option>
        `;

    }
}

function resetForm(formId)
{
    const form = document.getElementById(formId); 
    form.reset(); 
    document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
    document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
}