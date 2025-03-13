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
	<title>Users</title>
</head>
<style type="text/css">
	* {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body, html {
        height: 100%;
    }

    .sidebar {
        position: fixed;
        width: 250px;
        height: 100vh;
        background-color: #2b3a8f;
        color: #ffffff;
        padding: 2rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .sidebar .logo {
        margin-bottom: 1rem;
        margin-left: 5%;
    }

    hr{
        border: 1px solid white;
    }

    .sidebar .menu {
    	margin-top: 50%;
        display: flex;
        flex-direction: column;
        margin-bottom: 18rem;
    }

    .sidebar .menu a {
        color: #ffffff;
        text-decoration: none;
        padding: 1rem;
        display: flex;
        align-items: center;
        font-size: 1rem;
        border-radius: 5px;
        transition: background 0.3s;
        font-family: Tilt Warp Regular;
        margin-bottom: .5rem;
    }

    .sidebar .menu a:hover, .sidebar .menu a.active {
        background-color: white;
        color: #2b3a8f;
    }

    .sidebar .menu a i {
        margin-right: 0.5rem;
    }

    .profile {
        text-decoration: none;
        margin-top: 2%;
        margin-bottom: 7%;
        margin-left: 1.3rem;
        color: white;
        font-family: Tilt Warp;
        font-size: 1rem;
    }

    .profile i{
        font-size: 18px;
        margin-right: 0.5rem;
    }

    .content {
        flex: 1;
        background-color: #ffffff;
        padding: 4rem;
        margin-left: 17%;
    }

    .content-header h1 {
        font-size: 1.5rem;
        color: #333333;
        font-family: Wensley Demo;
        margin-left: 32%;
    }

    .content-header p {
        color: #999;
        font-size: 1rem;
        margin-top: -3%;
        font-family: LT Cushion Light;
        margin-left: 44%;
    }

    .content-header img {
        float: left;
        margin-left: 22%;
        margin-top: -1%;
        filter: drop-shadow(0px 4px 5px rgba(0, 0, 0, 0.3));
    }

    .content-body h1{
    	font-family: Montserrat ExtraBold;
    	font-size: 2rem;
    	padding: 10px;
    }

    .content-body .heading{
      display: flex;
    }

    .content-body hr{
    	border: 1px solid #95A613;
    }

    .personnel {
  display: flex;
  margin-bottom: 2rem;
  gap: 1rem;
  float: right;
}

.school, .division {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  padding: 12px 18px;
  font-size: 15px;
  font-weight: 600;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.school {
  background: linear-gradient(135deg, #19a155, #17935c);
  color: white;
}

.division {
  background: #e6f7ef;
  color: #19a155;
  border: 1px solid #bae6d3;
}

.school p, .division p {
  margin: 0;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  display: flex;
  align-items: center;
}

.school p:before, .division p:before {
  font-family: 'Font Awesome 5 Free';
  margin-right: 8px;
  font-size: 16px;
}

.school p:before {
  content: "\f19d"; /* School/Graduation Cap Icon */
}

.division p:before {
  content: "\f0b1"; /* Division/Briefcase Icon */
}

.filter-bar {
  display: flex;
  align-items: center;
  margin-bottom: 2rem;
  margin-right: 2%; 
  gap: 15px;
}

/* Enhanced Filter Container */
.filter-container {
  position: relative;
  min-width: 150px;
  height: 42px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background-color: white;
  display: flex;
  align-items: center;
  padding: 0 15px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.filter-container:hover {
  border-color: #cbd5e0;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
}

.filter-container:active {
  background-color: #f8fafc;
}

/* Filter Icon */
.filter-icon {
  color: #4a5568;
  margin-right: 8px;
  font-size: 14px;
  display: flex;
  align-items: center;
}

/* Filter Text */
.filter-text {
  font-size: 14px;
  font-weight: 500;
  font-family: 'Montserrat', sans-serif;
  color: #2d3748;
}

/* Add a down arrow indicator */
.filter-container::after {
  content: "\f107";
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
  font-size: 14px;
  color: #718096;
  margin-left: auto;
  transition: transform 0.2s ease;
}

.filter-container.active::after {
  transform: rotate(180deg);
}

/* Improved Filter Dropdown */
.filter-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 200px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  z-index: 100;
  border: 1px solid #e2e8f0;
  display: none;
  animation: dropdownFade 0.2s ease;
}

@keyframes dropdownFade {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.filter-dropdown.show {
  display: block;
}

/* Dropdown Items */
.dropdown-item {
  padding: 12px 16px;
  font-size: 14px;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: 'Montserrat', sans-serif;
  border-bottom: 1px solid #f1f5f9;
}

.dropdown-item:last-child {
  border-bottom: none;
}

.dropdown-item:hover {
  background-color: #f8fafc;
  color: #2b3a8f;
}

.dropdown-item.active {
  background-color: #edf2ff;
  color: #2b3a8f;
  font-weight: 600;
}
.search-container {
  position: relative;
  flex-grow: 1;
  max-width: fit-content;
}

/* Search Input */
.search-input {
  width: 100%;
  height: 42px;
  padding: 0 45px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Montserrat', sans-serif;
  color: #2d3748;
  background-color: white;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.search-input:focus {
  outline: none;
  border-color: #2b3a8f;
  box-shadow: 0 0 0 3px rgba(43, 58, 143, 0.1);
}

.search-input::placeholder {
  color: #a0aec0;
  font-weight: 400;
}

/* Search Icon */
.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #a0aec0;
  font-size: 14px;
}

/* Add clear button for search */
.search-container::after {
  content: "\f00d";
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #cbd5e0;
  font-size: 12px;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.search-container:has(.search-input:not(:placeholder-shown))::after {
  opacity: 1;
}

/* Position dropdown styling */
.positions-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: calc(100% + 8px);
  width: 200px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 101;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  animation: dropdownFade 0.2s ease;
}

.positions-dropdown .dropdown-item {
  padding: 12px 16px;
  font-size: 14px;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: 'Montserrat', sans-serif;
  border-bottom: 1px solid #f1f5f9;
}

.positions-dropdown .dropdown-item:last-child {
  border-bottom: none;
}

.positions-dropdown .dropdown-item:hover {
  background-color: #f8fafc;
  color: #2b3a8f;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-container {
    max-width: 100%;
    margin-top: 10px;
  }
  
  .filter-dropdown, .positions-dropdown {
    width: 100%;
    left: 0;
  }
  
  .positions-dropdown {
    top: 0;
    left: 0;
  }
}
    .bulk-actions {
      opacity: 0;
      visibility: hidden;
      margin-left: auto;
      margin-bottom: 1.5rem;
      transition: all 0.3s ease;
      transform: translateY(10px);
    }

    .bulk-actions.visible {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
    .delete-selected-btn {
      background-color: #ff3b30;
      color: white;
      border: none;
      padding: 10px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      font-family: 'Montserrat', sans-serif;
      box-shadow: 0 2px 4px rgba(255, 59, 48, 0.2);
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
    }

    .delete-selected-btn i {
      margin-right: 8px;
    }

    .delete-selected-btn:hover {
      background-color: #ff2d21;
      box-shadow: 0 4px 8px rgba(255, 59, 48, 0.3);
      transform: translateY(-2px);
    }

    .delete-selected-btn:active {
      transform: translateY(1px);
      box-shadow: 0 1px 2px rgba(255, 59, 48, 0.2);
    }

    /* Individual Delete Buttons in Table */
    .delete-btn {
      background-color: #fff5f5;
      color: #ff3b30;
      border: 1px solid #ffe5e5;
      padding: 6px 12px;
      border-radius: 4px;
      font-weight: 600;
      transition: all 0.2s ease;
    }

    .delete-btn:hover {
      background-color: #ffebeb;
      color: #ff2d21;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    th {
        background-color: #2b3a8f;
        color: white;
        text-align: left;
        padding: 16px;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    th:first-child {
        border-top-left-radius: 10px;
        padding-left: 20px;
    }

    th:last-child {
        border-top-right-radius: 10px;
        padding-right: 20px;
    }

    td {
        padding: 15px 16px;
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        border-bottom: 1px solid #edf2f7;
        color:rgb(1, 8, 20);
        transition: background-color 0.2s ease;
    }

    td:first-child {
        padding-left: 20px;
    }

    td:last-child {
        padding-right: 20px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:nth-child(even) {
        background-color:rgb(242, 248, 254);
    }

    tr:hover td {
        background-color: #edf2ff;
    }

    /* Enhance checkbox styling */
    .checkbox-cell {
        width: 40px;
        text-align: center;
    }

    input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e0;
        border-radius: 4px;
        outline: none;
        cursor: pointer;
        position: relative;
        vertical-align: middle;
        transition: all 0.2s ease;
    }

    input[type="checkbox"]:checked {
        background-color: #2b3a8f;
        border-color: #2b3a8f;
    }

    input[type="checkbox"]:checked::after {
        content: '✓';
        position: absolute;
        color: white;
        font-size: 12px;
        font-weight: bold;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Scrollable table for responsive design */
    .table-container {
        max-height: 600px;
        overflow-y: auto;
        border-radius: 10px;
        margin-top: 20px;
        background: #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    /* Status indicators (for future use) */
    .status-active {
        background-color: #d7f3e4;
        color: #19a155;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .status-inactive {
        background-color: #fee2e2;
        color: #e53e3e;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    /* Action buttons styling */
    .action-btn {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        margin-right: 5px;
    }

    .edit-btn {
        background-color: #ebf5ff;
        color: #3182ce;
    }

    .edit-btn:hover {
        background-color: #bee3f8;
    }

    .delete-btn {
        background-color: #fff5f5;
        color: #e53e3e;
    }

    .delete-btn:hover {
        background-color: #fed7d7;
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #a0aec0;
        font-style: italic;
    }

    /* Pagination styling (for future use) */
    .pagination {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
        align-items: center;
    }

    .pagination-btn {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        background-color: white;
        border-radius: 4px;
        margin: 0 5px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pagination-btn:hover {
        background-color: #f7fafc;
    }

    .pagination-btn.active {
        background-color: #2b3a8f;
        color: white;
        border-color: #2b3a8f;
    }

    .pagination-text {
        color: #718096;
        font-size: 14px;
        margin: 0 10px;
    }
</style>
<body>

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
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this user?')) {
            const userId = this.getAttribute('data-id');
            // Send AJAX request to delete user
            fetch('delete_user.php?id=' + userId, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from table
                    this.closest('tr').remove();
                } else {
                    alert('Error deleting user: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
});

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
        });
    }
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
    
    // Update filter text
    filterText.textContent = filterValue;
    
    if (filterValue === 'Positions') {
      // Create positions dropdown
      createPositionsDropdown();
    } else if (filterValue === 'All') {
      // Show all rows
      resetTableFilter();
      dropdown.classList.remove('show');
    } else {
      // Filter table by classification (Teaching/Non-Teaching)
      filterTableByClassification(filterValue);
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
      filterTableByPosition(position);
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
    resetTableFilter();
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
  document.querySelectorAll('table tbody tr').forEach(row => {
    row.style.display = '';
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
    // Show loading indicator
    tableBody.innerHTML = '<tr><td colspan="9" style="text-align:center">Searching...</td></tr>';
    
    // Send AJAX request to search users
    fetch('search_users.php?term=' + encodeURIComponent(searchValue))
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
  } else if (searchValue.length === 0) {
    // If search is cleared, reload the original table
    window.location.reload();
  }
}, 500)); // 500ms debounce
</script>        

</body>
</html>

<?php 
$conn->close();
?>