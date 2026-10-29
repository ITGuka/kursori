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
        'icon'   => 'bx bx-envelope',
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
        'icon'   => 'bx bx-detail',
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

    // საიტის შეკვეთა (order_form ბლოკი) — POST /{lang}/order (Front\OrderController: პაკეტი, დარგი და დიზაინი ბაზიდან,
    // Inbox-ში ინახება მათი სახელები; პაკეტს — ფასიც). handler — ზოგადი /forms/{form} ამ ფორმას არ იღებს.
    'order' => [
        'label'   => 'საიტის შეკვეთა',
        'icon'    => 'bx bx-cart',
        'handler' => 'order',
        'notify'  => true,
        'fields'  => [
            'name'        => ['rules' => 'required|max:100', 'label' => 'order.name'],
            'phone'       => ['rules' => ['required', ...$phone], 'label' => 'order.phone'],
            'email'       => ['rules' => 'required|email|max:190', 'label' => 'order.email'],
            'package'     => ['rules' => '', 'label' => 'order.package_name'],
            'category'    => ['rules' => '', 'label' => 'order.industry'],
            'design'      => ['rules' => '', 'label' => 'order.design_name'],
            'description' => ['rules' => 'required|max:5000', 'label' => 'order.description', 'multiline' => true],
            'has_domain'  => ['rules' => '', 'label' => 'order.has_domain', 'options' => ['yes' => 'order.yes', 'no' => 'order.no']],
            'has_hosting' => ['rules' => '', 'label' => 'order.has_hosting', 'options' => ['yes' => 'order.yes', 'no' => 'order.no']],
            'attachments' => ['type' => 'files', 'rules' => '', 'label' => 'form.attachments'],
        ],
    ],

    // ინდივიდუალური შეკვეთა — იგივე order_form, პაკეტი „ინდივიდუალური“ (package = custom): 5-ნაბიჯიანი ბრიფი
    // (views/front/partials/custom-order-wizard). handler — Front\OrderController → App\Services\CustomOrder.
    // ერთი წყარო ფორმისთვის, ვალიდაციისთვის, Inbox-ისა და წერილებისთვის:
    //   step — ბრიფის ნაბიჯი (steps; Inbox-ში და წერილში ჯგუფდება); for — ველი ეხება მხოლოდ ამ პროექტებს:
    //   ნიშნები „მომსახურება“ ან „მომსახურება:ქვეტიპი“ (ქვეტიპი — sub_keys-ის ველი, არ არის არჩეული → „-“); შეუსაბამო ველი არ ინახება.
    //   multiple — რამდენიმე მნიშვნელობა (name[]); type: budget | timeline (profiles-დან), links (ბმულები), file (storage/orders).
    'custom' => [
        'label'      => 'ინდივიდუალური შეკვეთა',
        'icon'       => 'bx bx-bulb',
        'handler'    => 'order',
        'notify'     => true,
        'confirm'    => true,   // კლიენტს — დადასტურების წერილი ბრიფის ასლით
        'hide_empty' => true,   // Inbox-ში და წერილში ცარიელი ველები არ ჩანს
        'steps'      => [
            'service' => 'custom.step_service',
            'scope'   => 'custom.step_scope_title',
            'budget'  => 'custom.step_budget',
            'assets'  => 'custom.step_assets_title',
            'contact' => 'custom.step_contact_title',
        ],
        'sub_keys'   => ['web' => 'site_type', 'design' => 'design_scope', 'support' => 'support_frequency'],
        // ბიუჯეტი და ვადა პროექტის მიხედვით: ნიშნები => [ranges — დიაპაზონები ₾ (0 — „-მდე“, null — „-დან“), monthly — თვეში,
        // timeline — სწრაფი/სტანდარტული ვარიანტის ხანგრძლივობა [weeks|months, დიაპაზონი] ან 'start' („როდის დავიწყოთ“)]
        'profiles'   => [
            'web:- web:corporate' => ['ranges' => [[1500, 3000], [3000, 7000], [7000, null]], 'monthly' => false, 'timeline' => [['weeks', '2–3'], ['months', '1–2']]],
            'web:landing'         => ['ranges' => [[0, 1500], [1500, 3000], [3000, null]], 'monthly' => false, 'timeline' => [['weeks', '1–2'], ['weeks', '3–4']]],
            'web:shop'            => ['ranges' => [[3000, 7000], [7000, 15000], [15000, null]], 'monthly' => false, 'timeline' => [['weeks', '4–6'], ['months', '2–3']]],
            'web:app'             => ['ranges' => [[7000, 15000], [15000, 30000], [30000, null]], 'monthly' => false, 'timeline' => [['months', '1–2'], ['months', '3–6']]],
            'design'              => ['ranges' => [[0, 2500], [2500, 6000], [6000, null]], 'monthly' => false, 'timeline' => [['weeks', '1–2'], ['weeks', '3–6']]],
            'branding'            => ['ranges' => [[0, 1500], [1500, 4000], [4000, null]], 'monthly' => false, 'timeline' => [['weeks', '1–2'], ['weeks', '3–4']]],
            'seo'                 => ['ranges' => [[0, 800], [800, 2000], [2000, null]], 'monthly' => true, 'timeline' => 'start'],
            'support:- support:monthly' => ['ranges' => [[0, 300], [300, 700], [700, null]], 'monthly' => true, 'timeline' => 'start'],
            'support:once'        => ['ranges' => [[0, 500], [500, 1500], [1500, null]], 'monthly' => false, 'timeline' => 'start'],
            'other'               => ['ranges' => [[0, 1500], [1500, 5000], [5000, null]], 'monthly' => false, 'timeline' => [['weeks', '2–3'], ['months', '1–2']]],
        ],
        'fields'     => [
            'service'             => ['step' => 'service', 'rules' => 'required', 'label' => 'custom.step_service', 'options' => [
                'web' => 'custom.service_web', 'design' => 'custom.service_design', 'branding' => 'custom.service_branding',
                'seo' => 'custom.service_seo', 'support' => 'custom.service_support', 'other' => 'custom.service_other',
            ]],

            'other_service'       => ['step' => 'scope', 'for' => 'other', 'rules' => 'required|max:200', 'label' => 'custom.other_service'],
            'site_type'           => ['step' => 'scope', 'for' => 'web', 'rules' => '', 'label' => 'custom.site_type', 'options' => [
                'landing' => 'custom.type_landing', 'corporate' => 'custom.type_corporate', 'shop' => 'custom.type_shop', 'app' => 'custom.type_app',
            ]],
            'pages'               => ['step' => 'scope', 'for' => 'web', 'rules' => '', 'label' => 'custom.pages', 'options' => [
                '1-3' => 'custom.pages_small', '4-10' => 'custom.pages_medium', '10+' => 'custom.pages_large',
            ]],
            'features'            => ['step' => 'scope', 'for' => 'web', 'multiple' => true, 'rules' => '', 'label' => 'custom.features', 'options' => [
                'cms' => 'custom.feature_cms', 'languages' => 'custom.feature_languages', 'payments' => 'custom.feature_payments',
                'animations' => 'custom.feature_animations', 'api' => 'custom.feature_api',
            ]],
            'design_scope'        => ['step' => 'scope', 'for' => 'design', 'rules' => '', 'label' => 'custom.design_scope', 'options' => [
                'new' => 'custom.design_new', 'redesign' => 'custom.design_redesign',
            ]],
            'design_platform'     => ['step' => 'scope', 'for' => 'design', 'rules' => '', 'label' => 'custom.design_platform', 'options' => [
                'website' => 'custom.platform_website', 'webapp' => 'custom.platform_webapp', 'mobile' => 'custom.platform_mobile',
            ]],
            'screens'             => ['step' => 'scope', 'for' => 'design', 'rules' => '', 'label' => 'custom.screens', 'options' => [
                '1-5' => 'custom.screens_small', '6-15' => 'custom.screens_medium', '15+' => 'custom.screens_large',
            ]],
            'design_deliverables' => ['step' => 'scope', 'for' => 'design', 'multiple' => true, 'rules' => '', 'label' => 'custom.deliverables', 'options' => [
                'wireframes' => 'custom.deliverable_wireframes', 'prototype' => 'custom.deliverable_prototype',
                'design_system' => 'custom.deliverable_design_system', 'handoff' => 'custom.deliverable_handoff',
            ]],
            'brand_stage'         => ['step' => 'scope', 'for' => 'branding', 'rules' => '', 'label' => 'custom.brand_stage', 'options' => [
                'new' => 'custom.brand_new', 'rebrand' => 'custom.brand_rebrand',
            ]],
            'brand_items'         => ['step' => 'scope', 'for' => 'branding', 'multiple' => true, 'rules' => '', 'label' => 'custom.brand_items', 'options' => [
                'logo' => 'custom.brand_logo', 'identity' => 'custom.brand_identity', 'brandbook' => 'custom.brand_brandbook',
                'print' => 'custom.brand_print', 'social' => 'custom.brand_social',
            ]],
            'seo_goals'           => ['step' => 'scope', 'for' => 'seo', 'multiple' => true, 'rules' => '', 'label' => 'custom.seo_goals', 'options' => [
                'audit' => 'custom.seo_audit', 'speed' => 'custom.seo_speed', 'keywords' => 'custom.seo_keywords',
                'local' => 'custom.seo_local', 'links' => 'custom.seo_links',
            ]],
            'seo_platform'        => ['step' => 'scope', 'for' => 'seo', 'rules' => '', 'label' => 'custom.site_platform', 'options' => $platforms = [
                'wordpress' => 'custom.platform_wordpress', 'shopify' => 'custom.platform_shopify',
                'custom' => 'custom.platform_custom', 'unknown' => 'custom.platform_unknown',
            ]],
            'support_items'       => ['step' => 'scope', 'for' => 'support', 'multiple' => true, 'rules' => '', 'label' => 'custom.support_items', 'options' => [
                'updates' => 'custom.support_updates', 'backups' => 'custom.support_backups', 'content' => 'custom.support_content',
                'hosting' => 'custom.support_hosting', 'bugs' => 'custom.support_bugs',
            ]],
            'support_frequency'   => ['step' => 'scope', 'for' => 'support', 'rules' => '', 'label' => 'custom.support_frequency', 'options' => [
                'once' => 'custom.support_once', 'monthly' => 'custom.support_monthly',
            ]],
            'support_platform'    => ['step' => 'scope', 'for' => 'support', 'rules' => '', 'label' => 'custom.site_platform', 'options' => $platforms],

            'budget'              => ['step' => 'budget', 'type' => 'budget', 'rules' => 'required', 'label' => 'custom.budget'],
            'timeline'            => ['step' => 'budget', 'type' => 'timeline', 'rules' => 'required', 'label' => 'custom.timeline'],

            'has_brandbook'       => ['step' => 'assets', 'for' => 'web design', 'rules' => '', 'label' => 'custom.has_brandbook', 'options' => $yesNo = ['yes' => 'order.yes', 'no' => 'order.no']],
            'content_ready'       => ['step' => 'assets', 'for' => 'web seo', 'rules' => '', 'label' => 'custom.content_ready', 'options' => [
                'yes' => 'custom.content_yes', 'progress' => 'custom.content_progress', 'help' => 'custom.content_help',
            ]],
            'has_photos'          => ['step' => 'assets', 'for' => 'web:landing web:corporate', 'rules' => '', 'label' => 'custom.has_photos', 'options' => $yesNo],
            'has_catalog'         => ['step' => 'assets', 'for' => 'web:shop', 'rules' => '', 'label' => 'custom.has_catalog', 'options' => $yesPartialNo = [
                'yes' => 'order.yes', 'partial' => 'custom.access_partial', 'no' => 'order.no',
            ]],
            'products'            => ['step' => 'assets', 'for' => 'web:shop', 'rules' => '', 'label' => 'custom.products', 'options' => [
                '50' => 'custom.products_small', '500' => 'custom.products_medium', '500+' => 'custom.products_large',
            ]],
            'has_spec'            => ['step' => 'assets', 'for' => 'web:app', 'rules' => '', 'label' => 'custom.has_spec', 'options' => $yesPartialNo],
            'has_wireframes'      => ['step' => 'assets', 'for' => 'design', 'rules' => '', 'label' => 'custom.has_wireframes', 'options' => $yesNo],
            'has_analytics_data'  => ['step' => 'assets', 'for' => 'design:redesign', 'rules' => '', 'label' => 'custom.has_ux_data', 'options' => $yesNo],
            'has_analytics'       => ['step' => 'assets', 'for' => 'seo', 'rules' => '', 'label' => 'custom.has_analytics', 'options' => [
                'yes' => 'order.yes', 'no' => 'order.no', 'unknown' => 'custom.dont_know',
            ]],
            'has_logo'            => ['step' => 'assets', 'for' => 'branding', 'rules' => '', 'label' => 'custom.has_logo', 'options' => $yesNo],
            'has_name'            => ['step' => 'assets', 'for' => 'branding', 'rules' => '', 'label' => 'custom.has_name', 'options' => $yesNo],
            'brand_style'         => ['step' => 'assets', 'for' => 'branding', 'multiple' => true, 'rules' => '', 'label' => 'custom.brand_style', 'options' => [
                'minimal' => 'custom.style_minimal', 'classic' => 'custom.style_classic', 'playful' => 'custom.style_playful',
                'premium' => 'custom.style_premium', 'bold' => 'custom.style_bold',
            ]],
            'has_access'          => ['step' => 'assets', 'for' => 'support', 'rules' => '', 'label' => 'custom.has_access', 'options' => $yesPartialNo],
            // ბმულები: მაქს. max ცალი, http/www-ის გარეშეც; required_for — ამ პროექტებზე ერთი მაინც სავალდებულოა
            'references'          => ['step' => 'assets', 'type' => 'links', 'rules' => '', 'label' => 'custom.reference_label', 'max' => 5,
                                      'required_for' => 'seo support design:redesign'],
            'description'         => ['step' => 'assets', 'rules' => 'required|max:5000', 'label' => 'custom.description', 'multiline' => true],
            'attachments'         => ['step' => 'assets', 'type' => 'files', 'rules' => '', 'label' => 'form.attachments'],

            'name'                => ['step' => 'contact', 'rules' => 'required|max:100', 'label' => 'custom.name'],
            'email'               => ['step' => 'contact', 'rules' => 'required|email|max:190', 'label' => 'order.email'],
            // ზარით ან WhatsApp-ით დაკავშირება → ტელეფონი სავალდებულოა
            'phone'               => ['step' => 'contact', 'rules' => $phone, 'label' => 'order.phone', 'required_if' => ['contact_method' => ['phone', 'whatsapp']]],
            'contact_method'      => ['step' => 'contact', 'rules' => '', 'label' => 'custom.contact_method', 'options' => [
                'any' => 'custom.method_any', 'email' => 'custom.method_email', 'phone' => 'custom.method_phone', 'whatsapp' => 'custom.method_whatsapp',
            ]],
        ],
    ],

    // შეკვეთის ფორმების მიმაგრებული ფაილები (ველი type => files; App\Services\Attachments): storage/orders/YYYY/MM (web root-ის გარეთ),
    // ჩამოტვირთვა — მხოლოდ ადმინიდან. max_total — ერთად (სერვერის post_max_size მასზე მეტი უნდა იყოს); ტიპი მოწმდება შიგთავსით.
    'attachments' => [
        'max_files' => 5,
        'max_size'  => 10 * 1024 * 1024,
        'max_total' => 20 * 1024 * 1024,
        'allowed'   => [
            'application/pdf'    => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'image/jpeg'         => 'jpg',
            'image/png'          => 'png',
            'image/webp'         => 'webp',
            'application/zip'    => 'zip',
        ],
    ],

    // ანტი-სპამი: honeypot ველი (ბოტი ავსებს, ადამიანი ვერ ხედავს) და გაგზავნების ლიმიტი IP-ზე
    'honeypot'   => 'website',
    'rate_limit' => ['max' => 5, 'decay' => 10 * 60],
];
