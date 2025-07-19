// Logout Modal
var modal = document.getElementById('logout-modal');
var logoutLink = document.getElementById('logout-link');
var mobileLogoutLink = document.getElementById('mobile-logout-link');
var closeBtn = document.querySelector('.modal .close');
var confirmLogoutBtn = document.getElementById('confirm-logout');
var cancelLogoutBtn = document.getElementById('cancel-logout');

// Show modal when either logout link is clicked
[logoutLink, mobileLogoutLink].forEach(function(link) {
  if (link) {
    link.addEventListener('click', function(event) {
      event.preventDefault();
      modal.style.display = 'block';
    });
  }
});

// Close modal functions
closeBtn.onclick = function() {
  modal.style.display = 'none';
}

cancelLogoutBtn.onclick = function() {
  modal.style.display = 'none';
}

// Close modal if clicking outside
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = 'none';
  }
}

confirmLogoutBtn.onclick = function() {
  var loadingIndicator = document.createElement('div');
  loadingIndicator.className = 'loading-indicator';
  loadingIndicator.textContent = 'Logging out...';
  document.body.appendChild(loadingIndicator);
  
  window.location.href = 'homePage.php?action=logout';
}
