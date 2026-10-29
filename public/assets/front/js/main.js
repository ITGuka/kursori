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
    var toggleBackToTop = function () {
        $('.back-to-top').toggleClass('is-visible', $(window).scrollTop() > 300);
    };
    $(window).scroll(toggleBackToTop);
    toggleBackToTop(); // გვერდი შუიდან რომ ჩაიტვირთოს (reload, anchor)

    // ზემოთ ასვლა requestAnimationFrame-ით: მოძრაობა კლიკისთანავე იწყება (easeOutQuart) და ბოლოს რბილად ჩერდება.
    // bootstrap-ის `scroll-behavior: smooth` ანიმაციის დროს გამორთულია, თორემ ყოველ კადრს თავისით აგვიანებს.
    // მომხმარებლის scroll/touch/კლავიში ანიმაციას წყვეტს; prefers-reduced-motion-ზე პირდაპირ ხტება.
    var scrollToTop = function () {
        var root = document.documentElement;
        var startY = window.pageYOffset;
        if (startY <= 0) {
            return;
        }
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            root.style.scrollBehavior = 'auto';
            window.scrollTo(0, 0);
            root.style.scrollBehavior = '';
            return;
        }
        var duration = Math.min(900, Math.max(450, startY / 5));
        var startTime = null;
        var frame;
        var stop = function () {
            cancelAnimationFrame(frame);
            root.style.scrollBehavior = '';
            window.removeEventListener('wheel', stop);
            window.removeEventListener('touchstart', stop);
            window.removeEventListener('keydown', stop);
        };
        var step = function (now) {
            if (startTime === null) {
                startTime = now;
            }
            var progress = Math.min((now - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 4);
            window.scrollTo(0, Math.round(startY * (1 - eased)));
            if (progress < 1) {
                frame = requestAnimationFrame(step);
            } else {
                stop();
            }
        };
        root.style.scrollBehavior = 'auto';
        window.addEventListener('wheel', stop, {passive: true});
        window.addEventListener('touchstart', stop, {passive: true});
        window.addEventListener('keydown', stop);
        frame = requestAnimationFrame(step);
    };

    $('.back-to-top').click(function () {
        var $button = $(this);
        $button.removeClass('is-launching');
        void this.offsetWidth; // ანიმაციის თავიდან გასაშვებად
        $button.addClass('is-launching');
        scrollToTop();
        return false;
    }).on('animationend', function () {
        $(this).removeClass('is-launching');
    });


    // [data-tilt] (about ბლოკის სურათი): კურსორის მიყოლით მსუბუქი 3D დახრა (±6°) — მხოლოდ მაუსით, reduced-motion-ის გარეშე
    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        $('[data-tilt]').on('mousemove', function (event) {
            var rect = this.getBoundingClientRect();
            var x = (event.clientX - rect.left) / rect.width - .5;
            var y = (event.clientY - rect.top) / rect.height - .5;
            this.classList.add('is-tilting');
            this.style.setProperty('--tilt-x', (x * 12).toFixed(2) + 'deg');
            this.style.setProperty('--tilt-y', (y * -12).toFixed(2) + 'deg');
        }).on('mouseleave', function () {
            this.classList.remove('is-tilting');
            this.style.removeProperty('--tilt-x');
            this.style.removeProperty('--tilt-y');
        });
    }


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

