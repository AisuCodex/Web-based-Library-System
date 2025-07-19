<?php
include 'config.php';

// Handle deletion if 'id' is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure id is an integer
    $deleteQuery = "DELETE FROM capstonebooks WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Record deleted successfully!'); window.location='capstoneCrud.php';</script>";
    } else {
        echo "<script>alert('Failed to delete record!');</script>";
    }
    $stmt->close();
}

// Initialize the search query and pagination variables
$search = '';
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $limit; // Offset for SQL query
$titleCount = 0;
$abstractCount = 0;

if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

// Function to highlight search terms
function highlightSearchTerm($text, $searchTerm) {
    if ($searchTerm === '') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    return preg_replace("/\b(" . preg_quote($searchTerm, '/') . ")\b/i", "<mark>$1</mark>", htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
}

// Prepare the SQL query with a search condition and limit/offset for pagination
$sql = "SELECT * FROM capstonebooks";
$countTitleSql = "SELECT COUNT(*) as title_count FROM capstonebooks";
$countAbstractSql = "SELECT COUNT(*) as abstract_count FROM capstonebooks";

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $sql .= " WHERE title LIKE '%$search%' OR abstract LIKE '%$search%' OR author LIKE '%$search%' OR year LIKE '%$search%' OR course LIKE '%$search%'";
    $countTitleSql .= " WHERE title LIKE '%$search%'";
    $countAbstractSql .= " WHERE abstract LIKE '%$search%'";
}

// Adjust limit and offset if a search is performed
if ($search !== '') {
    $sql .= ""; // Remove limit and offset
} else {
    $sql .= " LIMIT $limit OFFSET $offset"; // Apply limit and offset
}

$result = $conn->query($sql);
if (!$result) {
    die("Error in query: " . $conn->error);
}

// Get title count
$titleCountResult = $conn->query($countTitleSql);
if ($titleCountResult && $row = $titleCountResult->fetch_assoc()) {
    $titleCount = $row['title_count'];
}

// Get abstract count
$abstractCountResult = $conn->query($countAbstractSql);
if ($abstractCountResult && $row = $abstractCountResult->fetch_assoc()) {
    $abstractCount = $row['abstract_count'];
}

// Count total number of rows for pagination
$count_sql = "SELECT COUNT(*) as total FROM capstonebooks";
if ($search !== '') {
    $count_sql .= " WHERE title LIKE '%$search%' OR abstract LIKE '%$search%' OR author LIKE '%$search%' OR year LIKE '%$search%' OR course LIKE '%$search%'";
}
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capstones Projects</title>
    <link rel="stylesheet" href="../CSS/AdminTable.css">
