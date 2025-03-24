<?php 
require_once 'config.php';

// Fetch users from database
$sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name, u.suffix, u.sex, 
        u.contact_no, u.email, c.name as classification_name, cp.name as position_name 
        FROM users u 
        LEFT JOIN users_lnd ul ON u.id = ul.user_id
        LEFT JOIN class_position cp ON ul.position_id = cp.id 
        LEFT JOIN classification c ON ul.classification_id = c.id 
        WHERE u.deleted_at IS NULL 
        ORDER BY u.id";
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
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <link href="styles/admin-user.css" rel="stylesheet">
	<title>Users</title>
</head>
<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
        
            <div class="menu">
                <a href="admin-dashboard.php"><i class="fas fa-home"></i>Home</a>
                <a href="admin-events.php"><i class="fas fa-calendar-alt"></i>Events</a>
                <a href="admin-users.php" class="active"><i class="fas fa-users"></i>Users</a>
                <a href="admin-notif.php"><i class="fas fa-bell"></i>Notification</a> 
            </div>
        </div>

    <div class="content">
    	<div class="content-header">
	    	<img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
	    	<p>Learning and Development</p>
	    	<h1>EVENT MANAGEMENT SYSTEM</h1>
    	</div><br><br><br><br><br>

    	<div class="content-body">
	    	<h1>Users</h1>
	    	<hr><br>

        <div class="personnel">

                <?php
                if ($schoolResult) {
                    $row = $schoolResult->fetch_assoc();
                }
                ?>
                <div class="school">
                    <p>School personnel: <?php echo $row['count']; ?></p>
                </div>

                <?php
                if ($divResult) {
                    $row = $divResult->fetch_assoc();
                }
                ?>
                <div class="division">
                    <p>Division personnel: <?php echo $row['count']; ?></p>
                </div>
            </div>
    
            <div class="filter-bar">
            <div class="filter-container">
                <span class="filter-icon"><i class="fa fa-filter" aria-hidden="true"></i></span>
                <span class="filter-text">Filter by</span>
                <div class="filter-dropdown">
                    <div class="dropdown-item" data-filter="Teaching">Teaching</div>
                    <div class="dropdown-item" data-filter="Non-Teaching">Non-Teaching</div>
                    <div class="dropdown-item" data-filter="Positions">Positions</div>
                    <div class="dropdown-item" data-filter="All">Show All</div>
                </div>
            </div>
            <div class="search-container">
                <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
                <input type="text" class="search-input" placeholder="Search for users...">
            </div>
        </div>
        <div class="bulk-actions" id="bulk-actions">
                <button class="delete-selected-btn" id="delete-selected"><i class="fa fa-trash" aria-hidden="true"></i> Delete Selected</button>
            </div>
    
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
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

                            echo "<tr>";
                            echo "<td class='checkbox-cell'><input type='checkbox' class='user-checkbox' data-id='" . $row["id"] . "'></td>";
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
            <script>

// Function to check if any checkboxes are selected
function checkSelectedCheckboxes() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    
    if (checkboxes.length > 0) {
        bulkActions.classList.add('visible');
    } else {
        bulkActions.classList.remove('visible');
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
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this user?')) {
                const userId = this.getAttribute('data-id');
                deleteUser(userId, this.closest('tr'));
            }
        });
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
            // Remove row from table
            row.remove();
            alert('User deleted successfully.');
        } else {
            alert('Error deleting user: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while trying to delete the user.');
    });
}

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
                // Remove rows from table
                selectedCheckboxes.forEach(checkbox => {
                    checkbox.closest('tr').remove();
                });
                
                // Hide bulk actions button
                document.getElementById('bulk-actions').classList.remove('visible');
                
                // Uncheck select all
                document.getElementById('select-all').checked = false;
                
                alert('Selected users have been deleted successfully.');
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

// Store the current active filter
let currentFilter = 'All';
let currentPosition = '';

