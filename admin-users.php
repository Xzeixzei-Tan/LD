<?php 
require_once 'config.php';


// Initialize currentAffiliation - fixes undefined variable error
$currentAffiliation = '';

// Improved query to fetch users with their LND details - explicitly ordering by ID in ascending order
$sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name, u.suffix, u.sex, 
        u.contact_no, u.email, c.name as classification_name, cp.name as position_name,
        ul.affiliation_id
        FROM users u 
        INNER JOIN users_lnd ul ON u.id = ul.user_id
        LEFT JOIN classification c ON ul.classification_id = c.id 
        LEFT JOIN class_position cp ON ul.position_id = cp.id 
        WHERE u.deleted_at IS NULL 
        ORDER BY u.id ASC"; // Added ASC for explicitness
$result = $conn->query($sql);

// Error handling for SQL query
if ($result === false) {
    die("SQL Error: " . $conn->error);
}

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
    <title>Users</title>
    <style>
        /* Add CSS to hide checkboxes when needed */
        .checkbox-column.hidden,
        .checkbox-cell.hidden {
            display: none !important;
        }
    </style>
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
        </div><br><br><br>

        <div class="content-body">
            <h1>Users</h1><br>

            <!-- Search Bar Only -->
            <div class="filter-bar">
                <div class="search-container">
                    <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
                    <input type="text" class="search-input" placeholder="Search for users...">
                </div>
            </div>

            <br><br>
            <div class="personnel">
                <?php if ($totalResult) {
                    $row = $totalResult->fetch_assoc();
                } ?>
                <div class="all-personnel" id="all-personnel">
                    <p>All personnel: <?php echo $row['count']; ?></p>
                </div>
                
                <?php if ($schoolResult) {
                    $row = $schoolResult->fetch_assoc();
                } ?>
                <div class="school" id="school-filter">
                    <p>School personnel: <?php echo $row['count']; ?></p>
                </div>

                <?php if ($divResult) {
                    $row = $divResult->fetch_assoc();
                } ?>
                <div class="division" id="division-filter">
                    <p>Division personnel: <?php echo $row['count']; ?></p>
                </div>
            </div>

            <br><br><br>
            <div class="bulk-actions" id="bulk-actions">
                <button class="delete-selected-btn" id="delete-selected"><i class="fa fa-trash" aria-hidden="true"></i> Delete Selected</button>
            </div>
    
            <table id="usersTable">
            <thead>
                <tr>
                    <th class="checkbox-column"><input type="checkbox" id="select-all"></th>
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
                    $count = 1;
                    while ($row = $result->fetch_assoc()) {
                        // Format name with middle initial and suffix
                        $middle_initial = !empty($row["middle_name"]) ? " " . substr($row["middle_name"], 0, 1) . "." : "";
                        $suffix = !empty($row["suffix"]) ? " " . $row["suffix"] : "";
                        $full_name = $row["first_name"] . $middle_initial . " " . $row["last_name"] . $suffix;

                        echo "<tr data-affiliation='" . $row["affiliation_id"] . "'>";
                        echo "<td class='checkbox-cell'>";
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
                    echo "<tr><td colspan='10' style='text-align:center'>No users found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const content = document.getElementById('content');
    const toggleBtn = document.getElementById('toggleSidebar');
    const tableBody = document.querySelector('table tbody');
    const searchInput = document.querySelector('.search-input');
    const usersTable = document.getElementById('usersTable');
    const bulkActions = document.getElementById('bulk-actions');
    
    // Get current user ID from session (you'll need to add this to your PHP)
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
            usersTable.style.marginTop = '60px'; // Add space for the bulk actions bar
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

    // Handle delete button clicks for individual users
    function setupDeleteButtons() {
        document.querySelectorAll('.delete-btn').forEach(button => {
            const row = button.closest('tr');
            // Only enable delete buttons for school personnel (affiliation_id = 1)
            if (row.getAttribute('data-affiliation') === '1') {
                button.classList.remove('disabled');
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this user?')) {
                        const userId = this.getAttribute('data-id');
                        deleteUser(userId, row);
                    }
                });
            } else {
                // Disable delete button for division personnel
                button.classList.add('disabled');
                // Remove existing event listeners by cloning and replacing
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
            }
        });
    }

    // Function to delete a single user
    function deleteUser(userId, row) {
        // Send AJAX request to delete user
        fetch('delete_user.php?id=' + userId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Check if the deleted user is the currently logged-in user
                if (currentUserId === parseInt(data.deletedUserId)) {
                    // If admin deleted their own account or the account of the logged-in user
                    alert('This account has been deleted. Redirecting to signup page.');
                    window.location.href = 'signup.php?account_deleted=1';
                } else {
                    // Admin deleted someone else's account
                    row.remove();
                    alert('User deleted successfully.');
                }
            } else {
                alert('Error deleting user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while trying to delete the user.');
        });
    }

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

    // Call setupDeleteButtons when the page loads
    checkSelectedCheckboxes(); // Check if any checkboxes are already selected
    setupDeleteButtons(); // Set up delete buttons

    // Store original table rows for filtering (maintains ascending order)
    const originalRows = Array.from(document.querySelectorAll('table tbody tr'));

    // Personnel selector functionality (improved to maintain order)
    const schoolFilter = document.getElementById('school-filter');
    const divisionFilter = document.getElementById('division-filter');
    const allPersonnelFilter = document.getElementById('all-personnel');

    // Function to filter by affiliation while maintaining order
    function filterByAffiliation(affiliationId) {
        // Remove active class from all personnel buttons
        schoolFilter.classList.remove('active');
        divisionFilter.classList.remove('active');
        allPersonnelFilter.classList.remove('active');

        // Add active class to the selected button
        if (affiliationId === '1') {
            schoolFilter.classList.add('active');
        } else if (affiliationId === '2') {
            divisionFilter.classList.add('active');
        } else {
            allPersonnelFilter.classList.add('active');
        }

        // Handle checkbox column visibility
        const checkboxColumn = document.querySelector('.checkbox-column');
        const selectAllCheckbox = document.getElementById('select-all');
        
        // Show/hide checkbox column and bulk actions based on current affiliation
        if (affiliationId === '1') {
            // School personnel view - show checkboxes
            checkboxColumn.classList.remove('hidden');
            document.getElementById('bulk-actions').style.display = '';
        } else {
            // Division or All personnel view - hide checkboxes
            checkboxColumn.classList.add('hidden');
            document.getElementById('bulk-actions').style.display = 'none';
        }
        
        // Clear table body
        tableBody.innerHTML = '';
        
        // Filter and add rows in original order (preserves ascending order)
        let count = 1;
        originalRows.forEach(row => {
            const rowClone = row.cloneNode(true);
            const rowAffiliation = rowClone.getAttribute('data-affiliation');
            
            // Show row if it matches filter or if showing all
            if (!affiliationId || rowAffiliation === affiliationId) {
                // Update the row number to ensure consecutive numbers
                const indexCell = rowClone.querySelector('td:nth-child(2)');
                if (indexCell) {
                    indexCell.textContent = count++;
                }
                
                // Set visibility of checkbox cells based on current filter
                const checkboxCell = rowClone.querySelector('.checkbox-cell');
                if (checkboxCell) {
                    if (affiliationId === '1') {
                        checkboxCell.classList.remove('hidden');
                    } else {
                        checkboxCell.classList.add('hidden');
                    }
                }
                
                tableBody.appendChild(rowClone);
            }
        });

        // If no rows match the filter
        if (tableBody.children.length === 0) {
            const colSpan = affiliationId === '1' ? 9 : 8; // Adjust colspan based on checkbox visibility
            tableBody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center">No users found</td></tr>`;
        }

        // Set up event handlers for new rows
        setupDeleteButtons();
        
        // Add checkbox event listeners
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', checkSelectedCheckboxes);
        });
        
        // Check if we need to show/hide bulk actions
        checkSelectedCheckboxes();
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

    // Function to perform search
    function performSearch(searchValue) {
        // Show loading indicator
        const colSpan = document.querySelector('.checkbox-column.hidden') ? 8 : 9;
        tableBody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center">Searching...</td></tr>`;
        
        // Build the search URL
        let searchUrl = 'search_users.php?term=' + encodeURIComponent(searchValue);
        
        // Get current affiliation filter if any is active
        let currentAffiliation = '';
        if (schoolFilter.classList.contains('active')) {
            currentAffiliation = '1';
        } else if (divisionFilter.classList.contains('active')) {
            currentAffiliation = '2';
        }
        
        // Add affiliation filter if set
        if (currentAffiliation) {
            searchUrl += '&affiliation=' + encodeURIComponent(currentAffiliation);
        }
        
        // Also add ordering parameter to ensure ascending order
        searchUrl += '&orderby=id&order=asc';
        
        // Send AJAX request to search users
        fetch(searchUrl)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.data.length > 0) {
                        // Clear table and add new rows
                        tableBody.innerHTML = '';
                        
                        data.data.forEach((user, index) => {
                            const row = document.createElement('tr');
                            row.setAttribute('data-affiliation', user.affiliation_id);
                            
                            // Create checkbox cell
                            const checkboxCell = document.createElement('td');
                            checkboxCell.className = 'checkbox-cell';
                            
                            // Only add checkbox for school personnel and if in school view
                            if (user.affiliation_id === '1' && currentAffiliation === '1') {
                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.className = 'user-checkbox';
                                checkbox.setAttribute('data-id', user.id);
                                checkbox.addEventListener('change', checkSelectedCheckboxes);
                                checkboxCell.appendChild(checkbox);
                            } else {
                                // Hide checkbox cell if not school personnel or not in school view
                                checkboxCell.classList.add('hidden');
                            }
                            
                            row.appendChild(checkboxCell);
                            
                            // Add other cells with proper index (starting from 1)
                            row.innerHTML += `
                                <td>${index + 1}</td>
                                <td>${user.name}</td>
                                <td>${user.sex}</td>
                                <td>${user.contact_no}</td>
                                <td>${user.classification}</td>
                                <td>${user.position}</td>
                                <td>${user.email}</td>
                                <td>*****</td>
                            `;
                            
                            tableBody.appendChild(row);
                        });
                        
                        // Set up delete buttons for new rows
                        setupDeleteButtons();
                        // Check if we need to show/hide bulk actions
                        checkSelectedCheckboxes();
                    } else {
                        const colSpan = currentAffiliation === '1' ? 9 : 8;
                        tableBody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center">No users found matching your search criteria</td></tr>`;
                    }
                } else {
                    const colSpan = currentAffiliation === '1' ? 9 : 8;
                    tableBody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center">Error: ${data.message}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const colSpan = currentAffiliation === '1' ? 9 : 8;
                tableBody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center">Error connecting to the server</td></tr>`;
            });
    }

    // Add debounce function for search
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
    
    // Handle search input with server-side search
    searchInput.addEventListener('input', debounce(function() {
        const searchValue = this.value.trim();
        
        if (searchValue.length >= 2) {
            performSearch(searchValue);
        } else if (searchValue.length === 0) {
            // If search is cleared, refresh the current view instead of reloading
            const currentAffiliation = schoolFilter.classList.contains('active') ? '1' : 
                                      (divisionFilter.classList.contains('active') ? '2' : '');
            filterByAffiliation(currentAffiliation);
        }
    }, 500)); // 500ms debounce

    // Handle clear button (x) in search input
    searchInput.addEventListener('search', function() {
        // This event is triggered when the search input is cleared
        if (this.value === '') {
            // Refresh the current view instead of reloading
            const currentAffiliation = schoolFilter.classList.contains('active') ? '1' : 
                                      (divisionFilter.classList.contains('active') ? '2' : '');
            filterByAffiliation(currentAffiliation);
        }
    });

    // When clicking the X (clear) button
    const searchContainer = document.querySelector('.search-container');
    if (searchContainer) {
        searchContainer.addEventListener('click', function(e) {
            // Check if the click was on the after pseudo-element (approximated by position)
            const rect = searchContainer.getBoundingClientRect();
            
            // If click is in the right 30px of the container (where the X appears)
            if (searchInput && e.clientX > rect.right - 30 && searchInput.value !== '') {
                searchInput.value = '';
                // Refresh the current view instead of reloading
                const currentAffiliation = schoolFilter.classList.contains('active') ? '1' : 
                                          (divisionFilter.classList.contains('active') ? '2' : '');
                filterByAffiliation(currentAffiliation);
                searchInput.focus();
            }
        });
    }
    
    // Set allPersonnelFilter as active by default and apply filtering
    allPersonnelFilter.classList.add('active');
    filterByAffiliation(''); // Apply "All Personnel" filter on page load
});


    </script>
</body>
</html>

<?php 
$conn->close();
?>