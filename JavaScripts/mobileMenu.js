// Hamburger Menu Functionality
function toggleMenu() {
    const sideMenu = document.querySelector('.side-menu');
    const hamburger = document.querySelector('.hamburger-menu');
    sideMenu.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const sideMenu = document.querySelector('.side-menu');
    const hamburger = document.querySelector('.hamburger-menu');
    if (!event.target.closest('.hamburger-menu') && !event.target.closest('.side-menu')) {
        sideMenu.classList.remove('active');
        hamburger.classList.remove('active');
    }
});
