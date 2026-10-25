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
            'phone'         => ['ტელეფონი', 'Phone'],
            'about_you'     => ['მოგვიყევით თქვენ შესახებ', 'Tell us about yourself'],
            'apply'         => ['განაცხადის გაგზავნა', 'Submit Application'],
            'too_many'      => ['ძალიან ბევრი მცდელობა. გთხოვთ, სცადოთ რამდენიმე წუთში.', 'Too many attempts. Please try again in a few minutes.'],
            'consent'       => ['გაგზავნით ეთანხმებით თქვენი მონაცემების დამუშავებას :policy შესაბამისად.', 'By submitting, you agree to the processing of your data in line with our :policy.'],
            'consent_link'  => ['კონფიდენციალურობის პოლიტიკის', 'privacy policy'],
        ],
        'portfolio' => [
            'all'     => ['ყველა', 'All'],
            // პროექტის გვერდის მონაცემების ცხრილი (front/collections/detail)
            'client'  => ['კლიენტი', 'Client'],
            'year'    => ['წელი', 'Year'],
            'website' => ['საიტი', 'Website'],
        ],
        'validation' => [
            'required' => ['ველი „:attribute“ სავალდებულოა.', 'The :attribute field is required.'],
            'email'    => ['გთხოვთ, მიუთითოთ სწორი ელ-ფოსტა.', 'Please enter a valid email address.'],
            'max'      => ['ველი „:attribute“ ძალიან გრძელია (მაქს. :max).', 'The :attribute is too long (max :max).'],
            'regex'    => ['ველის „:attribute“ ფორმატი არასწორია.', 'The :attribute format is invalid.'],
        ],
        'newsletter' => [
            'email'                 => ['თქვენი ელ-ფოსტა', 'Your email'],
            'subscribe'             => ['გამოწერა', 'Sign Up'],
            'title'                 => ['გამოწერა', 'Newsletter'],
            'check_email'           => ['თითქმის მზადაა! გამოწერის დასადასტურებლად გადადით ბმულზე, რომელიც ელ-ფოსტაზე გამოგიგზავნეთ.', 'Almost done! Please confirm your subscription using the link we sent to your email.'],
            'error'                 => ['წერილის გაგზავნა ვერ მოხერხდა. გთხოვთ, სცადოთ მოგვიანებით.', 'We could not send the email. Please try again later.'],
            'confirmed'             => ['გამოწერა დადასტურებულია. მადლობა!', 'Your subscription is confirmed. Thank you!'],
            'invalid_link'          => ['ბმული არასწორია ან ვადაგასულია.', 'This link is invalid or has expired.'],
            'unsubscribe_text'      => ['გსურთ :email-ის გამოწერის გაუქმება?', 'Do you want to unsubscribe :email?'],
            'unsubscribe_button'    => ['გამოწერის გაუქმება', 'Unsubscribe'],
            'unsubscribed'          => ['გამოწერა გაუქმებულია. წერილებს აღარ მიიღებთ.', 'You have been unsubscribed and will no longer receive our emails.'],
            'unsubscribe_link'      => ['გამოწერის გაუქმება', 'Unsubscribe'],
            'email_confirm_subject' => [':site — გამოწერის დადასტურება', ':site — confirm your subscription'],
            'email_confirm_text'    => ['მადლობა :site-ის სიახლეების გამოწერისთვის! დასადასტურებლად დააჭირეთ ღილაკს.', 'Thank you for subscribing to :site! Please confirm your subscription.'],
            'email_confirm_button'  => ['გამოწერის დადასტურება', 'Confirm subscription'],
            'email_confirm_ignore'  => ['თუ გამოწერა თქვენ არ გიცდიათ, უბრალოდ უგულებელყავით ეს წერილი.', 'If you did not subscribe, simply ignore this email.'],
            'email_footer'          => ['ამ წერილს იღებთ, რადგან გამოწერილი გაქვთ :site-ის სიახლეები.', 'You receive this email because you subscribed to :site news.'],
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