// Call setupDeleteButtons when the page loads
document.addEventListener('DOMContentLoaded', function() {
    checkSelectedCheckboxes(); // Check if any checkboxes are already selected
    setupDeleteButtons(); // Set up delete buttons
});

// Get elements
const filterContainer = document.querySelector('.filter-container');
const filterIcon = document.querySelector('.filter-icon');
const filterText = document.querySelector('.filter-text');
const dropdown = document.querySelector('.filter-dropdown');
const searchInput = document.querySelector('.search-input');
const tableBody = document.querySelector('table tbody');

// Toggle dropdown when filter container is clicked
filterContainer.addEventListener('click', function() {
    dropdown.classList.toggle('show');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!filterContainer.contains(event.target)) {
        dropdown.classList.remove('show');
        // Also remove positions dropdown if it exists
        const positionsDropdown = document.querySelector('.positions-dropdown');
        if (positionsDropdown) {
            positionsDropdown.remove();
        }
    }
});

// Handle filter item clicks
const dropdownItems = document.querySelectorAll('.dropdown-item');
dropdownItems.forEach(item => {
    item.addEventListener('click', function() {
        // Remove active class from all items
        dropdownItems.forEach(i => i.classList.remove('active'));
        
        // Add active class to clicked item
        this.classList.add('active');
        
        const filterValue = this.getAttribute('data-filter');
        currentFilter = filterValue;
        
        // Update filter text
        filterText.textContent = filterValue;
        
        if (filterValue === 'Positions') {
            // Create positions dropdown
            createPositionsDropdown();
        } else if (filterValue === 'All') {
            // Show all rows
            currentPosition = '';
            
            // For "Show All" we need to clear the search and refresh the page
            // This is the most reliable way to reset everything
            searchInput.value = '';
            // We use setTimeout to ensure the UI updates before reload
            setTimeout(() => {
                window.location.reload();
            }, 100);
            
            dropdown.classList.remove('show');
        } else {
            // Filter table by classification (Teaching/Non-Teaching)
            currentPosition = '';
            
            if (searchInput.value.trim() !== '') {
                // If there's a search term, fetch results with the new filter
                performSearch(searchInput.value.trim());
            } else {
                // If no search term, just apply the filter
                filterTableByClassification(filterValue);
            }
            
            dropdown.classList.remove('show');
        }
    });
});

// Function to create positions dropdown
function createPositionsDropdown() {
    // Remove existing positions dropdown if it exists
    const existingDropdown = document.querySelector('.positions-dropdown');
    if (existingDropdown) {
        existingDropdown.remove();
        return;
    }
    
    // Get unique positions from the table
    const positions = [];
    document.querySelectorAll('table tbody tr').forEach(row => {
        const positionCell = row.cells[6]; // Position is in the 7th column (index 6)
        if (positionCell) {
            const position = positionCell.textContent.trim();
            if (position !== "Not Assigned" && !positions.includes(position) && position !== "") {
                positions.push(position);
            }
        }
    });
    
    // Create dropdown element
    const positionsDropdown = document.createElement('div');
    positionsDropdown.className = 'positions-dropdown';
    
    // Add positions to dropdown
    positions.forEach(position => {
        const item = document.createElement('div');
        item.className = 'dropdown-item';
        item.textContent = position;
        
        item.addEventListener('click', function() {
            currentFilter = 'Positions';
            currentPosition = position;
            
            if (searchInput.value.trim() !== '') {
                // If there's a search term, fetch results with the new filter
                performSearch(searchInput.value.trim());
            } else {
                // If no search term, just apply the filter
                filterTableByPosition(position);
            }
            
            filterText.textContent = position; // Update filter text
            positionsDropdown.remove();
            dropdown.classList.remove('show');
        });
        
        positionsDropdown.appendChild(item);
    });
    
    // Add "All Positions" option
    const allItem = document.createElement('div');
    allItem.className = 'dropdown-item';
    allItem.textContent = 'All Positions';
    allItem.style.fontWeight = 'bold';
    
    allItem.addEventListener('click', function() {
        currentFilter = 'All';
        currentPosition = '';
        
        // For "All Positions" we'll clear the search and reload
        searchInput.value = '';
        // We use setTimeout to ensure the UI updates before reload
        setTimeout(() => {
            window.location.reload();
        }, 100);
        
        filterText.textContent = 'All Positions'; // Update filter text
        positionsDropdown.remove();
        dropdown.classList.remove('show');
    });
    
    positionsDropdown.insertBefore(allItem, positionsDropdown.firstChild);
    
    // Add dropdown to page
    document.querySelector('.filter-container').appendChild(positionsDropdown);
    
    // Style for positions dropdown
    const style = document.createElement('style');
    style.textContent = `
        .positions-dropdown {
            position: absolute;
            top: 0;
            left: 100%;
            margin-left: 5px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 180px;
            z-index: 101;
            border: 1px solid #eee;
        }
        
        .positions-dropdown .dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.2s;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .positions-dropdown .dropdown-item:hover {
            background-color: #f5f5f5;
        }
        
        .positions-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }
    `;
    document.head.appendChild(style);
}

