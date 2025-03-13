document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('start-date-input');
    const endDateInput = document.getElementById('end-date-input');

    startDateInput.addEventListener('change', calculateDateRange);
    endDateInput.addEventListener('change', calculateDateRange);

    function toggleVenueField() {
        const eventMode = document.getElementById('event-mode').value;
        const venueField = document.getElementById('venue-field');
        if (eventMode === 'face-to-face') {
            venueField.style.display = 'block';
        } else {
            venueField.style.display = 'none';
        }
    }

    function toggleAmountField(source) {
        const amountField = document.getElementById(`${source}-amount`);
        const otherField = document.getElementById('other-specify');
        const otherAmount = document.getElementById('other-amount');

        if (source === 'other') {
            if (document.querySelector(`#other`).checked) {
                otherField.style.display = 'block';
                otherAmount.style.display = 'block';
            } else {
                otherField.style.display = 'none';
                otherAmount.style.display = 'none';
            }
        } else {
            if (document.querySelector(`[value=${source}]`).checked) {
                amountField.style.display = 'block';
            } else {
                amountField.style.display = 'none';
            }
        }
    }

    function calculateDateRange() {
        const startDate = document.getElementById('start-date-input').value;
        const endDate = document.getElementById('end-date-input').value;
        const dateRangeContainer = document.getElementById('date-range-container');

        if (startDate && endDate) {
            // Your date range calculation logic here
        }
    }

    function addSpeakerField() {
        const speakerContainer = document.getElementById('speakers-container');
        const newSpeakerInput = document.createElement('div');
        newSpeakerInput.innerHTML = `
            <input type="text" name="speaker[]" placeholder="Enter speaker/resource person">
            <button type="button" class="remove-speaker-btn" onclick="removeSpeakerField(this)">
                <i class="fas fa-minus"></i>
            </button>`;
        speakerContainer.appendChild(newSpeakerInput);
    }

    function removeSpeakerField(button) {
        button.parentElement.remove();
    }

    function showSchoolPersonnel() {
        document.getElementById('eligible-participants').style.display = 'block';
        document.getElementById('sectors-units').style.display = 'none';
        document.getElementById('division-specialization').style.display = 'none';

        document.getElementById('school-btn').classList.add('active');
        document.getElementById('division-btn').classList.remove('active');
        document.getElementById('all-btn').classList.remove('active');
    }

    function showDivisionPersonnel() {
        document.getElementById('eligible-participants').style.display = 'none';
        document.getElementById('sectors-units').style.display = 'block';
        document.getElementById('division-specialization').style.display = 'block';

        document.getElementById('school-btn').classList.remove('active');
        document.getElementById('division-btn').classList.add('active');
        document.getElementById('all-btn').classList.remove('active');
    }

    function showAllPersonnel() {
        document.getElementById('eligible-participants').style.display = 'block';
        document.getElementById('sectors-units').style.display = 'block';
        document.getElementById('division-specialization').style.display = 'block';

        document.getElementById('school-btn').classList.remove('active');
        document.getElementById('division-btn').classList.remove('active');
        document.getElementById('all-btn').classList.add('active');
    }

    function clearSchoolSelections() {
        const schoolCheckboxes = document.querySelectorAll('.school-participants input[type="checkbox"]');
        schoolCheckboxes.forEach(checkbox => checkbox.checked = false);
    }

    function clearDivisionSelections() {
        const divisionCheckboxes = document.querySelectorAll('.division-participants input[type="checkbox"]');
        divisionCheckboxes.forEach(checkbox => checkbox.checked = false);
    }

    // Initialize with school personnel fields visible
    showSchoolPersonnel();

    // Attach event listeners
    document.getElementById('event-mode').addEventListener('change', toggleVenueField);
    document.querySelectorAll('input[name="funding_source[]"]').forEach(input => {
        input.addEventListener('change', () => toggleAmountField(input.value));
    });
    document.querySelector('.add-speaker-btn').addEventListener('click', addSpeakerField);
    document.getElementById('start-date-input').addEventListener('change', calculateDateRange);
    document.getElementById('end-date-input').addEventListener('change', calculateDateRange);
    document.getElementById('school-btn').addEventListener('click', showSchoolPersonnel);
    document.getElementById('division-btn').addEventListener('click', showDivisionPersonnel);
    document.getElementById('all-btn').addEventListener('click', showAllPersonnel);

    const selectAllCheckbox = document.getElementById('select-all-sectors');
    selectAllCheckbox.addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('#sectors-units input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (checkbox !== selectAllCheckbox) {
                checkbox.checked = selectAllCheckbox.checked;
            }
        });
    });

    const schoolBtn = document.getElementById('school-btn');
    const divisionBtn = document.getElementById('division-btn');
    const allBtn = document.getElementById('all-btn');
    const schoolParticipants = document.querySelector('.school-participants');
    const divisionParticipants = document.querySelector('.division-participants');

    schoolBtn.addEventListener('click', function () {
        schoolParticipants.style.display = 'block';
        divisionParticipants.style.display = 'none';
        clearDivisionSelections();
    });

    divisionBtn.addEventListener('click', function () {
        schoolParticipants.style.display = 'none';
        divisionParticipants.style.display = 'block';
        clearSchoolSelections();
    });

    allBtn.addEventListener('click', function () {
        schoolParticipants.style.display = 'block';
        divisionParticipants.style.display = 'block';
    });

    function clearSchoolSelections() {
        const schoolCheckboxes = schoolParticipants.querySelectorAll('input[type="checkbox"]');
        schoolCheckboxes.forEach(checkbox => checkbox.checked = false);
    }

    function clearDivisionSelections() {
        const divisionCheckboxes = divisionParticipants.querySelectorAll('input[type="checkbox"]');
        divisionCheckboxes.forEach(checkbox => checkbox.checked = false);
    }
});

