document.addEventListener("click", function (e) {

    const btn = e.target.closest(".editBtn");

    if (!btn) return;

    e.preventDefault();

    let id = btn.dataset.id;

    fetch(`${BASE_URL}/psycho-inventory/view/${id}`)
        .then(response => response.json())
        .then(data => {

            console.log(data);

            document.getElementById("edit_id").value = data.id;
            document.getElementById("edit_item_name").value = data.item_name;
            document.getElementById("edit_category").value = data.category;
            document.getElementById("edit_laboratory").value = data.laboratory;
            document.getElementById("edit_total_item").value = data.total_item;
            document.getElementById("edit_available_item").value = data.available_item;
            document.getElementById("edit_status").value = data.status;

            let editModal = new bootstrap.Modal(
                document.getElementById("psyEditInventoryModal")
            );

            editModal.show();

        })
        .catch(error => console.error(error));

});

document.addEventListener("click", function (e) {

    const btn = e.target.closest(".viewBtn");

    if (!btn) return;

    e.preventDefault();

    let id = btn.dataset.id;

    fetch(`${BASE_URL}/he-inventory/view/${id}`)
        .then(response => response.json())
        .then(data => {

            document.getElementById("view_id").value = data.id;
            document.getElementById("view_item_name").value = data.item_name;
            document.getElementById("view_category").value = data.category;
            document.getElementById("view_laboratory").value = data.laboratory;
            document.getElementById("view_total_item").value = data.total_item;
            document.getElementById("view_available_item").value = data.available_item;
            document.getElementById("view_status").value = data.status;

            let viewModal = new bootstrap.Modal(
                document.getElementById("psyViewInventoryModal")
            );

            viewModal.show();

        })
        .catch(error => console.error(error));

});

//delete
document.addEventListener("click", function (e) {

    const btn = e.target.closest(".deleteBtn");

    if (!btn) return;

    e.preventDefault();

    let id = btn.dataset.id;

    if (confirm("Are you sure you want to delete this inventory?")) {

        window.location.href = `${BASE_URL}/psycho-inventory/delete/${id}`;

    }

});