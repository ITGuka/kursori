<?php
declare(strict_types=1);

return [
    // public/-ის მიმართ; URL: /uploads/2026/10/abc.jpg
    'directory'  => 'uploads',
    'max_size'   => 10 * 1024 * 1024,
    // GD სურათს მეხსიერებაში შლის (~4-5 ბაიტი პიქსელზე): 40 MP ≈ 200 MB
    'max_pixels' => 40_000_000,
    'quality'    => 85,

    // დაშვებული ტიპები: MIME (შიგთავსით) => გაფართოება. SVG განზრახ არ არის — შეიძლება შეიცავდეს JavaScript-ს.
    'allowed' => [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'image/gif'       => 'gif',
        'application/pdf' => 'pdf',
    ],

    // ზომის ვარიანტები: [სიგანე, სიმაღლე, 'fit' (ჩარჩოში) | 'crop' (ზუსტად, ცენტრიდან)]
    'variants' => [
        'thumb'  => [400, 400, 'crop'],
        'medium' => [1024, 1024, 'fit'],
        'large'  => [1920, 1920, 'fit'],
    ],

    'messages' => [
        'no_file'         => 'ფაილი არ აიტვირთა.',
        'too_large'       => 'ფაილი ძალიან დიდია (მაქს. :max MB).',
        'partial'         => 'ფაილი ნაწილობრივ აიტვირთა — სცადეთ თავიდან.',
        'failed'          => 'ფაილის შენახვა ვერ მოხერხდა.',
        'empty'           => 'ფაილი ცარიელია.',
        'type'            => 'ამ ტიპის ფაილი დაუშვებელია. დასაშვებია: JPG, PNG, WebP, GIF, PDF.',
        'invalid_image'   => 'სურათი დაზიანებულია ან მხარდაუჭერელია.',
        'too_many_pixels' => 'სურათის ზომა (პიქსელებში) ძალიან დიდია.',
    ],
];