function toggleVenueField() {
    var eventMode = document.getElementById("event-mode").value;
    var venueField = document.getElementById("venue-field");

    if (eventMode === "face-to-face" || eventMode === "hybrid-blended") {
        venueField.style.display = "block";
    } else {
        venueField.style.display = "none";
    }
}

function toggleAmountField(fundingSource) {
    var amountField = document.getElementById(fundingSource + '-amount');
    var specifyField = document.getElementById('other-specify');
    var checkbox = document.querySelector('input[name="funding_source[]"][value="' + fundingSource + '"]');
    if (checkbox.checked) {
        amountField.style.display = 'block';
        if (fundingSource === 'other') {
            specifyField.style.display = 'block';
        }
    } else {
        amountField.style.display = 'none';
        if (fundingSource === 'other') {
            specifyField.style.display = 'none';
        }
    }
}

function calculateDateRange() {
    const startDateInput = document.getElementById('start-date-input');
    const endDateInput = document.getElementById('end-date-input');
    const dateRangeContainer = document.getElementById('date-range-container');
    const sameTimeCheckboxContainer = document.getElementById('same-time-checkbox-container');

    // Clear previous date range columns
    dateRangeContainer.innerHTML = '';

    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);

    if (startDate && endDate && startDate <= endDate) {
        let currentDate = new Date(startDate);

        let dayCount = 1;
        const uniqueDates = [];
        while (currentDate <= endDate) {
            const dayColumn = document.createElement('div');
            dayColumn.className = 'day-column';

            const dayLabel = document.createElement('label');
            dayLabel.textContent = `Day ${dayCount} - ${currentDate.toDateString()}`;
            dayColumn.appendChild(dayLabel);

            const startTimeInput = document.createElement('input');
            startTimeInput.type = 'time';
            startTimeInput.name = `start_time_day_${dayCount}`;
            startTimeInput.placeholder = 'Start Time';
            dayColumn.appendChild(startTimeInput);

            const endTimeInput = document.createElement('input');
            endTimeInput.type = 'time';
            endTimeInput.name = `end_time_day_${dayCount}`;
            endTimeInput.placeholder = 'End Time';
            dayColumn.appendChild(endTimeInput);

            dateRangeContainer.appendChild(dayColumn);

            uniqueDates.push({
                fullDate: currentDate.toISOString(),
                formatted: currentDate.toDateString()
            });

            // Move to the next day
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
                const day1StartTime = document.querySelector('input[name="start_time_day_1"]').value;
                const day1EndTime = document.querySelector('input[name="end_time_day_1"]').value;

                if (this.checked && day1StartTime && day1EndTime) {
                    for (let i = 2; i < dayCount; i++) {
                        document.querySelector(`input[name="start_time_day_${i}"]`).value = day1StartTime;
                        document.querySelector(`input[name="end_time_day_${i}"]`).value = day1EndTime;
                    }
                } else {
                    for (let i = 2; i < dayCount; i++) {
                        document.querySelector(`input[name="start_time_day_${i}"]`).value = '';
                        document.querySelector(`input[name="end_time_day_${i}"]`).value = '';
                    }
                }
            });
        }

        // Update meal plan based on the unique dates
        updateMealPlanDays(uniqueDates);
    }
}

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
        <div class="checkbox-group meal-options">
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
} document.addEventListener('DOMContentLoaded', function () { document.getElementById('school-btn').addEventListener('click', showSchoolPersonnel); document.getElementById('division-btn').addEventListener('click', showDivisionPersonnel); document.getElementById('all-btn').addEventListener('click', showAllPersonnel); }); function showSchoolPersonnel() { document.getElementById('eligible-participants').style.display = 'block'; document.getElementById('sectors-units').style.display = 'none'; document.getElementById('division-specialization').style.display = 'none'; document.getElementById('school-btn').classList.add('active'); document.getElementById('division-btn').classList.remove('active'); document.getElementById('all-btn').classList.remove('active'); } function showDivisionPersonnel() { document.getElementById('eligible-participants').style.display = 'none'; document.getElementById('sectors-units').style.display = 'block'; document.getElementById('division-specialization').style.display = 'block'; document.getElementById('school-btn').classList.remove('active'); document.getElementById('division-btn').classList.add('active'); document.getElementById('all-btn').classList.remove('active'); }
function showAllPersonnel() {
    document.getElementById('eligible-participants').style.display = 'block';
    document.getElementById('sectors-units').style.display = 'block';
    document.getElementById('division-specialization').style.display = 'block';

    document.getElementById('school-btn').classList.remove('active');
    document.getElementById('division-btn').classList.remove('active');
    document.getElementById('all-btn').classList.add('active');
}
