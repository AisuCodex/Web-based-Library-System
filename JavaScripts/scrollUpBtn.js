   // Show the scroll to top button when scrolled 50px from the top
window.onscroll = function() {
    const topBtn = document.getElementById('topBtn');
    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
        topBtn.style.display = 'block';
    } else {
        topBtn.style.display = 'none';
    }
};

// Scroll to top function
function toTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}