// Function to filter table by classification (Teaching/Non-Teaching)
function filterTableByClassification(classification) {
    document.querySelectorAll('table tbody tr').forEach(row => {
        const classificationCell = row.cells[5]; // Classification is in the 6th column (index 5)
        if (classificationCell) {
            const cellValue = classificationCell.textContent.trim().toLowerCase();
            
            // Check if classification contains our filter value (case insensitive)
            if (cellValue === classification.toLowerCase()) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

// Function to filter table by position
function filterTableByPosition(position) {
    document.querySelectorAll('table tbody tr').forEach(row => {
        const positionCell = row.cells[6]; // Position is in the 7th column (index 6)
        if (positionCell) {
            const cellValue = positionCell.textContent.trim();
            
            if (cellValue === position) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

// Function to reset table filter
function resetTableFilter() {
    // Make all rows visible
    document.querySelectorAll('table tbody tr').forEach(row => {
        row.style.display = '';
    });
}

// Function to perform search with current filters
function performSearch(searchValue) {
    // Show loading indicator
    tableBody.innerHTML = '<tr><td colspan="9" style="text-align:center">Searching...</td></tr>';
    
    // Build the search URL with filter parameters
    let searchUrl = 'search_users.php?term=' + encodeURIComponent(searchValue);
    
    // Add filter parameters based on current selection
    if (currentFilter === 'Teaching' || currentFilter === 'Non-Teaching') {
        searchUrl += '&classification=' + encodeURIComponent(currentFilter);
    } else if (currentFilter === 'Positions' && currentPosition) {
        searchUrl += '&position=' + encodeURIComponent(currentPosition);
    }
    
    // Send AJAX request to search users
    fetch(searchUrl)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.data.length > 0) {
                    // Clear table and add new rows
                    tableBody.innerHTML = '';
                    
                    data.data.forEach(user => {
                        const row = document.createElement('tr');
                        
                        // Create checkbox cell
                        const checkboxCell = document.createElement('td');
                        checkboxCell.className = 'checkbox-cell';
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'user-checkbox';
                        checkbox.setAttribute('data-id', user.id);
                        checkbox.addEventListener('change', checkSelectedCheckboxes);
                        checkboxCell.appendChild(checkbox);
                        row.appendChild(checkboxCell);
                        
                        // Add other cells
                        row.innerHTML += `
                            <td>${user.index}</td>
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
                } else {
                    tableBody.innerHTML = '<tr><td colspan="9" style="text-align:center">No users found matching your search criteria</td></tr>';
                }
            } else {
                tableBody.innerHTML = `<tr><td colspan="9" style="text-align:center">Error: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="9" style="text-align:center">Error connecting to the server</td></tr>';
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
        // If search is cleared, reload the page to get fresh data
        window.location.reload();
    }
}, 500)); // 500ms debounce
</script>        

</body>
</html>

<?php 
$conn->close();
?>