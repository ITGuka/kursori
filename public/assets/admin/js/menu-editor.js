// მენიუს რედაქტორი: ჩადგმული drag & drop (max_depth დონემდე), რიგი ინახება AJAX-ით
(function ($) {
    "use strict";

    var $root = $('[data-menu-sort]');
    if (!$root.length) { return; }
    var url = $root.data('menu-sort');
    var maxDepth = Number($root.data('max-depth')) || 1;

    function serialize($list) {
        return $list.children('.menu-node').map(function () {
            var $children = $(this).children('.menu-children');
            return {id: $(this).data('id'), children: $children.length ? serialize($children) : []};
        }).get();
    }

    function save() {
        $.ajax({url: url, method: 'POST', contentType: 'application/json', headers: {'Accept': 'application/json'},
                data: JSON.stringify({tree: serialize($root)})})
            .done(function (json) {
                // სერვერმა ზედმეტად ღრმა პუნქტები აწია — გვერდი განახლდეს
                if (json && json.flattened) { window.location.reload(); }
            })
            .fail(function () { alert('რიგის შენახვა ვერ მოხერხდა — განაახლეთ გვერდი.'); });
    }

    $('.menu-list').each(function () {
        Sortable.create(this, {
            group: 'menu',
            handle: '.block-handle',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            // ქვეპუნქტების მქონე პუნქტი სხვის ქვეშ ვერ ჩაჯდება (სიღრმე > max_depth)
            onMove: function (evt) {
                var targetDepth = Number($(evt.to).data('depth')) || 1;
                var hasChildren = $(evt.dragged).find('.menu-children > .menu-node').length > 0;
                return targetDepth + (hasChildren ? 1 : 0) <= maxDepth;
            },
            onEnd: save
        });
    });
})(jQuery);
