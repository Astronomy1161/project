function openSeatMap() {
    document.getElementById("seatMapModal").style.display = "block";
}

function closeSeatMap() {
    document.getElementById("seatMapModal").style.display = "none";
}

function selectSeat(seatNumber) {
    document.getElementById("selectedSeat").value = seatNumber;
    closeSeatMap();
}

// Pricing calculation
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
