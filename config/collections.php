<?php
declare(strict_types=1);

/*
 * კოლექციები — ერთგვაროვანი ჩანაწერების სიები (სერვისები, გუნდი, შეფასებები, პორტფოლიო).
 * ყოველ ჩანაწერს აქვს: სათაური და slug (ენებზე), სურათი, კატეგორია (თუ categories = true), რიგი, აქტიურობა
 * + დამატებითი ველები `fields`-ში — იგივე ფორმატით, რაც ბლოკებში (config/blocks.php).
 *
 * label/singular — ადმინში; icon — sidebar-ში; title_label — სათაურის ველის წარწერა;
 * categories — კატეგორიების მხარდაჭერა;
 * public — საჯარო გვერდი ყოველ ჩანაწერს: /{lang}/{route[lang]}/{slug} (route აკლია ენაზე → ძირითადი ენის, შემდეგ გასაღები);
 * view — საჯარო გვერდის შაბლონი (default: front/collections/detail).
 * ახალი კოლექცია = ჩანაწერი აქ (მიგრაცია არ სჭირდება); ადმინის მენიუში ავტომატურად ჩნდება.
 */
return [
    'services' => [
        'label'       => 'სერვისები',
        'singular'    => 'სერვისი',
        'icon'        => 'fa fa-concierge-bell',
        'title_label' => 'სახელი',
        'categories'  => true,
        'public'      => true,
        'route'       => ['ka' => 'servisebi', 'en' => 'services'],
        'fields'      => [
            'excerpt' => ['type' => 'textarea', 'label' => 'მოკლე აღწერა', 'translatable' => true, 'max' => 1000, 'help' => 'ჩანს სიაში (სერვისების ბლოკში).'],
            'body'    => ['type' => 'wysiwyg', 'label' => 'სრული აღწერა', 'translatable' => true, 'help' => 'ჩანს სერვისის გვერდზე.'],
        ],
    ],

    'team' => [
        'label'       => 'გუნდი',
        'singular'    => 'გუნდის წევრი',
        'icon'        => 'fa fa-user-friends',
        'title_label' => 'სახელი',
        'categories'  => true,
        'public'      => false,
        'fields'      => [
            'position'   => ['type' => 'text', 'label' => 'პოზიცია', 'translatable' => true, 'max' => 100],
            'attributes' => ['type' => 'textarea', 'label' => 'მახასიათებლები', 'translatable' => true, 'max' => 500,
                             'help' => 'თითო ხაზზე „დასახელება: მნიშვნელობა“ — ჩანს ფოტოზე მიტანისას.'],
            'link'       => ['type' => 'link', 'label' => 'ბმული (მაგ. პროფილი)'],
        ],
    ],

    'testimonials' => [
        'label'       => 'შეფასებები',
        'singular'    => 'შეფასება',
        'icon'        => 'fa fa-quote-right',
        'title_label' => 'ავტორი',
        'categories'  => false,
        'public'      => false,
        'fields'      => [
            'quote'      => ['type' => 'textarea', 'label' => 'ტექსტი', 'translatable' => true, 'required' => true, 'max' => 1000],
            'profession' => ['type' => 'text', 'label' => 'პროფესია / კომპანია', 'translatable' => true, 'max' => 100],
        ],
    ],

    'portfolio' => [
        'label'       => 'პორტფოლიო',
        'singular'    => 'პროექტი',
        'icon'        => 'fa fa-briefcase',
        'title_label' => 'პროექტის სახელი',
        'categories'  => true,
        'public'      => true,
        'route'       => ['ka' => 'portpolio', 'en' => 'portfolio'],
        'fields'      => [
            'client'  => ['type' => 'text', 'label' => 'კლიენტი', 'translatable' => true, 'max' => 150],
            'year'    => ['type' => 'number', 'label' => 'წელი', 'min' => 1990, 'max' => 2100],
            'website' => ['type' => 'link', 'label' => 'საიტი'],
            'excerpt' => ['type' => 'textarea', 'label' => 'მოკლე აღწერა', 'translatable' => true, 'max' => 1000],
            'body'    => ['type' => 'wysiwyg', 'label' => 'პროექტის აღწერა', 'translatable' => true],
        ],
    ],
];
