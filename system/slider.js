const slider = document.querySelector('.slider');
const slides = Array.from(document.querySelectorAll('.slide'));
const leftArrow = document.querySelector('.left-arrow');
const rightArrow = document.querySelector('.right-arrow');

let currentIndex = 0;

const slideCount = slides.length;

function updateSlider() {
    const slideWidth = slides[0].getBoundingClientRect().width;
    slider.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
}

function showNext() {
    currentIndex = (currentIndex + 1) % slideCount;
    updateSlider();
}

function showPrev() {
    currentIndex = (currentIndex - 1 + slideCount) % slideCount;
    updateSlider();
}

// Initialize position
updateSlider();

// Attach event listeners
rightArrow.addEventListener('click', showNext);
leftArrow.addEventListener('click', showPrev);

// Optional: Keyboard support
document.addEventListener('keydown', (e) => {
    if(e.key === "ArrowRight") showNext();
    else if(e.key === "ArrowLeft") showPrev();
});
