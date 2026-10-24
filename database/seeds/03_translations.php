<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Seeder;

// საწყისი UI ტექსტები (Poseify-დან). განმეორებით გაშვებადია და ადმინში შეცვლილ მნიშვნელობებს არ ეხება.
return new class extends Seeder {
    private const LINES = [
        'nav' => [
            'home'     => ['მთავარი', 'Home'],
            'about'    => ['ჩვენ შესახებ', 'About'],
            'services' => ['სერვისები', 'Services'],
            'team'     => ['გუნდი', 'Our Team'],
            'contact'  => ['კონტაქტი', 'Contact'],
            'menu'     => ['მენიუ', 'Menu'],
        ],
        'common' => [
            'read_more'    => ['ვრცლად', 'Read More'],
            'explore_more' => ['გაიგე მეტი', 'Explore More'],
            'back_to_top'  => ['ზემოთ', 'Back to top'],
            'loading'      => ['იტვირთება...', 'Loading...'],
            'previous'     => ['წინა', 'Previous'],
            'next'         => ['შემდეგი', 'Next'],
            'language'     => ['ენა', 'Language'],
        ],
        'contact' => [
            'phone'      => ['ტელეფონი', 'Phone'],
            'email'      => ['ელ-ფოსტა', 'E-mail'],
            'address'    => ['მისამართი', 'Address'],
            'follow_us'  => ['გამოგვყევით', 'Follow Us'],
            'hours'      => ['სამუშაო საათები', 'Working Hours'],
        ],
        'form' => [
            'name'          => ['თქვენი სახელი', 'Your Name'],
            'email'         => ['თქვენი ელ-ფოსტა', 'Your Email'],
            'subject'       => ['თემა', 'Subject'],
            'message'       => ['შეტყობინება', 'Message'],
            'message_hint'  => ['დაწერეთ შეტყობინება', 'Leave a message here'],
            'send'          => ['გაგზავნა', 'Send Message'],
            'sent'          => ['შეტყობინება გაიგზავნა. მალე დაგიკავშირდებით.', 'Your message has been sent. We will get back to you soon.'],
        ],
        'newsletter' => [
            'email'     => ['თქვენი ელ-ფოსტა', 'Your email'],
            'subscribe' => ['გამოწერა', 'Sign Up'],
        ],
        'errors' => [
            'not_found_title' => ['გვერდი ვერ მოიძებნა', 'Page Not Found'],
            'not_found_text'  => ['სამწუხაროდ, ასეთი გვერდი ჩვენს საიტზე არ არსებობს.', 'We’re sorry, the page you have looked for does not exist in our website!'],
            'back_home'       => ['მთავარ გვერდზე დაბრუნება', 'Go Back To Home'],
            'server_title'    => ['დაფიქსირდა შეცდომა', 'Something went wrong'],
            'maintenance'     => ['ტექნიკური სამუშაოები', 'Maintenance'],
        ],
    ];

    public function run(DB $db): void
    {
        $translations = app(Translation::class);
        foreach (self::LINES as $group => $lines) {
            foreach ($lines as $key => [$ka, $en]) {
                $translations->ensure($group, $key, ['ka' => $ka, 'en' => $en]);
            }
        }
        $translations->forgetCache();
    }
};
