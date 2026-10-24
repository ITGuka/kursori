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

    // <button data-copy="#input"> — ველის მნიშვნელობის კოპირება
    $(document).on('click', '[data-copy]', function () {
        var $button = $(this);
        var $field = $($button.data('copy'));
        var done = function () {
            $button.find('i').attr('class', 'fa fa-check');
            setTimeout(function () { $button.find('i').attr('class', 'fa fa-copy'); }, 1500);
        };
        if (navigator.clipboard) {
            navigator.clipboard.writeText($field.val()).then(done);
        } else {
            $field.trigger('select');
            document.execCommand('copy');
            done();
        }
    });

    // AJAX მოთხოვნებს CSRF token ავტომატურად ერთვის
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
})(jQuery);
