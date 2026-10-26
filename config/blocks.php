<?php
declare(strict_types=1);

/*
 * გვერდის ბლოკების ტიპები. ადმინის ფორმა და ვალიდაცია აქედან გენერირდება (App\Blocks\BlockInput),
 * ფრონტზე ბლოკს ხატავს view (მონაცემები — $data, უკვე მიმდინარე ენაზე).
 *
 * ბლოკი: label, icon, view, header (true — გვერდის თავში, navbar-ის ქვეშ; Poseify-ის navbar absolute-ია),
 *        fields
 * ველი:  type         — text | textarea | wysiwyg | image | link | select | number | toggle | repeater | category
 *        label, help, translatable, required, default, options (select), max (სიგრძე), min/max (number),
 *        fields (repeater-ის ქვე-ველები), min_items/max_items (repeater), collection (category),
 *        show_if (['ველი' => 'მნიშვნელობა'] — ველი ჩანს/მოწმდება მხოლოდ მაშინ)
 * link-ის მნიშვნელობა: ['type' => 'page'|'url'|'', 'page_id' => .., 'url' => .., 'new_tab' => bool] → $data-ში href
 *
 * collection — ბლოკი კოლექციიდანაც ივსება (source = collection): type — კოლექცია (config/collections.php),
 *        target — repeater, რომელსაც ჩანაწერები ცვლის, map — repeater-ის ქვე-ველი => ჩანაწერის ველი
 *        (title, image, category, url ან config-ის ველი; '@url' — ჩანაწერის გვერდის ბმული, '@read_more' — „ვრცლად“).
 */

$title = [
    'subtitle' => ['type' => 'text', 'label' => 'ქვესათაური', 'translatable' => true, 'max' => 100],
    'title'    => ['type' => 'text', 'label' => 'სათაური', 'translatable' => true, 'max' => 190],
];
$background = ['type' => 'select', 'label' => 'ფონი', 'default' => 'dark',
               'options' => ['dark' => 'მუქი', 'secondary' => 'ღია მუქი (secondary)']];

// წყარო: ხელით (repeater) ან კოლექციიდან
$collectionLabels = array_map(fn (array $c) => $c['label'], require __DIR__ . '/collections.php');
$source = fn (string $collection, bool $categories = true) => [
    'source' => ['type' => 'select', 'label' => 'წყარო', 'default' => 'manual',
                 'options' => ['manual' => 'ხელით (ქვემოთ)', 'collection' => 'კოლექციიდან (ადმინი → ' . ($collectionLabels[$collection] ?? $collection) . ')']],
] + ($categories ? [
    'category' => ['type' => 'category', 'label' => 'კატეგორია', 'collection' => $collection, 'show_if' => ['source' => 'collection'], 'help' => 'ცარიელი — ყველა.'],
] : []) + [
    'limit'  => ['type' => 'number', 'label' => 'რაოდენობა', 'min' => 0, 'max' => 100, 'default' => 0, 'show_if' => ['source' => 'collection'], 'help' => '0 — ყველა.'],
];
$manual = ['show_if' => ['source' => 'manual']];

