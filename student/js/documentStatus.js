  
  document.addEventListener('DOMContentLoaded', function() {
    
    let currentStatus = 'all';
    let currentLimit = 10;
    let currentPage = 1;

    const documentRequestHistoryModal = new bootstrap.Modal(document.getElementById('documentRequestHistoryModal'));


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




    // buttons  

    document.getElementById("studentsTableBody").addEventListener("click", function(e) {

    if (e.target.classList.contains("view-btn")) {

        const requestId = e.target.dataset.id;
        const requestNumber = e.target.dataset.number;
        const documentType = e.target.dataset.document;
        const purpose = e.target.dataset.purpose;
        const status = e.target.dataset.status;


         document.getElementById("history-request-number").textContent =
        requestNumber;

        if(documentType == 'COR')
        {
            document.getElementById("history-document").textContent = "Certificate of Registration";
        }else{

            document.getElementById("history-document").textContent = "Transcript of Records";
        }
        

        document.getElementById("history-purpose").textContent =
        purpose;


        const statusElement = document.getElementById("history-status");

        statusElement.textContent = status;

     
        statusElement.classList.remove(
            "text-bg-secondary",
            "text-bg-primary",
            "text-bg-success",
            "text-bg-warning",
            "text-bg-danger",
            "text-bg-info"
        );

        
        switch (status.toLowerCase()) {

            case "submitted":
                statusElement.classList.add("text-bg-primary");
                break;

            case "under review":
                statusElement.classList.add("text-bg-warning");
                break;

            case "processing":
                statusElement.classList.add("text-bg-info");
                break;

            case "ready for pickup":
                statusElement.classList.add("text-bg-success");
                break;

            case "released":
                statusElement.classList.add("text-bg-success");
                break;

            case "rejected":
                statusElement.classList.add("text-bg-danger");
                break;

            default:
                statusElement.classList.add("text-bg-secondary");
        }


        getDocumentRequestHistory(requestId)

        documentRequestHistoryModal.show();
  
    }
    });





    });

     /*  ========================================================================================= 
       |                                                                                         |
       |      End of Dom Content                                                                 |
       |                                                                                         |
       ========================================================================================= 
    */


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

    fetch(`${BASE_URL}/documents?status=${status}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center">No request found</td></tr>`;
                return;
            }

            result.data.forEach(student => {
                tbody.innerHTML += `
                    <tr class="student-row">

                        <td>${student.request_number}</td>
                        <td>${student.document_type}</td>
                        <td>${student.requested_at}</td>
                        <td>
                            
                        <button type="button" class="btn btn-primary view-btn" 
                        data-id="${student.id}"
                        data-number="${student.request_number}"
                        data-document="${student.document_type}"
                        data-purpose="${student.purpose}"
                        data-status="${student.status}">
                        Details
                        </button> 
                            
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



function getDocumentRequestHistory(requestId) {

    fetch(`${BASE_URL}/documents/${requestId}/history`)
        .then(response => {

            if (!response.ok) {
                throw new Error("Failed to get document request history");

            }

            return response.json();

        })
        .then(history => {

            console.log("History API:", history);

            renderDocumentRequestHistory(history);

        })
        .catch(error => {

            console.error("History error:", error);

        });

}


function renderDocumentRequestHistory(history) {

    const timeline =
        document.getElementById("history-timeline");

    timeline.innerHTML = "";

    if (!history || history.length === 0) {

        timeline.innerHTML = `
            <div class="text-center text-muted py-4">

                <i class="bi bi-clock-history fs-3 d-block mb-2"></i>

                No history available.

            </div>
        `;

        return;
    }


    history.forEach((event, index) => {

        const isLast =
            index === history.length - 1;

        const item =
            document.createElement("div");

        item.className = "d-flex";


        item.innerHTML = `

        
            <div class="d-flex flex-column align-items-center me-3">

    
                <span class="rounded-circle
                             ${getTimelineColor(event.message)}
                             text-white
                             d-flex
                             align-items-center
                             justify-content-center
                             flex-shrink-0"
                      style="width: 38px; height: 38px;">

                    <i class="${getTimelineIcon(event.message)}"></i>

                </span>


             
                ${
                    !isLast
                    ? `
                        <div class="border-start border-2 flex-grow-1"></div>
                    `
                    : ""
                }

            </div>


            <!-- History Content -->
            <div class="flex-grow-1 pb-4">

                <div class="d-flex
                            justify-content-between
                            align-items-start
                            gap-3">

                    <div>

                        <h6 class="fw-semibold mb-1">
                            ${event.title}
                        </h6>

                        <p class="text-muted small mb-2">
                            ${event.message}
                        </p>

                    </div>


                    <small class="text-muted text-nowrap">
                        ${event.created_at}
                    </small>

                </div>


                <small class="text-muted">

                    <i class="bi bi-person me-1"></i>

                    Registrar

                </small>

            </div>

        `;

        timeline.appendChild(item);

         gsap.from(item, {
            x: -50,
            opacity: 0,
            duration: 0.5,
            ease: "power2.out"
        });



    });

}


function getTimelineIcon(message) {

    const text = message.toLowerCase();

    if (text.includes("verified")) {
        return "bi bi-check-circle";
    }

    if (text.includes("under review")) {
        return "bi bi-search";
    }

    if (text.includes("processing")) {
        return "bi bi-file-earmark-text";
    }

     if (text.includes("ready for release")) {
        return "bi bi-box-arrow-up";
    }

     if (text.includes("ready for pickup")) {
        return "bi bi-bag-check";
    }

    if (text.includes("rejected")) {
        return "bi bi-x-circle";
    }

    if (text.includes("approved")) {
        return "bi bi-check-lg";
    }

   

    return "bi bi-clock";

}


function getTimelineColor(message) {

    const text = message.toLowerCase();

    if (text.includes("verified")) {
        return "bg-secondary";
    }

    if (text.includes("under review")) {
        return "bg-primary";
    }

    if(text.includes("ready for release")){
        return "bg-warning";
    }

     if(text.includes("ready for pickup")){
        return "bg-success";
    }

    if (text.includes("processing")) {
        return "bg-warning";
    }

    if (text.includes("rejected")) {
        return "bg-danger";
    }

    if (text.includes("approved")) {
        return "bg-success";
    }

    return "bg-secondary";

}









