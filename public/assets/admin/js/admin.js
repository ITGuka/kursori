// ადმინის JS: DASHMIN-ის main.js-ის საჭირო ნაწილი (დემო დიაგრამების გარეშე) + ფორმების დადასტურება
(function ($) {
    "use strict";

    // Back to top
    $(window).on('scroll', function () {
        $('.back-to-top').toggleClass('show', $(this).scrollTop() > 300);
    });
    $('.back-to-top').on('click', function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });

    // Sidebar toggler
    $('.sidebar-toggler').on('click', function () {
        $('.sidebar, .content').toggleClass('open');
        return false;
    });

    // <form data-confirm="..."> — დადასტურება გაგზავნამდე (მაგ. წაშლა)
    $(document).on('submit', 'form[data-confirm]', function (e) {
        if (!window.confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });

    // AJAX მოთხოვნებს CSRF token ავტომატურად ერთვის
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
})(jQuery);