</head>
<body id="top">
    <div class="back-btn-container">
        <a class="back-btn" href="../admin/adminPage.php">Back</a>
    </div>
    
    <h1>CAPSTONES PROJECTS</h1>
    
    <!-- Search Form -->
    <form method="post" action="">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title, abstract, or author">
        <button type="submit">Search</button>
    </form>
    
    <div class="create-btn-container">
        <a class="create-btn" href="create.php">Add New Capstone</a>
    </div>

    <!-- Display the count of matching titles and abstracts centered with darkolivegreen color -->
    <?php if ($search !== ''): ?>
    <div class="count-display">
        <p class="count-text">
            <?php echo $titleCount; ?> title(s) <br> and <br> 
            <?php echo $abstractCount; ?> abstract(s) <br>
            Match = "<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
        </p>
    </div>
    <?php endif; ?>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Control Number</th>
            <th>Course</th>
            <th>Title</th>
            <th>Abstract</th>
            <th>Year</th>
            <th>Author</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><span>ID</span> <?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span>Control Number</span> <?php echo str_pad(htmlspecialchars($row['control_number'], ENT_QUOTES, 'UTF-8'), 3, '0', STR_PAD_LEFT); ?></td>
            <td><span>Course</span> <?php echo htmlspecialchars($row['course'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span>Title</span> <?php echo highlightSearchTerm($row['title'], $search); ?></td>
            
            <!-- Abstract text with "Show Abstract" button below -->
            <td>
                <p id="abstract-<?php echo $row['id']; ?>" class="abstract-text" style="display:none;">
                    <?php echo highlightSearchTerm($row['abstract'], $search); ?>
                </p>
                <button class="show-abstract-btn" onclick="toggleAbstract(<?php echo $row['id']; ?>, 'capstone')">SHOW ABSTRACT</button>
            </td>

            <td><span>Year</span> <?php echo htmlspecialchars($row['year'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span>Author</span> <?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <div class="actions">
                <a href="edit.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="edit-btn">Edit</a>
                <a href="capstoneCrud.php?id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Are you sure?')" class="delete-btn">Delete</a>
                </div>

            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    



    <!-- Pagination Links -->
    <?php if ($search === ''): // Show pagination only if not searching ?>
    <div class="pagination" id="pagination-container">
        <?php 
        // Check if there's more than one page
        if ($total_pages > 1): 
        ?>
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
            <?php endif; ?>

            <?php 
            // Show only 5 page numbers at a time
            $startPage = max(1, $page - 2); // Start two pages before the current page
            $endPage = min($total_pages, $startPage + 4); // End page, 5 numbers in total

            // Adjust startPage if near the last page
            if ($endPage - $startPage < 4 && $total_pages >= 5) {
                $startPage = max(1, $endPage - 4);
            }

            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
            <?php endif; ?>

            <!-- Show "Show All" Button if total pages exceed 5 -->
            <?php if ($total_pages > 5): ?>
                <button id="show-all-btn" onclick="showAllPages()">Show All</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>


    <!-- Footer -->
<footer class="footer">
    <div class="footer-container">
        <p>&copy; <?php echo date("Y"); ?> Web-based Library System. All rights reserved.</p>
    </div>
</footer>   

    <script src="../JavaScripts/scrollUpBtn.js"></script>
    <script src="../JavaScripts/showAbstract.js"></script>
    <script>
    // Function to show all pages
    function showAllPages() {
        const paginationContainer = document.getElementById('pagination-container');
        paginationContainer.innerHTML = '';

        const totalPages = <?php echo $total_pages; ?>;
        const currentPage = <?php echo $page; ?>;

        // Add all pages
        for (let i = 1; i <= totalPages; i++) {
            const pageLink = document.createElement('a');
            pageLink.href = '?page=' + i;
            pageLink.textContent = i;
            if (i == currentPage) {
                pageLink.classList.add('active');
            }
            paginationContainer.appendChild(pageLink);
        }

        // Add Previous and Next buttons
        if (currentPage > 1) {
            const prevLink = document.createElement('a');
            prevLink.href = '?page=' + (currentPage - 1);
            prevLink.innerHTML = '&laquo; Previous';
            paginationContainer.insertBefore(prevLink, paginationContainer.firstChild);
        }

        if (currentPage < totalPages) {
            const nextLink = document.createElement('a');
            nextLink.href = '?page=' + (currentPage + 1);
            nextLink.innerHTML = 'Next &raquo;';
            paginationContainer.appendChild(nextLink);
        }

        // Add "Show Less" button
        const showLessBtn = document.createElement('button');
        showLessBtn.id = 'show-less-btn';
        showLessBtn.textContent = 'Show Less';
        showLessBtn.onclick = showLimitedPages;
        paginationContainer.appendChild(showLessBtn);
    }

    // Function to revert back to showing only 5 pages
    function showLimitedPages() {
        const paginationContainer = document.getElementById('pagination-container');
        paginationContainer.innerHTML = '';

        const totalPages = <?php echo $total_pages; ?>;
        const currentPage = <?php echo $page; ?>;

        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);

        // Adjust startPage if near the last page
        const adjustedStartPage = (endPage - startPage < 4 && totalPages >= 5) ? Math.max(1, endPage - 4) : startPage;

        // Display limited 5 pages again
        for (let i = adjustedStartPage; i <= endPage; i++) {
            const pageLink = document.createElement('a');
            pageLink.href = '?page=' + i;
            pageLink.textContent = i;
            if (i == currentPage) {
                pageLink.classList.add('active');
            }
            paginationContainer.appendChild(pageLink);
        }

        // Add Previous and Next buttons
        if (currentPage > 1) {
            const prevLink = document.createElement('a');
            prevLink.href = '?page=' + (currentPage - 1);
            prevLink.innerHTML = '&laquo; Previous';
            paginationContainer.insertBefore(prevLink, paginationContainer.firstChild);
        }

        if (currentPage < totalPages) {
            const nextLink = document.createElement('a');
            nextLink.href = '?page=' + (currentPage + 1);
            nextLink.innerHTML = 'Next &raquo;';
            paginationContainer.appendChild(nextLink);
        }

        // Add "Show All" button back
        const showAllBtn = document.createElement('button');
        showAllBtn.id = 'show-all-btn';
        showAllBtn.textContent = 'Show All';
        showAllBtn.onclick = showAllPages;
        paginationContainer.appendChild(showAllBtn);
    }
    </script>
</body>
</html>
