 // Show the logout confirmation modal
 function showModal(event) {
  event.preventDefault();
  var modal = document.getElementById('logout-modal');
  modal.style.display = 'block';
}

// Hide the logout confirmation modal
function closeModal() {
  var modal = document.getElementById('logout-modal');
  modal.style.display = 'none';
}

// Confirm logout and handle redirect
document.getElementById('confirm-logout').addEventListener('click', function() {
  showLoadingScreen();
  setTimeout(function() {
      window.location.href = '?action=logout';
  }, 500);
});

// Close modal when clicking the close button or cancel button
document.querySelector('#logout-modal .close').addEventListener('click', closeModal);
document.getElementById('cancel-logout').addEventListener('click', closeModal);

function toggleMenu() {
var sideMenu = document.querySelector('.side-menu');
var hamburgerMenu = document.querySelector('.hamburger-menu');
sideMenu.classList.toggle('open');
hamburgerMenu.classList.toggle('active');
}