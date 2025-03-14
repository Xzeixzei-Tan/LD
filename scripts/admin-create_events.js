// Function to toggle venue field visibility based on delivery mode
function toggleVenueFieldVisibility() {
    const deliverySelect = document.getElementById('event-mode');
    const venueField = document.getElementById('venue-field');

    if (deliverySelect.value === 'online') {
        venueField.style.display = 'none';
        // Clear the venue field when it's hidden
        document.querySelector('input[name="venue"]').removeAttribute('required');
    } else {
        venueField.style.display = 'block';
        // Make the venue field required when it's visible
        document.querySelector('input[name="venue"]').setAttribute('required', '');
    }
}

// Function to toggle meal plan field visibility based on delivery mode
function toggleMealPlanFieldVisibility() {
    const deliverySelect = document.getElementById('event-mode');
    const mealPlanSection = document.getElementById('meal-plan-section');

    if (deliverySelect.value === 'online') {
        mealPlanSection.style.display = 'none';
    } else {
        mealPlanSection.style.display = 'block';
    }
}

// Function to toggle amount field visibility
function toggleAmountField(source) {
    const amountField = document.getElementById(source + '-amount');
    const checkbox = document.querySelector(`input[name="funding_source[]"][value="${source}"]`);
    if (checkbox.checked) {
        amountField.style.display = 'block';
    } else {
        amountField.style.display = 'none';
    }
}

// Function to add speaker field
function addSpeakerField() {
    const speakersContainer = document.getElementById('speakers-container');
    const speakerInputGroup = document.createElement('div');
    speakerInputGroup.className = 'speaker-input-group';

    const speakerInput = document.createElement('input');
    speakerInput.type = 'text';
    speakerInput.name = 'speaker[]';
    speakerInput.placeholder = 'Enter speaker/resource person';

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'remove-speaker-btn';
    removeButton.innerHTML = '<i class="fas fa-minus"></i>';
    removeButton.onclick = function () {
        speakersContainer.removeChild(speakerInputGroup);
    };

    speakerInputGroup.appendChild(speakerInput);
    speakerInputGroup.appendChild(removeButton);
    speakersContainer.appendChild(speakerInputGroup);
}

