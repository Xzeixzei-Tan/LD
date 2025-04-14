<?php 
require_once 'config.php';

// Initialize currentAffiliation
$currentAffiliation = isset($_GET['affiliation']) ? $_GET['affiliation'] : '';

// Add pagination parameters
$records_per_page = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base SQL query components
$select_sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name, u.suffix, u.sex, 
        u.contact_no, u.email, c.name as classification_name, cp.name as position_name,
        ul.affiliation_id
        FROM users u 
        INNER JOIN users_lnd ul ON u.id = ul.user_id
        LEFT JOIN classification c ON ul.classification_id = c.id 
        LEFT JOIN class_position cp ON ul.position_id = cp.id 
        WHERE u.deleted_at IS NULL";

// Add affiliation filter if set
if (!empty($currentAffiliation)) {
    $select_sql .= " AND ul.affiliation_id = '$currentAffiliation'";
}

// Add search condition if provided
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search_term)) {
    $search_term = $conn->real_escape_string($search_term);
    $select_sql .= " AND (u.first_name LIKE '%$search_term%' OR 
                          u.last_name LIKE '%$search_term%' OR 
                          u.email LIKE '%$search_term%' OR 
                          u.contact_no LIKE '%$search_term%')";
}

// Complete the query with ordering and pagination
$select_sql .= " ORDER BY u.id ASC LIMIT $records_per_page OFFSET $offset";

// Execute the query
$result = $conn->query($select_sql);

// Error handling for SQL query
if ($result === false) {
    die("SQL Error: " . $conn->error);
}

// Count total number of users with the same filters (for pagination)
$count_sql = "SELECT COUNT(*) as total 
              FROM users u 
              INNER JOIN users_lnd ul ON u.id = ul.user_id
              WHERE u.deleted_at IS NULL";

// Add the same filters to the count query
if (!empty($currentAffiliation)) {
    $count_sql .= " AND ul.affiliation_id = '$currentAffiliation'";
}

if (!empty($search_term)) {
    $count_sql .= " AND (u.first_name LIKE '%$search_term%' OR 
                          u.last_name LIKE '%$search_term%' OR 
                          u.email LIKE '%$search_term%' OR 
                          u.contact_no LIKE '%$search_term%')";
}

$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_users = $count_row['total'];
$total_pages = ceil($total_users / $records_per_page);

