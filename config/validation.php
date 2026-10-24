<?php
declare(strict_types=1);

// ვალიდაციის შეტყობინებები (ადმინის ენა — ქართული). ფრონტის ფორმებისთვის თარგმანები ფაზა 2-ში.
return [
    'messages' => [
        'required'  => 'ველი „:attribute“ სავალდებულოა.',
        'string'    => 'ველი „:attribute“ უნდა იყოს ტექსტი.',
        'email'     => 'ველი „:attribute“ უნდა იყოს სწორი ელ-ფოსტა.',
        'numeric'   => 'ველი „:attribute“ უნდა იყოს რიცხვი.',
        'integer'   => 'ველი „:attribute“ უნდა იყოს მთელი რიცხვი.',
        'boolean'   => 'ველი „:attribute“ არასწორია.',
        'min'       => 'ველი „:attribute“ უნდა შეიცავდეს მინიმუმ :min სიმბოლოს.',
        'max'       => 'ველი „:attribute“ არ უნდა აღემატებოდეს :max სიმბოლოს.',
        'confirmed' => '„:attribute“ და მისი დადასტურება არ ემთხვევა.',
        'same'      => '„:attribute“ და „:other“ არ ემთხვევა.',
        'in'        => 'ველის „:attribute“ მნიშვნელობა არასწორია.',
        'unique'    => '„:attribute“ უკვე გამოყენებულია.',
    ],

    'attributes' => [
        'name'             => 'სახელი',
        'email'            => 'ელ-ფოსტა',
        'password'         => 'პაროლი',
        'current_password' => 'მიმდინარე პაროლი',
    ],
];