// Function to calculate date range and create time inputs and meal plan options
function calculateDateRange() {
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const dateRangeContainer = document.getElementById('date-range-container');
    const mealPlanContainer = document.getElementById('meal-plan-container');

    // Create container for the same-time checkbox if it doesn't exist
    let sameTimeCheckboxContainer = document.getElementById('same-time-checkbox-container');
    if (!sameTimeCheckboxContainer) {
        sameTimeCheckboxContainer = document.createElement('div');
        sameTimeCheckboxContainer.id = 'same-time-checkbox-container';
        dateRangeContainer.parentNode.insertBefore(sameTimeCheckboxContainer, dateRangeContainer.nextSibling);
    }

    if (startDateInput && endDateInput && dateRangeContainer) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDate && endDate && startDate <= endDate) {
            dateRangeContainer.innerHTML = ''; // Clear previous content
            mealPlanContainer.innerHTML = ''; // Clear previous meal plan content

            let currentDate = new Date(startDate);
            let dayCount = 1;

            while (currentDate <= endDate) {
                const dateItem = document.createElement('div');
                dateItem.className = 'date-item';
                dateItem.innerHTML = `<strong>Day ${dayCount} - ${currentDate.toDateString()}</strong>`;

                const timeContainer = document.createElement('div');
                timeContainer.className = 'time-container';

                const startTimeLabel = document.createElement('label');
                startTimeLabel.textContent = 'Start Time: ';
                const startTimeInput = document.createElement('input');
                startTimeInput.type = 'time';
                startTimeInput.name = `start-time-day-${dayCount}`;
                startTimeInput.className = 'time-input';
                startTimeLabel.appendChild(startTimeInput);

                const endTimeLabel = document.createElement('label');
                endTimeLabel.textContent = 'End Time: ';
                const endTimeInput = document.createElement('input');
                endTimeInput.type = 'time';
                endTimeInput.name = `end-time-day-${dayCount}`;
                endTimeInput.className = 'time-input';
                endTimeLabel.appendChild(endTimeInput);

                timeContainer.appendChild(startTimeLabel);
                timeContainer.appendChild(endTimeLabel);
                dateItem.appendChild(timeContainer);
                dateRangeContainer.appendChild(dateItem);

                // Create meal plan fields
                const mealPlanItem = document.createElement('div');
                mealPlanItem.className = 'meal-day';
                mealPlanItem.innerHTML = `<h4>Day ${dayCount} - ${currentDate.toDateString()}</h4>`;

                const mealTypes = ['breakfast', 'am-snack', 'lunch', 'pm-snack', 'dinner'];
                mealTypes.forEach(mealType => {
                    const mealLabel = document.createElement('label');

                    const mealCheckbox = document.createElement('input');
                    mealCheckbox.type = 'checkbox';
                    mealCheckbox.name = `meal-${mealType}-day-${dayCount}`;
                    mealCheckbox.value = 'on';  // Changed from '1' to 'on' to match the PHP expected value

                    mealLabel.appendChild(mealCheckbox);
                    mealLabel.appendChild(document.createTextNode(` ${mealType.replace('-', ' ').toUpperCase()}`));

                    mealPlanItem.appendChild(mealLabel);
                    mealPlanItem.appendChild(document.createElement('br'));
                });

                mealPlanContainer.appendChild(mealPlanItem);

                currentDate.setDate(currentDate.getDate() + 1);
                dayCount++;
            }

            // Show the "set the same time for other days" checkbox if we have multiple days
            if (dayCount > 2) {
                sameTimeCheckboxContainer.innerHTML = '';  // Clear previous checkbox if any

                const sameTimeCheckbox = document.createElement('input');
                sameTimeCheckbox.type = 'checkbox';
                sameTimeCheckbox.id = 'same-time-checkbox';
                sameTimeCheckbox.name = 'same_time_for_others';

                const label = document.createElement('label');
                label.setAttribute('for', 'same-time-checkbox');
                label.textContent = 'Set the same time for all days';

                sameTimeCheckboxContainer.appendChild(sameTimeCheckbox);
                sameTimeCheckboxContainer.appendChild(label);

                // Add event listener to the checkbox
                sameTimeCheckbox.addEventListener('change', function () {
                    const day1StartTime = document.querySelector('input[name="start-time-day-1"]').value;
                    const day1EndTime = document.querySelector('input[name="end-time-day-1"]').value;

                    if (this.checked && day1StartTime && day1EndTime) {
                        for (let i = 2; i < dayCount; i++) {
                            document.querySelector(`input[name="start-time-day-${i}"]`).value = day1StartTime;
                            document.querySelector(`input[name="end-time-day-${i}"]`).value = day1EndTime;
                        }
                    }
                });
            } else {
                sameTimeCheckboxContainer.innerHTML = '';
            }

            // After generating the meal plan, check if we should show it
            toggleMealPlanFieldVisibility();
        }
    }
}

// Function to generate event days - alias for calculateDateRange for backward compatibility
// Function to generate day fields based on start and end dates
function generateDayFields() {
    const startDate = new Date(document.getElementById('start-date').value);
    const endDate = new Date(document.getElementById('end-date').value);

    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        return;
    }

    const dayDiff = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
    const eventDaysContainer = document.getElementById('event-days-container');
    const mealPlanContainer = document.getElementById('meal-plan-container');

    eventDaysContainer.innerHTML = '';
    mealPlanContainer.innerHTML = '';

    if (dayDiff <= 0) {
        alert('End date should be after start date');
        return;
    }

    for (let i = 0; i < dayDiff; i++) {
        const currentDate = new Date(startDate);
        currentDate.setDate(startDate.getDate() + i);
        const dateString = currentDate.toISOString().split('T')[0];
        const dayNumber = i + 1;

        // Create event day field
        const dayDiv = document.createElement('div');
        dayDiv.className = 'event-day';
        dayDiv.innerHTML = `
            <h4>Day ${dayNumber} - ${dateString}</h4>
            <input type="hidden" name="event_days[${dayNumber}][date]" value="${dateString}">
            <div class="time-inputs">
                <div>
                    <label>Start Time:</label>
                    <input type="time" name="event_days[${dayNumber}][start_time]" value="08:00">
                </div>
                <div>
                    <label>End Time:</label>
                    <input type="time" name="event_days[${dayNumber}][end_time]" value="17:00">
                </div>
            </div>
        `;
        eventDaysContainer.appendChild(dayDiv);

        // Create meal plan field for this day
        const mealDiv = document.createElement('div');
        mealDiv.className = 'meal-day';
        mealDiv.innerHTML = `
            <h4>Meals for Day ${dayNumber} - ${dateString}</h4>
            <div class="checkbox-subgroup">
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="Breakfast"> Breakfast</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="AM Snack"> AM Snack</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="Lunch"> Lunch</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="PM Snack"> PM Snack</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="Dinner"> Dinner</label>
            </div>
        `;
        mealPlanContainer.appendChild(mealDiv);
    }
}

