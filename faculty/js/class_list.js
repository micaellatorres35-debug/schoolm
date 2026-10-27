  

    let filters = {
    school_year: '',
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
    const semesterDiv = document.getElementById('semester-container');

    const defaultSchoolYear = schoolYearSelect.options[schoolYearSelect.selectedIndex].value;

    // reset filter

  document.getElementById('resetFilter').addEventListener('click', function() {
    
    filters.semester = semesterSelect.value = '';
    filters.school_year = schoolYearSelect.value = defaultSchoolYear;


    updateFilterBadge();
    
    getData(currentOrder, currentLimit,currentPage);
  
    modal.hide();
         
    }) 

    // filter send
    document.getElementById("applyFilter").addEventListener("click", () => {

    filters.semester = semesterSelect.value;
    filters.school_year =  schoolYearSelect.value;
 
    
    updateFilterBadge();

    getData(currentOrder, currentLimit,currentPage);
  
    modal.hide();

   });

    
// form configurations

   schoolYearSelect.addEventListener('change', function () {

    const schoolYearId = this.value;

    semesterSelect.innerHTML =
        '<option value="">All Semester</option>';
                        


    if (!schoolYearId) return;

    fetch(`${BASE_URL}/semester/all?school_year=${schoolYearId}`)
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



// view class
  

document.getElementById("studentsTableBody").addEventListener("click", async function(e) {

    if (!e.target.classList.contains("view-btn")) {
        return;
    }

    const scheduleId = e.target.dataset.id;


    console.log(scheduleId);


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

    fetch(`${BASE_URL}/class/all?order=${order}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}&semester=${filters.semester}&school_year=${filters.school_year}`)
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
                          <td>${classList.room_code} - ${classList.room_name}</td>
                          <td>${classList.student_count}</td>

                        <td>

                           <a 
                                href="${BASE_URL}/${classList.schedule_id}/class"
                                class="btn btn-sm btn-primary"
                            >
                                Go to Class
                            </a>

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







