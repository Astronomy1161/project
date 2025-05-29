document.addEventListener('DOMContentLoaded', function () {
    const movieTitleDisplay = document.getElementById("movieTitle");
    const selectedSeatsDisplay = document.getElementById("selectedSeats");
    const selectedDateDisplay = document.getElementById("selectedDate");
    const selectedTimeDisplay = document.getElementById("selectedTime");

    // Get data from the URL parameters
    const movieTitle = getQueryParam("movie");
    const selectedSeats = getQueryParam("seats");
    const selectedDate = getQueryParam("date");
    const selectedTime = getQueryParam("time");

    // Populate the ticket details
    movieTitleDisplay.innerText = movieTitle;
    selectedSeatsDisplay.innerText = selectedSeats;
    selectedDateDisplay.innerText = selectedDate;
    selectedTimeDisplay.innerText = selectedTime;

    // Function to get query parameter from URL
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }
});
