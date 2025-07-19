// Get the modal and buttons
var modal = document.getElementById('logout-modal');
var closeBtn = document.querySelector('.modal .close');
var confirmLogoutBtn = document.getElementById('confirm-logout');
var cancelLogoutBtn = document.getElementById('cancel-logout');

document
  .getElementById('logout-link')
  .addEventListener('click', function (event) {
    event.preventDefault(); // Prevent the default link behavior
    modal.style.display = 'block'; // Show the modal
  });

closeBtn.onclick = function () {
  modal.style.display = 'none'; // Close the modal
};

cancelLogoutBtn.onclick = function () {
  modal.style.display = 'none'; // Close the modal
};

confirmLogoutBtn.onclick = function () {
  window.location.href = 'homePage.php?logout=true'; // Redirect to logout URL
};

