document.addEventListener("DOMContentLoaded", function () {
  const logoutLink = document.getElementById("logout-link");
  const logoutModal = document.getElementById("logout-modal");
  const confirmLogoutBtn = document.getElementById("confirm-logout");
  const cancelLogoutBtn = document.getElementById("cancel-logout");
  const closeModal = document.querySelector(".close");

  // Open the logout confirmation modal
  logoutLink.addEventListener("click", function (event) {
    event.preventDefault(); // Prevent default action
    logoutModal.style.display = "block";
  });

  // Confirm logout and redirect with `?logout=true` to handle session cleanup
  confirmLogoutBtn.addEventListener("click", function () {
    window.location.href = window.location.pathname + "?logout=true"; // Append `?logout=true` to URL
  });

  // Cancel logout and close the modal
  cancelLogoutBtn.addEventListener("click", function () {
    logoutModal.style.display = "none";
  });

  // Close modal when the 'x' is clicked
  closeModal.addEventListener("click", function () {
    logoutModal.style.display = "none";
  });

  // Close modal when clicking outside of the modal content
  window.addEventListener("click", function (event) {
    if (event.target == logoutModal) {
      logoutModal.style.display = "none";
    }
  });
});