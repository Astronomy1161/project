document.addEventListener("DOMContentLoaded", function () {
    const movieTitleDisplay = document.getElementById("movieTitle");
    const selectedSeatsDisplay = document.getElementById("selectedSeats");
    const confirmButton = document.getElementById("confirmButton");
    const timeSelect = document.getElementById("time");

    // Get query parameters from URL
    const movieTitle = getQueryParam("movie");
    const selectedSeats = getQueryParam("seats").split(","); // Convert string to an array

    // Set movie title and selected seats in the UI
    movieTitleDisplay.innerText = movieTitle;
    selectedSeatsDisplay.innerText = selectedSeats.join(", ");

    // Populate time options starting from 6:00 AM with 3-hour increments
    const startHour = 6; // Start time is 6:00 AM
    const endHour = 24; // End by midnight
    const timeOptions = generateTimeOptions(startHour, endHour);

    // Add options to the select dropdown
    timeOptions.forEach(time => {
        const option = document.createElement("option");
        option.value = time;
        option.innerText = time;
        timeSelect.appendChild(option);
    });

    // Enable the confirm button when both date and time are selected
    timeSelect.addEventListener("change", toggleConfirmButton);
    const dateInput = document.getElementById("date");
    dateInput.addEventListener("change", toggleConfirmButton);

    function toggleConfirmButton() {
        const isDateSelected = dateInput.value !== "";
        const isTimeSelected = timeSelect.value !== "";
        confirmButton.disabled = !(isDateSelected && isTimeSelected);
    }

    // Function to generate time options (6:00 AM, 9:00 AM, etc.) with AM/PM
    function generateTimeOptions(startHour, endHour) {
        const options = [];
        for (let hour = startHour; hour < endHour; hour += 3) {
            let hour12 = hour % 12 || 12; // Convert to 12-hour format
            let period = hour < 12 ? "AM" : "PM"; // Determine AM or PM
            let formattedHour = hour12 < 10 ? `0${hour12}` : hour12; // Add leading zero if hour is single digit
            options.push(`${formattedHour}:00 ${period}`);
        }
        return options;
    }

    // Function to get query parameter from URL
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Function to confirm booking and redirect
    function confirmDetails() {
        const date = dateInput.value;
        const time = timeSelect.value;

        if (date && time) {
            // Redirect to confirming.html with all details
            window.location.href = `confirming.html?movie=${encodeURIComponent(movieTitle)}&seats=${encodeURIComponent(selectedSeats.join(", "))}&date=${date}&time=${time}`;
        }
    }

    // Attach the confirmDetails function to the confirm button
    confirmButton.addEventListener('click', confirmDetails);
});
