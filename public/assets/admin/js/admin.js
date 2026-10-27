// ადმინის JS (layout-ი და მენიუ — Sneat-ის main.js): ფორმების დადასტურება, კოპირება, ენის გადამრთველი, CSRF
(function ($) {
    "use strict";

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

    // ფორმის ენის გადამრთველი (partials/lang-switch) — ჩანს მხოლოდ თუ ფორმაში თარგმნადი ველებია ([data-lang])
    (function () {
        var $switch = $('[data-lang-switch]');
        if (!$switch.length || !$('[data-lang]').length) {
            return;
        }
        var root = document.documentElement;
        var $options = $switch.find('[data-lang-option]');
        var set = function (lang, remember) {
            root.setAttribute('data-admin-lang', lang);
            $options.each(function () {
                var active = this.getAttribute('data-lang-option') === lang;
                $(this).toggleClass('active', active).attr('aria-pressed', active ? 'true' : 'false');
            });
            if (remember) {
                try { localStorage.setItem('admin.lang', lang); } catch (e) {}
            }
            $(window).trigger('resize'); // WYSIWYG რედაქტორები დამალვის შემდეგ ზომას ითვლიან
        };

        // შეცდომიანი ენები ღილაკზე აღინიშნება; დამალულ ენაში შეცდომა თუ არის — ის გამოჩნდება
        var errored = [];
        $options.each(function () {
            var code = this.getAttribute('data-lang-option');
            if (code !== 'all' && $('[data-lang="' + code + '"]').find('.is-invalid').length) {
                errored.push(code);
                $(this).addClass('has-error');
            }
        });
        var current = root.getAttribute('data-admin-lang');
        if (errored.length && current !== 'all' && errored.indexOf(current) === -1) {
            current = errored.length === 1 ? errored[0] : 'all';
        }

        $switch.removeClass('d-none');
        set(current, false);
        $options.on('click', function () {
            set(this.getAttribute('data-lang-option'), true);
        });
    })();

    // AJAX მოთხოვნებს CSRF token ავტომატურად ერთვის
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
})(jQuery);
