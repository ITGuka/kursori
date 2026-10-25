<?php
declare(strict_types=1);

use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBlock;
use Core\Database\DB;
use Core\Database\Seeder;
use Core\Settings\Settings;

// კონფიდენციალურობის პოლიტიკის გვერდი (ka/en) + footer მენიუს პუნქტი + პარამეტრი legal.privacy_page.
// ტექსტი შაბლონია — გაშვებამდე იურისტმა უნდა გადახედოს. გვერდი უკვე არსებობს → არაფერს აკეთებს.
return new class extends Seeder {
    private const SLUGS = ['konfidencialuroba', 'privacy-policy'];

    public function run(DB $db): void
    {
        $settings = app(Settings::class);
        $existing = $db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = ?", [self::SLUGS[1]]);
        if ($existing !== null) {
            $this->remember($settings, (int)$existing);
            return;
        }

        $pageId = app(Page::class)->save(null, ['parent_id' => null, 'status' => 'published', 'is_home' => false, 'sort' => 90, 'og_image_id' => null], [
            'ka' => ['title' => 'კონფიდენციალურობის პოლიტიკა', 'slug' => self::SLUGS[0], 'meta_title' => '',
                     'meta_description' => 'როგორ აგროვებს, იყენებს და იცავს WEBDEV ფორმებიდან და გამოწერიდან მიღებულ პერსონალურ მონაცემებს.'],
            'en' => ['title' => 'Privacy Policy', 'slug' => self::SLUGS[1], 'meta_title' => '',
                     'meta_description' => 'How WEBDEV collects, uses and protects the personal data received through its forms and newsletter.'],
        ]);

        $blocks = app(PageBlock::class);
        $blocks->save(null, $pageId, 'page_header', ['image' => null, 'breadcrumb' => true], ['ka' => ['title' => ''], 'en' => ['title' => '']], []);
        $blocks->save(null, $pageId, 'rich_text', ['align' => 'left', 'background' => 'dark'], [
            'ka' => ['subtitle' => '', 'title' => '', 'body' => self::bodyKa()],
            'en' => ['subtitle' => '', 'title' => '', 'body' => self::bodyEn()],
        ], []);

        $menus = app(Menu::class);
        $menus->saveItem(null, $menus->idFor('footer'), ['type' => 'page', 'page_id' => $pageId, 'url' => '', 'parent_id' => null, 'new_tab' => false, 'is_active' => true], []);

        $this->remember($settings, $pageId);
    }

    /** legal.privacy_page — თუ ცარიელია ან წაშლილ გვერდზე მიუთითებს (მაგ. content:reset-ის შემდეგ) */
    private function remember(Settings $settings, int $pageId): void
    {
        $current = (int)$settings->raw('legal.privacy_page');
        if ($current === 0 || app(Page::class)->find($current) === null) {
            $settings->save(['legal.privacy_page' => (string)$pageId]);
        }
    }

    private static function bodyKa(): string
    {
        return <<<'HTML'
<p><em>ბოლო განახლება: 2026 წლის 25 ოქტომბერი</em></p>
<p>ეს პოლიტიკა აღწერს, როგორ აგროვებს, იყენებს და იცავს WEBDEV (შემდგომში — „ჩვენ“) პერსონალურ მონაცემებს, რომლებსაც საიტის მეშვეობით გვიზიარებთ. მონაცემებს ვამუშავებთ საქართველოს კანონის „პერსონალურ მონაცემთა დაცვის შესახებ“ შესაბამისად, ხოლო ევროკავშირის მომხმარებლების შემთხვევაში — GDPR-ის მოთხოვნების გათვალისწინებით.</p>
<h3>რა მონაცემებს ვაგროვებთ</h3>
<ul>
<li><strong>საკონტაქტო ფორმა:</strong> სახელი, ელ-ფოსტა, თემა და შეტყობინების ტექსტი.</li>
<li><strong>განაცხადის ფორმა:</strong> სახელი, ელ-ფოსტა, ტელეფონი და თქვენ მიერ დაწერილი ინფორმაცია.</li>
<li><strong>სიახლეების გამოწერა:</strong> ელ-ფოსტა და გამოწერის დადასტურების თარიღი.</li>
<li><strong>ტექნიკური მონაცემები:</strong> IP მისამართი, ბრაუზერის ტიპი და გვერდი, საიდანაც ფორმა გაიგზავნა — სპამისა და ბოროტად გამოყენებისგან დასაცავად.</li>
</ul>
<h3>რისთვის ვიყენებთ მონაცემებს</h3>
<ul>
<li>თქვენს მოთხოვნაზე ან განაცხადზე პასუხისთვის და თანამშრომლობის შესათავაზებლად;</li>
<li>სიახლეების გასაგზავნად — მხოლოდ გამოწერის დადასტურების შემდეგ;</li>
<li>საიტის უსაფრთხოებისა და გამართული მუშაობისთვის.</li>
</ul>
<p>მონაცემებს არ ვყიდით და არ გადავცემთ მესამე პირებს მარკეტინგული მიზნით.</p>
<h3>სამართლებრივი საფუძველი</h3>
<p>ფორმის გაგზავნისას მონაცემებს ვამუშავებთ თქვენი მოთხოვნის შესასრულებლად და თქვენი თანხმობით; სიახლეების გამოწერა ეფუძნება თქვენს თანხმობას, რომლის გაუქმებაც ნებისმიერ დროს შეგიძლიათ; ტექნიკურ მონაცემებს — ჩვენი კანონიერი ინტერესით, დავიცვათ საიტი.</p>
<h3>შენახვის ვადა</h3>
<ul>
<li>ფორმებიდან მიღებული შეტყობინებები — მოთხოვნის დამუშავებისთვის საჭირო ვადით, მაგრამ არაუმეტეს 2 წლისა;</li>
<li>გამოწერა — გაუქმებამდე (გამოწერის გაუქმება შესაძლებელია ყოველი წერილის ბოლოს არსებული ბმულით);</li>
<li>ტექნიკური ჩანაწერები — არაუმეტეს 12 თვისა.</li>
</ul>
<h3>ვის შეიძლება გადაეცეს მონაცემები</h3>
<p>მხოლოდ მომსახურების მიმწოდებლებს, რომლებიც საიტის მუშაობისთვის გვჭირდება (ჰოსტინგი, ელ-ფოსტის გაგზავნის სერვისი), და მხოლოდ იმ მოცულობით, რაც ამ მომსახურებისთვისაა აუცილებელი, ან კანონით გათვალისწინებულ შემთხვევაში.</p>
<h3>Cookie-ები</h3>
<p>საიტი იყენებს მხოლოდ აუცილებელ სესიის cookie-ს, რომელიც ფორმების უსაფრთხო გაგზავნისთვისაა საჭირო და ბრაუზერის დახურვისას იშლება. ანალიტიკის ან სარეკლამო cookie-ების დამატების შემთხვევაში, მათ გამოყენებამდე თქვენს თანხმობას ვითხოვთ.</p>
<h3>თქვენი უფლებები</h3>
<p>გაქვთ უფლება მოითხოვოთ თქვენ შესახებ არსებული მონაცემების ნახვა, გასწორება, წაშლა ან დამუშავების შეწყვეტა და გამოიხმოთ თანხმობა. მოთხოვნისთვის მოგვწერეთ ელ-ფოსტაზე, რომელიც მითითებულია კონტაქტის გვერდზე. ასევე შეგიძლიათ მიმართოთ პერსონალურ მონაცემთა დაცვის სამსახურს.</p>
<h3>ცვლილებები</h3>
<p>პოლიტიკის განახლებისას ამ გვერდზე შეიცვლება „ბოლო განახლების“ თარიღი.</p>
HTML;
    }

    private static function bodyEn(): string
    {
        return <<<'HTML'
<p><em>Last updated: 25 October 2026</em></p>
<p>This policy explains how WEBDEV ("we") collects, uses and protects the personal data you share with us through this website. We process data in accordance with the Law of Georgia on Personal Data Protection and, for users in the European Union, the GDPR.</p>
<h3>What we collect</h3>
<ul>
<li><strong>Contact form:</strong> name, email, subject and message.</li>
<li><strong>Application form:</strong> name, email, phone number and the information you write.</li>
<li><strong>Newsletter:</strong> email address and the date you confirmed your subscription.</li>
<li><strong>Technical data:</strong> IP address, browser type and the page a form was sent from — to protect the site from spam and abuse.</li>
</ul>
<h3>How we use it</h3>
<ul>
<li>to reply to your enquiry or application and to offer our services;</li>
<li>to send our newsletter — only after you confirm your subscription;</li>
<li>to keep the website secure and working properly.</li>
</ul>
<p>We do not sell your data or share it with third parties for marketing.</p>
<h3>Legal basis</h3>
<p>Form submissions are processed to respond to your request and with your consent; the newsletter is based on your consent, which you can withdraw at any time; technical data is processed in our legitimate interest of protecting the website.</p>
<h3>How long we keep it</h3>
<ul>
<li>form messages — as long as needed to handle your request, and no longer than 2 years;</li>
<li>newsletter subscription — until you unsubscribe (every email contains an unsubscribe link);</li>
<li>technical records — no longer than 12 months.</li>
</ul>
<h3>Who may receive your data</h3>
<p>Only the service providers we need to run the website (hosting, email delivery), to the extent required for their service, or where required by law.</p>
<h3>Cookies</h3>
<p>The site uses only a strictly necessary session cookie that secures form submissions and is deleted when you close your browser. If we add analytics or advertising cookies, we will ask for your consent before using them.</p>
<h3>Your rights</h3>
<p>You may request access to, correction or deletion of your data, ask us to stop processing it, and withdraw your consent. Write to the email address listed on our contact page. You can also lodge a complaint with the Personal Data Protection Service of Georgia.</p>
<h3>Changes</h3>
<p>When this policy is updated, the "last updated" date on this page will change.</p>
HTML;
    }
};
