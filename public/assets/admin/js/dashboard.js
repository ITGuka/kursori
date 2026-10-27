// Dashboard: დიაგრამები (ApexCharts, Sneat-ის ფერებით), კალენდარი (Tempus Dominus), დავალებების სია
(function ($) {
    "use strict";

    var data = window.DashboardData || {};
    var colors = (window.config && window.config.colors) || {};
    var font = (window.config && window.config.fontFamily) || 'inherit';

    // შეტყობინებები და გამომწერები დღეების მიხედვით
    var daily = document.getElementById('chart-daily');
    if (daily && data.daily && window.ApexCharts) {
        new ApexCharts(daily, {
            chart: {type: 'area', height: 300, fontFamily: font, toolbar: {show: false}, zoom: {enabled: false}},
            series: [
                {name: 'შეტყობინებები', data: data.daily.submissions},
                {name: 'გამომწერები', data: data.daily.subscribers}
            ],
            xaxis: {
                categories: data.daily.labels,
                tickAmount: 6,
                axisBorder: {show: false},
                axisTicks: {show: false},
                labels: {rotate: 0, hideOverlappingLabels: true, style: {colors: colors.textMuted}}
            },
            yaxis: {min: 0, forceNiceScale: true, labels: {formatter: function (v) { return Math.round(v); }, style: {colors: colors.textMuted}}},
            colors: [colors.primary, colors.info],
            stroke: {curve: 'smooth', width: 2},
            fill: {type: 'gradient', gradient: {shadeIntensity: .6, opacityFrom: .35, opacityTo: .05, stops: [0, 95, 100]}},
            dataLabels: {enabled: false},
            grid: {borderColor: colors.borderColor, strokeDashArray: 6, padding: {top: -10}},
            legend: {position: 'top', horizontalAlign: 'left', labels: {colors: colors.bodyColor}, markers: {offsetX: -3}},
            tooltip: {shared: true, intersect: false}
        }).render();
    }

    // კონტენტი კოლექციების მიხედვით
    var content = document.getElementById('chart-content');
    if (content && data.content && window.ApexCharts) {
        var total = data.content.values.reduce(function (a, b) { return a + b; }, 0);
        new ApexCharts(content, {
            chart: {type: 'donut', height: 300, fontFamily: font},
            series: data.content.values,
            labels: data.content.labels,
            colors: [colors.primary, colors.success, colors.info, colors.warning, colors.danger, colors.secondary],
            stroke: {width: 4, colors: [colors.cardColor]},
            dataLabels: {enabled: false},
            legend: {position: 'bottom', labels: {colors: colors.bodyColor}},
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            value: {fontSize: '22px', fontWeight: 600, color: colors.headingColor, offsetY: -12},
                            name: {offsetY: 18, color: colors.textMuted},
                            total: {show: true, label: 'სულ', color: colors.textMuted, formatter: function () { return total; }}
                        }
                    }
                }
            }
        }).render();
    }

    // კალენდარი ქართულად
    if (window.moment && $.fn.datetimepicker && $('#calender').length) {
        // moment-ის build-ში ka უკვე შეიძლება იყოს — მაშინ defineLocale გაფრთხილებას აგდებს
        (moment.locales().indexOf('ka') === -1 ? moment.defineLocale : moment.updateLocale).call(moment, 'ka', {
            months: 'იანვარი_თებერვალი_მარტი_აპრილი_მაისი_ივნისი_ივლისი_აგვისტო_სექტემბერი_ოქტომბერი_ნოემბერი_დეკემბერი'.split('_'),
            monthsShort: 'იან_თებ_მარ_აპრ_მაი_ივნ_ივლ_აგვ_სექ_ოქტ_ნოე_დეკ'.split('_'),
            weekdays: 'კვირა_ორშაბათი_სამშაბათი_ოთხშაბათი_ხუთშაბათი_პარასკევი_შაბათი'.split('_'),
            weekdaysShort: 'კვი_ორშ_სამ_ოთხ_ხუთ_პარ_შაბ'.split('_'),
            weekdaysMin: 'კვ_ორ_სა_ოთ_ხუ_პა_შა'.split('_'),
            longDateFormat: {LT: 'HH:mm', LTS: 'HH:mm:ss', L: 'DD.MM.YYYY', LL: 'D MMMM YYYY', LLL: 'D MMMM YYYY HH:mm', LLLL: 'dddd, D MMMM YYYY HH:mm'},
            week: {dow: 1, doy: 7}
        });
        // ისრები Boxicons-ით (ნაგულისხმევად Font Awesome-ს ელოდება)
        $('#calender').datetimepicker({
            inline: true, format: 'L', locale: 'ka',
            icons: {previous: 'icon-base bx bx-chevron-left', next: 'icon-base bx bx-chevron-right', today: 'icon-base bx bx-calendar-check', clear: 'icon-base bx bx-trash', close: 'icon-base bx bx-x'}
        });
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
