<?php
declare(strict_types=1);

/*
 * საჯარო ფორმები. გაგზავნა: POST /{lang}/forms/{form} → Inbox (ადმინი). Email არ იგზავნება (D7).
 * ველი: rules — Core\Support\Validator-ის წესები, label — UI თარგმანის გასაღები (t()), multiline — Inbox-ში აბზაცებით.
 * ახალი ფორმა: ჩანაწერი აქ + ბლოკის view (ველების სახელები იგივე).
 */
return [
    'contact' => [
        'label'  => 'კონტაქტი',
        'icon'   => 'fa fa-envelope',
        'fields' => [
            'name'    => ['rules' => 'required|max:100', 'label' => 'form.name'],
            'email'   => ['rules' => 'required|email|max:190', 'label' => 'form.email'],
            'subject' => ['rules' => 'max:190', 'label' => 'form.subject'],
            'message' => ['rules' => 'required|max:5000', 'label' => 'form.message', 'multiline' => true],
        ],
    ],
    'application' => [
        'label'  => 'განაცხადი',
        'icon'   => 'fa fa-file-signature',
        'fields' => [
            'name'    => ['rules' => 'required|max:100', 'label' => 'form.name'],
            'email'   => ['rules' => 'required|email|max:190', 'label' => 'form.email'],
            'phone'   => ['rules' => ['required', 'max:50', 'regex:/^[0-9+()\s-]{5,}$/'], 'label' => 'form.phone'],
            'message' => ['rules' => 'required|max:5000', 'label' => 'form.about_you', 'multiline' => true],
        ],
    ],

    // ანტი-სპამი: honeypot ველი (ბოტი ავსებს, ადამიანი ვერ ხედავს) და გაგზავნების ლიმიტი IP-ზე
    'honeypot'   => 'website',
    'rate_limit' => ['max' => 5, 'decay' => 10 * 60],
];
