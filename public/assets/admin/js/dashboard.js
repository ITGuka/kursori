// Dashboard: დიაგრამები (Chart.js), კალენდარი (Tempus Dominus), დავალებების სია
(function ($) {
    "use strict";

    var data = window.DashboardData || {};
    var primary = function (alpha) { return 'rgba(0, 156, 255, ' + alpha + ')'; };

    // შეტყობინებები და გამომწერები დღეების მიხედვით
    var daily = document.getElementById('chart-daily');
    if (daily && data.daily && window.Chart) {
        new Chart(daily, {
            type: 'line',
            data: {
                labels: data.daily.labels,
                datasets: [
                    {label: 'შეტყობინებები', data: data.daily.submissions, backgroundColor: primary(.5), borderColor: primary(1), fill: true, tension: .3, pointRadius: 2},
                    {label: 'გამომწერები', data: data.daily.subscribers, backgroundColor: primary(.2), borderColor: primary(.5), fill: true, tension: .3, pointRadius: 2}
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {mode: 'index', intersect: false},
                scales: {y: {beginAtZero: true, ticks: {precision: 0}}}
            }
        });
    }

    // კონტენტი კოლექციების მიხედვით
    var content = document.getElementById('chart-content');
    if (content && data.content && window.Chart) {
        new Chart(content, {
            type: 'doughnut',
            data: {
                labels: data.content.labels,
                datasets: [{
                    data: data.content.values,
                    backgroundColor: data.content.values.map(function (v, i) { return primary(Math.max(.25, .85 - i * .18)); })
                }]
            },
            options: {responsive: true, maintainAspectRatio: false, plugins: {legend: {position: 'bottom'}}}
        });
    }

    // კალენდარი ქართულად
    if (window.moment && $.fn.datetimepicker && $('#calender').length) {
        moment.defineLocale('ka', {
            months: 'იანვარი_თებერვალი_მარტი_აპრილი_მაისი_ივნისი_ივლისი_აგვისტო_სექტემბერი_ოქტომბერი_ნოემბერი_დეკემბერი'.split('_'),
            monthsShort: 'იან_თებ_მარ_აპრ_მაი_ივნ_ივლ_აგვ_სექ_ოქტ_ნოე_დეკ'.split('_'),
            weekdays: 'კვირა_ორშაბათი_სამშაბათი_ოთხშაბათი_ხუთშაბათი_პარასკევი_შაბათი'.split('_'),
            weekdaysShort: 'კვი_ორშ_სამ_ოთხ_ხუთ_პარ_შაბ'.split('_'),
            weekdaysMin: 'კვ_ორ_სა_ოთ_ხუ_პა_შა'.split('_'),
            longDateFormat: {LT: 'HH:mm', LTS: 'HH:mm:ss', L: 'DD.MM.YYYY', LL: 'D MMMM YYYY', LLL: 'D MMMM YYYY HH:mm', LLLL: 'dddd, D MMMM YYYY HH:mm'},
            week: {dow: 1, doy: 7}
        });
        $('#calender').datetimepicker({inline: true, format: 'L', locale: 'ka'});
    }

    // დავალებები (AJAX; CSRF header — admin.js)
    var $todos = $('[data-todos]');
    if (!$todos.length) {
        return;
    }
    var base = $todos.data('url');
    var $list = $todos.find('[data-todo-list]');
    var $error = $todos.find('[data-todo-error]');
    var template = $todos.find('[data-todo-template]').get(0);
    var refresh = function () {
        $todos.find('[data-todo-empty]').toggleClass('d-none', $list.children().length > 0);
    };
    var fail = function (message) {
        $error.text(message || 'ვერ შეინახა — სცადეთ თავიდან.').removeClass('d-none');
    };
    var markDone = function ($item, done) {
        $item.find('[data-todo-text]').toggleClass('text-decoration-line-through text-muted', done);
        $item.find('[data-todo-delete]').toggleClass('text-primary', done);
    };

    $todos.on('submit', '[data-todo-form]', function (e) {
        e.preventDefault();
        var $input = $(this).find('[name="text"]');
        var text = $.trim($input.val());
        if (text === '') {
            return;
        }
        $error.addClass('d-none');
        $.post(base, {text: text}).done(function (response) {
            if (!response.ok) {
                return fail(response.error);
            }
            var $item = $(template.content.firstElementChild.cloneNode(true));
            $item.attr('data-id', response.id).find('[data-todo-text]').text(response.text);
            $list.prepend($item);
            $input.val('').trigger('focus');
            refresh();
        }).fail(function () { fail(); });
    });

    $todos.on('change', '[data-todo-toggle]', function () {
        var $item = $(this).closest('[data-todo-item]');
        var done = this.checked;
        markDone($item, done);
        $.post(base + '/' + $item.data('id') + '/toggle', {done: done ? '1' : '0'}).fail(function () {
            markDone($item, !done);
            $item.find('[data-todo-toggle]').prop('checked', !done);
            fail();
        });
    });

    $todos.on('click', '[data-todo-delete]', function () {
        var $item = $(this).closest('[data-todo-item]');
        $.post(base + '/' + $item.data('id'), {_method: 'DELETE'}).done(function () {
            $item.remove();
            refresh();
        }).fail(function () { fail(); });
    });
})(jQuery);
