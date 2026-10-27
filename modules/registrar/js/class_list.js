  

    let filters = {
    school_year: '',
    section:'',
    subject:'',
    semester: ''
    };
  
  
  document.addEventListener('DOMContentLoaded', function() {
    
   
    const filterBtn = document.getElementById('filterBtn');
    const modal = new bootstrap.Modal(document.getElementById('filterModal'));
    const showModal = new bootstrap.Modal(document.getElementById('showClassList'));
    const viewClassBtn = document.getElementById('viewClassBtn');
 


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
       |     Add Modal                                                                           |
       |                                                                                         |
       ========================================================================================= 
    */
    

     // filter 

     filterBtn.addEventListener('click',function(){
  
        modal.show();
        

     });


    const schoolYearSelect = document.getElementById('filter_school_year');
    const semesterSelect = document.getElementById('filter_semester');
    const sectionSelect = document.getElementById('filter_section');
    const subjectSelect = document.getElementById('filter_subject');
    const sectionDiv = document.getElementById('section-container');
    const semesterDiv = document.getElementById('semester-container');


    const defaultSchoolYear = schoolYearSelect.options[schoolYearSelect.selectedIndex].value;

    // reset filter

  document.getElementById('resetFilter').addEventListener('click', function() {
    
    filters.semester = semesterSelect.value = '';
    filters.school_year = schoolYearSelect.value = defaultSchoolYear;
    filters.section = sectionSelect.value = '';
    filters.subject = subjectSelect.value = '';

    updateFilterBadge();
    
    getData(currentOrder, currentLimit,currentPage);
  
    modal.hide();
         
    }) 

    // filter send
    document.getElementById("applyFilter").addEventListener("click", () => {

    filters.semester = semesterSelect.value;
    filters.section  = sectionSelect.value;
    filters.school_year =  schoolYearSelect.value;
    filters.subject = subjectSelect.value
    
    updateFilterBadge();

    getData(currentOrder, currentLimit,currentPage);
  
    modal.hide();

   });

    
// form configurations

   schoolYearSelect.addEventListener('change', function () {

    const schoolYearId = this.value;

    semesterSelect.innerHTML =
        '<option value="">All Semester</option>';

    sectionSelect.innerHTML =
        '<option value="">All Sections</option>';                             


    if (!schoolYearId) return;

    fetch(`${BASE_URL}/section/semester?school_year=${schoolYearId}`)
        .then(res => res.json())
        .then(data => {

            data.forEach(semester => {

                semesterSelect.innerHTML += `
                    <option value="${semester.id}">
                        ${semester.name}
                    </option>
                `;

            });

        });
});

   semesterSelect.addEventListener('change', function () {

    const semesterId = this.value;


    if (!semesterId) return;

    fetch(`${BASE_URL}/semester/${semesterId}/section`)
        .then(res => res.json())
        .then(data => {

            data.forEach(section => {

                sectionSelect.innerHTML += `
                    <option value="${section.id}">
                        ${section.section_code}
                    </option>
                `;

            });

        });
});


// view class
  

document.getElementById("studentsTableBody").addEventListener("click", async function(e) {

    if (!e.target.classList.contains("view-btn")) {
        return;
    }

    const scheduleId = e.target.dataset.id;

    try {

        const scheduleResponse = await fetch(
            `${BASE_URL}/class-list/${scheduleId}/schedule`
        );

        const scheduleData = await scheduleResponse.json();

        console.log("Schedule:", scheduleData);

        if (!scheduleData || scheduleData.length === 0) {
            console.error("No class found.");
            return;
        }

        const schedule = scheduleData[0];


        

        document.getElementById("show_course_code").textContent =
            schedule.course_code ?? "—";

        document.getElementById("show_course_name").textContent =
            schedule.course_name ?? "—";

        document.getElementById("show_section_name").textContent =
            schedule.section_code ?? "—";

        document.getElementById("show_year_level").textContent =
            schedule.grade_level ?? "—";


       

        document.getElementById("show_subject_code").textContent =
            schedule.subject_code ?? "—";

        document.getElementById("show_subject_name").textContent =
            schedule.subject_name ?? "—";

        document.getElementById("show_semester").textContent =
            schedule.semester ?? "—";

        document.getElementById("show_teacher_name").textContent =
            schedule.teacher_name ?? "—";

        document.getElementById("show_units").textContent =
            schedule.subject_units ?? "—";

        document.getElementById("show_lecture_hours").textContent =
            "—";

        document.getElementById("show_laboratory_hours").textContent =
            "—";



        document.getElementById("show_day").textContent =
            schedule.day_of_week ?? "—";

        document.getElementById("show_start_time").textContent =
            schedule.start_time ?? "—";

        document.getElementById("show_end_time").textContent =
            schedule.end_time ?? "—";


        
        document.getElementById("show_room").textContent =
             schedule.room_name ?? "—";

        document.getElementById("show_room_type").textContent =
            schedule.room_type ?? "—";

        document.getElementById("show_building").textContent =
            schedule.building ??  "—";


   

        const enrollmentResponse = await fetch(
            `${BASE_URL}/enrollment/${scheduleId}/schedule`
        );

        const students = await enrollmentResponse.json();

        console.log("Students:", students);




        document.getElementById("show_student_count").textContent =
            `${students.length} Student${students.length !== 1 ? "s" : ""}`;


      

        const studentList =
            document.getElementById("show_student_list");

        studentList.innerHTML = "";

        if (!students || students.length === 0) {

            studentList.innerHTML = `
                <tr>
                    <td colspan="4"
                        class="text-center text-muted py-4">
                        No students found.
                    </td>
                </tr>
            `;

        } else {

            students.forEach((student, index) => {

                const fullName = [
                    student.first_name,
                    student.middle_name,
                    student.surname ?? student.last_name
                ]
                .filter(Boolean)
                .join(" ");

                studentList.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>

                        <td>
                            ${student.student_number ?? "—"}
                        </td>

                        <td>
                            ${fullName}
                        </td>

                        <td>
                            <span class="badge bg-success">
                                ${student.enrollment_status ?? "—"}
                            </span>
                        </td>
                    </tr>
                `;
            });
        }


        showModal.show();

    } catch (error) {

        console.error("Error loading class details:", error);

    }
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

    if (filters.semester) count++;
    if (filters.school_year) count++;
    if(filters.section) count++;
    if(filters.subject) count++;
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

    fetch(`${BASE_URL}/class-list/all?order=${order}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}&semester=${filters.semester}&school_year=${filters.school_year}&section=${filters.section}&subject=${filters.subject}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center">No result found</td></tr>`;
                return;
            }

            result.data.forEach(classList => {
                tbody.innerHTML += `
                    <tr class="activity-row">
                        <td>${classList.section} </td>
                        <td>${classList.subject_name}</td>
                         <td>${classList.day_of_week}
                          ${classList.start_time} - ${classList.end_time}</td>
                          <td>${classList.room}</td>
                          <td>${classList.adviser_code}</td>
                          <td>${classList.student_count}</td>

                        <td>

                           <button class="btn btn-sm btn-secondary view-btn" data-id="${classList.schedule_id}" >
                                View
                            </button>

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

            const status = document.getElementById("range").value;
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







