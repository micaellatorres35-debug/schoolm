document.addEventListener('DOMContentLoaded', function () {
   
  loadAnnouncements();

  
 });



async function loadAnnouncements(page = 1) {

    const announcementList =
        document.getElementById("announcementList");

    try {

        const response = await fetch(
            `${BASE_URL}/allAnnouncement?page=${page}&limit=5`
        );

        const announcements =
            await response.json();

        let html = "";

        announcements.data.forEach(announcement => {

            const image = announcement.image_file
                ? `
                    <img
                        src="${BASE_URL}/assets/images/bg.jpg"
                        class="card-img-top img-fluid w-100 h-25"
                        alt="${announcement.title}"
                    >
                `
                : "";

            const date = announcement.publish_date
                ? announcement.publish_date
                : "Recently posted";


            html += `

                <div class="card border mb-4">

                    ${image}

                    <div class="card-body">

                        <div
                            class="
                                d-flex
                                justify-content-between
                                align-items-start
                                flex-wrap
                                gap-2
                            "
                        >

                            <div>

                                <h4 class="fw-bold">

                                    ${announcement.title}

                                </h4>

                            </div>


                            <span class="badge bg-primary">

                                NEW

                            </span>

                        </div>


                        <p class="mb-3">

                            ${announcement.message}

                        </p>


                        <small class="text-muted">

                            <i
                                class="
                                    bi
                                    bi-calendar-event
                                    me-1
                                "
                            ></i>

                            Posted on ${date}

                        </small>

                    </div>

                </div>

            `;

        });


        announcementList.innerHTML = html;

          renderPagination(
            announcements.current_page,
            announcements.last_page
        );


    }
    catch (error) {
        console.error(
            "Failed to load announcements:",
            error
        );

        announcementList.innerHTML = `

            <div class="alert alert-danger">

                Failed to load announcements.

            </div>

        `;

    }

    

}

function renderPagination(current, last)
{
    let html = `
        <li class="page-item ${current <= 1 ? 'disabled':''}">
            <button class="page-link"
                onclick="loadAnnouncements(${current - 1})">
                Previous
            </button>
        </li>

        <li class="page-item disabled">
            <span class="page-link">
                Page ${current} of ${last}
            </span>
        </li>

        <li class="page-item ${current >= last ? 'disabled':''}">
            <button class="page-link"
                onclick="loadAnnouncements(${current + 1})">
                Next
            </button>
        </li>
    `;


    document.querySelector("#pagination").innerHTML = html;
}

