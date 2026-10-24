// ბლოკის ფორმა: WYSIWYG (Jodit), repeater (დამატება/წაშლა/გადათრევა), link ველის ტიპი
(function ($) {
    "use strict";

    var joditOptions = {
        height: 260,
        toolbarAdaptive: false,
        askBeforePasteHTML: false,
        askBeforePasteFromWord: false,
        defaultActionOnPaste: 'insert_clear_html',
        buttons: ['paragraph', '|', 'bold', 'italic', 'underline', 'strikethrough', '|', 'ul', 'ol', '|', 'link', '|', 'eraser', 'undo', 'redo', '|', 'source'],
        controls: {
            paragraph: {
                list: {p: 'ტექსტი', h2: 'სათაური 2', h3: 'სათაური 3', h4: 'სათაური 4', blockquote: 'ციტატა'}
            }
        },
        link: {noFollowCheckbox: false, modeClassName: false}
    };

    function initEditors(root) {
        $(root).find('textarea[data-wysiwyg]').each(function () {
            if (!this.dataset.ready) {
                this.dataset.ready = '1';
                Jodit.make(this, joditOptions);
            }
        });
    }

    function initLinks(root) {
        $(root).find('[data-link-input]').each(function () {
            var $input = $(this);
            var update = function () {
                var type = $input.find('[data-link-type]').val();
                $input.find('[data-link-show]').each(function () {
                    $(this).toggleClass('d-none', $(this).data('link-show').split(' ').indexOf(type) === -1);
                });
            };
            $input.find('[data-link-type]').off('change.link').on('change.link', update);
            update();
        });
    }

    function randomId() {
        return Math.random().toString(36).slice(2, 12).replace(/[^a-z0-9]/g, 'x').padEnd(10, '0');
    }

    function renumber($repeater) {
        var $items = $repeater.find('> [data-repeater-items] > [data-repeater-item]');
        $items.each(function (i) { $(this).find('[data-repeater-number]').first().text('#' + (i + 1)); });
        $repeater.find('> [data-repeater-add]').prop('disabled', $items.length >= Number($repeater.data('max')));
    }

    $('[data-repeater]').each(function () {
        var $repeater = $(this);
        var $items = $repeater.find('> [data-repeater-items]');
        var template = $repeater.find('> template[data-repeater-template]').html();
        var counter = $items.children().length;

        $repeater.find('> [data-repeater-add]').on('click', function () {
            // უნიკალური ინდექსი (სერვერი რიგს DOM-ის მიხედვით აწყობს) და ელემენტის id თარგმნადი ველებისთვის
            var html = template.split('__INDEX__').join('n' + (counter++)).split('__ID__').join(randomId());
            var $item = $(html);
            $items.append($item);
            initEditors($item);
            initLinks($item);
            renumber($repeater);
        });

        $items.on('click', '[data-repeater-remove]', function () {
            if (window.confirm('წავშალოთ ეს ელემენტი?')) {
                $(this).closest('[data-repeater-item]').remove();
                renumber($repeater);
            }
        });

        Sortable.create($items[0], {handle: '.repeater-handle', animation: 150, onEnd: function () { renumber($repeater); }});
        renumber($repeater);
    });

    // data-show-if='{"source":"manual"}' — ველი ჩანს მხოლოდ მაშინ, როცა settings[source] = manual
    function updateConditions() {
        $('[data-show-if]').each(function () {
            var rules = $(this).data('show-if');
            var visible = Object.keys(rules).every(function (field) {
                return $('[name="settings[' + field + ']"]').val() === String(rules[field]);
            });
            $(this).toggleClass('d-none', !visible);
        });
    }
    $(document).on('change', '.block-form select[name^="settings["]', updateConditions);
    updateConditions();

    initEditors(document);
    initLinks(document);

    // შეცდომისას — პირველ შეცდომიან ველზე გადასვლა
    var $firstError = $('.block-form .is-invalid, .block-form .invalid-feedback.d-block').first();
    if ($firstError.length) {
        $('html, body').animate({scrollTop: $firstError.offset().top - 120}, 300);
    }
})(jQuery);
