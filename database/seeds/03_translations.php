<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Seeder;

// საწყისი UI ტექსტები (Kursori-დან). განმეორებით გაშვებადია და ადმინში შეცვლილ მნიშვნელობებს არ ეხება.
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
            'one_of_hint'   => ['საკმარისია ელ-ფოსტა ან ტელეფონი.', 'An email or a phone number is enough.'],
            'reply_time'    => ['გიპასუხებთ 1 სამუშაო დღეში.', 'We reply within 1 business day.'],
            'service'          => ['რა გაინტერესებთ?', 'What do you need?'],
            'service_website'  => ['საიტი', 'Website'],
            'service_shop'     => ['ონლაინ მაღაზია', 'Online store'],
            'service_branding' => ['ბრენდინგი', 'Branding'],
            'service_seo'      => ['SEO და მარკეტინგი', 'SEO & marketing'],
            'service_other'    => ['სხვა', 'Something else'],
            'budget'           => ['სავარაუდო ბიუჯეტი', 'Estimated budget'],
            'budget_small'     => ['3 000 ₾-მდე', 'Up to 3,000 GEL'],
            'budget_medium'    => ['3 000–7 000 ₾', '3,000–7,000 GEL'],
            'budget_large'     => ['7 000–15 000 ₾', '7,000–15,000 GEL'],
            'budget_xl'        => ['15 000 ₾-ზე მეტი', 'Over 15,000 GEL'],
            'budget_unknown'   => ['ჯერ არ ვიცი', 'Not sure yet'],
        ],
        // საიტის შეკვეთის ფორმა (order_form ბლოკი, Inbox, წერილი ადმინს)
        'order' => [
            'name'                => ['სახელი და გვარი', 'Full name'],
            'phone'               => ['ტელეფონის ნომერი', 'Phone number'],
            'email'               => ['ელ-ფოსტა', 'Email'],
            'category'            => ['ბიზნესის კატეგორია', 'Business category'],
            'all_designs'         => ['ყველა დიზაინი', 'All designs'],
            'design'              => ['აირჩიეთ საიტის დიზაინი', 'Choose a website design'],
            'design_name'         => ['დიზაინი', 'Design'],
            'design_required'     => ['აირჩიეთ დიზაინი.', 'Please choose a design.'],
            'no_designs'          => ['დიზაინები მალე დაემატება.', 'Designs are coming soon.'],
            'no_designs_category' => ['ამ კატეგორიაში დიზაინები ჯერ არ არის — აირჩიეთ „ყველა დიზაინი“.', 'No designs in this category yet — choose “All designs”.'],
            'preview'             => ['ნახვა', 'Preview'],
            'design_hint'         => ['დააკლიკეთ დიზაინს სანახავად, მოგეწონებათ — დააჭირეთ „ამ დიზაინის არჩევას“.', 'Click a design to preview it, then press “Choose this design” if you like it.'],
            'click_to_view'       => ['დააკლიკეთ სანახავად', 'Click to preview'],
            'selected'            => ['არჩეული დიზაინი', 'Selected design'],
            'show_more'           => ['მეტის ჩვენება (+:count)', 'Show more (+:count)'],
            'shown_of'            => ['ნაჩვენებია :shown / :total', 'Showing :shown of :total'],
            'description_hint'    => ['მაგ.: რას საქმიანობთ და რას სთავაზობთ კლიენტებს; ვინ არიან თქვენი მომხმარებლები; რა გვერდები გჭირდებათ (მენიუ, ფასები, ჯავშანი, ონლაინ გადახდა...); რა ენებზე; მოგწონთ თუ არა რომელიმე საიტი.', 'E.g.: what your business does and offers; who your customers are; which pages you need (menu, prices, booking, online payment...); which languages; any websites you like.'],
            'choose'              => ['ამ დიზაინის არჩევა', 'Choose this design'],
            'close'               => ['დახურვა', 'Close'],
            'device'              => ['ეკრანის ზომა', 'Screen size'],
            'desktop'             => ['კომპიუტერი', 'Desktop'],
            'tablet'              => ['პლანშეტი', 'Tablet'],
            'mobile'              => ['მობილური', 'Mobile'],
            'description'         => ['ბიზნესის აღწერა', 'Business description'],
            'extras'              => ['დამატებით', 'Additionally'],
            'has_domain'          => ['უკვე მაქვს დომენი', 'I already have a domain'],
            'has_hosting'         => ['უკვე მაქვს ჰოსტინგი', 'I already have hosting'],
            'yes'                 => ['კი', 'Yes'],
            'no'                  => ['არა', 'No'],
            'submit'              => ['შეკვეთის გაგზავნა', 'Send Order'],
            'sent'                => ['შეკვეთა მიღებულია! მალე დაგიკავშირდებით.', 'Your order has been received! We will contact you soon.'],
        ],
        // სერვისის გვერდი (front/collections/service)
        'service' => [
            'price'             => ['ფასი', 'Price'],
            'duration'          => ['ვადა', 'Timeline'],
            'cta'               => ['მოითხოვეთ შეთავაზება', 'Request a Proposal'],
            'included_subtitle' => ['პროექტში', 'In the project'],
            'included'          => ['რა შედის', 'What’s Included'],
            'cases_subtitle'    => ['პორტფოლიო', 'Portfolio'],
            'cases'             => ['ნამუშევრები ამ მიმართულებით', 'Related Work'],
            'faq_subtitle'      => ['კითხვები', 'Questions'],
            'faq'               => ['ხშირად გვეკითხებიან', 'Frequently Asked'],
            'form_subtitle'     => ['ახალი პროექტი', 'New Project'],
            'form_title'        => ['მოითხოვეთ შეთავაზება', 'Request a Proposal'],
            'form_text'         => ['მოგვიყევით იდეაზე — 1 სამუშაო დღეში დაგიკავშირდებით შეთავაზებით.', 'Tell us about your idea — we will get back to you with a proposal within 1 business day.'],
            'form_message'      => ['მოგვიყევით პროექტზე', 'Tell us about the project'],
        ],
        // ბლოგი (blog_list ბლოკი, collections/post)
        'blog' => [
            'min_read'            => [':min წთ საკითხავი', ':min min read'],
            'all_posts'           => ['ყველა სტატია', 'All articles'],
            'cta_text'            => ['ახალ საიტზე ან მაღაზიაზე ფიქრობთ? მოგვიყევით იდეაზე — პირველი კონსულტაცია უფასოა.', 'Thinking about a new site or store? Tell us about your idea — the first consultation is free.'],
            'newsletter_subtitle' => ['სიახლეები', 'Newsletter'],
            'newsletter_title'    => ['მიიღეთ ახალი სტატიები', 'Get New Articles'],
            'newsletter_text'     => ['თვეში ერთხელ — რჩევები ვებ დიზაინზე, ახალი პროექტები და სტუდიის სიახლეები.', 'Once a month — web design tips, new projects and studio news.'],
        ],
        'mobile_cta' => [
            'call' => ['დარეკვა', 'Call'],
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
            'in'       => ['აირჩიეთ ჩამონათვალიდან.', 'Please choose one of the options.'],
            'one_of'   => ['მიუთითეთ ელ-ფოსტა ან ტელეფონი.', 'Please enter an email or a phone number.'],
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
