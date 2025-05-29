function redirectToBooking(title, genre, duration, summary, image) {
  // Encode the movie details and pass them as query parameters to booking.php
  const bookingUrl = `booking.html?title=${encodeURIComponent(title)}&genre=${encodeURIComponent(genre)}&duration=${encodeURIComponent(duration)}&summary=${encodeURIComponent(summary)}&image=${encodeURIComponent(image)}`;
  window.location.href = bookingUrl; // Redirect to the booking page with the parameters
}