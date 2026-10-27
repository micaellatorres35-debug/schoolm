  

    let filters = {
    course_id: '',
    school_year:'',
    section: ''
    };

  
  document.addEventListener('DOMContentLoaded', function() {
    
   const selectAll = document.getElementById('select-all');
   const tbody = document.querySelector('tbody');
   const deleteBtn = document.getElementById('delete-btn');
   const classOfferingBtn = document.getElementById('classOfferingBtn');
   const addScheduleBtn = document.getElementById('addScheduleBtn');

    const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    const classOfferingModal = new bootstrap.Modal(document.getElementById('classOfferingModal'));
    const showClassOfferModal = new bootstrap.Modal(document.getElementById('showClassOfferModal'));
    const modal = new bootstrap.Modal(document.getElementById('filterModal'));

      
  
    const params = new URLSearchParams(window.location.search);

    filters.course_id = params.get('course_id') || '';
    filters.year_level = params.get('year_level') || '';
    filters.section  = params.get('section') || '';
    filters.semester = params.get('semester') || '';
    
    let currentOrder = 'desc';
    let currentLimit = 10;
    let currentPage = 1;


    getData(currentOrder, currentLimit, currentPage);


    document.getElementById("order").addEventListener('change', (e) => {

        currentOrder = e.target.value;
        currentPage = 1;
        getData(currentOrder, currentLimit,currentPage);

    });


    
    document.getElementById("limit").addEventListener('change', (e) => {


        currentLimit = e.target.value;
        currentPage = 1; 

        getData(currentOrder, currentLimit, currentPage);

    });


    document.getElementById("search").addEventListener("input", function(e) {

    const order = document.getElementById("order").value;
    const limit = document.getElementById("limit").value;
    
    getData(order, limit, 1); 

    });



    document.getElementById('pdf').addEventListener('click',()=>{

        const order = document.getElementById("order").value;
         getPdf(order);

    });

    document.getElementById('excel').addEventListener('click',()=>{

       const order = document.getElementById("order").value;
       getExcel(order);

    });

    document.getElementById('csv').addEventListener('click',()=>{

       const order = document.getElementById("order").value;
       getCsv(order);

    });



      /*  ========================================================================================= 
       |                                                                                         |
       |     Buttons                                                                             |
       |                                                                                         |
       ========================================================================================= 
    */

    
    // show and hide the delete button when there is checked box.

    function updateDeleteButton() {
        const checkedCount = tbody.querySelectorAll('.activity-checkbox:checked').length;
        deleteBtn.classList.toggle('d-none', checkedCount === 0);
    }

    // reset the checkbox and button delete
    function resetAll()
    {
        selectAll.checked =false;
        deleteBtn.classList.add('d-none');    
    }



    tbody.addEventListener('change', function(e) {
        if(e.target.classList.contains('activity-checkbox')) {
            updateDeleteButton();
        }
    });

    selectAll.addEventListener('change', function() {

        const allChildren = tbody.querySelectorAll('.activity-checkbox');

        allChildren.forEach(checkbox => {
            checkbox.checked = this.checked;
        });

        updateDeleteButton(); 
    });



    deleteBtn.addEventListener('click', function(){

        const ids = Array.from(
            tbody.querySelectorAll('.activity-checkbox:checked')
        ).map(cb => cb.value);

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: `Yes, delete (${ids.length}) item/s!`,
            }).then((result) => {
            if (result.isConfirmed) {

                fetch(`${BASE_URL}/class-offering/delete`, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){

                        Swal.fire({
                            title: "Deleted!",
                            text: "Item/s has been deleted.",
                            icon: "success"
                            });

                        resetAll();
                        getData(currentOrder, currentLimit, currentPage);
                    } else {
                        alert('Delete failed!');
                    }

                    

                });
                
            }
            });

        });

     /*  ========================================================================================= 
       |                                                                                         |
       |     Add Modal                                                                           |
       |                                                                                         |
       ========================================================================================= 
    */
    

    // show  

    classOfferingBtn.addEventListener('click',function(){

        classOfferingModal.show();
    
    });


    // submit button connect to form

    document.getElementById('classOfferingSubmit').addEventListener('click', function() {

    document.getElementById('classOfferingForm').requestSubmit();
       
    });


    // close button with reset

    document.getElementById('closeBtn').addEventListener('click',function(){

    resetForm('classOfferingForm');
    stepper.to(1);

    });

    // ai input cleaner text-wrapper    

    const aiInputClean = document.querySelectorAll('.ai-clean');
    const aiToggleEnabler  = document.getElementById('aiAutoCorrect');
    
    aiInputClean.forEach(input => {


        input.addEventListener('blur', function(){

        if (!aiToggleEnabler.checked) return;

        const originalText = this.value.trim();

        if (originalText.length < 4) return;


        fetch(`${BASE_URL}/cleaner`, { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: originalText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.cleaned) {
                this.value = data.cleaned;
            }
        })
        .catch(err => {
            console.error('AI Cleanup Error:', err);
        });

    });



    });

   
    // form action

    document.getElementById('classOfferingForm').addEventListener('submit', function(e) {
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

               Swal.fire({
                title: "Error!",
                text: data.message,
                icon: "error"
             });

        } else if (data.status === 'success') {

        
            const scheduleBody = document.getElementById('scheduleBody');
    
            const form = document.getElementById('classOfferingForm'); 
            
            form.reset();
            document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

            getData(currentOrder, currentLimit, currentPage);


            schedules = [];
            scheduleBody.innerHTML = '';

            classOfferingModal.hide();
            stepper.to(1);

                Swal.fire({
                title: "Success!",
                text: data.message,
                icon: "success"
             });
       

        }

        })
        .catch(err => console.log(err));
    });


      // ---- logic for view modal -----

    // show the view modal

    document.getElementById("studentsTableBody").addEventListener("click", function(e) {

    if (e.target.classList.contains("view-btn")) {

        const classOffering = e.target.dataset.id;


        fetch(`${BASE_URL}/class-offering/${classOffering}`)
        .then(response => response.json())
         .then(result => {
          
            // course info
            document.getElementById('show_course_code').textContent = result.course_code;
            document.getElementById('show_course_name').textContent = result.course_name;
            document.getElementById('show_section_name').textContent = result.section_name;
            document.getElementById('show_year_level').textContent = result.year_level;

            // subject info
            document.getElementById('show_subject_code').textContent = result.subject_code;
            document.getElementById('show_subject_name').textContent = result.subject_name;
            document.getElementById('show_semester').textContent = result.semester_name;
            document.getElementById('show_teacher_name').textContent = result.teacher_name;
            document.getElementById('show_units').textContent = result.units;  
            document.getElementById('show_lecture_hours').textContent = result.lec_hours;
            document.getElementById('show_laboratory_hours').textContent = result.lab_hours;
            

            // schedule and class info
            document.getElementById('show_room').textContent = result.room_name;
            document.getElementById('show_room_type').textContent = result.room_type;
            document.getElementById('show_building').textContent = result.building_name;
            document.getElementById('show_day').textContent = result.schedule_day;
            document.getElementById('show_start_time').textContent = result.start_time ;
            document.getElementById('show_end_time').textContent = result.end_time ;

            document.getElementById("scheduleCard").addEventListener("click", function () {
                
             const section = result.section_id;
             window.location.href = `${BASE_URL}/section-schedule?section_id=${section}`;
             });
            
             showClassOfferModal.show();


         });

    }
    });


    // ---- logic for dynamic section modal  -----

    document.getElementById("course_opt").addEventListener("change", function() {

    const courseId = this.value;
    const sectionSelect = document.getElementById("section_opt");

    sectionSelect.innerHTML = '<option value="">Select Section</option>';

    if (!courseId) return;

    fetch(`${BASE_URL}/class-offering/sections?course_id=${courseId}`)
        .then(res => res.json())
        .then(data => {

            data.forEach(section => {
                sectionSelect.innerHTML += `
                    <option value="${section.id}">
                        ${section.name}
                    </option>
                `;
            });

        });
  });
 
   // get the semester through section 

    document.getElementById('section_opt').addEventListener('change', function(){
      const sectionId = this.value;
      const semesterSelect = document.getElementById('semester_opt');

      semesterSelect.innerHTML = '<option value="">Select Semester</option>';

      if (!sectionId) return;

      fetch(`${BASE_URL}/class-offering/sectionSemester?section=${sectionId}`)
        .then(res => res.json())
        .then(data => {

            data.forEach(semester => {
                semesterSelect.innerHTML += `
                    <option value="${semester.semester_id}">
                        ${semester.semester_name}
                    </option>
                `;
            });

        });
      
    

    });


    // schedule modal 

    addScheduleBtn.addEventListener('click',function(){

        scheduleModal.show();
    });



    // ---- logic for filter modal -----

    
    // filter modal 

  
    const schoolYearSelect = document.getElementById('filter_school_year');

    const courseSelect = document.getElementById('filter_course');
    
    const sectionFilter = document.getElementById('filter_section');
    const sectionDiv = document.getElementById('section-container');
    
    const defaultSchoolYear = schoolYearSelect.options[schoolYearSelect.selectedIndex].value;

    
    // reset filter

    document.getElementById('resetFilter').addEventListener('click', function() {
    
    filters.course_id = courseSelect.value = '';
    filters.school_year = schoolYearSelect.value = defaultSchoolYear;
    filters.section = sectionFilter.value = '';

    sectionDiv.classList.toggle('d-none', this.value === '');

    updateFilterBadge();
    
    getData(currentOrder, currentLimit,currentPage);
  
    modal.hide();
         
    }) 


 schoolYearSelect.addEventListener('change', function () {

    const schoolYearId = this.value;

    sectionDiv.classList.add('d-none');

    
    courseSelect.innerHTML = '<option value="">All Courses</option>';
    sectionFilter.innerHTML = '<option value="">All Section</option>';

    if (!schoolYearId) return;

    fetch(`${BASE_URL}/class-offering/schoolYearCourse?school_year=${schoolYearId}`)
        .then(res => res.json())
        .then(data => {

            data.forEach(course => {

                courseSelect.innerHTML += `
                    <option value="${course.course_id}">
                        ${course.course_name}
                    </option>
                `;

            });

        });

});



