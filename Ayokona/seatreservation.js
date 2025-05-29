document.addEventListener("DOMContentLoaded", function () {
    const seatGrid = document.getElementById("seatGrid");
    const selectedSeatsDisplay = document.getElementById("selectedSeats");
    const totalPriceDisplay = document.getElementById("totalPrice");
    const bookNowButton = document.getElementById("bookNowButton");

    const ticketPrice = 10; // Price per seat
    const rows = 10; // 10 rows
    const cols = 10; // 10 columns

    // List of occupied seats (could be fetched from a server)
    const occupiedSeats = ["1-3", "2-5", "4-7", "5-2", "7-8", "9-9"]; 

    let selectedSeats = [];

    // Generate seat grid
    for (let row = 1; row <= rows; row++) {
        for (let col = 1; col <= cols; col++) {
            const seatId = `${row}-${col}`;
            const seat = document.createElement("div");
            seat.classList.add("seat");
            seat.innerText = seatId;
            seat.dataset.seatId = seatId;

            // Mark the seat as occupied if it's in the list
            if (occupiedSeats.includes(seatId)) {
                seat.classList.add("occupied");
                seat.title = "Occupied";
            } else {
                seat.addEventListener("click", () => toggleSeatSelection(seat));
            }

            seatGrid.appendChild(seat);
        }
    }

    function toggleSeatSelection(seat) {
        // Prevent selecting occupied seats
        if (seat.classList.contains("occupied")) return;

        // Toggle selected seat state
        if (seat.classList.contains("selected")) {
            seat.classList.remove("selected");
            selectedSeats = selectedSeats.filter(s => s !== seat.dataset.seatId);
        } else {
            seat.classList.add("selected");
            selectedSeats.push(seat.dataset.seatId);
        }

        updateSummary();
    }

    function updateSummary() {
        selectedSeatsDisplay.innerText = selectedSeats.length > 0 ? selectedSeats.join(", ") : "None";
        totalPriceDisplay.innerText = selectedSeats.length * ticketPrice;

        // Enable the "Book Now" button if at least one seat is selected
        bookNowButton.disabled = selectedSeats.length === 0;
    }

    function proceedToPayment() {
        const movieTitle = getQueryParam('movie'); // Get the movie title from URL (if available)
        console.log(`Redirecting with movie: ${movieTitle} and selected seats: ${selectedSeats.join(", ")}`);

        // Check if there are selected seats before redirecting
        if (selectedSeats.length > 0) {
            window.location.href = `time.html?movie=${encodeURIComponent(movieTitle)}&seats=${encodeURIComponent(selectedSeats.join(", "))}`;
        } else {
            alert("Please select at least one seat.");
        }
    }

    // Get query parameter from URL
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Attach the proceedToPayment function to the button
    bookNowButton.addEventListener('click', proceedToPayment);
});
