// booking.js

// bookedSeats will be injected inline in the HTML page before this script loads

function openSeatMap() {
    let date = document.querySelector('input[name="booking_date"]').value;
    let time = document.querySelector('select[name="booking_time"]').value;

    if (!date || !time) {
        alert("Please select a date and time first.");
        return;
    }

    // Enable all seats first
    document.querySelectorAll('.seat').forEach(btn => {
        btn.disabled = false;
        btn.style.backgroundColor = '';
    });

    // Disable booked seats for selected date/time
    bookedSeats.forEach(seatNum => {
        let seatButton = document.querySelector(`button.seat[data-seat="${seatNum}"]`);
        if (seatButton) {
            seatButton.disabled = true;
            seatButton.style.backgroundColor = '#ccc';
        }
    });

    document.getElementById("seatMapModal").style.display = "block";
}

function closeSeatMap() {
    document.getElementById("seatMapModal").style.display = "none";
}

function selectSeat(seatNumber) {
    let seatButton = document.querySelector(`button.seat[data-seat="${seatNumber}"]`);
    if (seatButton && seatButton.disabled) {
        alert("This seat is already booked. Please choose another.");
        return;
    }
    document.getElementById("selectedSeat").value = seatNumber;
    closeSeatMap();
}

const ticketPrice = parseFloat(document.querySelector('meta[name="ticket-price"]').getAttribute("content"));
const discountRate = parseFloat(document.querySelector('meta[name="discount-rate"]').getAttribute("content"));
const ticketsInput = document.getElementById("tickets");
const totalPriceElem = document.getElementById("totalPrice");

function updateTotalPrice() {
    const tickets = parseInt(ticketsInput.value) || 1;
    const total = ticketPrice * tickets;
    const discount = total * discountRate;
    const finalPrice = total - discount;
    totalPriceElem.textContent = finalPrice.toFixed(2);
}

if (ticketsInput) {
    ticketsInput.addEventListener("input", updateTotalPrice);
    updateTotalPrice();
}
