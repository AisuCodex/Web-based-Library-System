  function toggleAbstract(id, bookType) {
    var abstractText = document.getElementById('abstract-' + id);
    var showAbstractBtn = abstractText.nextElementSibling;
    
    if (abstractText.style.display === 'none') {
        abstractText.style.display = 'block';
        showAbstractBtn.textContent = 'HIDE ABSTRACT';
        
        // Only track view when showing the abstract
        const formData = new FormData();
        formData.append('book_id', id);
        formData.append('book_type', bookType || 'thesis'); // Default to thesis if not specified

        // Use fetch API with error handling
        fetch('../PHP/track_book_view.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                console.error('Network response error:', response.status, response.statusText);
                return response.text().then(text => {
                    throw new Error(`Server responded with ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('View tracked successfully:', data);
            if (!data.success) {
                console.error('Error tracking view:', data.message);
            }
        })
        .catch(error => {
            console.error('Error tracking view:', error);
        });
    } else {
        abstractText.style.display = 'none';
        showAbstractBtn.textContent = 'SHOW ABSTRACT';
    }
}