courseSelect.addEventListener('change', function () {

    const courseId = this.value;

    const schoolYearId = schoolYearSelect.value;

    sectionDiv.classList.toggle('d-none', courseId === '');

    sectionFilter.innerHTML = '<option value="">All Section</option>';

    if (!courseId || !schoolYearId) return;

    fetch(`${BASE_URL}/class-offering/courseSection?course_id=${courseId}&school_year=${schoolYearId}`)
        .then(res => res.json())
        .then(data => {

            data.forEach(section => {

                sectionFilter.innerHTML += `
                    <option value="${section.section_id}">
                        ${section.section_name}
                    </option>
                `;

            });

        });

    });

 

    // modal show for filter 
    document.getElementById("filterBtn").addEventListener("click", () => {
       modal.show();
    });


    // filter send
    document.getElementById("applyFilter").addEventListener("click", () => {

    filters.course_id = courseSelect.value;
    filters.section  = sectionFilter.value;
    filters.school_year =  schoolYearSelect.value;
    
    updateFilterBadge();

    getData(currentOrder, currentLimit,currentPage);
  
    modal.hide();

   });

   


    });


     /*  ========================================================================================= 
       |                                                                                         |
       |     End of DOM Content                                                                  |
       |                                                                                         |
       ========================================================================================= 
    */

    
    function updateFilterBadge() {

    const badge = document.getElementById("filterBadge");

    let count = 0;

    if (filters.course_id) count++;
    if (filters.school_year) count++;
    if(filters.section) count++;
    if (count > 0) {
        badge.classList.remove("d-none");
        badge.textContent = count;
    } else {
        badge.classList.add("d-none");
    }
}
  

    function resetForm(formId)
    {

        const form = document.getElementById(formId); 

        form.reset(); 
        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
        document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
    }


    // pdf data 

    function getPdf(order)
    {
        const search = document.getElementById("search").value;

        //  fetch(`${BASE_URL}/students/pdf?status=${status}&search=${encodeURIComponent(search)}`)
        // .then(response => response.blob())
        // .then(blob => {
        //     const url = URL.createObjectURL(blob);
        //     window.open(url); 
        // })
        // .catch(err => console.error(err));
        

        // take this if you dont want redirection confirmation 

        window.open(
        `${BASE_URL}/course/pdf?order=${order}&search=${encodeURIComponent(search)}`,
        "_blank"
        );
    }


    function getExcel(order)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/course/excel?range=${order}&search=${encodeURIComponent(search)}`;
    }


    function getCsv(order)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/course/csv?order=${order}&search=${encodeURIComponent(search)}`;
    }




    // get data from students 

    function getData(order, limit, page = 1){

    const search = document.getElementById("search").value;
    const tbody = document.getElementById("studentsTableBody");


    fetch(`${BASE_URL}/class-offering/all?order=${order}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}&course_id=${filters.course_id}&school_year=${filters.school_year}&section=${filters.section}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center">No result found</td></tr>`;
                return;
            }

            result.data.forEach( (offer) => {
                tbody.innerHTML += `
                    <tr class="activity-row">

                     <td><input type="checkbox" class="activity-checkbox" value="${offer.id}"></td>
                        <td>${offer.subject_code}</td>
                        <td>${offer.subject_name} </td>
                        <td>${offer.section_name}</td>   
                        <td>${offer.course_name}</td>
                        <td>${offer.year_level}</td>
                        <td>${offer.semester_name}</td>
                        <td>${offer.teacher_first} ${offer.teacher_last}</td>
                        <td>${offer.room_name}</td>
                        <td>
                            <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                             data-bs-strategy="fixed">
                             Actions
                             </button>

                          <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <button class="dropdown-item view-btn" data-id="${offer.id}" type="button">
                                View Details
                            </button>
                          </li>

                        <li>
                            <a class="dropdown-item" href="${BASE_URL}/section-schedule?section_id=${offer.section_id}" target="_blank">
                                Go to Schedule
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="${BASE_URL}/section-schedule/pdf?section_id=${offer.section_id}" target="_blank">
                                Export to PDF
                            </a>
                        </li>
                     </ul>
                    </div>

                    </td>
                        
                    </tr>
                `;
            });

            const rows = tbody.querySelectorAll(".activity-row");

            if (rows.length > 0) {
                gsap.from(rows, {
                x: -200,          
                opacity: 0,       
                duration: 0.5,
                stagger: 0.3,      
                ease: "power2.out" 
                });
            }

            renderPagination(result.current_page, result.last_page);
            renderResultInfo(result);

        });
}

