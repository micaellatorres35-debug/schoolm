  
  document.addEventListener('DOMContentLoaded', function() {
    
   const selectAll = document.getElementById('select-all');
   const tbody = document.querySelector('tbody');
   const deleteBtn = document.getElementById('delete-btn');
   const viewDetailsModal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
    const rejectDocumentModal = new bootstrap.Modal(document.getElementById('rejectDocumentModal'));

   const reasonSelect = document.getElementById('rejectionReasonSelect'); 
   const otherReasonContainer = document.getElementById('otherReasonContainer');
    const otherReason = document.getElementById('otherReason'); 
    const characterCount = document.getElementById('reasonCharacterCount');
   

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





      /*  ========================================================================================= 
       |                                                                                         |
       |     Buttons                                                                             |
       |                                                                                         |
       ========================================================================================= 
    */

       reasonSelect.addEventListener('change', function () { 
        if (this.value === 'Others') {
             otherReasonContainer.classList.remove('d-none');
              otherReason.required = true; 
        } else { 
        otherReasonContainer.classList.add('d-none'); 
        otherReason.required = false; otherReason.value = '';
         characterCount.textContent = '0'; 
        }
        });

    otherReason.addEventListener('input', function () {
         characterCount.textContent = this.value.length;
     });

    
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

                fetch(`${BASE_URL}/reports-approval/delete`, {
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
    

   

    
   document.getElementById("studentsTableBody").addEventListener("click", function(e) {

  
    // approved

    if (e.target.classList.contains("approved")) {

        const reportId = e.target.dataset.id;
        const studentId = e.target.dataset.student;
        

        fetch(`${BASE_URL}/document-request/verify/${reportId}?student_id=${studentId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {

            if (data.status === 'success') {

                console.log(data.message);

                getData(currentOrder, currentLimit, currentPage);

            } else {
                console.error(data.message);
            }

        })
        .catch(error => {
            console.error(error);
        });

        }

        // reject

        else if (e.target.classList.contains("reject")) {

            const reportId = e.target.dataset.id;
            const studentId  = e.target.dataset.student;


            rejectDocumentModal.show();

            document.getElementById("confirmRejectBtn").addEventListener("click", async function () {

    const selectedReason =
        document.getElementById("rejectionReasonSelect").value;

    const otherReason =
        document.getElementById("otherReason").value.trim();


    if (!selectedReason) {
        alert("Please select a reason.");
        return;
    }


  
    let rejectionReason = selectedReason;

    if (selectedReason === "Others") {

        if (!otherReason) {
            alert("Please specify the reason.");
            return;
        }

        rejectionReason = otherReason;
    }



    
    try {

        const response = await fetch(
            `${BASE_URL}/document-request/rejected/${reportId}?student_id=${studentId}`,
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    rejection_reason: rejectionReason
                })
            }
        );


        const result = await response.json();

        console.log("Server result:", result);


        if (result.status == 'success') {

            rejectDocumentModal.hide();


            getData(
                currentOrder,
                currentLimit,
                currentPage
            );

        } else {

            alert(result.message || "Failed to reject document.");

        }

    } catch (error) {

        console.error("Reject error:", error);

        alert("Something went wrong while rejecting the document.");

    }

    });

            


            

    
 
        }

         else if (e.target.classList.contains("view")) {

            const reportId = e.target.dataset.id;
            const studentId = e.target.dataset.student;



        fetch(`${BASE_URL}/allDocument/${reportId}/view`)
        .then(response => response.json())
        .then(request => {


    

        document.getElementById('requestNumber').textContent =
        request.request_number;

        setRequestStatus(request.document_status);

        document.getElementById('documentType').textContent =
        request.document_type;

         document.getElementById('semesterName').textContent = request.semester_name ?? '-';

         document.getElementById('courseName').textContent = request.course_name;
         
         document.getElementById('purpose').textContent = request.purpose;

         document.getElementById('requestedAt').textContent = request.requested_at;
         
         document.getElementById('studentName').textContent = `${request.surname}, ${request.first_name}`

         document.getElementById('copies').textContent = request.copies;

         document.getElementById('fileName').textContent = request.file_path;
        

         viewDetailsModal.show();

        })
        .catch(error => {

            console.error('Error:', error);

        });

            
 
        }

        else if (e.target.classList.contains("process")) {

            const reportId = e.target.dataset.id;
            const studentId = e.target.dataset.student;


            fetch(`${BASE_URL}/document-request/process/${reportId}?student_id=${studentId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {

            if (data.status === 'success') {
                console.log(data.message);

                getData(currentOrder, currentLimit, currentPage);

            } else {
                console.error(data.message);
            }

        })
        .catch(error => {
            console.error(error);
        });

       }
        else if (e.target.classList.contains("ready")) {

            const reportId = e.target.dataset.id;
            const studentId = e.target.dataset.student;


            fetch(`${BASE_URL}/document-request/ready/${reportId}?student_id=${studentId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {

            if (data.status === 'success') {
                console.log(data.message);

                getData(currentOrder, currentLimit, currentPage);

            } else {
                console.error(data.message);
            }

        })
        .catch(error => {
            console.error(error);
        });

       }


        else if (e.target.classList.contains("release")) {

            const reportId = e.target.dataset.id;
            const studentId = e.target.dataset.student;


            fetch(`${BASE_URL}/document-request/release/${reportId}?student_id=${studentId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {

            if (data.status === 'success') {
                console.log(data.message);

                getData(currentOrder, currentLimit, currentPage);

            } else {
                console.error(data.message);
            }

        })
        .catch(error => {
            console.error(error);
        });

       }


    });
 


    });


     /*  ========================================================================================= 
       |                                                                                         |
       |     End of DOM Content                                                                  |
       |                                                                                         |
       ========================================================================================= 
    */


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
        `${BASE_URL}/school-year/pdf?order=${order}&search=${encodeURIComponent(search)}`,
        "_blank"
        );
    }


    function getExcel(order)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/school-year/excel?range=${order}&search=${encodeURIComponent(search)}`;
    }


    function getCsv(order)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/school-year/csv?order=${order}&search=${encodeURIComponent(search)}`;
    }




    // get data from students 

    function getData(order, limit, page = 1){

    const search = document.getElementById("search").value;
    const tbody = document.getElementById("studentsTableBody");

    fetch(`${BASE_URL}/allDocumentRequest?order=${order}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center">No approvals found.</td></tr>`;
                return;
            }

            result.data.forEach((request)=> {
                tbody.innerHTML += `
                    <tr class="activity-row">

                     <td><input type="checkbox" class="activity-checkbox" value="${request.id}"></td>
                        <td>${request.request_number}</td>
                        <td>${request.document_type}</td>
                         <td>${request.purpose}</td>
                         <td>${request.copies}</td>
                       <td>${getAction(request)}</td>
                        
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


function getAction(request)
{

    switch(request.status)
    {
       case 'pending':

         return `<button class="btn btn-primary btn-sm approved" data-id="${request.id}" data-student="${request.student_id}">
            Approve
            </button>
            <button class="btn btn-danger btn-sm reject" data-id="${request.id}" data-student="${request.student_id}">
            Reject
            </button>
            <button class="btn btn-secondary btn-sm view" data-id="${request.id}">
             View Details 
            </button>
            
            `

            break;
            case 'verified':

             return ` <button class="btn btn-secondary btn-sm view" data-id="${request.id}">
                        View Details 
                        </button>

                        <button class="btn btn-danger btn-sm process" data-id="${request.id}" data-student="${request.student_id}">
                            Start Processing
                        </button>`
            
            break;

            case 'processing':

             return ` <button class="btn btn-secondary btn-sm view" data-id="${request.id}">
                        View Details 
                        </button>

                        <button class="btn btn-success btn-sm ready" data-id="${request.id}" data-student="${request.student_id}">
                            Mark Ready
                        </button>`
            
            break;


            case 'ready for release':

             return ` <button class="btn btn-secondary btn-sm view" data-id="${request.id}">
                        View Details 
                        </button>

                        <button class="btn btn-warning btn-sm release" data-id="${request.id}" data-student="${request.student_id}">
                            Release
                        </button>`
            
            break;

             case 'released':

             return ` <button class="btn btn-secondary btn-sm view" data-id="${request.id}">
                        View Details 
                        </button>`
            break;
    }
}



function setRequestStatus(status) {
    const statusElement = document.getElementById('requestStatus');

  
    statusElement.className = 'badge px-3 py-2';

    switch (status?.toLowerCase()) {

        case 'pending':
            statusElement.classList.add(
                'bg-warning-subtle',
                'text-warning-emphasis'
            );
            break;

        case 'verified':
            statusElement.classList.add(
                'bg-primary-subtle',
                'text-primary-emphasis'
            );
            break;

        case 'processing':
            statusElement.classList.add(
                'bg-info-subtle',
                'text-info-emphasis'
            );
            break;

        case 'ready for release':
            statusElement.classList.add(
                'bg-success-subtle',
                'text-success-emphasis'
            );
            break;

        case 'released':
            statusElement.classList.add(
                'bg-success',
                'text-white'
            );
            break;

        case 'rejected':
            statusElement.classList.add(
                'bg-danger-subtle',
                'text-danger-emphasis'
            );
            break;

        default:
            statusElement.classList.add(
                'bg-secondary-subtle',
                'text-secondary-emphasis'
            );
    }

    statusElement.textContent = status ?? '-';
}






