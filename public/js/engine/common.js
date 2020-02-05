//demo myCarousel demonstrating the slid and slide events
document.addEventListener("DOMContentLoaded", function(event) {
    var mainSlider = document.getElementById('myCarousel');
    var mainSliderItems = mainSlider.querySelectorAll('.item');

    mainSlider.addEventListener('slide.bs.carousel', function(e) {
        var currentActive = mainSlider.Carousel.getActiveIndex();
        var activeCaption = mainSliderItems[currentActive].querySelector('.carousel-caption');
        activeCaption.classList.remove('slide');
    });

    mainSlider.addEventListener('slid.bs.carousel', function(e) {
        var activeCaption = e.relatedTarget.querySelector('.carousel-caption');
        activeCaption.classList.add('slide');
    });

    var caleCarousel = new Carousel(mainSlider);
});

