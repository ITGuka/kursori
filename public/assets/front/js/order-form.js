/**
 * საიტის შეკვეთის ფორმა (front/blocks/order_form): პაკეტი → მისი დარგები (ღილაკები) → დიზაინების ფილტრი, „მეტის ჩვენება“;
 * „ინდივიდუალური“ — იმავე ფორმაში, დარგისა და დიზაინის ნაცვლად 5-ნაბიჯიანი ბრიფით (setupWizard)
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
        var customOption = form.querySelector('[data-order-custom-option]');
        var customStep = form.querySelector('[data-custom-step]');
        var commonStep = form.querySelector('[data-order-common-step]');
        var wizard = form.querySelector('[data-custom-wizard]');
        var common = form.querySelector('[data-order-common]');
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
            // „ინდივიდუალური“ — დარგის, დიზაინისა და საერთო ველების ნაცვლად ბრიფი (ნაბიჯებად); დამალული ნაკრები disabled — არ იგზავნება
            var isCustom = !!current && current.hasAttribute('data-order-custom-option');
            if (steps) { steps.classList.toggle('is-pending', !current || isCustom); }
            if (pending) { pending.classList.toggle('d-none', !!current); }
            if (customStep) { customStep.classList.toggle('d-none', !isCustom); }
            if (commonStep) { commonStep.classList.toggle('d-none', isCustom); }
            if (wizard) { wizard.disabled = !isCustom; }
            if (common) { common.disabled = isCustom; }
            filter();
            if (scroll && current && steps && !isCustom) { steps.scrollIntoView({behavior: 'smooth', block: 'start'}); }
            if (scroll && isCustom && customStep) { customStep.scrollIntoView({behavior: 'smooth', block: 'start'}); }
        }

        if (wizard) { setupWizard(wizard); }

        // „ვერ იპოვეთ შესაფერისი?“ → იმავე ფორმაში „ინდივიდუალური“, პაკეტების ნაბიჯზე ასვლით
        if (custom && customOption) {
            custom.addEventListener('click', function (event) {
                event.preventDefault();
                customOption.checked = true;
                selectPackage(false);
                customOption.closest('fieldset').scrollIntoView({behavior: 'smooth', block: 'start'});
            });
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

    // ინდივიდუალური შეკვეთის ბრიფი (partials/custom-order-wizard): ერთი ნაბიჯი ერთდროულად, „შემდეგი“ — ნაბიჯის ვალიდაციით;
    // ვალიდაციის შეცდომით დაბრუნებისას იხსნება პირველი ნაბიჯი, რომელშიც შეცდომაა
    function setupWizard(wizard) {
        var panels = Array.prototype.slice.call(wizard.querySelectorAll('[data-wizard-panel]'));
        var tabs = Array.prototype.slice.call(wizard.querySelectorAll('[data-wizard-tab]'));
        var prev = wizard.querySelector('[data-wizard-prev]');
        var next = wizard.querySelector('[data-wizard-next]');
        var submit = wizard.querySelector('[data-wizard-submit]');
        var counter = wizard.querySelector('[data-wizard-counter]');
        var progress = wizard.querySelector('[data-wizard-progress]');
        var services = Array.prototype.slice.call(wizard.querySelectorAll('input[name=service]'));
        var dependent = Array.prototype.slice.call(wizard.querySelectorAll('[data-wizard-for]'));
        var serviceName = wizard.querySelector('[data-wizard-service-name]');
        var projectName = wizard.querySelector('[data-wizard-project-name]');
        var keys = Array.prototype.slice.call(wizard.querySelectorAll('[data-wizard-key]'));
        var methods = Array.prototype.slice.call(wizard.querySelectorAll('input[name=contact_method]'));
        var phone = wizard.querySelector('[data-wizard-phone] input');
        var phoneLabel = wizard.querySelector('[data-wizard-phone] label');
        var phoneText = phoneLabel ? phoneLabel.textContent.replace(/\s*\*$/, '') : '';
        var current = 0;
        var reached = 0;

        wizard.classList.add('is-stepped');

        function show(index, focus) {
            current = index;
            reached = Math.max(reached, index);
            panels.forEach(function (panel, i) { panel.classList.toggle('is-active', i === index); });
            tabs.forEach(function (tab, i) {
                tab.classList.toggle('is-current', i === index);
                tab.classList.toggle('is-done', i <= reached && i !== index);
                var button = tab.querySelector('button');
                button.disabled = i > reached;
                if (i === index) { button.setAttribute('aria-current', 'step'); } else { button.removeAttribute('aria-current'); }
            });
            var last = index === panels.length - 1;
            prev.classList.toggle('invisible', index === 0);
            next.classList.toggle('d-none', last);
            submit.classList.toggle('d-none', !last);
            if (counter) {
                counter.textContent = counter.getAttribute('data-label').replace(':n', String(index + 1)).replace(':total', String(panels.length));
            }
            if (progress) { progress.style.width = ((index + 1) / panels.length * 100) + '%'; }
            if (focus) {
                var title = panels[index].querySelector('.custom-panel-title');
                title.setAttribute('tabindex', '-1');
                title.focus({preventScroll: true});
                wizard.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        }

        // ნაბიჯის ველები: ბრაუზერის ვალიდაცია (required, email, url) + „მინიმუმ ერთი“ checkbox-ების ჯგუფში
        function valid(index) {
            var panel = panels[index];
            var ok = true;
            panel.querySelectorAll('[data-wizard-group-required]').forEach(function (group) {
                if (group.closest('fieldset:disabled')) { return; }
                var empty = !group.querySelector('input:checked');
                group.querySelector('[data-wizard-error]').classList.toggle('d-none', !empty);
                if (empty && ok) {
                    ok = false;
                    group.querySelector('input').focus();
                }
            });
            if (!ok) { return false; }
            var fields = Array.prototype.slice.call(panel.querySelectorAll('input, textarea, select'));
            for (var i = 0; i < fields.length; i++) {
                if (!fields[i].checkValidity()) {
                    // ფაილების ველი დამალულია — შეცდომა მის ქვეშ უკვე ჩანს, ფოკუსი — არჩევის ღილაკზე
                    if (fields[i].type === 'file') {
                        fields[i].focus();
                    } else {
                        fields[i].reportValidity();
                    }
                    return false;
                }
            }
            return true;
        }

        function labelOf(input) {
            var label = input && wizard.querySelector('label[for="' + input.id + '"]');
            if (!label) { return ''; }
            var name = label.querySelector('.custom-option-name');
            return (name || label).textContent.trim();
        }

        // პროექტი → ნაბიჯები 2–4: ნიშნები — მომსახურება (web) და მომსახურება:ქვეტიპი (web:shop; ქვეტიპი არ არის → web:-);
        // data-wizard-for-ის რომელიმე ნიშანი ემთხვევა → ჩანს, თორემ დამალული და disabled
        function syncService() {
            var service = services.filter(function (s) { return s.checked; })[0];
            var code = service ? service.value : '';
            var sub = keys.filter(function (k) { return k.checked && k.getAttribute('data-wizard-key') === code; })[0];
            var tokens = [code, code + ':' + (sub ? sub.value : '-')];
            dependent.forEach(function (el) {
                var match = el.getAttribute('data-wizard-for').split(' ').some(function (token) { return tokens.indexOf(token) !== -1; });
                el.hidden = !match;
                el.disabled = !match;
            });
            if (serviceName) { serviceName.textContent = labelOf(service); }
            if (projectName) { projectName.textContent = [labelOf(service), labelOf(sub)].filter(Boolean).join(' · '); }
            if (references) {
                var required = references.getAttribute('data-required-for').split(' ').some(function (token) { return tokens.indexOf(token) !== -1; });
                references.querySelectorAll('[data-wizard-reference-list] input').forEach(function (input, i) { input.required = required && i === 0; });
                references.querySelector('[data-wizard-required-mark]').hidden = !required;
            }
        }

        // ბმულები: + ამატებს რიგს შაბლონიდან (მაქს. data-max), × შლის; პირველი რიგი არ იშლება
        var references = wizard.querySelector('[data-wizard-references]');
        if (references) {
            var list = references.querySelector('[data-wizard-reference-list]');
            var template = references.querySelector('[data-wizard-reference-template]');
            var add = references.querySelector('[data-wizard-reference-add]');
            var max = parseInt(references.getAttribute('data-max'), 10) || 5;
            var label = list.querySelector('input').getAttribute('aria-label').replace(/\s*\d+$/, '');
            var syncReferences = function () {
                var rows = list.querySelectorAll('[data-wizard-reference]');
                rows.forEach(function (row, i) {
                    row.querySelector('input').setAttribute('aria-label', label + ' ' + (i + 1));
                    row.querySelector('[data-wizard-reference-remove]').hidden = rows.length === 1;
                });
                add.classList.toggle('d-none', rows.length >= max);
                syncService(); // სავალდებულოა მხოლოდ პირველი ბმული
            };
            add.addEventListener('click', function () {
                list.appendChild(template.content.cloneNode(true));
                syncReferences();
                list.lastElementChild.querySelector('input').focus();
            });
            list.addEventListener('click', function (event) {
                var remove = event.target.closest('[data-wizard-reference-remove]');
                if (!remove) { return; }
                var row = remove.closest('[data-wizard-reference]');
                var sibling = row.previousElementSibling || row.nextElementSibling;
                row.remove();
                syncReferences();
                if (sibling) { sibling.querySelector('input').focus(); }
            });
            syncReferences();
        }

        // ზარით ან WhatsApp-ით დაკავშირება → ტელეფონი სავალდებულოა
        function syncPhone() {
            if (!phone) { return; }
            var method = methods.filter(function (m) { return m.checked; })[0];
            var required = !!method && (method.value === 'phone' || method.value === 'whatsapp');
            phone.required = required;
            if (phoneLabel) { phoneLabel.textContent = phoneText + (required ? ' *' : ''); }
        }

        prev.addEventListener('click', function () { show(Math.max(0, current - 1), true); });
        next.addEventListener('click', function () {
            if (valid(current)) { show(current + 1, true); }
        });
        tabs.forEach(function (tab, i) {
            tab.querySelector('button').addEventListener('click', function () {
                if (i <= current || valid(current)) { show(i, true); }
            });
        });
        wizard.querySelectorAll('[data-wizard-group-required] input').forEach(function (input) {
            input.addEventListener('change', function () {
                var group = input.closest('[data-wizard-group-required]');
                if (group.querySelector('input:checked')) { group.querySelector('[data-wizard-error]').classList.add('d-none'); }
            });
        });
        methods.forEach(function (m) { m.addEventListener('change', syncPhone); });
        services.concat(keys).forEach(function (s) { s.addEventListener('change', syncService); });
        // Enter ტექსტურ ველში — შემდეგ ნაბიჯზე და არა გაგზავნა
        wizard.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.tagName === 'INPUT' && current < panels.length - 1) {
                event.preventDefault();
                next.click();
            }
        });
        // გაგზავნამდე ყველა ნაბიჯი მოწმდება: შეცდომიანი ნაბიჯი იხსნება
        wizard.closest('form').addEventListener('submit', function (event) {
            if (wizard.disabled) { return; }
            for (var i = 0; i < panels.length; i++) {
                show(i, false);
                if (!valid(i)) {
                    event.preventDefault();
                    show(i, true);
                    return;
                }
            }
        });

        syncService();
        syncPhone();
        var invalid = panels.findIndex(function (panel) { return panel.querySelector('.is-invalid, [data-wizard-server-error]'); });
        if (invalid > 0) { reached = panels.length - 1; }
        show(Math.max(0, invalid), false);
    }

    // ფაილების არჩევა (partials/form-files): ფაილები გროვდება რამდენჯერმე არჩევით ან ჩაგდებით (DataTransfer), სიაში — × წაშლა;
    // ზომა, ჯამი და რაოდენობა მოწმდება გაგზავნამდე (სერვერზე post_max_size-ზე დიდი მოთხოვნა საერთოდ ვერ მივა)
    document.querySelectorAll('[data-form-files]').forEach(function (box) {
        var input = box.querySelector('input[type=file]');
        var drop = box.querySelector('[data-form-files-drop]');
        var list = box.querySelector('[data-form-files-list]');
        var error = box.querySelector('[data-form-files-error]');
        var maxFiles = parseInt(box.getAttribute('data-max-files'), 10);
        var maxSize = parseInt(box.getAttribute('data-max-size'), 10);
        var maxTotal = parseInt(box.getAttribute('data-max-total'), 10);
        var files = [];
        if (typeof DataTransfer === 'undefined') { return; }

        function size(bytes) {
            return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function sync() {
            var transfer = new DataTransfer();
            files.forEach(function (file) { transfer.items.add(file); });
            input.files = transfer.files;

            var message = '';
            var total = 0;
            list.innerHTML = '';
            files.forEach(function (file, i) {
                total += file.size;
                var tooLarge = file.size > maxSize;
                if (tooLarge && !message) { message = file.name + ': ' + box.getAttribute('data-msg-too-large'); }
                var item = document.createElement('li');
                item.className = 'form-files-item' + (tooLarge ? ' is-invalid' : '');
                item.innerHTML = '<i class="fa fa-file-alt" aria-hidden="true"></i><span class="form-files-name"></span>'
                    + '<span class="form-files-size"></span><button type="button" class="form-files-remove"><i class="fa fa-times" aria-hidden="true"></i></button>';
                item.querySelector('.form-files-name').textContent = file.name;
                item.querySelector('.form-files-size').textContent = size(file.size);
                var remove = item.querySelector('.form-files-remove');
                remove.setAttribute('aria-label', box.getAttribute('data-msg-remove') + ': ' + file.name);
                remove.addEventListener('click', function () {
                    files.splice(i, 1);
                    sync();
                    (list.querySelector('.form-files-remove') || input).focus();
                });
                list.appendChild(item);
            });
            if (!message && total > maxTotal) { message = box.getAttribute('data-msg-total'); }
            input.setCustomValidity(message);
            error.textContent = message;
            error.classList.toggle('d-none', message === '');
        }

        // დუბლიკატი (სახელი + ზომა) არ ემატება; ლიმიტზე მეტი — შეცდომა, ზედმეტი არ ემატება
        function add(newFiles) {
            var skipped = false;
            Array.prototype.forEach.call(newFiles, function (file) {
                var duplicate = files.some(function (f) { return f.name === file.name && f.size === file.size; });
                if (duplicate) { return; }
                if (files.length < maxFiles) { files.push(file); } else { skipped = true; }
            });
            sync();
            if (skipped && !input.validationMessage) {
                error.textContent = box.getAttribute('data-msg-too-many');
                error.classList.remove('d-none');
            }
        }

        input.addEventListener('change', function () { add(Array.prototype.slice.call(input.files)); });
        ['dragenter', 'dragover'].forEach(function (type) {
            drop.addEventListener(type, function (event) { event.preventDefault(); drop.classList.add('is-dragover'); });
        });
        ['dragleave', 'drop'].forEach(function (type) {
            drop.addEventListener(type, function () { drop.classList.remove('is-dragover'); });
        });
        drop.addEventListener('drop', function (event) {
            event.preventDefault();
            if (!input.closest('fieldset:disabled')) { add(event.dataTransfer.files); }
        });
        // ფორმა ფაილების შეცდომით არ იგზავნება (პაკეტის ფორმა; ბრიფს — ნაბიჯების ვალიდაცია)
        input.form.addEventListener('submit', function (event) {
            if (!input.disabled && input.validationMessage) {
                event.preventDefault();
                input.focus();
            }
        });
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
