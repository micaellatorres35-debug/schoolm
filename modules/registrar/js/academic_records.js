  
   let filters = {
    course:'',
    year_level:''
    };
  
  
  
  document.addEventListener('DOMContentLoaded', function() {


    const modal = new bootstrap.Modal(document.getElementById('filterModal'));
    
    let currentStatus = 'all';
    let currentLimit = 10;
    let currentPage = 1;


    getData(currentStatus, currentLimit, currentPage);

    document.getElementById("status").addEventListener('change', (e) => {

        currentStatus = e.target.value;
        currentPage = 1;
        getData(currentStatus, currentLimit,currentPage);

    });


    
    document.getElementById("limit").addEventListener('change', (e) => {


        currentLimit = e.target.value;
        currentPage = 1; 

        getData(currentStatus, currentLimit, currentPage);

    });


    document.getElementById("search").addEventListener("input", function(e) {

    const status = document.getElementById("status").value;
    const limit = document.getElementById("limit").value;
    

    getData(status, limit, 1); 

    });




    const schoolYearLevelSelect = document.getElementById('filter_year_level');
    const courseSelect = document.getElementById('filter_course');

    const defaultCourseSelect = courseSelect.value;
    const defaultSchoolYearLevelSelect = schoolYearLevelSelect.value;



    filterBtn.addEventListener('click',function(){
  
        modal.show();
    
    });


    
    document.getElementById('resetFilter').addEventListener('click', function() {
    
    filters.course = courseSelect.value = defaultCourseSelect;
    filters.year_level = schoolYearLevelSelect.value = defaultSchoolYearLevelSelect;

    

    updateFilterBadge();
    
     getData(currentStatus, currentLimit, currentPage);
  
     modal.hide();
         
    }) 


     document.getElementById("applyFilter").addEventListener("click", () => {

    filters.course = courseSelect.value;
    filters.year_level = schoolYearLevelSelect.value;
    
    updateFilterBadge();

    getData(currentStatus, currentLimit,currentPage);
  
    modal.hide();

   });




    document.getElementById('pdf').addEventListener('click',()=>{

        const status = document.getElementById("status").value;
       getPdf(status);

    });

    document.getElementById('excel').addEventListener('click',()=>{

       const status = document.getElementById("status").value;
       getExcel(status);

    });

    document.getElementById('csv').addEventListener('click',()=>{

       const status = document.getElementById("status").value;
       getCsv(status);

    });



    // buttons  

    document.getElementById("studentsTableBody").addEventListener("click", function(e) {

    if (e.target.classList.contains("view-btn")) {

        const studentId = e.target.dataset.id;

        alert(studentId);
  
    }
    });







    });

     /*  ========================================================================================= 
       |                                                                                         |
       |      End of Dom Content                                                                 |
       |                                                                                         |
       ========================================================================================= 
    */

    function updateFilterBadge() {

    const badge = document.getElementById("filterBadge");

    let count = 0;

    if (filters.year_level) count++;
    if(filters.course) count++;
    if (count > 0) {
        badge.classList.remove("d-none");
        badge.textContent = count;
    } else {
        badge.classList.add("d-none");
    }   
    }

    // pdf data 

    function getPdf(status)
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
        `${BASE_URL}/students/pdf?status=${status}&search=${encodeURIComponent(search)}`,
        "_blank"
        );
    }


    function getExcel(status)
    {

     
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/students/excel?status=${status}&search=${encodeURIComponent(search)}`;

    }


    function getCsv(status)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/students/csv?status=${status}&search=${encodeURIComponent(search)}`;
    }




    // get data from students 

    function getData(status, limit, page = 1){

    const search = document.getElementById("search").value;
    const tbody = document.getElementById("studentsTableBody");

    fetch(`${BASE_URL}/students/all?status=${status}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}&year_level=${filters.year_level}&course=${filters.course}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center">No students found</td></tr>`;
                return;
            }

            result.data.forEach(student => {
                tbody.innerHTML += `
                    <tr class="student-row">
                        <td>${student.student_number}</td>
                        <td> ${student.first_name.charAt(0).toUpperCase() + student.first_name.slice(1)}   ${student.surname.charAt(0).toUpperCase() + student.surname.slice(1)} </td>
                        <td>${student.course_code}</td>
                        <td> ${(() => {
                            switch (parseInt(student.year_level)) {
                                case 1:
                                    return '1st Year';
                                case 2:
                                    return '2nd Year';
                                case 3:
                                    return '3rd Year';
                                case 4:
                                    return '4th Year';
                                case 5:
                                    return '5th Year';
                                default:
                                    return 'Unknown';
                            }
                        })()} </td>
                        <td>${student.enrollment_status.charAt(0).toUpperCase() + student.enrollment_status.slice(1)}</td>
                        <td>
                            
                             <div class="dropdown">
        <button class="btn btn-sm btn-primary dropdown-toggle"
               data-bs-toggle="dropdown"
               data-bs-strategy="fixed">
            Actions
        </button>

        <ul class="dropdown-menu dropdown-menu-end">

            <li>
               <a class="dropdown-item" href="${BASE_URL}/students/view/${student.student_id}/academic-records">
                  View Academic Records
               </a>
            </li>
            
            <li>
                <a class="dropdown-item" onclick="getPdf(${student.student_id}); return false;" target="_blank">
                  Export to PDF
                </a>
            </li>

        </ul>
    </div>
                            
                        </td>
                    </tr>
                `;
            });

            const rows = tbody.querySelectorAll(".student-row");

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

    const ul = document.createElement("ul");
    ul.className = "pagination";

    let start = Math.max(1, current - 2);
    let end = Math.min(last, current + 2);



    if (current > 1) {
        const li = document.createElement("li");
        li.className = "page-item";

        const link = document.createElement("a");
        link.className = "page-link";
        link.href = "#";
        link.innerHTML = "&laquo;";

        link.addEventListener("click", function(e){
            e.preventDefault();

            const status = document.getElementById("status").value;
            const limit = document.getElementById("limit").value;
            getData(status, limit, current - 1);
        });

        li.appendChild(link);
        ul.appendChild(li);
    }



    for (let i = start; i <= end; i++) {

        const li = document.createElement("li");
        li.className = "page-item " + (i === current ? "active" : "");

        const link = document.createElement("a");
        link.className = "page-link";
        link.href = "#";
        link.textContent = i;

        link.addEventListener("click", function(e) {
            e.preventDefault();

            const status = document.getElementById("status").value;
            const limit = document.getElementById("limit").value;

            getData(status, limit, i);
        });

        li.appendChild(link);
        ul.appendChild(li);
    }

    if (current < last) {
        const li = document.createElement("li");
        li.className = "page-item";

        const link = document.createElement("a");
        link.className = "page-link";
        link.href = "#";
        link.innerHTML = "&raquo;";

        link.addEventListener("click", function(e){
            e.preventDefault();

            const status = document.getElementById("status").value;
                const limit = document.getElementById("limit").value;
            getData(status, limit, current + 1);
        });

        li.appendChild(link);
        ul.appendChild(li);
    }


    container.appendChild(ul);


    
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







