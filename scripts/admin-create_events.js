function toggleVenueFieldVisibility() {
    const deliverySelect = document.getElementById('event-mode');
    const venueField = document.getElementById('venue-field');
    if (deliverySelect.value === 'online') {
        venueField.style.display = 'none';
    } else {
        venueField.style.display = 'block';
    }
}

function toggleMealPlanFieldVisibility() {
    const deliverySelect = document.getElementById('event-mode');
    const mealPlanField = document.getElementById('meal-plan-field');
    if (deliverySelect.value === 'online') {
        mealPlanField.style.display = 'none';
    } else {
        mealPlanField.style.display = 'block';
    }
}

function toggleAmountField(funding) {
    let amountField = document.getElementById(funding + "-amount");
    if (document.querySelector(`input[value='${funding}']`).checked) {
        amountField.style.display = "block";
    } else {
        amountField.style.display = "none";
    }
}

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

function calculateDateRange() {
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const dateRangeContainer = document.getElementById('date-range-container');
    const sameTimeCheckboxContainer = document.getElementById('same-time-checkbox-container');
    const mealPlanContainer = document.getElementById('meal-plan-container');

    if (startDateInput && endDateInput && dateRangeContainer) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDate && endDate && startDate <= endDate) {
            dateRangeContainer.innerHTML = ''; // Clear previous content
            mealPlanContainer.innerHTML = ''; // Clear previous meal plan content

            let currentDate = startDate;
            let dayCount = 1;

            while (currentDate <= endDate) {
                const dateItem = document.createElement('div');
                dateItem.className = 'date-item';
                dateItem.innerHTML = `Day ${dayCount} - ${currentDate.toDateString()}`;

                const startTimeInput = document.createElement('input');
                startTimeInput.type = 'time';
                startTimeInput.name = `start-time-day-${dayCount}`;
                startTimeInput.className = 'time-input';

                const endTimeInput = document.createElement('input');
                endTimeInput.type = 'time';
                endTimeInput.name = `end-time-day-${dayCount}`;
                endTimeInput.className = 'time-input';

                dateItem.appendChild(startTimeInput);
                dateItem.appendChild(endTimeInput);
                dateRangeContainer.appendChild(dateItem);

                // Create meal plan fields
                const mealPlanItem = document.createElement('div');
                mealPlanItem.className = 'meal-plan-item';
                mealPlanItem.innerHTML = `<strong>Day ${dayCount} - ${currentDate.toDateString()}</strong>`;

                const mealTypes = ['breakfast', 'am-snack', 'lunch', 'pm-snack', 'dinner'];
                mealTypes.forEach(mealType => {
                    const mealCheckbox = document.createElement('input');
                    mealCheckbox.type = 'checkbox';
                    mealCheckbox.name = `meal-${mealType}-day-${dayCount}`;
                    mealCheckbox.value = mealType;

                    const mealLabel = document.createElement('label');
                    mealLabel.textContent = mealType.replace('-', ' ').toUpperCase();
                    mealLabel.appendChild(mealCheckbox);

                    mealPlanItem.appendChild(mealLabel);
                });

                mealPlanContainer.appendChild(mealPlanItem);

                currentDate.setDate(currentDate.getDate() + 1);
                dayCount++;
            }

            // Show the "set the same time for other days" checkbox after Day 1's time is entered
            if (startDateInput.value && endDateInput.value) {
                const sameTimeCheckbox = document.createElement('input');
                sameTimeCheckbox.type = 'checkbox';
                sameTimeCheckbox.id = 'same-time-checkbox';
                sameTimeCheckbox.name = 'same_time_for_others';
                const label = document.createElement('label');
                label.setAttribute('for', 'same-time-checkbox');
                label.textContent = 'Set the same time for other days';

                // Add checkbox to the container (ensure there's a container for the checkbox)
                sameTimeCheckboxContainer.innerHTML = '';  // Clear previous checkbox if any
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
                    } else {
                        for (let i = 2; i < dayCount; i++) {
                            document.querySelector(`input[name="start-time-day-${i}"]`).value = '';
                            document.querySelector(`input[name="end-time-day-${i}"]`).value = '';
                        }
                    }
                });
            }
        }
    }
}

function showSchoolPersonnel() {
    document.getElementById('school-personnel').style.display = 'block';
    document.getElementById('division-personnel').style.display = 'none';
    document.getElementById('all-personnel').style.display = 'none';
    setActiveButton('school-btn');
}

function showDivisionPersonnel() {
    document.getElementById('school-personnel').style.display = 'none';
    document.getElementById('division-personnel').style.display = 'block';
    document.getElementById('all-personnel').style.display = 'none';
    setActiveButton('division-btn');
}

function showAllPersonnel() {
    document.getElementById('school-personnel').style.display = 'none';
    document.getElementById('division-personnel').style.display = 'none';
    document.getElementById('all-personnel').style.display = 'block';
    setActiveButton('all-btn');
}

function setActiveButton(buttonId) {
    const buttons = document.querySelectorAll('.personnel-btn');
    buttons.forEach(button => {
        button.classList.remove('active');
    });
    document.getElementById(buttonId).classList.add('active');
}

function selectAllDivision() {
    const selectAllCheckbox = document.getElementById('select-all-division');
    const checkboxes = document.querySelectorAll('#division-personnel input[type="checkbox"]:not(#select-all-division)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function updateSelectAllDivision() {
    const selectAllCheckbox = document.getElementById('select-all-division');
    const checkboxes = document.querySelectorAll('#division-personnel input[type="checkbox"]:not(#select-all-division)');
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    selectAllCheckbox.checked = allChecked;
}

function selectAllAll() {
    const selectAllCheckbox = document.getElementById('select-all-all');
    const checkboxes = document.querySelectorAll('#all-personnel input[type="checkbox"]:not(#select-all-all)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function updateSelectAllAll() {
    const selectAllCheckbox = document.getElementById('select-all-all');
    const checkboxes = document.querySelectorAll('#all-personnel input[type="checkbox"]:not(#select-all-all)');
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    selectAllCheckbox.checked = allChecked;
}

// Call the functions on page load to set the initial state
document.addEventListener('DOMContentLoaded', function () {
    toggleVenueFieldVisibility();
    toggleMealPlanFieldVisibility();
    calculateDateRange();
    showSchoolPersonnel(); // Default to showing school personnel

    // Add event listeners to individual checkboxes in the division personnel section
    const divisionCheckboxes = document.querySelectorAll('#division-personnel input[type="checkbox"]:not(#select-all-division)');
    divisionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllDivision);
    });

    // Add event listeners to individual checkboxes in the all personnel section
    const allCheckboxes = document.querySelectorAll('#all-personnel input[type="checkbox"]:not(#select-all-all)');
    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllAll);
    });

    // Add event listener to the delivery mode dropdown
    document.getElementById('event-mode').addEventListener('change', function () {
        toggleVenueFieldVisibility();
        toggleMealPlanFieldVisibility();
    });
});

// Add event listeners to recalculate the date range when the dates change
document.getElementById('start-date').addEventListener('change', calculateDateRange);
document.getElementById('end-date').addEventListener('change', calculateDateRange);