//Query to count the number of Users from Schools
$schoolCount = $conn->prepare("
    SELECT COUNT(*) as count
        FROM users_lnd WHERE affiliation_id = '1'");
$schoolCount->execute();
$schoolResult = $schoolCount->get_result();

//Query to count the number of Users from Division
$divCount = $conn->prepare("
    SELECT COUNT(*) as count
        FROM users_lnd WHERE affiliation_id = '2'");
$divCount->execute();
$divResult = $divCount->get_result();

// Count total users
$totalCount = $conn->prepare("
    SELECT COUNT(*) as count
        FROM users_lnd");
$totalCount->execute();
$totalResult = $totalCount->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/admin-dashboard.css" rel="stylesheet">
    <link href="styles/admin-user.css" rel="stylesheet">
    <title>Users - Events Management System</title>
</head>

<body data-current-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0'; ?>">
<div class="sidebar" id="sidebar">
    <div class="logo">
        <button id="toggleSidebar" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
        
    <div class="menu">
        <a href="admin-dashboard.php">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="admin-events.php">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        <a href="admin-users.php"class="active">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
    </div>
</div>

    <div class="content" id="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br>

        <div class="content-body">
            <h1>Users</h1>
            <hr><br>

            <!-- Search Bar Only -->
            <div class="filter-bar">
                <div class="search-container">
                    <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search for users..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
            </div>

            <br><br>
            <div class="personnel">
                <?php if ($totalResult) {
                    $row = $totalResult->fetch_assoc();
                } ?>
                <div class="all-personnel <?php echo empty($currentAffiliation) ? 'active' : ''; ?>" id="all-personnel">
                    <p>All personnel: <?php echo $row['count']; ?></p>
                </div>
                
                <?php if ($schoolResult) {
                    $row = $schoolResult->fetch_assoc();
                } ?>
                <div class="school <?php echo $currentAffiliation === '1' ? 'active' : ''; ?>" id="school-filter">
                    <p>School personnel: <?php echo $row['count']; ?></p>
                </div>

                <?php if ($divResult) {
                    $row = $divResult->fetch_assoc();
                } ?>
                <div class="division <?php echo $currentAffiliation === '2' ? 'active' : ''; ?>" id="division-filter">
                    <p>Division personnel: <?php echo $row['count']; ?></p>
                </div>
            </div>
            
            <div class="bulk-actions" id="bulk-actions" style="<?php echo $currentAffiliation === '1' ? '' : 'display: none;'; ?>">
                <button class="delete-selected-btn" id="delete-selected"><i class="fa fa-trash" aria-hidden="true"></i> Delete Selected</button>
            </div>
    
            <table id="usersTable">
            <thead>
                <tr>
                    <th class="checkbox-column <?php echo $currentAffiliation === '1' ? '' : 'hidden'; ?>"><input type="checkbox" id="select-all"></th>
                    <th>#</th>
                    <th>Name</th>
                    <th>Sex</th>
                    <th>Contact Number</th>
                    <th>School Assignment</th>
                    <th>Position</th>
                    <th>E-mail</th>
                    <th>Password</th>
                </tr>
            </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Calculate starting row number based on pagination
                    $count = $offset + 1;
                    while ($row = $result->fetch_assoc()) {
                        // Format name with middle initial and suffix
                        $middle_initial = !empty($row["middle_name"]) ? " " . substr($row["middle_name"], 0, 1) . "." : "";
                        $suffix = !empty($row["suffix"]) ? " " . $row["suffix"] : "";
                        $full_name = $row["first_name"] . $middle_initial . " " . $row["last_name"] . $suffix;

                        echo "<tr data-affiliation='" . $row["affiliation_id"] . "'>";
                        echo "<td class='checkbox-cell " . ($currentAffiliation === '1' ? '' : 'hidden') . "'>";
                        // Only show checkboxes for school personnel (affiliation_id = 1)
                        if ($row["affiliation_id"] == 1) {
                            echo "<input type='checkbox' class='user-checkbox' data-id='" . $row["id"] . "'>";
                        }
                        echo "</td>";
                        echo "<td>" . $count . "</td>";
                        echo "<td>" . $full_name . "</td>";
                        echo "<td>" . $row["sex"] . "</td>";
                        echo "<td>" . $row["contact_no"] . "</td>";
                        echo "<td>" . ($row["classification_name"] ?? "Not Assigned") . "</td>";
                        echo "<td>" . ($row["position_name"] ?? "Not Assigned") . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>*****</td>";
                        echo "</tr>";
                        $count++;
                    }
                } else {
                    $colspan = $currentAffiliation === '1' ? 9 : 8;
                    echo "<tr><td colspan='$colspan' style='text-align:center'>No users found</td></tr>";
                }
                ?>
                </tbody>
            </table>

            <!-- Pagination navigation -->
            <div class="pagination" id="pagination">
                <?php if ($total_pages > 1): ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo !empty($currentAffiliation) ? '&affiliation='.$currentAffiliation : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>" class="first-page"><i class="fas fa-angle-double-left"></i></a>
                        <a href="?page=<?php echo $page-1; ?><?php echo !empty($currentAffiliation) ? '&affiliation='.$currentAffiliation : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>" class="prev-page"><i class="fas fa-angle-left"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                        <span class="disabled"><i class="fas fa-angle-left"></i></span>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($start_page + 4, $total_pages);
                    
                    if ($end_page - $start_page < 4) {
                        $start_page = max(1, $end_page - 4);
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($currentAffiliation) ? '&affiliation='.$currentAffiliation : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo !empty($currentAffiliation) ? '&affiliation='.$currentAffiliation : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>" class="next-page"><i class="fas fa-angle-right"></i></a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo !empty($currentAffiliation) ? '&affiliation='.$currentAffiliation : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>" class="last-page"><i class="fas fa-angle-double-right"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-right"></i></span>
                        <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Display pagination info -->
            <div class="pagination-info">
                Showing <?php echo min($total_users, 1 + $offset); ?> to <?php echo min($offset + $records_per_page, $total_users); ?> of <?php echo $total_users; ?> entries
            </div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const content = document.getElementById('content');
    const toggleBtn = document.getElementById('toggleSidebar');
    const tableBody = document.querySelector('table tbody');
    const searchInput = document.getElementById('searchInput');
    const usersTable = document.getElementById('usersTable');
    const bulkActions = document.getElementById('bulk-actions');
    
    // Get current affiliation from URL or empty string if not set
    const urlParams = new URLSearchParams(window.location.search);
    const currentAffiliation = urlParams.get('affiliation') || '';
    
    // Get current user ID from session
    const currentUserId = parseInt(document.body.getAttribute('data-current-user-id') || '0');

    // Check if sidebar state is saved in localStorage
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Set initial state based on localStorage
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
    }
    
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Function to check if any checkboxes are selected
    function checkSelectedCheckboxes() {
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        
        if (checkboxes.length > 0) {
            // Show bulk actions and add margin to table
            bulkActions.classList.add('visible');
            usersTable.style.marginTop = '20px'; // Add space for the bulk actions bar
            bulkActions.style.opacity = '1';
            bulkActions.style.transform = 'translateY(0)';
        } else {
            // Hide bulk actions and remove margin from table
            bulkActions.classList.remove('visible');
            usersTable.style.marginTop = '0'; // Reset margin when no checkboxes selected
            bulkActions.style.opacity = '0';
            bulkActions.style.transform = 'translateY(-100%)';
        }
    }

    // Handle checkbox changes to show/hide bulk delete button
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', checkSelectedCheckboxes);
    });

    // Handle select all checkbox
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        checkSelectedCheckboxes();
    });

    // Initialize the UI state
    checkSelectedCheckboxes(); // Check if any checkboxes are already selected

    // Handle bulk delete button click
    document.getElementById('delete-selected').addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('Please select at least one user to delete.');
            return;
        }
        
        if (confirm('Are you sure you want to delete ' + selectedCheckboxes.length + ' selected user(s)?')) {
            // Collect all selected user IDs
            const userIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-id'));
            
            // Send AJAX request to delete multiple users
            fetch('delete_multiple_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ userIds: userIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Check if the current user's ID is in the list of deleted IDs
                    const deletedUserIds = data.deletedUserIds.map(id => parseInt(id));
                    if (deletedUserIds.includes(currentUserId)) {
                        // Current user was deleted in bulk operation
                        alert('This account has been deleted. Redirecting to signup page.');
                        window.location.href = 'signup.php?account_deleted=1';
                    } else {
                        // Current user not deleted, just remove the rows
                        selectedCheckboxes.forEach(checkbox => {
                            checkbox.closest('tr').remove();
                        });
                        
                        // Hide bulk actions button
                        document.getElementById('bulk-actions').classList.remove('visible');
                        
                        // Uncheck select all
                        document.getElementById('select-all').checked = false;
                        
                        alert('Selected users have been deleted successfully.');
                    }
                } else {
                    alert('Error deleting users: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while trying to delete the selected users.');
            });
        }
    });

    // Personnel selector functionality
    const schoolFilter = document.getElementById('school-filter');
    const divisionFilter = document.getElementById('division-filter');
    const allPersonnelFilter = document.getElementById('all-personnel');

    // Function to filter by affiliation
    function filterByAffiliation(affiliationId) {
        // Build filter URL with pagination reset
        let filterUrl = window.location.pathname + '?page=1';
        
        // Add affiliation filter if set
        if (affiliationId) {
            filterUrl += '&affiliation=' + encodeURIComponent(affiliationId);
        }

        // Keep any existing search parameter
        const searchParam = urlParams.get('search');
        if (searchParam) {
            filterUrl += '&search=' + encodeURIComponent(searchParam);
        }

        // Navigate to filtered view
        window.location.href = filterUrl;
    }
    
    // Add event listeners to personnel filter buttons
    schoolFilter.addEventListener('click', function() {
        filterByAffiliation('1');
    });
    
    divisionFilter.addEventListener('click', function() {
        filterByAffiliation('2');
    });
    
    allPersonnelFilter.addEventListener('click', function() {
        filterByAffiliation('');
    });

    // Search functionality with debounce
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    function performSearch() {
        const searchValue = searchInput.value.trim();
        
        if (searchValue.length >= 2 || searchValue.length === 0) {
            // Build the search URL, resetting to page 1
            let searchUrl = window.location.pathname + '?page=1';
            
            // Add search param if not empty
            if (searchValue.length >= 2) {
                searchUrl += '&search=' + encodeURIComponent(searchValue);
            }
            
            // Add affiliation filter if set
            if (currentAffiliation) {
                searchUrl += '&affiliation=' + encodeURIComponent(currentAffiliation);
            }
            
            // Navigate to search results page
            window.location.href = searchUrl;
        }
    }
    
    // Add search input event listeners
    searchInput.addEventListener('input', debounce(function() {
        if (this.value.trim().length >= 2 || this.value.trim().length === 0) {
            performSearch();
        }
    }, 500)); // 500ms debounce

    // Handle Enter key in search input
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
});
    </script>
</body>
</html>

<?php 
$conn->close();
?>