// Function to validate date range
function validateDateRange() {
    const startDate = new Date(document.getElementById('start-date').value);
    const endDate = new Date(document.getElementById('end-date').value);

    if (startDate > endDate) {
        alert('End date must be after start date');
        document.getElementById('end-date').value = document.getElementById('start-date').value;
    }

    // Update date range and meal plan after validating dates
    calculateDateRange();
}

// Functions to toggle personnel display
function togglePersonnelFields() {
    const target = document.getElementById('target-personnel').value;
    const schoolPersonnel = document.getElementById('school-personnel');
    const divisionPersonnel = document.getElementById('division-personnel');

    // Hide all first
    schoolPersonnel.style.display = 'none';
    divisionPersonnel.style.display = 'none';

    // Show based on selection
    if (target === 'School' || target === 'Both') {
        schoolPersonnel.style.display = 'block';
    }

    if (target === 'Division' || target === 'Both') {
        divisionPersonnel.style.display = 'block';
    }
}

// Function to select all division checkboxes
function selectAllDivision() {
    const selectAllCheckbox = document.getElementById('select-all-division');
    const divisionCheckboxes = document.querySelectorAll('.division-checkbox');

    divisionCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Function to update "Select All" checkbox state based on individual checkboxes
function updateSelectAllDivision() {
    const selectAllCheckbox = document.getElementById('select-all-division');
    const checkboxes = document.querySelectorAll('.division-checkbox');
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    selectAllCheckbox.checked = allChecked;
}

// Call the functions on page load to set the initial state
document.addEventListener('DOMContentLoaded', function () {
    // Initialize toggle functions
    const eventModeSelect = document.getElementById('event-mode');

    // Set initial event mode value handling
    if (eventModeSelect.value === '') {
        // If no value is selected, both sections should be visible until a specific option is selected
        document.getElementById('venue-field').style.display = 'block';
        document.getElementById('meal-plan-section').style.display = 'none'; // Hide meal plan initially
    } else {
        // Apply toggles based on current selection
        toggleVenueFieldVisibility();
        toggleMealPlanFieldVisibility();
    }

    togglePersonnelFields();
    calculateDateRange();

    // Add event listener for delivery/event-mode select change
    eventModeSelect.addEventListener('change', function () {
        toggleVenueFieldVisibility();
        toggleMealPlanFieldVisibility();
        calculateDateRange();
    });

    // Add event listeners for date fields
    document.getElementById('start-date').addEventListener('change', validateDateRange);
    document.getElementById('end-date').addEventListener('change', validateDateRange);

    // Add event listener for target personnel dropdown
    document.getElementById('target-personnel').addEventListener('change', togglePersonnelFields);

    // Add event listeners to division checkboxes
    const divisionCheckboxes = document.querySelectorAll('.division-checkbox');
    divisionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllDivision);
    });

    // Initialize form validation
    const form = document.getElementById('create-event-form');
    form.addEventListener('submit', function (event) {
        const startDate = new Date(document.getElementById('start-date').value);
        const endDate = new Date(document.getElementById('end-date').value);

        if (startDate > endDate) {
            alert('End date must be after start date');
            event.preventDefault();
            return false;
        }

        const targetPersonnel = document.getElementById('target-personnel').value;

        // Also fix these capitalization issues in your validation
        if (targetPersonnel === 'School' || targetPersonnel === 'Both') {
            const schoolChecked = document.querySelectorAll('input[name="school_level[]"]:checked, input[name="type[]"]:checked, input[name="specialization[]"]:checked').length > 0;
            if (!schoolChecked) {
                alert('Please select at least one school personnel option.');
                event.preventDefault();
                return false;
            }
        }

        if (targetPersonnel === 'Division' || targetPersonnel === 'Both') {
            const divisionChecked = document.querySelectorAll('input[name="department[]"]:checked').length > 0;
            if (!divisionChecked) {
                alert('Please select at least one division department option.');
                event.preventDefault();
                return false;
            }
        }

        return true;
    });
});