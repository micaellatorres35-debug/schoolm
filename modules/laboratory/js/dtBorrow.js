document.addEventListener("click", function (e) {
  const btn = e.target.closest(".editBtn");

  if (!btn) return;

  e.preventDefault();

  const id = btn.dataset.id;

  fetch(`${BASE_URL}/defense-tactics-borrow/view/${id}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Failed to fetch borrow record.");
      }

      return response.json();
    })
    .then((data) => {
      console.log("EDIT DATA:", data);

      document.getElementById("edit_id").value = data.id;
      document.getElementById("edit_laboratory").value = data.laboratory;
      document.getElementById("edit_borrower_name").value = data.borrower_name;
      document.getElementById("edit_student_id").value = data.student_id;
      document.getElementById("edit_section").value = data.section;
      document.getElementById("edit_item_name").value = data.item_name;
      document.getElementById("edit_quantity").value = data.quantity;
      document.getElementById("edit_borrowed_date").value = data.borrowed_date;
      document.getElementById("edit_expected_return").value = data.expected_return;

      // returned_date can be NULL
      document.getElementById("edit_returned_date").value = data.returned_date ?? "";

      document.getElementById("edit_status").value = data.status;

      const modalElement = document.getElementById("dtEditBorrowModal");

      const editModal = bootstrap.Modal.getOrCreateInstance(modalElement);

      editModal.show();
    })
    .catch((error) => {
      console.error("EDIT ERROR:", error);
    });
});

// view borrow
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".viewBtn");

  if (!btn) return;

  e.preventDefault();

  const id = btn.dataset.id;

  fetch(`${BASE_URL}/defense-tactics-borrow/view/${id}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Failed to fetch borrow record.");
      }

      return response.json();
    })
    .then((data) => {
      console.log("VIEW DATA:", data);

      document.getElementById("view_laboratory").value = data.laboratory;
      document.getElementById("view_borrower_name").value = data.borrower_name;
      document.getElementById("view_item_name").value = data.item_name;
      document.getElementById("view_student_id").value = data.student_id;
      document.getElementById("view_section").value = data.section;
      document.getElementById("view_quantity").value = data.quantity;
      document.getElementById("view_borrowed_date").value = data.borrowed_date;
      document.getElementById("view_expected_return").value = data.expected_return;
      document.getElementById("view_returned_date").value = data.returned_date ?? "";
      document.getElementById("view_status").value = data.status;

      const modalElement = document.getElementById("dtViewBorrowModal");
      const viewModal = bootstrap.Modal.getOrCreateInstance(modalElement);

      viewModal.show();
    })
    .catch((error) => {
      console.error("VIEW ERROR:", error);
    });
});

// delete borrow
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".deleteBtn");

  if (!btn) return;

  e.preventDefault();

  const id = btn.dataset.id;

  if (!confirm("Are you sure you want to delete this borrow record?")) {
    return;
  }

  window.location.href = `${BASE_URL}/defense-tactics-borrow/delete/${id}`;
});
