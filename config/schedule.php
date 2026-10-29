<?php
declare(strict_types=1);

/*
 * გრაფიკით გაშვებადი ბრძანებები (schedule:run, D57). ადმინში ჯობი მხოლოდ ამ სიიდან იქმნება — ნებისმიერი ბრძანების
 * გაშვება ადმინიდან შეუძლებელია. args — ოფციების სტრიქონი (ნიმუშით დაშვებული სიმბოლოები), default — ახალი ჯობის მნიშვნელობები.
 *
 * cron (cPanel → Cron Jobs), ყოველ 5 წუთში:
 *   cd ~/kursori.dev && php bin/console schedule:run >/dev/null 2>&1
 */
return [
    // ერთი ჯობის მაქსიმალური ხანგრძლივობა (წუთი); გადაცილებისას პროცესი წყდება, ჟურნალში — failed
    'timeout_minutes' => 30,
    // ჟურნალში (job_runs) ინახება ბოლო N დღე
    'keep_runs_days'  => 30,
    // ოფციების დაშვებული ფორმა: --name=value ან --flag, გამოყოფილი ჰარით (ასევე ციფრული არგუმენტი — მაგ. feed id)
    'args_pattern'    => '/^(\s*(--[a-z][a-z0-9-]*(=[A-Za-z0-9._-]+)?|\d+))*\s*$/',

    // check — App\Services\JobChecks-ის მეთოდი: გაშვებამდე ამოწმებს, არის თუ არა სამუშაო (არა → skipped, პროცესი არ იწყება)
    'commands' => [
        'news:run'        => ['label' => 'სიახლე dev.to-დან (თარგმანით, ბლოგში)', 'check' => 'newsRun', 'default' => ['interval' => 360, 'args' => '']],
        'newsletter:send' => ['label' => 'Newsletter-ის გაგზავნის გაგრძელება', 'check' => 'newsletterSend', 'default' => ['interval' => 10, 'args' => '']],
    ],
];
