<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
	<title>Create Events</title>
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

    .content {
        flex: 1;
        background-color: #ffffff;
        padding: 4rem;
        margin-left: 250px;
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
    	font-family: Montserrat Extrabold;
    	font-size: 2rem;
    	padding: 10px;
    }

    .content-body hr{
    	border: 1px solid #95A613;
    }
    
    .form-container {
        background-color: #f9f9f9;    
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .form-container h3 {
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        color: #95A613;
        margin-bottom: 1.5rem;
        font-family:   Montserrat ExtraBold;
    }
    
    .form-container form {
        display: flex;
        flex-direction: column;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        font-family: Montserrat;
        display: block;
        font-weight: bold;
        color: #4f5663;
        margin-bottom: 0.5rem;
    }
    
    .form-group p {
        margin-bottom: 0.5rem;
        color: #555;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="datetime-local"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        margin-bottom: 1rem;
        font-family: Montserrat Light;
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .radio-group {
        margin: auto;
        margin-left: 5%;
    }

    .special {
        margin-left: 5%;
    }
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 2rem;
        flex: 1;
    }
    
    .radio-group label,
    .checkbox-group label {
        display: flex;
        align-items: center;
        margin-right: 0;
        margin-bottom: 0.5rem;
        font-weight: normal;
        min-width: 100px;
    }
    
    .radio-group input,
    .checkbox-group input[type="checkbox"] {
    margin-right: 0.5rem;
}

/* Responsive adjustments for the meal plan */
@media (max-width: 768px) {
    .meal-day {
        flex-direction: column;
    }
    
    .meal-day span {
        margin-bottom: 0.5rem;
    }
    
    .checkbox-group {
        margin-left: 0;
    }
}

/* For the date indicator */
.date-indicator {
    font-size: 0.85rem;
    color: #4b5563;
    font-style: italic;
    margin-left: 4px;
}
    
    
    .form-col {
        flex: 1;
        min-width: 250px;
        margin-bottom: auto;
        margin-right: 1rem;
    }

    .form-elig {
        display: flex;
        margin: auto;
        margin-left: 5;
    }

    .meal-plan {
    margin: auto;
    background-color: rgb(215, 222, 247);
    padding: 1rem;
    border-radius: 0.25rem;
    margin-top: 0.5rem;
}

.meal-day {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
}

.meal-day:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.meal-day span {
    font-weight: bold;
    margin-right: 1rem;
    min-width: 120px;
    display: flex;
    align-items: center;
}

    
    .submit-btn {
        text-align: center;
        margin-top: 1.5rem;
    }
    
    .submit-btn button {
        background-color: #12753E;
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        font-weight: bold;
        font-size: 1rem;
        transition: background-color 0.3s;
    }
    
    .submit-btn button:hover {
        background-color: #15803d;
    }
    
    .section-title {
        font-family: Montserrat ExtraBold;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .content-header h1 {
            margin-left: 20%;
        }
        
        .content-header p {
            margin-left: 30%;
        }
    }
    
    @media (max-width: 768px) {
        .sidebar {
            width: 200px;
        }
        
        .content {
            margin-left: 200px;
            padding: 1.5rem;
        }
        
        .content-header img {
            margin-left: 10%;
        }
        
        .content-header h1 {
            margin-left: 15%;
        }
        
        .content-header p {
            margin-left: 25%;
        }
        
        .form-row {
            flex-direction: column;
        }
        
        .form-col {
            margin: auto;
            width: 100%;
            margin-left: 5%;
        }
    }
    
    @media (max-width: 576px) {
        .sidebar {
            width: 70px;
            padding: 1rem 0.5rem;
        }
        
        .sidebar .menu a span {
            display: none;
        }
        
        .content {
            margin-left: 70px;
            padding: 1rem;
        }
        
        .content-header {
            text-align: center;
        }
        
        .content-header img {
            float: none;
            margin: 0 auto 1rem;
            display: block;
        }
        
        .content-header h1,
        .content-header p {
            margin-left: 0;
        }
    }

    .certificate-template.dragover {
        border-color: #16a34a;
        background-color: #f0fdf4;
    }
    
    .file-preview {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        background-color: #f0f9ff;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    
    .file-preview img {
        max-width: 50px;
        max-height: 50px;
        margin-right: 1rem;
    }

    .dynamic-dates {
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .add-date-btn {
        background-color: #2b3a8f;
        color: white;
        border: none;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        cursor: pointer;
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(43, 58, 143, 0.2);
        width: 100%;
    }

    .add-date-btn:hover {
        background-color: #1e2a6f;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(43, 58, 143, 0.3);
    }

    .add-date-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(43, 58, 143, 0.3);
    }

    .add-date-btn:active {
        transform: translateY(1px);
        box-shadow: 0 1px 2px rgba(43, 58, 143, 0.2);
    }

    .add-date-btn i {
        font-size: 1rem;
    }

    .remove-date-btn {
        background-color: #dc2626;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .remove-date-btn:hover {
        background-color: #b91c1c;
        transform: scale(1.1);
    }

    .remove-date-btn i {
        font-size: 0.75rem;
    }
    
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    /* New styles for the speaker input group */
    .speaker-input-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .add-speaker-btn {
        background-color: #2b3a8f;
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 1rem;
        margin-top: -4%;
        align-self: flex-end;
    }

    .add-speaker-btn:hover {
        background-color: #1e2a6f;
        transform: scale(1.1);
    }

    .add-speaker-btn i {
        margin-bottom: 2%;
        font-size: 0.875rem;
    }

    /* Date indicator for meal plan days */
    .date-indicator {
        font-size: 0.85rem;
        color: #4b5563;
        font-style: italic;
        margin-left: 4px;
    }

    .personnel-selection {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .personnel-btn {
            flex: 1;
            padding: 1rem;
            border: 2px solid #2b3a8f;
            background-color: white;
            color: #2b3a8f;
            border-radius: 0.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .personnel-btn.active {
            background-color: #2b3a8f;
            color: white;
        }
        
        .personnel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(43, 58, 143, 0.2);
        }
        
        .personnel-btn i {
            font-size: 1.2rem;
        }
        
        /* Hidden sections */
        .school-personnel-sections {
            display: block;
        }
        
        .hidden {
            display: none;
        }

        .division-personnel-section {
            display: block;

        }
        .school-personnel-sections, 
.division-personnel-section {
    display: none;
}
</style>
<body>
	<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="menu">
                <a href="admin-dashboard.php"><i class="fas fa-home mr-3"></i>Home</a>
                <a href="admin-events.php" class="active" ><i class="fas fa-calendar-alt mr-3"></i>Events</a>
                <a href="admin-users.php"><i class="fas fa-users mr-3"></i>Users</a>
                <a href="admin-notif.php"><i class="fas fa-bell mr-3"></i>Notification</a> 
            </div>
        </div>

        <div class="content">
            <div class="content-header">
                <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
                <p>Learning and Development</p>
                <h1>EVENT MANAGEMENT SYSTEM</h1>
            </div><br><br><br><br><br>

            <div class="content-body">
                <h1>Events</h1>
                <hr><br><br>

                <div class="form-container">
                    <h3>CREATE AN EVENT</h3>
                    <form>
                        <!-- Basic Event Details -->
                        <div class="form-group">
                            <div class="section-title">Basic Event Details</div>
                            
                            <label>Event Title:</label>
                            <input type="text" placeholder="Enter event title">
                            
                            <label>Specification of Event:</label>
                            <select>
                                <option value="">Select event specification</option>
                                <option value="training">Training</option>
                                <option value="activity">Activity</option>
                            </select>
                            
                            <label>Event Mode:</label>
                            <select>
                                <option value=""disable selected>Select event mode</option>
                                <option value="face-to-face">Face-to-face</option>
                                <option value="online">Online</option>
                                <option value="hybrid-blended">Hybrid/Blended</option>
                            </select>

                            <label>Funding Source:</label>
                            <select>
                                <option value="">Choose funding source</option>
                                <option value="mooe">Maintenance and Other Operating Expenses (MOOE)</option>
                                <option value="sef">Special Education Fund (SEF)</option>
                                <option value="psf">Program Support Fund (PSF)</option>
                            </select>
                            
                            <div class="form-row">
                                <div class="form-col" id="start-dates-container">
                                    <label>Start Date</label>
                                    <div class="dynamic-dates">
                                        <input type="datetime-local" name="start_date[]" class="start-date-input">
                                    </div>
                                    <button type="button" class="add-date-btn" onclick="addStartDateField()">
                                        <i class="fas fa-plus"></i> Add Another Date
                                    </button>
                                </div>
                                
                                <div class="form-col" id="end-dates-container">
                                    <label>End Date/Time:</label>
                                    <div class="dynamic-dates">
                                        <input type="datetime-local" name="end_date[]" class="end-date-input">
                                    </div>
                                    <button type="button" class="add-date-btn" onclick="addEndDateField()">
                                        <i class="fas fa-plus"></i> Add Another Date
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="section-title">Meal Plan</div>
                            <div class="meal-plan">
                                <!-- Meal plan days will be dynamically populated here -->
                            </div>
                        </div>
                            
                        <script>
                            // Track the number of days in the meal plan
                            let mealPlanDays = 1;
                            // Store date information for each day
                            let eventDates = [];

                            // Helper function to format date for display
                            function formatDateForDisplay(dateString) {
                                if (!dateString) return '';
                                const date = new Date(dateString);
                                return date.toLocaleDateString('en-US', { 
                                    weekday: 'short',
                                    month: 'short', 
                                    day: 'numeric',
                                    year: 'numeric'
                                });
                            }

                            // Function to add a start date field
                            function addStartDateField() {
                                const container = document.getElementById('start-dates-container');
                                const newDateDiv = document.createElement('div');
                                newDateDiv.classList.add('dynamic-dates');
                                
                                const newDateInput = document.createElement('input');
                                newDateInput.type = 'datetime-local';
                                newDateInput.name = 'start_date[]';
                                newDateInput.classList.add('start-date-input');
                                newDateInput.addEventListener('change', updateMealPlanDates);
                                
                                const removeButton = document.createElement('button');
                                removeButton.type = 'button';
                                removeButton.classList.add('remove-date-btn');
                                removeButton.innerHTML = '<i class="fas fa-times"></i>';
                                removeButton.onclick = function() {
                                    container.removeChild(newDateDiv);
                                    // When removing a date, update meal plan
                                    updateMealPlanDates();
                                };
                                
                                newDateDiv.appendChild(newDateInput);
                                newDateDiv.appendChild(removeButton);
                                container.insertBefore(newDateDiv, container.lastElementChild);
                                
                                // Update meal plan
                                updateMealPlanDates();
                            }
                            
                            // Function to add an end date field
                            function addEndDateField() {
                                const container = document.getElementById('end-dates-container');
                                const newDateDiv = document.createElement('div');
                                newDateDiv.classList.add('dynamic-dates');
                                
                                const newDateInput = document.createElement('input');
                                newDateInput.type = 'datetime-local';
                                newDateInput.name = 'end_date[]';
                                newDateInput.classList.add('end-date-input');
                                
                                const removeButton = document.createElement('button');
                                removeButton.type = 'button';
                                removeButton.classList.add('remove-date-btn');
                                removeButton.innerHTML = '<i class="fas fa-times"></i>';
                                removeButton.onclick = function() {
                                    container.removeChild(newDateDiv);
                                };
                                
                                newDateDiv.appendChild(newDateInput);
                                newDateDiv.appendChild(removeButton);
                                container.insertBefore(newDateDiv, container.lastElementChild);
                            }

                            // Function to collect all start dates
                            function collectStartDates() {
                                const startDateInputs = document.querySelectorAll('.start-date-input');
                                const dates = [];
                                
                                startDateInputs.forEach((input) => {
                                    if (input.value) {
                                        // Extract just the date part for meal planning
                                        const dateObj = new Date(input.value);
                                        const dateStr = dateObj.toISOString().split('T')[0];
                                        dates.push({
                                            fullDate: input.value,
                                            dateOnly: dateStr,
                                            formatted: formatDateForDisplay(input.value)
                                        });
                                    }
                                });
                                
                                // Sort dates chronologically
                                dates.sort((a, b) => new Date(a.fullDate) - new Date(b.fullDate));
                                return dates;
                            }

                            // Function to update meal plan dates based on the start dates
                            function updateMealPlanDates() {
                                const dates = collectStartDates();
                                eventDates = dates;
                                
                                // Get unique dates (in case multiple sessions on same day)
                                const uniqueDates = [];
                                const uniqueDateStrings = new Set();
                                
                                dates.forEach(date => {
                                    if (!uniqueDateStrings.has(date.dateOnly)) {
                                        uniqueDateStrings.add(date.dateOnly);
                                        uniqueDates.push(date);
                                    }
                                });
                                
                                // Update meal plan
                                updateMealPlanDays(uniqueDates);
                            }

                            // Function to update meal plan days based on the unique dates
                            function updateMealPlanDays(uniqueDates) {
                                const numberOfDates = uniqueDates.length;
                                const mealPlanContainer = document.querySelector('.meal-plan');
                                
                                // Clear existing meal plan
                                mealPlanContainer.innerHTML = '';
                                
                                // Check if there are any dates
                                if (numberOfDates === 0) {
                                    // Display a message when no dates are selected
                                    const noDateMessage = document.createElement('div');
                                    noDateMessage.classList.add('no-date-message');
                                    noDateMessage.innerHTML = `
                                        <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                            <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 1rem; color: #9ca3af;"></i>
                                            <p style="font-style: italic;">No event dates have been selected.</p>
                                            <p>Please add at least one start date to configure the meal plan.</p>
                                        </div>
                                    `;
                                    mealPlanContainer.appendChild(noDateMessage);
                                } else {
                                    // Add meal plan days based on the number of dates
                                    for (let i = 0; i < numberOfDates; i++) {
                                        const dayNumber = i + 1;
                                        const dateInfo = uniqueDates[i] || { formatted: '' };
                                        const mealDay = document.createElement('div');
                                        mealDay.classList.add('meal-day');
                                        
                                        mealDay.innerHTML = `
                                            <span>Day ${dayNumber}: <span class="date-indicator">${dateInfo.formatted}</span></span>
                                            <div class="checkbox-group">
                                                <label><input type="checkbox" name="meal${dayNumber}" value="breakfast"> Breakfast</label>
                                                <label><input type="checkbox" name="meal${dayNumber}" value="am-snack"> AM Snack</label>
                                                <label><input type="checkbox" name="meal${dayNumber}" value="lunch"> Lunch</label>
                                                <label><input type="checkbox" name="meal${dayNumber}" value="pm-snack"> PM Snack</label>
                                                <label><input type="checkbox" name="meal${dayNumber}" value="dinner"> Dinner</label>
                                            </div>
                                        `;
                                        
                                        mealPlanContainer.appendChild(mealDay);
                                    }
                                }
                                
                                // Update the tracked number of days
                                mealPlanDays = numberOfDates;
                            }

                            // Add event listeners to start date inputs for date changes
                            function addStartDateListeners() {
                                const startDateInputs = document.querySelectorAll('.start-date-input');
                                startDateInputs.forEach(input => {
                                    input.addEventListener('change', updateMealPlanDates);
                                });
                            }

                            // Initialize the form when the page loads
                            document.addEventListener('DOMContentLoaded', function() {
                                // Set up event listeners for date changes
                                const startDatesContainer = document.getElementById('start-dates-container');
                                const addStartDateButton = startDatesContainer.querySelector('.add-date-btn');
                                
                                // Initial date listeners
                                addStartDateListeners();
                                
                                // Replace the original onclick with our enhanced version
                                addStartDateButton.onclick = addStartDateField;
                                
                                // Initialize the meal plan - this will show the "no dates" message initially
                                updateMealPlanDates();
                            });
                        </script>

                        <!-- Organizers & Trainers -->
                        <div class="form-group">
                            <div class="section-title">Organizers & Trainers</div>
                            
                            <label>Proponets:</label>
                            <input type="text" placeholder="Enter organizer name">
                            
                            <div id="speakers-container">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <label>Speaker/Resource Person:</label>
                                    
                                </div>
                                <div class="speaker-input-group">
                                    <input type="text" name="speaker[]" placeholder="Enter speaker/resource person">
                                    <button type="button" class="add-speaker-btn" onclick="addSpeakerField()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <script>
                                function addSpeakerField() {
                                    const container = document.getElementById('speakers-container');
                                    const newSpeakerDiv = document.createElement('div');
                                    newSpeakerDiv.classList.add('speaker-input-group');
                                    
                                    const newSpeakerInput = document.createElement('input');
                                    newSpeakerInput.type = 'text';
                                    newSpeakerInput.name = 'speaker[]';
                                    newSpeakerInput.placeholder = 'Enter speaker/resource person';
                                    
                                    const removeButton = document.createElement('button');
                                    removeButton.type = 'button';
                                    removeButton.classList.add('remove-date-btn');
                                    removeButton.innerHTML = '<i class="fas fa-times"></i>';
                                    removeButton.onclick = function() {
                                        container.removeChild(newSpeakerDiv);
                                    };
                                    
                                    newSpeakerDiv.appendChild(newSpeakerInput);
                                    newSpeakerDiv.appendChild(removeButton);
                                    container.appendChild(newSpeakerDiv);
                                }
                            </script>
                        </div>

                        <div class="form-group">
                                <div class="section-title">Target Personnel</div>
                                <div class="personnel-selection">
                                    <button type="button" id="school-btn" class="personnel-btn active">
                                     <i class="fas fa-school"></i> School Personnel
                            </button>
                                     <button type="button" id="division-btn" class="personnel-btn">
                                    <i class="fas fa-building"></i> Division Personnel
                            </button>
                                    <button type="button" id="all-btn" class="personnel-btn">
                                     <i class="fas fa-users"></i> All Personnel
                            </button>
            </div>
                            </div>

                        

                     <!-- Eligible Participants -->
<div class="form-group">
<div class="form-group school-personnel-sections" id="eligible-participants">
    <div class="section-title">Eligible Participants</div>
    
    <div class="form-elig">
        <div class="checkbox-group">
            <div class="form-row">
                <div class="form-col">
                    <label style="font-weight: bold;">Educational Level:</label>
                    
                    <label><input type="checkbox" name="level" value="elementary"> Elementary</label>
                    <label><input type="checkbox" name="level" value="junior"> Junior High School</label>
                    <label><input type="checkbox" name="level" value="senior"> Senior High School</label>
                
                </div>
            </div>
        </div>
    
        <div class="checkbox-group">
            <div class="form-row">
                <div class="form-col">
                    <label style="font-weight: bold;">Participants:</label>
                    <label><input type="checkbox" name="participants" value="teaching"> Teaching</label>
                    <label><input type="checkbox" name="participants" value="non-teaching"> Non-teaching</label>
                
                </div>
            </div>
        </div>
    </div>
<br>

    <hr>
    <br><br>
    

    <div class="form-group school-personnel-sections" id="specialization">
    <div class="special"></div>
    <div class="section-title">Specialization</div>
    
    <div class="checkbox-group">
        <div class="form-col">
            <label><input type="checkbox" name="specialization" value="mtb-mle"> Mother Tongue-Based Multilingual Education (MTB-MLE)</label>
            <label><input type="checkbox" name="specialization" value="filipino"> Filipino</label>
            <label><input type="checkbox" name="specialization" value="english"> English</label>
            <label><input type="checkbox" name="specialization" value="mathematics"> Mathematics</label>
            <label><input type="checkbox" name="specialization" value="science"> Science</label>
            <label><input type="checkbox" name="specialization" value="tle"> Technology and Livelihood Education</label>
        </div>
        <div class="form-col">
            <label><input type="checkbox" name="specialization" value="esp"> Edukasyon sa Pagpapakatao (EsP)</label>
            <label><input type="checkbox" name="specialization" value="mapeh"> Music, Arts, PE & Health (MAPEH)</label>
            <label><input type="checkbox" name="specialization" value="epp"> Edukasyong Pantahanan at Pangkabuhayan</label>
            <label><input type="checkbox" name="specialization" value="sped"> Special Education</label>
            <label><input type="checkbox" name="specialization" value="non-teaching"> N/A (for non-teaching)</label>
        </div>
    </div>
</div>
    </div>

<hr>

<div class="form-group division-personnel-section" id="sectors-units"><br>
<div class="section-title">Sectors/Units</div>
<div class="checkbox-group">
    <div class="form-col">
        <label><input type="checkbox" name="sector" value="sds">Office of the Schools Division Superintendent</label>
        <label><input type="checkbox" name="sector" value="asds">Office of the Assistant Schools Division Superintendent</label>
        <label><input type="checkbox" name="sector" value="legal-services">Legal Services</label>
        <label><input type="checkbox" name="sector" value="ict-services"> ICT Services</label>
        <label><input type="checkbox" name="sector" value="administrative"> Administrative Services</label>
        <label><input type="checkbox" name="sector" value="personnel">Personnel</label>
        <label><input type="checkbox" name="sector" value="records">Records</label>
        <label><input type="checkbox" name="sector" value="cashier">Cashier</label>
        <label><input type="checkbox" name="sector" value="suply-property"> Supply & Property</label>
        <label><input type="checkbox" name="sector" value="budget"> Budget</label>
        <label><input type="checkbox" name="sector" value="finance">Finance</label>
    </div>
    <div class="form-col">
        <label><input type="checkbox" name="sector" value="cid">Curriculum Implementation Division</label>
        <label><input type="checkbox" name="sector" value="lrm">Learning Resource Management</label>
        <label><input type="checkbox" name="sector" value="cim"> Curriculum Implementation Management</label>
        <label><input type="checkbox" name="sector" value="dim">District Instructional Management</label>
        <label><input type="checkbox" name="sector" value="als">Alternative Learning System</label>
        <label><input type="checkbox" name="sector" value="sgod">School Governance and Operations Division</label>
        <label><input type="checkbox" name="sector" value="smme">School Management Monitoring & Evaluation</label>
        <label><input type="checkbox" name="sector" value="hrd">Human Resource Development</label>
        <label><input type="checkbox" name="sector" value="smn">Social Mobilization and Networking</label>
        <label><input type="checkbox" name="sector" value="pr"> Planning & Research</label>
        <label><input type="checkbox" name="sector" value="educ-facilities">Education Facilities</label>
        <label><input type="checkbox" name="sector" value="sh-nu">School Health & Nutrition Unit</label>
    </div>
</div>
</div>
        <div class="submit-btn">
            <button type="submit">Create Event</button>
        </div>
        </form>
                        
                   
        
<script>
function selectPersonnel(type) {
    // Get the buttons
    const schoolBtn = document.getElementById('school-btn');
    const divisionBtn = document.getElementById('division-btn');
    const allBtn = document.getElementById('all-btn');
    
    // Get all the sections we need to toggle
    const schoolSections = document.querySelectorAll('.school-personnel-sections');
    const divisionSection = document.querySelector('.division-personnel-section');
    
    // Reset all buttons first
    schoolBtn.classList.remove('active');
    divisionBtn.classList.remove('active');
    allBtn.classList.remove('active');
    
    if (type === 'school') {
        // Update button state
        schoolBtn.classList.add('active');
        
        // Show school sections
        schoolSections.forEach(section => {
            section.style.display = 'block';
        });
        
        // Hide division section
        if (divisionSection) {
            divisionSection.style.display = 'none';
        }
    } else if (type === 'division') {
        // Update button state
        divisionBtn.classList.add('active');
        
        // Hide school sections
        schoolSections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Show division section
        if (divisionSection) {
            divisionSection.style.display = 'block';
        }
    } else if (type === 'all') {
        // Update button state
        allBtn.classList.add('active');
        
        // Show all sections
        schoolSections.forEach(section => {
            section.style.display = 'block';
        });
        
        if (divisionSection) {
            divisionSection.style.display = 'block';
        }
    } else {
        // No selection - hide all sections
        schoolSections.forEach(section => {
            section.style.display = 'none';
        });
        
        if (divisionSection) {
            divisionSection.style.display = 'none';
        }
    }
}

// Make sure this runs after the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for the buttons
    const schoolBtn = document.getElementById('school-btn');
    const divisionBtn = document.getElementById('division-btn');
    const allBtn = document.getElementById('all-btn');
    
    // Get all the sections we need to initially hide
    const schoolSections = document.querySelectorAll('.school-personnel-sections');
    const divisionSection = document.querySelector('.division-personnel-section');
    
    // Initially hide all sections
    schoolSections.forEach(section => {
        section.style.display = 'none';
    });
    
    if (divisionSection) {
        divisionSection.style.display = 'none';
    }
    
    // Remove default active class from school button
    if (schoolBtn) {
        schoolBtn.classList.remove('active');
    }
    
    if (schoolBtn) {
        schoolBtn.addEventListener('click', function() {
            selectPersonnel('school');
        });
    }
    
    if (divisionBtn) {
        divisionBtn.addEventListener('click', function() {
            selectPersonnel('division');
        });
    }
    
    if (allBtn) {
        allBtn.addEventListener('click', function() {
            selectPersonnel('all');
        });
    }
    
    // No need to call selectPersonnel('school') here since we want sections hidden by default
});
    </script>

                       

                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>