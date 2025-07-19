// Function to toggle the abstract modal
function toggleAbstract(id, type) {
    const modal = document.getElementById('abstract-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalAbstract = document.getElementById('modal-abstract');
    const titleElement = document.querySelector(`tr:has(#abstract-${id}) td:nth-child(4)`);
    const abstractElement = document.getElementById(`abstract-${id}`);

    if (!titleElement || !abstractElement) {
        console.error('Could not find title or abstract elements');
        return;
    }

    modalTitle.textContent = titleElement.textContent.replace('Title ', '');
    modalAbstract.innerHTML = abstractElement.innerHTML;
    modal.style.display = 'block';

    // Track the view when showing the abstract
    const formData = new FormData();
    formData.append('book_id', id);
    formData.append('book_type', type || 'thesis'); // Use provided type or default to thesis

    fetch('../PHP/track_book_view.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('View tracked successfully');
        } else {
            console.error('Error tracking view:', data.message);
            // You might want to show a small notification to admin users
            if (document.querySelector('.admin-content')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-warning';
                errorDiv.style.position = 'fixed';
                errorDiv.style.top = '20px';
                errorDiv.style.right = '20px';
                errorDiv.style.zIndex = '9999';
                errorDiv.textContent = 'Note: View tracking encountered an error';
                document.body.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 3000);
            }
        }
    })
    .catch(error => {
        console.error('Error tracking view:', error);
    });

    // Close button functionality
    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    // Click outside modal to close
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}
