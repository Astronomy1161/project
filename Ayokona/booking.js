// Function to get query parameters from the URL
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Movie data with trailer URLs and images
const movies = {
    'Spirited Away': {
        title: 'Spirited Away',
        description: 'The story is about the adventures of a young ten-year-old girl named Chihiro as she wanders into the world of the gods and spirits. She is forced to work at a bathhouse following her parents being turned into pigs by the evil witch Yubaba.',
        genre: 'Fantasy',
        duration: '125 minutes',
        trailer: 'video1.mp4', // Path to trailer video for Spirited Away
        image: 'image1.jpg' // Path to image for Spirited Away
    },
    'Ponyo': {
        title: 'Ponyo',
        description: 'A goldfish princess wants to be human.',
        genre: 'Adventure',
        duration: '101 minutes',
        trailer: 'video2.mp4', // Path to trailer video for Ponyo
        image: 'image2.jpg' // Path to image for Ponyo
    },
    'Arrietty': {
        title: 'Arrietty',
        description: 'Arrietty, a tiny girl who lives under the floorboards, befriends a human boy named Sho.',
        genre: 'Fantasy',
        duration: '94 minutes',
        trailer: 'video3.mp4', // Path to trailer video for Arrietty
        image: 'image3.jpg' // Path to image for Arrietty
    },
    'Howl\'s Moving Castle': {
        title: 'Howl\'s Moving Castle',
        description: 'Sophie, a young woman, is cursed by a witch and transformed into an old woman, leading her to seek help from the mysterious wizard Howl.',
        genre: 'Fantasy',
        duration: '119 minutes',
        trailer: 'video4.mp4', // Path to trailer video for Howl's Moving Castle
        image: 'image4.jpg' // Path to image for Howl's Moving Castle
    },
    'Totoro': {
        title: 'Totoro',
        description: 'Two sisters discover a magical creature, Totoro, who helps them through difficult times in rural Japan.',
        genre: 'Fantasy',
        duration: '86 minutes',
        trailer: 'video5.mp4', // Path to trailer video for Totoro
        image: 'image5.jpg' // Path to image for Totoro
    },
    'A Whisker Away': {
        title: 'A Whisker Away',
        description: 'A girl turns into a cat to get closer to the boy she loves, but soon realizes that being a cat is not as easy as it seems.',
        genre: 'Romance',
        duration: '104 minutes',
        trailer: 'video6.mp4', // Path to trailer video for A Whisker Away
        image: 'image6.jpg' // Path to image for A Whisker Away
    },
    'Your Name': {
        title: 'Your Name',
        description: 'Two teenagers, Taki and Mitsuha, mysteriously swap bodies and start to develop a deep connection across time and space.',
        genre: 'Romance',
        duration: '106 minutes',
        trailer: 'video7.mp4', // Path to trailer video for Your Name
        image: 'image7.jpg' // Path to image for Your Name
    },
    'Weathering with You': {
        title: 'Weathering with You',
        description: 'A boy runs away to Tokyo and meets a girl who can control the weather, leading to a series of events that change their lives forever.',
        genre: 'Romance',
        duration: '112 minutes',
        trailer: 'video8.mp4', // Path to trailer video for Weathering with You
        image: 'image8.jpg' // Path to image for Weathering with You
    },
    'Suzume': {
        title: 'Suzume',
        description: 'A young girl, Suzume, embarks on a journey to prevent mysterious doors from wreaking havoc across Japan.',
        genre: 'Adventure',
        duration: '120 minutes',
        trailer: 'video9.mp4', // Path to trailer video for Suzume
        image: 'image9.jpg' // Path to image for Suzume
    },
    '5 Centimeters per Second': {
        title: '5 Centimeters per Second',
        description: 'A story of love and loss as two people, separated by distance and time, struggle to maintain their connection.',
        genre: 'Romance',
        duration: '63 minutes',
        trailer: 'video10.mp4', // Path to trailer video for 5 Centimeters per Second
        image: 'image10.jpg' // Path to image for 5 Centimeters per Second
    }
};

// Function to update the movie details and trailer dynamically
function updateMovieDetails() {
    const movieName = getQueryParam('movie');
    const trailerFile = getQueryParam('trailer');
    const movie = movies[movieName];

    if (movie) {
        // Update movie details
        document.getElementById('movieTitle').innerText = movie.title;
        document.getElementById('movieDescription').innerText = movie.description;
        document.getElementById('movieGenre').innerText = movie.genre;
        document.getElementById('movieDuration').innerText = movie.duration;

        // Update the background trailer
        const videoSource = document.getElementById('videoSource');
        videoSource.src = trailerFile;
        document.getElementById('backgroundVideo').load(); // Reload video with new source

        // Update the movie image
        const selectedImage = document.getElementById('selectedImage');
        selectedImage.src = movie.image;
    }
}

// Call the function to update the page when it loads
window.onload = updateMovieDetails;
// Function to redirect to booknow.html with the movie title as a query parameter
function redirectToBooking() {
    const movieTitle = document.getElementById('movieTitle').innerText;  // Get the movie title from the page
    const url = `seatreservation.html?movie=${encodeURIComponent(movieTitle)}`;  // Create the URL with the movie title
    window.location.href = url;  // Redirect to booknow.html
}
