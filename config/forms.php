<?php
declare(strict_types=1);

/*
 * საჯარო ფორმები. გაგზავნა: POST /{lang}/forms/{form} → Inbox (ადმინი). Email არ იგზავნება (D7).
 * ველი: rules — Core\Support\Validator-ის წესები, label — UI თარგმანის გასაღები (t()), multiline — Inbox-ში აბზაცებით,
 *       options — არჩევანი [მნიშვნელობა => თარგმანის გასაღები] (ფორმაში ღილაკებად, Inbox-ში წარწერით; rules-ში in: ავტომატურად ემატება).
 * one_of — ველები, რომელთაგან ერთი მაინც უნდა შეივსოს (მაგ. ელ-ფოსტა ან ტელეფონი); შეცდომა ჩანს პირველ ველზე.
 * ახალი ფორმა: ჩანაწერი აქ + ბლოკის view (ველების სახელები იგივე).
 */
$phone = ['max:50', 'regex:/^[0-9+()\s-]{5,}$/'];
$service = ['label' => 'form.service', 'options' => [
    'website'  => 'form.service_website',
    'shop'     => 'form.service_shop',
    'branding' => 'form.service_branding',
    'seo'      => 'form.service_seo',
    'other'    => 'form.service_other',
]];

return [
    'contact' => [
        'label'  => 'კონტაქტი',
        'icon'   => 'fa fa-envelope',
        'one_of' => ['email', 'phone'],
        'fields' => [
            'name'    => ['rules' => 'required|max:100', 'label' => 'form.name'],
            'email'   => ['rules' => 'email|max:190', 'label' => 'form.email'],
            'phone'   => ['rules' => $phone, 'label' => 'form.phone'],
            'service' => ['rules' => ''] + $service,
            'message' => ['rules' => 'required|max:5000', 'label' => 'form.message', 'multiline' => true],
        ],
    ],
    'application' => [
        'label'  => 'შეთავაზების მოთხოვნა',
        'icon'   => 'fa fa-file-signature',
        'one_of' => ['email', 'phone'],
        'fields' => [
            'name'    => ['rules' => 'required|max:100', 'label' => 'form.name'],
            'email'   => ['rules' => 'email|max:190', 'label' => 'form.email'],
            'phone'   => ['rules' => $phone, 'label' => 'form.phone'],
            'service' => ['rules' => ''] + $service,
            'budget'  => ['rules' => '', 'label' => 'form.budget', 'options' => [
                'small'   => 'form.budget_small',
                'medium'  => 'form.budget_medium',
                'large'   => 'form.budget_large',
                'xl'      => 'form.budget_xl',
                'unknown' => 'form.budget_unknown',
            ]],
            'message' => ['rules' => 'required|max:5000', 'label' => 'form.about_you', 'multiline' => true],
        ],
    ],

    // საიტის შეკვეთა (order_form ბლოკი) — POST /{lang}/order (Front\OrderController: დიზაინები და კატეგორიები ბაზიდან,
    // Inbox-ში ინახება მათი სახელები). handler — ზოგადი /forms/{form} ამ ფორმას არ იღებს.
    'order' => [
        'label'   => 'საიტის შეკვეთა',
        'icon'    => 'fa fa-shopping-cart',
        'handler' => 'order',
        'notify'  => true,
        'fields'  => [
            'name'        => ['rules' => 'required|max:100', 'label' => 'order.name'],
            'phone'       => ['rules' => ['required', ...$phone], 'label' => 'order.phone'],
            'email'       => ['rules' => 'required|email|max:190', 'label' => 'order.email'],
            'category'    => ['rules' => '', 'label' => 'order.category'],
            'design'      => ['rules' => '', 'label' => 'order.design_name'],
            'description' => ['rules' => 'required|max:5000', 'label' => 'order.description', 'multiline' => true],
            'has_domain'  => ['rules' => '', 'label' => 'order.has_domain', 'options' => ['yes' => 'order.yes', 'no' => 'order.no']],
            'has_hosting' => ['rules' => '', 'label' => 'order.has_hosting', 'options' => ['yes' => 'order.yes', 'no' => 'order.no']],
        ],
    ],

    // ანტი-სპამი: honeypot ველი (ბოტი ავსებს, ადამიანი ვერ ხედავს) და გაგზავნების ლიმიტი IP-ზე
    'honeypot'   => 'website',
    'rate_limit' => ['max' => 5, 'decay' => 10 * 60],
];
