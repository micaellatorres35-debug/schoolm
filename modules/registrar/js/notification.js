  document.addEventListener('DOMContentLoaded', function() {

    let notifBtn = document.getElementById('notif-btn');
    const notifModal = new bootstrap.Modal(document.getElementById('notif-modal'));
    

   setInterval(() => {
        NotificationBody();
          notificationCounts();
    }, 5000); 

    notifBtn.addEventListener('click',function(){

        notifModal.show();

         NotificationBody();
        markNotificationsAsRead();

    });

   

  });


  function notificationCounts()
  {

     const notifCount = document.getElementById('notifCount'); 

      fetch(`${BASE_URL}/notificationsCount`)
        .then(response => response.json())
        .then(notifications => {
  
          const count = notifications.notification_counts;

        if (count == 0) {
            notifCount.style.display = "none";
        } else {
            notifCount.style.display = "inline-block";
            notifCount.textContent = count;
        }

        })
     
  }


  function NotificationBody() {

    const notificationList = document.getElementById('notification-list');

    fetch(`${BASE_URL}/notifications`)
        .then(response => response.json())
        .then(notifications => {

            notificationList.innerHTML = "";

            if (notifications.data.length === 0) {
                notificationList.innerHTML = `
                    <p class="text-center text-muted">No notifications.</p>
                `;
                return;
            }

            notifications.data.forEach(notification => {

                notificationList.innerHTML += `
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-3">

                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">${notification.title}</h6>

                                ${notification.is_read == 0
                                    ? '<span class="badge rounded-pill bg-danger">New</span>'
                                    : ''}
                            </div>

                            <p class="small text-secondary mb-2 lh-base">
                            ${notification.message}
                            </p>

                              <div class="d-flex align-items-center">
                        <small class="text-muted"><i class="bi bi-clock me-1"></i>${notification.created_at}</small>
                         </div>

                        </div>
                    </div>
                `;

            });

        })
        .catch(error => console.error(error));

}


function markNotificationsAsRead() {

    fetch(`${BASE_URL}/notifications/read`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {

        if(data.success){

            const notifCount = document.getElementById('notif-count');

            notifCount.textContent = "";
            notifCount.classList.add("d-none");

        }

    })
    .catch(error => console.error(error));

}




