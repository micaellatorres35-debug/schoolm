let CreateDamageBtn = document.getElementById("CreateDamageBtn");
let viewButtons = document.querySelectorAll(".viewDamageBtn");
let editButton = document.querySelectorAll(".editDamageBtn");
let deleteBtn = document.querySelectorAll(".deleteBtn");


CreateDamageBtn.addEventListener("click", function() {
    let addDamageModal = new bootstrap.Modal(document.getElementById("addDamageModal"));
    addDamageModal.show();
});


    viewButtons.forEach(button => {
        button.addEventListener("click", function () {

            let id = this.getAttribute("data-id");

            let modal = new bootstrap.Modal(document.getElementById('viewDamageModal'));
            modal.show();

      
            fetch(`${BASE_URL}/damages/view/${id}`)
                .then(response => response.json())
                .then(data => {
                    
                    document.getElementById("view_item_name").value = data.item_name;
                    document.getElementById("view_laboratory").value = data.laboratory;
                    document.getElementById("view_issue").value = data.issue;
                    document.getElementById("view_reported_by").value = data.reported_by;
                    document.getElementById("view_date_reported").value = data.date_reported;
                    document.getElementById("view_status").value = data.status;

                 
                })
                .catch(error => {
                    console.error('Error fetching damage details:', error);
                });
               

        });
    });

    // edit modal 


    editButton.forEach( button => {

            button.addEventListener('click',function(){

             let id = this.getAttribute("data-id");

             let modal = new bootstrap.Modal(document.getElementById('editDamageModal'));
             modal.show();


              fetch(`${BASE_URL}/damages/edit/${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id
                    document.getElementById('edit_item_name').value = data.item_name;
                    document.getElementById("edit_laboratory").value = data.laboratory;
                    document.getElementById("edit_issue").value = data.issue;
                    document.getElementById("edit_reported_by").value = data.reported_by;
                    document.getElementById("edit_date_reported").value = data.date_reported;
                    document.getElementById("edit_status").value = data.status;
                })
                .catch(error => {
                    console.error('Error fetching damage details:', error);
                });
            
            });



           
    });


document.getElementById('editDamageForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`${BASE_URL}/damages/update`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const modalEl = document.getElementById('editDamageModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            location.reload();

        } else {
            alert('Error: ' + data.error);
        }


    })
    .catch(
        
        err => console.error('Error updating damage:', err)
    );
});

    // deletion 

    deleteBtn.forEach( button => {

        button.addEventListener('click', function() {

            let id = this.getAttribute("data-id");

        if(confirm("Are you sure you want to delete this damage?")) {
            fetch(`${BASE_URL}/damages/delete/${id}`, {
                method: "POST"
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                   location.reload()
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(err => console.error('Error deleting damage:', err));
        }
    });


    })





  


   
