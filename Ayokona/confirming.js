// Function to get query parameters from the URL
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Movie data with trailer URLs and images
const movies = {
    'Spirited Away': {
        title: 'Spirited Away',
        description: 'The story is about the adventures of a young ten-year-old girl named Chihiro...',
        genre: 'Fantasy',
        duration: '125 minutes',
        trailer: 'video1.mp4',
        image: 'image1.jpg'
    },
    'Ponyo': {
        title: 'Ponyo',
        description: 'A goldfish princess wants to be human.',
        genre: 'Adventure',
        duration: '101 minutes',
        trailer: 'video2.mp4',
        image: 'image2.jpg'
    },
    'Arrietty': {
        title: 'Arrietty',
        description: 'Arrietty, a tiny girl who lives under the floorboards, befriends a human boy named Sho.',
        genre: 'Fantasy',
        duration: '94 minutes',
        trailer: 'video3.mp4',
        image: 'image3.jpg'
    },
    'Howl\'s Moving Castle': {
        title: 'Howl\'s Moving Castle',
        description: 'Sophie is cursed and seeks help from the wizard Howl.',
        genre: 'Fantasy',
        duration: '119 minutes',
        trailer: 'video4.mp4',
        image: 'image4.jpg'
    },
    'Totoro': {
        title: 'Totoro',
        description: 'Two sisters discover a magical creature, Totoro.',
        genre: 'Fantasy',
        duration: '86 minutes',
        trailer: 'video5.mp4',
        image: 'image5.jpg'
    },
    'A Whisker Away': {
        title: 'A Whisker Away',
        description: 'A girl turns into a cat to be closer to the boy she loves.',
        genre: 'Romance',
        duration: '104 minutes',
        trailer: 'video6.mp4',
        image: 'image6.jpg'
    },
    'Your Name': {
        title: 'Your Name',
        description: 'Two teenagers mysteriously swap bodies and develop a deep connection.',
        genre: 'Romance',
        duration: '106 minutes',
        trailer: 'video7.mp4',
        image: 'image7.jpg'
    },
    'Weathering with You': {
        title: 'Weathering with You',
        description: 'A boy meets a girl who can control the weather.',
        genre: 'Romance',
        duration: '112 minutes',
        trailer: 'video8.mp4',
        image: 'image8.jpg'
    },
    'Suzume': {
        title: 'Suzume',
        description: 'Suzume embarks on a journey to prevent magical disasters.',
        genre: 'Adventure',
        duration: '120 minutes',
        trailer: 'video9.mp4',
        image: 'image9.jpg'
    },
    '5 Centimeters per Second': {
        title: '5 Centimeters per Second',
        description: 'A story of love and loss over time and distance.',
        genre: 'Romance',
        duration: '63 minutes',
        trailer: 'video10.mp4',
        image: 'image10.jpg'
    }
};

// Populate the movie and booking details on page load
window.onload = function () {
    const movieName = getQueryParam('movie');
    const seats = getQueryParam('seats');
    const date = getQueryParam('date');
    const time = getQueryParam('time');

    if (movieName && movies[movieName]) {
        const movie = movies[movieName];
        document.getElementById('movieTitle').innerText = movie.title;
    } else {
        document.getElementById('movieTitle').innerText = movieName || "Unknown";
    }

    document.getElementById('selectedSeats').innerText = seats || "Not selected";
    document.getElementById('selectedDate').innerText = date || "Not selected";
    document.getElementById('selectedTime').innerText = time || "Not selected";
};

// Send booking data to PHP and redirect
function confirmAndGoToTicketPage() {
    const data = {
        movie_title: document.getElementById("movieTitle").innerText,
        selected_seats: document.getElementById("selectedSeats").innerText,
        selected_date: document.getElementById("selectedDate").innerText,
        selected_time: document.getElementById("selectedTime").innerText
    };

    fetch("insert_booking.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams(data)
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        window.location.href = "ticket.html"; // Redirect after confirmation
    });
}

// Optional: redirect to seat page with selected movie
function redirectToBooking() {
    const movieTitle = document.getElementById('movieTitle').innerText;
    if (!movieTitle) {
        console.error("Movie title is missing!");
        return;
    }
    const url = `seatreservation.html?movie=${encodeURIComponent(movieTitle)}`;
    window.location.href = url;
}
