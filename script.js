// Dynamic year in footer
document.getElementById('year').textContent = new Date().getFullYear();

// Back to top
document.querySelector('.to-top').addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Simple continuous slider (right-to-left) with 4s per slide
const slidesContainer = document.querySelector('.slides');
const slideCount = document.querySelectorAll('.slide').length;
let index = 0;

function nextSlide() {
  index = (index + 1) % slideCount;
  slidesContainer.style.transition = 'transform 0.8s ease-in-out';
const slideWidth = document.querySelector('.slider').offsetWidth;
slidesContainer.style.transform = `translateX(-${index * slideWidth}px)`;
}

slidesContainer.addEventListener('transitionend', () => {
  // Loop without flicker
});

setInterval(nextSlide, 4000);

// Adjust slider height on orientation change (mobile)
function adjustHeroHeight() {
  const slider = document.querySelector('.slider');
  if (window.innerWidth < 640) {
    slider.style.height = '220px';
  } else {
    slider.style.height = '340px';
  }
}
window.addEventListener('resize', () => {
  adjustHeroHeight();
  // recalc width so the slider adapts
  slidesContainer.style.transform = `translateX(-${index * document.querySelector('.slider').offsetWidth}px)`;
});
window.addEventListener('orientationchange', adjustHeroHeight);
adjustHeroHeight();

// Enable/disable Register button when checkbox is toggled
const termsCheckbox = document.getElementById('termsCheckbox');
const registerBtn = document.getElementById('registerBtn');

termsCheckbox.addEventListener('change', () => {
  registerBtn.disabled = !termsCheckbox.checked;
});

