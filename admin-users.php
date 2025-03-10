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

    .content-body hr{
    	border: 1px solid #95A613;
    }

    .personnel{
        display: flex;
    }

    .school{
        width: 30%;
        border-radius: 5px;
        background: #19a155;
        color: white;
        padding: 15px;
        font-size: 18px;
        margin-bottom: 4%;
        margin-right: 3%;
    }

    .division{
        width: 30%;
        border-radius: 5px;
        background: #d7f3e4;
        color: #19a155;
        padding: 15px;
        font-size: 18px;
        margin-bottom: 4%;
    }

    .school p, .division p{
        font-weight: bold;
        font-family: Montserrat;
    }

    .filter-bar {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        background-color: #f5f5f5;
        padding: 8px;
        border-radius: 4px;
        position: relative;
    }
    .filter-icon {
        background-color: #ddd;
        padding: 5px 10px;
        margin-right: 10px;
        border-radius: 4px;
    }

    .filter-icon:hover {
      background-color: #19a155;
      color: white;
    }

    .search-container {
        position: relative;
        flex-grow: 1;
    }
    .search-icon {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
    }
    .search-input {
        padding: 6px 10px 6px 30px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 200px;
    }

    .filter-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      margin-top: 5px;
      background-color: #f5f5f5;
      border-radius: 4px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 180px;
      display: none;
      z-index: 100;
    }
    
    .filter-dropdown.show {
      display: block;
    }
    
    .dropdown-item {
      padding: 10px 15px;
      cursor: pointer;
      transition: background-color 0.2s;
      font-family: Montserrat !important;
    }
    
    .dropdown-item:hover {
      background-color: white;
    }
    
    .dropdown-item:first-child {
      border-radius: 4px 4px 0 0;
    }
    
    .dropdown-item:last-child {
      border-radius: 0 0 4px 4px;
    }

    .positions-dropdown {
      position: absolute;
      top: 100%;
      left: 200px;
      margin-top: 5px;
      background-color: white;
      border-radius: 4px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 180px;
      z-index: 100;
      border: 1px solid black;
    }

    .positions-dropdown .dropdown-item {
      padding: 10px 15px;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .positions-dropdown .dropdown-item:hover {
      background-color: #f0f7ff;
    }

    .bulk-actions {
        opacity: 0;
        visibility: hidden;
        margin-left: auto;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .bulk-actions.visible {
        opacity: 1;
        visibility: visible;
    }
    .delete-selected-btn {
        background-color: #ff4d4d;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-family: Tilt Warp;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .delete-selected-btn:hover {
        background-color: #ff3333;
        transform: scale(1.05);
    }
    .delete-selected-btn:active {
        transform: scale(0.95);
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        background-color: #374ab6;
        color: white;
        text-align: left;
        padding: 15px;
        border: 1px solid #ddd;
        font-weight: bolder;
        font-family: Montserrat;
        font-size: 14px;
    }
    td {
        padding: 8px 10px;
        border: 1px solid #ddd;
        font-family: Montserrat;
        font-weight: medium;
        font-size: 13px;
    }
    tr:nth-child(even) {
        background-color:  rgb(215, 222, 247);
    }
    .checkbox-cell {
        text-align: center;
    }

    .checkbox-cell {
    width: 40px;
    text-align: center;
    }

    tr:hover {
        background-color: #f5f5f5;
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
                <a href="admin-notification.php"><i class="fas fa-bell"></i>Notification</a> 
                <a href="admin-archives.php"><i class="fa fa-archive" aria-hidden="true"></i>Archives</a>
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
                <span class="filter-icon"><i class="fa fa-filter" aria-hidden="true"></i></span>
                <div class="filter-dropdown">
                    <div class="dropdown-item">Teaching</div>
                    <div class="dropdown-item">Non-Teaching</div>
                    <div class="dropdown-item">Positions</div>
                </div>
                <div class="search-container">
                    <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
                    <input type="text" class="search-input" placeholder="Search for users...">
                </div>
                <div class="bulk-actions" id="bulk-actions">
                    <button class="delete-selected-btn" id="delete-selected"><i class="fa fa-trash" aria-hidden="true"></i> Delete Selected</button>
                </div>
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
        const filterIcon = document.querySelector('.filter-icon');
        const dropdown = document.querySelector('.filter-dropdown');
        const searchInput = document.querySelector('.search-input');
        const tableRows = document.querySelectorAll('table tbody tr');

        // Toggle dropdown when filter icon is clicked
        filterIcon.addEventListener('click', function() {
          dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
          if (!filterIcon.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
            // Also remove positions dropdown if it exists
            const positionsDropdown = document.querySelector('.positions-dropdown');
            if (positionsDropdown) {
              positionsDropdown.remove();
            }
          }
        });

        // Handle teaching/non-teaching filter clicks
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
          item.addEventListener('click', function() {
            const filterValue = this.textContent.trim();
            
            if (filterValue === 'Positions') {
              // Create positions dropdown
              createPositionsDropdown();
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
          tableRows.forEach(row => {
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
          positionsDropdown.style.position = 'absolute';
          positionsDropdown.style.top = '100%';
          positionsDropdown.style.left = '200px'; // Position it next to the search input
          positionsDropdown.style.marginTop = '5px';
          positionsDropdown.style.backgroundColor = 'white';
          positionsDropdown.style.borderRadius = '4px';
          positionsDropdown.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
          positionsDropdown.style.width = '180px';
          positionsDropdown.style.zIndex = '100';
          positionsDropdown.style.border = '1px solid black';
          
          // Add positions to dropdown
          positions.forEach(position => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.textContent = position;
            item.style.padding = '10px 15px';
            item.style.cursor = 'pointer';
            
            item.addEventListener('click', function() {
              filterTableByPosition(position);
              positionsDropdown.remove();
              dropdown.classList.remove('show');
            });
            
            positionsDropdown.appendChild(item);
          });
          
          // Add "All Positions" option
          const allItem = document.createElement('div');
          allItem.className = 'dropdown-item';
          allItem.textContent = 'All Positions';
          allItem.style.padding = '10px 15px';
          allItem.style.cursor = 'pointer';
          allItem.style.fontWeight = 'bold';
          
          allItem.addEventListener('click', function() {
            resetTableFilter();
            positionsDropdown.remove();
            dropdown.classList.remove('show');
          });
          
          positionsDropdown.insertBefore(allItem, positionsDropdown.firstChild);
          
          // Add dropdown to page
          document.querySelector('.filter-bar').appendChild(positionsDropdown);
        }

        // Function to filter table by classification (Teaching/Non-Teaching)
        function filterTableByClassification(classification) {
          tableRows.forEach(row => {
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
          tableRows.forEach(row => {
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
          tableRows.forEach(row => {
            row.style.display = '';
          });
        }

        // Handle search input
        searchInput.addEventListener('input', function() {
          const searchValue = this.value.toLowerCase();
          
          tableRows.forEach(row => {
            let found = false;
            
            // Search through all cells in the row (except the first checkbox cell)
            for (let i = 1; i < row.cells.length; i++) {
              const cellValue = row.cells[i].textContent.toLowerCase();
              if (cellValue.includes(searchValue)) {
                found = true;
                break;
              }
            }
            
            if (found) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          });
        });
</script>        

</body>
</html>

<?php 
$conn->close();
?>