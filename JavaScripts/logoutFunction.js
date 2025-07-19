function toggleMenu() {
  let sideMenu = document.querySelector('.side-menu');
  let hamburgerMenu = document.querySelector('.hamburger-menu');
  sideMenu.classList.toggle('open');
  hamburgerMenu.classList.toggle('active'); // Toggle active class for animation
}