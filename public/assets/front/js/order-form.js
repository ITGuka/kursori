/**
 * საიტის შეკვეთის ფორმა (front/blocks/order_form): პაკეტი → მისი დარგები (ღილაკები) → დიზაინების ფილტრი, „მეტის ჩვენება“
 * და შაბლონის preview მოდალში (ბარათზე დაკლიკებით; iframe, მოწყობილობის სიგანის გადამრთველით).
 * დიზაინი აირჩევა მოდალის ღილაკით „ამ დიზაინის არჩევა“.
 */
(function () {
    'use strict';

    var forms = [];

    document.querySelectorAll('[data-order-form]').forEach(function (form) {
        var packages = Array.prototype.slice.call(form.querySelectorAll('[data-order-package]'));
        var groups = Array.prototype.slice.call(form.querySelectorAll('[data-order-industries]'));
        var steps = form.querySelector('[data-order-steps]');
        var pending = form.querySelector('[data-order-pending]');
        var custom = form.querySelector('[data-order-custom]');
        var industries = form.querySelector('[data-order-industries-step]');
        var designStep = form.querySelector('[data-order-design-step]');
        var chips = Array.prototype.slice.call(form.querySelectorAll('[data-order-category]'));
        var cards = Array.prototype.slice.call(form.querySelectorAll('[data-order-design]'));
        var empty = form.querySelector('[data-order-empty]');
        var more = form.querySelector('[data-order-more]');
        var moreButton = more && more.querySelector('[data-order-more-button]');
        var moreCount = more && more.querySelector('[data-order-more-count]');
        var perPage = more ? parseInt(more.getAttribute('data-per-page'), 10) || 6 : cards.length;
        var selected = form.querySelector('[data-order-selected]');
        var matching = cards;
        var limit = perPage;

        // ჩანს პირველი limit ცალი; დანარჩენი — „მეტის ჩვენებით“ (+perPage)
        function render() {
            cards.forEach(function (card) { card.classList.add('d-none'); });
            matching.slice(0, limit).forEach(function (card) { card.classList.remove('d-none'); });
            if (empty) { empty.classList.toggle('d-none', matching.length > 0); }
            if (!more) { return; }
            var shown = Math.min(limit, matching.length);
            var rest = matching.length - shown;
            more.classList.toggle('d-none', rest <= 0);
            moreButton.textContent = moreButton.getAttribute('data-label').replace(':count', String(Math.min(rest, perPage)));
            moreCount.textContent = moreCount.getAttribute('data-label').replace(':shown', String(shown)).replace(':total', String(matching.length));
        }

        function checkedPackage() {
            return packages.filter(function (p) { return p.checked; })[0] || null;
        }

        // პაკეტი → მისი დარგების ჯგუფი (სხვა ჯგუფები გათიშულია, რომ არ გაიგზავნოს); დარგი არ არის არჩეული → „ყველა“
        function selectPackage(scroll) {
            var current = checkedPackage();
            var code = current ? current.value : null;
            groups.forEach(function (group) {
                var active = group.getAttribute('data-order-industries') === code;
                group.classList.toggle('d-none', !active);
                group.querySelectorAll('input').forEach(function (input) {
                    input.disabled = !active;
                    if (!active) { input.checked = false; }
                });
                if (active && !group.querySelector('input:checked')) {
                    group.querySelector('input').checked = true;
                }
            });
            // დარგების გარეშე პაკეტი (მხოლოდ „ყველა“) — ნაბიჯი 2 იმალება, დიზაინი ხდება მე-2 ნაბიჯი
            var group = current ? form.querySelector('[data-order-industries="' + current.value + '"]') : null;
            var single = !group || group.querySelectorAll('input').length < 2;
            if (industries) { industries.classList.toggle('d-none', single); }
            if (designStep) { designStep.textContent = single ? '2' : '3'; }
            if (steps) { steps.classList.toggle('is-pending', !current); }
            if (pending) { pending.classList.toggle('d-none', !!current); }
            if (custom && current) { custom.href = current.getAttribute('data-custom-url'); }
            filter();
            if (scroll && current && steps) { steps.scrollIntoView({behavior: 'smooth', block: 'start'}); }
        }

        // დარგი → მისი დიზაინები; „ყველა“ → პაკეტისა და მისი დარგების დიზაინები; პაკეტი არ არის → არცერთი
        function filter() {
            var chip = chips.filter(function (c) { return c.checked && !c.disabled; })[0];
            var current = checkedPackage();
            var ids = chip ? JSON.parse(chip.getAttribute('data-ids') || '[]') : (current ? JSON.parse(current.getAttribute('data-ids') || '[]') : []);
            matching = cards.filter(function (card) {
                var categories = JSON.parse(card.getAttribute('data-categories') || '[]');
                var show = categories.some(function (id) { return ids.indexOf(id) !== -1; });
                var radio = card.querySelector('input[type=radio]');
                if (!show && radio && radio.checked) { radio.checked = false; }
                return show;
            });
            // არჩეული დიზაინი ჩანდეს (მაგ. ვალიდაციის შეცდომის შემდეგ)
            var checked = matching.findIndex(function (card) { var r = card.querySelector('input[type=radio]'); return r && r.checked; });
            limit = Math.max(perPage, Math.ceil((checked + 1) / perPage) * perPage);
            updateSelected();
            render();
        }

        if (moreButton) {
            moreButton.addEventListener('click', function () {
                var first = matching[limit];
                limit += perPage;
                render();
                // კლავიატურით მომხმარებელი ახლად გამოჩენილ პირველ ბარათზე გადადის
                if (first) { first.querySelector('[data-order-preview]').focus({preventScroll: true}); }
            });
        }

        function updateSelected() {
            if (!selected) { return; }
            var radio = form.querySelector('input[name=design]:checked');
            selected.classList.toggle('d-none', !radio);
            selected.querySelector('[data-order-selected-title]').textContent = radio ? radio.getAttribute('data-title') : '';
        }

        packages.forEach(function (input) { input.addEventListener('change', function () { selectPackage(true); }); });
        chips.forEach(function (chip) { chip.addEventListener('change', filter); });
        selectPackage(false);
        forms.push({form: form, updateSelected: updateSelected});
    });

    var modalEl = document.querySelector('[data-order-modal]');
    if (!modalEl) { return; }
    var frame = modalEl.querySelector('[data-order-frame]');
    var title = modalEl.querySelector('[data-order-modal-title]');
    var choose = modalEl.querySelector('[data-order-choose]');
    var devices = modalEl.querySelectorAll('[data-order-device]');
    var inputId = null;

    // Bootstrap 5.0-ს getOrCreateInstance არ აქვს
    function modal() {
        return bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    }

    function setDevice(width) {
        frame.style.width = width;
        devices.forEach(function (button) {
            var active = button.getAttribute('data-order-device') === width;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-order-preview]');
        if (!button) { return; }
        inputId = button.getAttribute('data-input');
        title.textContent = button.getAttribute('data-title');
        setDevice('100%');
        frame.src = button.getAttribute('data-url');
        modal().show();
    });

    devices.forEach(function (button) {
        button.addEventListener('click', function () { setDevice(button.getAttribute('data-order-device')); });
    });

    // „ამ დიზაინის არჩევა“ → რადიო მოინიშნება და ფორმაზე დავბრუნდებით
    choose.addEventListener('click', function () {
        var input = inputId && document.getElementById(inputId);
        if (input) { input.checked = true; }
        forms.forEach(function (f) { f.updateSelected(); });
        modal().hide();
        if (input) { input.closest('[data-order-design]').scrollIntoView({behavior: 'smooth', block: 'center'}); }
    });

    // დახურვისას შაბლონი ჩერდება (ვიდეო, ანიმაციები)
    modalEl.addEventListener('hidden.bs.modal', function () { frame.src = 'about:blank'; });
})();
