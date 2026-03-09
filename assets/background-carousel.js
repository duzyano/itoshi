const images = [
    "assets/images/breakfast.png",
    "assets/images/breakfast2.png",
    "assets/images/breakfast3.png",
    "assets/images/breakfast4.png",
    "assets/images/broodjes.png",
    "assets/images/broodjes2.png",
    "assets/images/broodjes3.png",
    "assets/images/broodjes4.png",
    'assets/images/dips.png',
    'assets/images/dips2.png',
    'assets/images/dips3.png',
    'assets/images/dips4.png',
    'assets/images/dips5.png',
    'assets/images/drinks.png',
    'assets/images/drinks2.png',
    'assets/images/drinks3.png',
    'assets/images/drinks4.png',
    'assets/images/drinks5.png',
    'assets/images/lunch&dinner.png',
    'assets/images/lunch&dinner2.png',
    'assets/images/lunch&dinner3.png',
    'assets/images/lunch&dinner4.png',
    'assets/images/small.png',
    'assets/images/small2.png',
    'assets/images/small3.png',
    'assets/images/small4.png',

];

let index = 0;

const slides = document.querySelectorAll(".bg-slide");

slides[0].style.backgroundImage = `url(${images[0]})`;

setInterval(() => {

    index++;
    if (index >= images.length) {
        index = 0;
    }

    const active = document.querySelector(".bg-slide.active");
    const next = active.nextElementSibling || slides[0];

    next.style.backgroundImage = `url(${images[index]})`;

    active.classList.remove("active");
    next.classList.add("active");

}, 4000);











