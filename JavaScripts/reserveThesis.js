function reserveThesis(bookId) {
    // References to modals
    const confirmModal = document.getElementById('reservation-confirm-modal');
    const responseModal = document.getElementById('reservation-response-modal');
    const responseTitle = document.getElementById('response-title');
    const responseMessage = document.getElementById('response-message');
    const modalContent = responseModal.querySelector('.modal-content');
    
    // Close button handlers
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.onclick = function() {
            confirmModal.style.display = 'none';
            responseModal.style.display = 'none';
        }
    });
    
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == confirmModal) {
            confirmModal.style.display = 'none';
        }
        if (event.target == responseModal) {
            responseModal.style.display = 'none';
        }
    }
    
    // Response OK button
    document.getElementById('response-ok').onclick = function() {
        responseModal.style.display = 'none';
        // Reload if the last operation was successful
        if (modalContent.classList.contains('success-response')) {
            location.reload();
        }
    };
    
    // Get the user's email from the session
    fetch('../ThesisPage/get_user_email.php', {
        credentials: 'same-origin'  // Include cookies in the request
    })
    .then(response => response.json())
    .then(data => {
        if (data.email) {
            // Show confirmation modal instead of alert
            document.getElementById('reservation-confirm-text').textContent = 
                "Are you sure you want to reserve this thesis?";
            
            // Show the confirmation modal
            confirmModal.style.display = 'block';
            
            // Confirm button handler
            document.getElementById('confirm-reservation').onclick = function() {
                confirmModal.style.display = 'none';
                
                // Create the reservation
                const formData = new FormData();
                formData.append('book_id', bookId);
                formData.append('user_email', data.email);
                
                // Show loading in response modal
                responseTitle.textContent = "Processing...";
                responseMessage.textContent = "Please wait while we process your reservation.";
                modalContent.classList.remove('success-response', 'error-response');
                responseModal.style.display = 'block';
                
                fetch('../ThesisPage/reserve_thesis.php', {
                    method: 'POST',
                    credentials: 'same-origin',  // Include cookies in the request
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    try {
                        const result = JSON.parse(data);
                        
                        // Update modal with response
                        if (result.success) {
                            responseTitle.textContent = "Success!";
                            modalContent.classList.add('success-response');
                            modalContent.classList.remove('error-response');
                        } else {
                            responseTitle.textContent = "Reservation Failed";
                            modalContent.classList.add('error-response');
                            modalContent.classList.remove('success-response');
                        }
                        
                        responseMessage.textContent = result.message || 'An error occurred';
                        responseModal.style.display = 'block';
                    } catch (e) {
                        // If the response is not JSON, show as is
                        responseTitle.textContent = "Error";
                        responseMessage.textContent = data;
                        modalContent.classList.add('error-response');
                        modalContent.classList.remove('success-response');
                        responseModal.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    responseTitle.textContent = "Error";
                    responseMessage.textContent = 'An error occurred while processing your reservation.';
                    modalContent.classList.add('error-response');
                    modalContent.classList.remove('success-response');
                    responseModal.style.display = 'block';
                });
            };
            
            // Cancel button handler
            document.getElementById('cancel-reservation').onclick = function() {
                confirmModal.style.display = 'none';
            };
        } else {
            // Show login required message
            responseTitle.textContent = "Login Required";
            responseMessage.textContent = 'Please log in to reserve a thesis book.';
            modalContent.classList.add('error-response');
            modalContent.classList.remove('success-response');
            
            // Add a custom handler for the OK button in this case
            document.getElementById('response-ok').onclick = function() {
                responseModal.style.display = 'none';
                window.location.href = '../PHP/loginPage.php';
            };
            
            responseModal.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        responseTitle.textContent = "Error";
        responseMessage.textContent = 'An error occurred while getting user information. Please try again.';
        modalContent.classList.add('error-response');
        modalContent.classList.remove('success-response');
        responseModal.style.display = 'block';
    });
}