function renderPagination(current, last) {

    
    const container = document.getElementById("pagination");
    container.innerHTML = "";

    if (last <= 1) return;

    for (let i = 1; i <= last; i++) {

    const btn = document.createElement("button");
        btn.textContent = i;
        btn.className = "btn btn-sm " + (i === current ? "btn-primary" : "btn-outline-primary");
        
        btn.addEventListener("click", function() {

            const status = document.getElementById("order").value;
            const limit = document.getElementById("limit").value;

            getData(status, limit, i);

        });

        container.appendChild(btn);
    }

    
}


function renderResultInfo(result) {

    const info = document.getElementById("pageInfo");

    if (result.total === 0) {
        info.textContent = "No results found";
        return;
    }

    const start = (result.current_page - 1) * result.data.length + 1;
    let end = result.current_page * result.data.length;

    if (end > result.total) {
        end = result.total;
    }

    info.textContent = `Showing ${start}–${end} of ${result.total} results`;
}


// schedule 

let schedules = [];


const saveScheduleBtn = document.getElementById('saveScheduleBtn');
const scheduleBody = document.getElementById('scheduleBody');


saveScheduleBtn.addEventListener('click', () => {
    const day = document.getElementById('schedule_day').value;
    const start = document.getElementById('schedule_start').value;
    const end = document.getElementById('schedule_end').value;
    const classOfferingSubmit = document.getElementById('classOfferingSubmit');

    if (!day || !start || !end) {
         Swal.fire({
                title: "Error!",
                text: "Fill all fields.",
                icon: "error"
             });
        return;
    }

    classOfferingSubmit.classList.remove('d-none');


    schedules.push({ day, start, end });

    const scheduleModalEl = document.getElementById('scheduleModal');
    const scheduleModal = bootstrap.Modal.getInstance(scheduleModalEl); 
    
    scheduleModal.hide();

    renderSchedules();

    document.getElementById('schedule_day').value = '';
    document.getElementById('schedule_start').value = '';
    document.getElementById('schedule_end').value = '';

    

});

function renderSchedules() {
    scheduleBody.innerHTML = '';

    schedules.forEach((sched, index) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                ${sched.day}
                <input type="hidden" name="schedules[day][]" value="${sched.day}">
            </td>
            <td>
                ${sched.start}
                <input type="hidden" name="schedules[start_time][]" value="${sched.start}:00">
            </td>
            <td>
                ${sched.end}
                <input type="hidden" name="schedules[end_time][]" value="${sched.end}:00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeBtn" data-index="${index}">
                    Remove
                </button>
            </td>
        `;

        scheduleBody.appendChild(row);
    });
}

scheduleBody.addEventListener('click', (e) => {

    const classOfferingSubmit = document.getElementById('classOfferingSubmit');

    
    if (e.target.classList.contains('removeBtn')) {
        const index = e.target.getAttribute('data-index');

        schedules.splice(index, 1); 
        renderSchedules();

        
     if (schedules.length > 0) {

        classOfferingSubmit.classList.add('d-none');

    } else {
        classOfferingSubmit.classList.remove('d-none');
    }

    }
   


});




