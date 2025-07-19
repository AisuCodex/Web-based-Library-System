<?php
include '../database/Register_database.php';
session_name('assistant_session');
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: ../PHP/assistantLogin.php");
    exit();
}

// Create book_views table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS book_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    book_type ENUM('thesis', 'capstone') NOT NULL,
    view_count INT DEFAULT 0,
    last_viewed DATETIME,
    UNIQUE KEY book_unique (book_id, book_type)
)";

if (!$conn->query($create_table_sql)) {
    die("Error creating table: " . $conn->error);
}

// Join with thesisbooks and capstonebooks tables to get titles
$sql = "SELECT 
            bv.*, 
            CASE 
                WHEN bv.book_type = 'thesis' THEN tb.title 
                WHEN bv.book_type = 'capstone' THEN cb.title 
            END as book_title 
        FROM book_views bv 
        LEFT JOIN thesisbooks tb ON bv.book_type = 'thesis' AND bv.book_id = tb.id 
        LEFT JOIN capstonebooks cb ON bv.book_type = 'capstone' AND bv.book_id = cb.id 
        ORDER BY bv.view_count DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Views Analytics</title>
    <link rel="stylesheet" href="../CSS/loading_screen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --darkest-shade: #2a3417;
            --darker-shade: #3f4a22;
            --base-color: #556b2f;
            --lighter-tint: #758b4d;
            --lightest-tint: #99b27a;
            --extra-lightest-tint: #dbe4d0;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--extra-lightest-tint);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--extra-lightest-tint);
        }

        .header-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .back-btn, .print-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: var(--base-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-btn:hover, .print-btn:hover {
            background-color: var(--darker-shade);
        }

        .back-btn i, .print-btn i {
            margin-right: 8px;
        }

        .header h2 {
            color: var(--darkest-shade);
            margin: 0;
            font-size: 24px;
        }

        .search-section {
            background-color: var(--extra-lightest-tint);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--lighter-tint);
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--base-color);
        }

        .type-filter {
            padding: 10px 15px;
            border: 1px solid var(--lighter-tint);
            border-radius: 4px;
            background-color: white;
            min-width: 150px;
            font-size: 14px;
            cursor: pointer;
        }

        .type-filter:focus {
            outline: none;
            border-color: var(--base-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: white;
        }

        th {
            background-color: var(--base-color);
            color: white;
            font-weight: 600;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid var(--darker-shade);
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--extra-lightest-tint);
            color: var(--darker-shade);
        }

        tr:hover {
            background-color: var(--extra-lightest-tint);
        }

        .book-type {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .thesis {
            background-color: var(--extra-lightest-tint);
            color: var(--darkest-shade);
        }

        .capstone {
            background-color: var(--lightest-tint);
            color: var(--darkest-shade);
        }

        .view-count {
            font-weight: 600;
            color: var(--base-color);
        }

        .timestamp {
            color: var(--lighter-tint);
            font-size: 13px;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .header-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .search-section {
                flex-direction: column;
                gap: 10px;
            }

            .type-filter {
                width: 100%;
            }

            /* Make table responsive */
            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                margin-bottom: 15px;
                border: 1px solid var(--lighter-tint);
                border-radius: 8px;
                background: white;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            td {
                position: relative;
                padding: 12px 10px 12px 50%;
                border: none;
                border-bottom: 1px solid var(--extra-lightest-tint);
            }

            td:last-child {
                border-bottom: none;
            }

            td:before {
                position: absolute;
                left: 10px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                content: attr(data-label);
                font-weight: bold;
                color: var(--darkest-shade);
            }

            /* Add data labels for mobile view */
            td:nth-of-type(1):before { content: "Title:"; }
            td:nth-of-type(2):before { content: "Type:"; }
            td:nth-of-type(3):before { content: "View Count:"; }
            td:nth-of-type(4):before { content: "Last Viewed:"; }

            .book-type {
                display: inline-block;
                margin-top: 5px;
            }

            .view-count {
                margin-top: 5px;
            }

            .timestamp {
                margin-top: 5px;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                padding: 0;
                background: white;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
            }
            
            th, td {
                border: 1px solid #ddd;
            }
            
            .book-type {
                border: none;
                padding: 0;
                background: none;
                color: black;
            }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="container">
        <div class="header">
            <h2>Book Views Analytics</h2>
            <div class="header-buttons">
                <button class="print-btn no-print" onclick="printReport()">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <a href="assistantPage.php" class="back-btn no-print" onclick="showLoadingScreen()">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <div class="search-section no-print">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by title...">
            <select id="typeFilter" class="type-filter">
                <option value="all">All Types</option>
                <option value="thesis">Thesis</option>
                <option value="capstone">Capstone</option>
            </select>
        </div>

        <table id="viewsTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>View Count</th>
                    <th>Last Viewed</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $typeClass = strtolower($row['book_type']);
                        echo "<tr class='book-row' data-type='" . htmlspecialchars($row['book_type']) . "'>";
                        echo "<td data-label='Title'>" . htmlspecialchars($row['book_title']) . "</td>";
                        echo "<td data-label='Type'><span class='book-type " . $typeClass . "'>" . ucfirst(htmlspecialchars($row['book_type'])) . "</span></td>";
                        echo "<td data-label='View Count' class='view-count'>" . htmlspecialchars($row['view_count']) . "</td>";
                        echo "<td data-label='Last Viewed' class='timestamp'>" . date('M d, Y g:i A', strtotime($row['last_viewed'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No views recorded yet</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="../JavaScripts/loadingScreen.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const typeFilter = document.getElementById('typeFilter');
            const rows = document.querySelectorAll('.book-row');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedType = typeFilter.value;

                rows.forEach(row => {
                    const title = row.cells[0].textContent.toLowerCase();
                    const type = row.dataset.type;
                    
                    const matchesSearch = title.includes(searchTerm);
                    const matchesType = selectedType === 'all' || type === selectedType;

                    row.classList.toggle('hidden', !matchesSearch || !matchesType);
                });
            }

            searchInput.addEventListener('input', filterTable);
            typeFilter.addEventListener('change', filterTable);
        });

        function printReport() {
            // Remove hidden rows before printing
            const hiddenRows = document.querySelectorAll('.book-row.hidden');
            hiddenRows.forEach(row => row.style.display = 'none');
            
            // Add print header with current date
            const printHeader = document.createElement('div');
            printHeader.className = 'print-only';
            printHeader.style.textAlign = 'center';
            printHeader.style.marginBottom = '20px';
            printHeader.innerHTML = `
                <h1 style="color: #2a3417;">Book Views Report</h1>
                <p>Generated on: ${new Date().toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</p>
            `;
            document.querySelector('.container').insertBefore(printHeader, document.querySelector('.header'));
            
            window.print();
            
            // Cleanup after printing
            printHeader.remove();
            hiddenRows.forEach(row => row.style.display = '');
        }
    </script>
</body>
</html>
