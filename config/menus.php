<?php
declare(strict_types=1);

// მენიუს ადგილები საიტზე. ახალი ადგილი: ჩანაწერი აქ + view-ში menu('location') — მენიუ ადმინში ავტომატურად ჩნდება.
// max_depth: 1 — მხოლოდ ზედა დონე, 2 — ზედა დონე + ჩამოსაშლელი.
return [
    'header' => ['label' => 'მთავარი მენიუ (header)', 'max_depth' => 2],
    'footer' => ['label' => 'Footer-ის მენიუ', 'max_depth' => 1],
];