return [
    'hero_carousel' => [
        'label'  => 'სლაიდერი (Hero)',
        'icon'   => 'fa fa-images',
        'view'   => 'front/blocks/hero_carousel',
        'header' => true,
        'fields' => [
            'mode'   => ['type' => 'select', 'label' => 'რეჟიმი', 'default' => 'carousel',
                         'options' => ['carousel' => 'სლაიდერი', 'static' => 'სტატიკური (მხოლოდ პირველი სლაიდი)'],
                         'help' => 'სტატიკური — ერთი გზავნილი, ისრებისა და ავტომატური გადართვის გარეშე; დანარჩენი სლაიდები ინახება.'],
            'interval' => ['type' => 'number', 'label' => 'სლაიდის ხანგრძლივობა (წამი)', 'min' => 2, 'max' => 30, 'default' => 5,
                           'show_if' => ['mode' => 'carousel'], 'help' => 'რამდენ წამში ერთხელ გადაირთოს სლაიდი.'],
            'arrows'   => ['type' => 'toggle', 'label' => 'ისრების ჩვენება', 'default' => false, 'show_if' => ['mode' => 'carousel']],
            'slides' =>['type' => 'repeater', 'label' => 'სლაიდები', 'min_items' => 1, 'max_items' => 8, 'fields' => [
                'image'        => ['type' => 'image', 'label' => 'სურათი', 'required' => true, 'help' => 'რეკომენდებული 1920×1080'],
                'subtitle'     => ['type' => 'text', 'label' => 'ქვესათაური', 'translatable' => true, 'max' => 100],
                'title'        => ['type' => 'text', 'label' => 'სათაური', 'translatable' => true, 'max' => 190],
                'text'         => ['type' => 'textarea', 'label' => 'ტექსტი', 'translatable' => true, 'max' => 500],
                'button_label' => ['type' => 'text', 'label' => 'მთავარი ღილაკის ტექსტი', 'translatable' => true, 'max' => 60, 'help' => 'გამოკვეთილი ღილაკი — მაგ. „უფასო კონსულტაცია“.'],
                'button_link'  => ['type' => 'link', 'label' => 'მთავარი ღილაკის ბმული'],
                'button2_label' => ['type' => 'text', 'label' => 'მეორე ღილაკის ტექსტი', 'translatable' => true, 'max' => 60, 'help' => 'კონტურიანი ღილაკი — მაგ. „ნამუშევრები“.'],
                'button2_link'  => ['type' => 'link', 'label' => 'მეორე ღილაკის ბმული'],
            ]],
        ],
    ],

    'page_header' => [
        'label'  => 'გვერდის სათაური',
        'icon'   => 'fa fa-heading',
        'view'   => 'front/blocks/page_header',
        'header' => true,
        'fields' => [
            'title'      => ['type' => 'text', 'label' => 'სათაური', 'translatable' => true, 'max' => 190, 'help' => 'ცარიელის შემთხვევაში — გვერდის სახელი.'],
            'image'      => ['type' => 'image', 'label' => 'ფონის სურათი', 'help' => 'ცარიელის შემთხვევაში — თემის სტანდარტული ფონი.'],
            'breadcrumb' => ['type' => 'toggle', 'label' => 'ნავიგაციის ჯაჭვის ჩვენება', 'default' => true],
        ],
    ],

    'about' => [
        'label'  => 'ჩვენ შესახებ',
        'icon'   => 'fa fa-info-circle',
        'view'   => 'front/blocks/about',
        'fields' => $title + [
            'text'          => ['type' => 'wysiwyg', 'label' => 'ტექსტი', 'translatable' => true],
            'items'         => ['type' => 'repeater', 'label' => 'ჩამონათვალი', 'max_items' => 10, 'fields' => [
                'text' => ['type' => 'text', 'label' => 'პუნქტი', 'translatable' => true, 'max' => 190],
            ]],
            'image'         => ['type' => 'image', 'label' => 'სურათი'],
            'button1_label' => ['type' => 'text', 'label' => 'ღილაკი 1 — ტექსტი', 'translatable' => true, 'max' => 60],
            'button1_link'  => ['type' => 'link', 'label' => 'ღილაკი 1 — ბმული'],
            'button2_label' => ['type' => 'text', 'label' => 'ღილაკი 2 — ტექსტი', 'translatable' => true, 'max' => 60],
            'button2_link'  => ['type' => 'link', 'label' => 'ღილაკი 2 — ბმული'],
            'background'    => ['default' => 'secondary'] + $background,
        ],
    ],

    'services' => [
        'label'  => 'სერვისები',
        'icon'   => 'fa fa-concierge-bell',
        'view'   => 'front/blocks/services',
        'collection' => ['type' => 'services', 'target' => 'items', 'map' => [
            'image' => 'image', 'title' => 'title', 'text' => 'excerpt', 'button_label' => '@read_more', 'button_link' => '@url',
        ]],
        'fields' => $title + $source('services') + [
            'layout' => ['type' => 'select', 'label' => 'განლაგება', 'default' => 'alternating',
                         'options' => ['alternating' => 'მონაცვლე (დიდი წრიული ფოტოებით)', 'grid' => 'ბადე (კომპაქტური ბარათები, 2 სვეტი)']],
            'items' => $manual +['type' => 'repeater', 'label' => 'სერვისები', 'max_items' => 20, 'fields' => [
                'image'        => ['type' => 'image', 'label' => 'სურათი', 'help' => 'კვადრატული, მინ. 500×500'],
                'title'        => ['type' => 'text', 'label' => 'სახელი', 'translatable' => true, 'max' => 190],
                'text'         => ['type' => 'textarea', 'label' => 'აღწერა', 'translatable' => true, 'max' => 1000],
                'button_label' => ['type' => 'text', 'label' => 'ღილაკის ტექსტი', 'translatable' => true, 'max' => 60],
                'button_link'  => ['type' => 'link', 'label' => 'ღილაკის ბმული'],
            ]],
        ],
    ],

    'stats' => [
        'label'  => 'ციფრები (ნდობის ზოლი)',
        'icon'   => 'fa fa-chart-bar',
        'view'   => 'front/blocks/stats',
        'fields' => [
            'items'      => ['type' => 'repeater', 'label' => 'ციფრები', 'min_items' => 1, 'max_items' => 6, 'fields' => [
                'value' => ['type' => 'text', 'label' => 'მნიშვნელობა', 'translatable' => true, 'max' => 20, 'help' => 'მაგ. „10+“, „150+“, „24 სთ“.'],
                'label' => ['type' => 'text', 'label' => 'წარწერა', 'translatable' => true, 'max' => 100],
            ]],
            'background' => ['default' => 'secondary'] + $background,
        ],
    ],

    'process' => [
        'label'  => 'პროცესი (ეტაპები)',
        'icon'   => 'fa fa-list-ol',
        'view'   => 'front/blocks/process',
        'fields' => $title + [
            'steps'      => ['type' => 'repeater', 'label' => 'ეტაპები', 'min_items' => 2, 'max_items' => 8, 'fields' => [
                'title' => ['type' => 'text', 'label' => 'ეტაპი', 'translatable' => true, 'max' => 100],
                'text'  => ['type' => 'textarea', 'label' => 'აღწერა', 'translatable' => true, 'max' => 300],
            ]],
            'background' => $background,
        ],
    ],

    'blog_list' => [
        'label'  => 'ბლოგი (სტატიების სია)',
        'icon'   => 'fa fa-newspaper',
        'view'   => 'front/blocks/blog_list',
        // ყოველთვის კოლექციიდან, უახლესი პირველი (collections.php → blog.order)
        'collection' => ['type' => 'blog', 'target' => 'posts', 'always' => true, 'map' => [
            'image' => 'image', 'title' => 'title', 'category' => 'category', 'excerpt' => 'excerpt', 'date' => 'date', 'body' => 'body', 'link' => '@url',
        ]],
        'fields' => $title + [
            'category'     => ['type' => 'category', 'label' => 'კატეგორია', 'collection' => 'blog', 'help' => 'ცარიელი — ყველა სტატია.'],
            'limit'        => ['type' => 'number', 'label' => 'რაოდენობა', 'min' => 0, 'max' => 100, 'default' => 0, 'help' => '0 — ყველა. მთავარ გვერდზე, მაგ., 3.'],
            'button_label' => ['type' => 'text', 'label' => 'ღილაკის ტექსტი', 'translatable' => true, 'max' => 60, 'help' => 'მაგ. „ყველა სტატია“ — ბლოგის გვერდზე ბმულით.'],
            'button_link'  => ['type' => 'link', 'label' => 'ღილაკის ბმული'],
            'background'   => $background,
        ],
    ],

    'faq' => [
        'label'  => 'ხშირი კითხვები (FAQ)',
        'icon'   => 'fa fa-question-circle',
        'view'   => 'front/blocks/faq',
        'fields' => $title + [
            'items'      => ['type' => 'repeater', 'label' => 'კითხვები', 'min_items' => 1, 'max_items' => 20, 'fields' => [
                'question' => ['type' => 'text', 'label' => 'კითხვა', 'translatable' => true, 'max' => 190],
                'answer'   => ['type' => 'textarea', 'label' => 'პასუხი', 'translatable' => true, 'max' => 1500],
            ]],
            'background' => $background,
        ],
    ],

    'pricing' => [
        'label'  => 'ფასები (პაკეტები)',
        'icon'   => 'fa fa-tags',
        'view'   => 'front/blocks/pricing',
        'fields' => $title + [
            'text'       => ['type' => 'textarea', 'label' => 'ტექსტი', 'translatable' => true, 'max' => 500],
            'packages'   => ['type' => 'repeater', 'label' => 'პაკეტები', 'min_items' => 1, 'max_items' => 6, 'fields' => [
                'name'         => ['type' => 'text', 'label' => 'სახელი', 'translatable' => true, 'max' => 60],
                'price'        => ['type' => 'text', 'label' => 'ფასი', 'translatable' => true, 'max' => 40, 'help' => 'მაგ. „1 500 ₾-დან“.'],
                'note'         => ['type' => 'text', 'label' => 'ფასის მინიშნება', 'translatable' => true, 'max' => 100, 'help' => 'მაგ. „ვადა: 3–4 კვირა“.'],
                'features'     => ['type' => 'textarea', 'label' => 'რა შედის', 'translatable' => true, 'max' => 1500, 'help' => 'თითო პუნქტი ახალ ხაზზე.'],
                'badge'        => ['type' => 'text', 'label' => 'ნიშანი', 'translatable' => true, 'max' => 40, 'help' => 'მაგ. „პოპულარული“ — პაკეტი გამოიკვეთება. ცარიელი — ჩვეულებრივი.'],
                'button_label' => ['type' => 'text', 'label' => 'ღილაკის ტექსტი', 'translatable' => true, 'max' => 60],
                'button_link'  => ['type' => 'link', 'label' => 'ღილაკის ბმული'],
            ]],
            'background' => $background,
        ],
    ],

    'banner_signup' => [
        'label'  => 'ბანერი + გამოწერა',
        'icon'   => 'fa fa-envelope-open-text',
        'view'   => 'front/blocks/banner_signup',
        'fields' => $title + [
            'text'       => ['type' => 'textarea', 'label' => 'ტექსტი', 'translatable' => true, 'max' => 500],
            'background' => ['default' => 'secondary'] + $background,
        ],
    ],

    'team_grid' => [
        'label'  => 'გუნდი',
        'icon'   => 'fa fa-users',
        'view'   => 'front/blocks/team_grid',
        'collection' => ['type' => 'team', 'target' => 'members', 'map' => [
            'image' => 'image', 'name' => 'title', 'position' => 'position', 'attributes' => 'attributes', 'link' => 'link',
        ]],
        'fields' => $title + $source('team') + [
            'members' => $manual + ['type' => 'repeater', 'label' => 'წევრები', 'max_items' => 40, 'fields' => [
                'image'      => ['type' => 'image', 'label' => 'ფოტო', 'help' => 'ვერტიკალური, მაგ. 600×800'],
                'name'       => ['type' => 'text', 'label' => 'სახელი', 'translatable' => true, 'max' => 100],
                'position'   => ['type' => 'text', 'label' => 'პოზიცია', 'translatable' => true, 'max' => 100],
                'attributes' => ['type' => 'textarea', 'label' => 'მახასიათებლები', 'translatable' => true, 'max' => 500,
                                 'help' => 'თითო ხაზზე „დასახელება: მნიშვნელობა“ — ჩანს ფოტოზე მიტანისას. მაგ. „პოზიცია: დიზაინერი“.'],
                'link'       => ['type' => 'link', 'label' => 'ბმული'],
            ]],
        ],
    ],

    'testimonials' => [
        'label'  => 'შეფასებები',
        'icon'   => 'fa fa-quote-right',
        'view'   => 'front/blocks/testimonials',
        'collection' => ['type' => 'testimonials', 'target' => 'items', 'map' => [
            'image' => 'image', 'quote' => 'quote', 'name' => 'title', 'profession' => 'profession',
        ]],
        'fields' => $title + $source('testimonials', false) + [
            'items'      => $manual + ['type' => 'repeater', 'label' => 'შეფასებები', 'min_items' => 1, 'max_items' => 20, 'fields' => [
                'image'      => ['type' => 'image', 'label' => 'ფოტო', 'help' => 'კვადრატული'],
                'quote'      => ['type' => 'textarea', 'label' => 'ტექსტი', 'translatable' => true, 'max' => 1000],
                'name'       => ['type' => 'text', 'label' => 'სახელი', 'translatable' => true, 'max' => 100],
                'profession' => ['type' => 'text', 'label' => 'პროფესია / კომპანია', 'translatable' => true, 'max' => 100],
            ]],
            'background' => ['default' => 'secondary'] + $background,
        ],
    ],

    'contact' => [
        'label'  => 'კონტაქტი',
        'icon'   => 'fa fa-address-card',
        'view'   => 'front/blocks/contact',
        'fields' => $title + [
            'text'      => ['type' => 'wysiwyg', 'label' => 'ტექსტი', 'translatable' => true],
            'show_form' => ['type' => 'toggle', 'label' => 'საკონტაქტო ფორმის ჩვენება', 'default' => true,
                            'help' => 'კონტაქტები (ტელეფონი, ელ-ფოსტა, მისამართი, სოც. ქსელები) — პარამეტრებიდან.'],
        ],
    ],

    'portfolio_grid' => [
        'label'  => 'პორტფოლიო',
        'icon'   => 'fa fa-briefcase',
        'view'   => 'front/blocks/portfolio_grid',
        // ყოველთვის კოლექციიდან (always) — ხელით შევსება ამ ბლოკს არ აქვს
        'collection' => ['type' => 'portfolio', 'target' => 'items', 'always' => true, 'map' => [
            'image' => 'image', 'title' => 'title', 'category' => 'category', 'category_id' => 'category_id', 'excerpt' => 'excerpt', 'link' => '@url',
        ]],
        'fields' => $title + [
            'category'    => ['type' => 'category', 'label' => 'კატეგორია', 'collection' => 'portfolio', 'help' => 'ცარიელი — ყველა პროექტი.'],
            'limit'       => ['type' => 'number', 'label' => 'რაოდენობა', 'min' => 0, 'max' => 100, 'default' => 0, 'help' => '0 — ყველა.'],
            'show_filter' => ['type' => 'toggle', 'label' => 'კატეგორიების ფილტრის ჩვენება', 'default' => true],
            'background'  => $background,
        ],
    ],

    'application_form' => [
        'label'  => 'განაცხადის ფორმა',
        'icon'   => 'fa fa-file-signature',
        'view'   => 'front/blocks/application_form',
        'fields' => $title + [
            'text'         => ['type' => 'wysiwyg', 'label' => 'ტექსტი', 'translatable' => true],
            'message_label' => ['type' => 'text', 'label' => 'ტექსტის ველის წარწერა', 'translatable' => true, 'max' => 100, 'help' => 'ცარიელი — „მოგვიყევით თქვენ შესახებ“. მაგ. „მოგვიყევით პროექტზე“.'],
            'button_label' => ['type' => 'text', 'label' => 'ღილაკის ტექსტი', 'translatable' => true, 'max' => 60, 'help' => 'ცარიელი — „განაცხადის გაგზავნა“.'],
            'background'   => ['default' => 'secondary'] + $background,
        ],
    ],

    'map' => [
        'label'  => 'რუკა',
        'icon'   => 'fa fa-map-marked-alt',
        'view'   => 'front/blocks/map',
        'fields' => [
            'embed_url' => ['type' => 'text', 'label' => 'Google Maps embed ბმული', 'max' => 2000,
                            'help' => 'ცარიელის შემთხვევაში — პარამეტრები → კონტაქტი → რუკა.'],
            'height'    => ['type' => 'number', 'label' => 'სიმაღლე (px)', 'default' => 450, 'min' => 200, 'max' => 900],
        ],
    ],

    'rich_text' => [
        'label'  => 'ტექსტი',
        'icon'   => 'fa fa-align-left',
        'view'   => 'front/blocks/rich_text',
        'fields' => $title + [
            'body'       => ['type' => 'wysiwyg', 'label' => 'ტექსტი', 'translatable' => true],
            'align'      => ['type' => 'select', 'label' => 'სათაურის განლაგება', 'default' => 'left', 'options' => ['left' => 'მარცხნივ', 'center' => 'ცენტრში']],
            'background' => $background,
        ],
    ],
];
