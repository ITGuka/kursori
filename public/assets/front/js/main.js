(function ($) {
    "use strict";

    // Scroll reveal (WOW.js-ის ნაცვლად): .wow ბლოკები თავიდანვე ჩანს და ეკრანზე გამოჩენისას მხოლოდ მსუბუქად ამოდის.
    // ეკრანზე უკვე მყოფს, prefers-reduced-motion-ს ან IntersectionObserver-ის გარეშე ანიმაცია არ აქვს.
    var reveal = function () {
        if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {rootMargin: '0px 0px -40px 0px'});
        document.querySelectorAll('.wow').forEach(function (item) {
            if (item.getBoundingClientRect().top < window.innerHeight) {
                return;
            }
            var delay = parseFloat(item.getAttribute('data-wow-delay')) || 0;
            item.style.transitionDelay = Math.min(delay, 0.5) / 2 + 's';
            item.classList.add('reveal');
            observer.observe(item);
        });
    };
    reveal();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 0) {
            $('.navbar').addClass('position-fixed bg-dark shadow-sm');
        } else {
            $('.navbar').removeClass('position-fixed bg-dark shadow-sm');
        }
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Testimonials carousel
    $('.testimonial-carousel').owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        loop: true,
        nav: false,
        dots: true,
        items: 1,
        dotsData: true,
    });

    
})(jQuery);

