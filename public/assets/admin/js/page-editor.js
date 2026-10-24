// გვერდის რედაქტორი: slug სათაურიდან, ბლოკების რიგის შეცვლა გადათრევით
(function ($) {
    "use strict";

    var translit = {
        'ა': 'a', 'ბ': 'b', 'გ': 'g', 'დ': 'd', 'ე': 'e', 'ვ': 'v', 'ზ': 'z', 'თ': 't', 'ი': 'i', 'კ': 'k', 'ლ': 'l',
        'მ': 'm', 'ნ': 'n', 'ო': 'o', 'პ': 'p', 'ჟ': 'zh', 'რ': 'r', 'ს': 's', 'ტ': 't', 'უ': 'u', 'ფ': 'p', 'ქ': 'k',
        'ღ': 'gh', 'ყ': 'q', 'შ': 'sh', 'ჩ': 'ch', 'ც': 'ts', 'ძ': 'dz', 'წ': 'ts', 'ჭ': 'ch', 'ხ': 'kh', 'ჯ': 'j', 'ჰ': 'h'
    };

    // იგივე წესი, რაც Core\Support\Str::slug-ში (სერვერი საბოლოოდ მაინც ამოწმებს)
    function slugify(text) {
        return text.toLowerCase().split('').map(function (ch) { return translit[ch] !== undefined ? translit[ch] : ch; }).join('')
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 150);
    }

    $('[data-slug-group]').each(function () {
        var $target = $(this).find('[data-slug-target]');
        // ხელით შეცვლილ slug-ს სათაური აღარ ცვლის
        $target.on('input', function () { $target.attr('data-touched', $target.val() === '' ? '0' : '1'); });
        $(this).find('[data-slug-source]').on('input', function () {
            if ($target.attr('data-touched') !== '1') { $target.val(slugify(this.value)); }
        });
    });

    $('[data-block-sort]').each(function () {
        var list = this;
        var url = $(list).data('block-sort');
        Sortable.create(list, {
            handle: '.block-handle',
            animation: 150,
            onEnd: function () {
                var ids = $(list).children('[data-id]').map(function () { return $(this).data('id'); }).get();
                $.ajax({url: url, method: 'POST', contentType: 'application/json', data: JSON.stringify({ids: ids}),
                        headers: {'Accept': 'application/json'}})
                    .fail(function () { alert('რიგის შენახვა ვერ მოხერხდა — განაახლეთ გვერდი.'); });
            }
        });
    });
})(jQuery);
