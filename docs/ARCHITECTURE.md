# KURSORI — არქიტექტურა

> ცოცხალი დოკუმენტი: ახლდება ყოველი ფაზის ან არქიტექტურული გადაწყვეტილების შემდეგ.
> ბოლო განახლება: 2026-10-28 · **ფაზები 0–10 დასრულდა** · საიტი live-ზეა: https://kursori.dev (CI/CD, D55) · დარჩენილი: §11 „Production-ის ჩამონათვალი“

## 1. მიზანი

მრავალენოვანი საიტი, რომლის ფრონტში **ყველაფერი ადმინიდან იმართება**: გვერდები, ბლოკები, მენიუ, კატეგორიები, თარგმანები, საიტის პარამეტრები, მედია, ფორმები, newsletter.

- **ფრონტი:** Kursori — სტილები და markup უცვლელად რჩება, იცვლება მხოლოდ კონტენტი.
- **ადმინი:** DASHMIN — Bootstrap Admin Template Free.
- **კონტენტი:** ვებ დიზაინ სტუდია (ფაზა 9, D49). კოლექციები და ფორმები **ზოგადია** — სხვა საიტისთვისაც გამოდგება.
- **ფრეიმვორკი:** საკუთარი, მსუბუქი, მრავალჯერადი — სამომავლოდ სხვა პროექტებზეც გამოიყენება.

## 2. გადაწყვეტილებები

| # | თემა | გადაწყვეტილება |
|---|---|---|
| D1 | სტეკი | ლოკალურად PHP 8.3 + MySQL 8.4 (Laragon), production-ში PHP 8.2 + MariaDB (D56); Bootstrap 5 + jQuery (თემფლეითებიდან) |
| D2 | ფრეიმვორკი | საკუთარი MVC: `core/` პროექტისგან დამოუკიდებელია, `app/` — პროექტის კოდი. Composer მხოლოდ autoload-ისა და რამდენიმე ბიბლიოთეკისთვის |
| D3 | ენები | `ka` (ძირითადი) და `en`. URL-ს **ყველა ენაზე** აქვს პრეფიქსი: `/ka/...`, `/en/...`; `/` → 302 `/ka/` |
| D4 | კონტენტის რედაქტირება | ფიქსირებული ბლოკის ტიპები (Kursori-ის სექციები) + WYSIWYG textarea ველებში და ცალკე `rich_text` ბლოკი |
| D5 | WYSIWYG | Jodit (MIT, jQuery-ს არ საჭიროებს); შენახვისას HTML იწმინდება HTMLPurifier-ით |
| D6 | როლები | ამ ეტაპზე ერთი როლი (ადმინი). სქემა ისეა, რომ როლები მოგვიანებით დაემატოს |
| D7 | ფორმები | Contact და განაცხადის ფორმა (ყოფილი Casting) — ფოტოების გარეშე, ინახება მხოლოდ ადმინის Inbox-ში, email არ იგზავნება. გამონაკლისი — შეკვეთის ფორმა (D52): Inbox + წერილი `orders.notify_email`-ზე |
| D8 | Newsletter | გამოწერების შეგროვება + კამპანიის შექმნა და გაგზავნა ადმინიდან (PHPMailer/SMTP, პარტიებად) |
| D9 | გარემო | ლოკალი — `http://kursori.test` (Laragon); production — `https://kursori.dev` (Namecheap cPanel, 2026-10-27). განახლება მხოლოდ CI/CD-ით (D55) |
| D10 | დიზაინის პარამეტრები | ადმინიდან იმართება მხოლოდ კონტენტი; ფერები/ფონტები — CSS-ში |
| D11 | HTML Codex ატრიბუცია | ✅ მოხსნილია (ფაზა 9) ფრონტის და ადმინის footer-იდან — **მოქმედებს მხოლოდ შეძენილი Credit Removal ლიცენზიით** ორივე თემფლეითზე (Kursori, DASHMIN). საიტის გაშვებამდე ლიცენზიები უნდა იყოს ნაყიდი |
| D12 | Front controller | ერთი `public/index.php` ფრონტისა და ადმინისთვის; ადმინი — `/admin` route-ების ჯგუფი |
| D13 | გარემოს პარამეტრები | `.env` (git-ის გარეშე, ასლი `.env.example`) → `config/*.php` → `config('app.name')` |
| D14 | ლოკალური მისამართი | `http://kursori.test` (vhost → `public/`); სარეზერვო `http://localhost/KURSORI/` root `.htaccess`-ით |
| D15 | ადმინის ავტორიზაცია | სესია + `password_hash`; მომხმარებელი ყოველ მოთხოვნაზე ბაზიდან იკითხება; პაროლის შეცვლა ან გათიშვა ხურავს მის ყველა სესიას; 2 სთ უმოქმედობის შემდეგ — გამოსვლა. „დამიმახსოვრე“ — 30 დღე, selector/validator token (`admin_remember_tokens`: ინახება მხოლოდ validator-ის sha256 და პაროლის ანაბეჭდი; cookie HttpOnly, SameSite=Lax, HTTPS-ზე Secure; ყოველ გამოყენებაზე token იცვლება, პაროლის შეცვლა ან logout მას აუქმებს; პარამეტრები `config/auth.php`). პაროლის აღდგენა email-ით — არ არის (აღდგენა: `admin:create`) |
| D16 | Login-ის ლიმიტი | 5 მცდელობა email+IP-ზე და 20 — IP-ზე, 15 წუთში; ზოგადი `rate_limits` ცხრილი (`login_attempts`-ის ნაცვლად), საჯარო ფორმებიც მას გამოიყენებს |
| D17 | ადმინის ენა | ადმინის ინტერფეისი ქართულადაა (ტექსტები view-ებში); თარგმანების სისტემა (ფაზა 2) ფრონტისთვისაა |
| D18 | ენების წყარო | `languages` ცხრილი (ადმინიდან); route-ში `{lang}` = `[a-z]{2}`, აქტიურობას `SetLocale` ამოწმებს. კოდი შექმნის შემდეგ არ იცვლება. ძირითადი ენა ყოველთვის ერთია და აქტიური; მისი წაშლა/გათიშვა აკრძალულია. ენის წაშლა შლის მის UI თარგმანებსა და პარამეტრებს |
| D19 | UI თარგმანები | `translation_keys` (group, key) + `translation_values` (key_id, lang) — ცალკე გასაღებები, რომ ნაკლული თარგმანები ჩანდეს. `t('group.key')`; აკლია → ძირითადი ენა → გასაღები. Seed (`03_translations`) ადმინში შეცვლილ ტექსტებს არ ეხება |
| D20 | პარამეტრები | განსაზღვრებები კოდში (`config/settings.php`: ჯგუფი → ველები, ტიპი, თარგმნადობა, default), მნიშვნელობები ბაზაში (`settings`: setting_key, lang — '' არათარგმნადისთვის). ადმინის ფორმა განსაზღვრებებიდან გენერირდება. ახალი პარამეტრი = ერთი ხაზი კონფიგში, მიგრაციის გარეშე |
| D21 | ქეში | `Core\Support\Cache` — ფაილური (`storage/cache/data`, PHP ფაილები opcache-ისთვის). ენები, თარგმანები (ენაზე), პარამეტრები ქეშირდება ვადის გარეშე და იშლება ადმინში შენახვისას |
| D22 | ტექნიკური რეჟიმი | `general.maintenance_mode` — სტუმრებს 503 (`errors/maintenance`), შესულ ადმინს — საიტი |
| D23 | მედიის შენახვა | `public/uploads/YYYY/MM/{20 hex}.{ext}`; ტიპი — შიგთავსით (finfo), გაფართოება — დაშვებული MIME-ის რუკიდან; სურათი მოწმდება getimagesize-ით, პიქსელების ლიმიტით (40 MP) და GD-ით სრული გახსნით. დაშვებულია JPG, PNG, WebP, GIF, PDF, მაქს. 10 MB. SVG აკრძალულია (JavaScript-ის რისკი) |
| D24 | სურათის ვარიანტები | ატვირთვისას: `thumb` 400×400 (crop), `small` 600, `medium` 1024 და `large` 1920 (fit), `-{variant}` სუფიქსით, თითოეულის WebP ასლით (`media.webp`). პატარა სურათი არ დიდდება; ორიგინალის ტოლი ვარიანტი არ ინახება; GIF (ანიმაცია) და PDF — ვარიანტების გარეშე. ხელახალი კოდირება EXIF/GPS-ს შლის, JPEG-ის ორიენტაცია სწორდება. ფრონტში `picture()` → `<picture>` WebP-ით, srcset/sizes, width/height; არსებული მედიის ხელახლა დამუშავება — `media:regenerate` |
| D25 | მედიაზე მითითება | ყველგან ინახება მედიის **id** (არა ბილიკი): `media_url($id, 'medium')`, `media_alt($id)`. Alt/წარწერა — თითო ენაზე (`media_translations`). გამოყენებული ფაილი არ იშლება (`Media::usages()` — ყოველი ახალი მოდული აქ ემატება) |
| D26 | ატვირთვა ადმინში | ერთი ფაილი თითო AJAX მოთხოვნაზე (რიგრიგობით, პროგრესით, CSRF header-ით), drag & drop; ერთი საერთო picker-ფანჯარა ყველა ადმინ-გვერდზე (`partials/media-input` + `media.js`) |
| D27 | გვერდის URL | ბრტყელი: `/{lang}/{slug}` (slug უნიკალურია ენის ფარგლებში; `[a-z0-9-]`, ქართულიდან ავტომატური ტრანსლიტერაციით — `Str::slug`), მთავარი — `/{lang}`. მშობელი გვერდი URL-ზე არ მოქმედებს — მხოლოდ breadcrumb-სა და მენიუზე. თარგმანი ენაზე არ არის → გვერდი იმ ენაზე 404, ენების გადამრთველი → იმ ენის მთავარი |
| D28 | ბლოკის შენახვა | `page_blocks.settings` — არათარგმნადი ველები და repeater-ის სტრუქტურა (`_id`-ით), `page_block_translations.content` — თარგმნადი ველები ენაზე (repeater: `content[field][_id][sub]`), `media_ids` — გამოყენებული მედია (`JSON_CONTAINS` — წაშლისგან დაცვა). ცარიელი თარგმნადი ველი → ძირითადი ენა → default |
| D29 | Header ბლოკი | Kursori-ის navbar absolute-ია, ამიტომ პირველი ბლოკი თუ `header` ტიპი არ არის (სლაიდერი / გვერდის სათაური), ავტომატურად ემატება გვერდის სათაური |
| D30 | Link ველი | `page` (id → URL მიმდინარე ენაზე ავტომატურად) ან `url` (მხოლოდ http(s), `/...`, `#...`, `mailto:`, `tel:`); ახალ ფანჯარაში → `rel="noopener"` |
| D31 | მონახაზი | `draft` გვერდი სტუმრისთვის 404, შესული ადმინისთვის — წინასწარი ხედი ზოლით და `noindex`-ით. დამალული ბლოკი არ იხატება |
| D32 | ნავიგაცია მენიუს გარეშე | ~~ფაზა 5-მდე~~ ახლა მხოლოდ სარეზერვო: header მენიუში არცერთი პუნქტი არ არის (ახალი საიტი) → ზედა დონის გამოქვეყნებული გვერდები (`Page::navigation`). ჩანაცვლდა D34-ით |
| D34 | მენიუები | ადგილები კოდში (`config/menus.php`: `header` — 2 დონე, `footer` — 1 დონე), პუნქტები ბაზაში. პუნქტი = გვერდი (წარწერა და URL ყველა ენაზე გვერდიდან, წარწერის შეცვლა შეიძლება) ან ბმული (წარწერა ენაზე, აკლია → ძირითადი; ბმული ენაზე — არასავალდებულო). ახალი გვერდი მენიუში ავტომატურად **არ** ემატება. გვერდი ენაზე თარგმნილი/გამოქვეყნებული არ არის ან პუნქტი დამალულია → პუნქტი შვილებით არ ჩანს. გვერდის წაშლა მის პუნქტებს შლის (CASCADE) |
| D35 | მენიუს ქეში | `menus.{location}.{lang}.{base path hash}` — ქეში იშლება პუნქტის, გვერდის (სათაური/slug/სტატუსი) და ენის ცვლილებისას |
| D36 | ბმულების უსაფრთხოება | ერთი წესი ყველგან (`Core\Support\Url::isSafeHref`): http(s), `/ბილიკი` (არა `//host`), `#ღუზა`, `mailto:`, `tel:` |
| D37 | კოლექციები | ტიპები კოდში (`config/collections.php`): label, icon, categories, public, route (URL-ის ნაწილი ენებზე), fields (ბლოკების ფორმატი — იგივე BlockInput/BlockResolver და ადმინის ველები). ჩანაწერი: სათაური + slug (ენებზე), სურათი, კატეგორია, რიგი, აქტიურობა + fields JSON-ში. ახალი კოლექცია = ჩანაწერი კონფიგში (sidebar-ში ავტომატურად), მიგრაციის გარეშე |
| D38 | კატეგორიები | კოლექციის ფარგლებში, 2 დონე (მშობელი — მხოლოდ იმავე კოლექციის ზედა დონე). ფილტრი კატეგორიით ქვეკატეგორიებსაც მოიცავს. წაშლისას ჩანაწერები კატეგორიის გარეშე რჩება (SET NULL) |
| D39 | კოლექციის საჯარო გვერდი | `public` კოლექციები: `/{lang}/{route}/{slug}` (`route` აკლია ენაზე → ძირითადის → გასაღები). Slug უნიკალურია კოლექცია+ენაზე (შეცდომა). არასაჯარო კოლექციაში slug შიდაა — დამთხვევისას სუფიქსი (`-2`). ჩანაწერი ენაზე სათაურის გარეშე → იმ ენაზე არ ჩანს. Breadcrumb/„უკან“ — იმავე slug-ის გვერდზე (მაგ. `/ka/servisebi`), თუ არსებობს |
| D41 | საიდუმლოებები | `APP_KEY` (.env, `php bin/console key:generate`) + libsodium secretbox (`Core\Support\Crypt`). პარამეტრის ტიპი `password` ბაზაში დაშიფრულად ინახება, ფორმაში არ ჩანს; ცარიელი ველი — ძველი რჩება. APP_KEY-ის შეცვლისას SMTP პაროლი თავიდან შესაყვანია |
| D42 | ფორმები / newsletter | §8: ფორმები Inbox-ში (email-ის გარეშე), honeypot + rate limit, double opt-in, პარტიებად გაგზავნა, one-click unsubscribe, ბმულები მხოლოდ APP_URL-იდან |
| D43 | sitemap / robots | `/sitemap.xml` — გამოქვეყნებული გვერდები და საჯარო კოლექციების აქტიური ჩანაწერები, ყოველი აქტიური ენის ვერსია ცალკე `<url>`-ით და `xhtml:link` hreflang/x-default-ით; `/robots.txt` — `Disallow: /admin` + დამატებითი წესები (პარამეტრები → SEO) + Sitemap. „საძიებოსთვის ღია“ გამორთულია → `Disallow: /`, ცარიელი sitemap, noindex. ყველა სრული URL — APP_URL-იდან |
| D44 | გადამისამართებები | `redirects` (from_path → to_url, 301/302) მუშაობს **მხოლოდ 404-ზე** (ცოცხალი გვერდი ყოველთვის იგებს): core `App::onNotFound()` hook. Query string გადაეცემა. გამოქვეყნებული გვერდის / საჯარო ჩანაწერის slug-ის შეცვლისას 301 ავტომატურად იქმნება, ჯაჭვების გარეშე (ძველი ყველა → უახლესი; ახალ მისამართზე არსებული გადამისამართება იშლება) |
| D45 | 404 ჟურნალი | `not_found_log` (GET, /admin-ის გარეშე): ბილიკი, რაოდენობა, Referer; მაქს. 2000 ჩანაწერი (ძველი იშლება). ადმინში — „გადამისამართების შექმნა“ ერთი დაწკაპებით |
| D47 | გვერდის ქეში | სრული HTML სტუმრებისთვის, CSRF-ის ჩანაცვლებით; ადმინის ნებისმიერი ცვლილება მთელ ქეშს შლის (ზუსტ ინვალიდაციას სიმარტივე სჯობს — გვერდი, მენიუ, პარამეტრი, თარგმანი ერთმანეთზეა დამოკიდებული). §10 |
| D48 | უსაფრთხოების header-ები | მსუბუქი CSP (iframe-ში ჩასმა, object, base, ფორმების სხვა დომენზე გაგზავნა) — სკრიპტების CSP-ს ანალიტიკის/CDN კოდი დაარღვევდა. §9 |
| D49 | საწყისი კონტენტი | ვებ დიზაინ სტუდია (ka/en): გვერდები (მთავარი, სერვისები, პორტფოლიო, ფასები, ბლოგი, ჩვენ შესახებ, კონტაქტი, კონფიდენციალურობა, შეკვეთა), 4 სერვისი (ფასი, ვადა, FAQ), გუნდი, შეფასებები, 6 ქეისი, 3 სტატია, სტუდიის პარამეტრები — seed-ები 05–10. სურათები — Unsplash stock (`resources/stock` → მედია, საქაღალდე `demo`). **ქეისები, შეფასებები, მეტრიკები და კლიენტები გამოგონილია** — გაშვებამდე ჩასანაცვლებელია (აუდიტი CNT-01). `php bin/console content:reset --force` — გვერდები/მენიუ/კოლექციები საწყის მდგომარეობაში (მედია, ენები, თარგმანები, პარამეტრები, მომხმარებლები, Inbox, newsletter, დიზაინები არ იშლება) |
| D46 | სტრუქტურირებული მონაცემები | ყოველ გვერდზე JSON-LD `Organization` (სახელი, URL, ლოგო, ელ-ფოსტა, ტელეფონი, სოც. ქსელები — პარამეტრებიდან), `og:url`, `og:locale`, `twitter:card` |
| D40 | ბლოკი კოლექციიდან | services / team_grid / testimonials: `source` = ხელით (repeater) ან კოლექციიდან (+ კატეგორია, რაოდენობა). `config/blocks.php` → `collection.map` ჩანაწერის ველებს repeater-ის ფორმატზე გადაიყვანს (`@url`, `@read_more`). `show_if` — დამოკიდებული ველები ფორმაში იმალება და არ მოწმდება (მონაცემები ინახება — წყაროს დაბრუნებისას რომ არ დაიკარგოს) |
| D50 | ენის გადამრთველი ადმინში | ერთი გადამრთველი layout-ის ზედა ზოლში (ენები + „ყველა“), ჩანს მხოლოდ გვერდზე, სადაც `[data-lang="xx"]` ელემენტია. ენაზე დამოკიდებულ ნაწილს (ველის სვეტი, fieldset) აქვს `data-lang`; დამალვა CSS-ით `html[data-admin-lang]`-ზე (repeater-ის ახალ ელემენტებზეც მუშაობს), არჩევანი `localStorage`-ში, ნაგულისხმევი — ძირითადი ენა. ვალიდაციის შეცდომა დამალულ ენაზე → ღილაკზე წითელი ნიშანი და ავტომატური გადართვა. ახალ ფორმაში საკმარისია `data-lang` ატრიბუტი |
| D33 | შრიფტები | ~~Noto Sans Georgian სარეზერვოდ~~ → self-hosted FiraGO 1.001 (400/600/700, Latin + Mkhedruli subset-ები, ~29 KB) მთელ საიტზე; Google Fonts ფრონტში აღარ არის. სათაურები, მენიუ, ღილაკები და რიცხვითი მნიშვნელობები — „FiraGO Mtavruli“ (Mkhedruli → Mtavruli glyph-ები, ~5 KB) + uppercase: ka-ზე მთავრული, en-ზე დიდი ასოები, DOM-ის ტექსტი უცვლელია (SEO, კოპირება). `.rich-text`-ის სათაურები ჩვეულებრივ რეგისტრშია. `style.css` უცვლელია, ყველა დამატება `site.css`-შია |
| D51 | საერთო ბლოკები | `'shared' => true` (`config/blocks.php`; ახლა `pricing`, `faq`): შენახვისას settings და თარგმანები კოპირდება ამ ტიპის ყველა ბლოკში, ახალი ბლოკი არსებული შიგთავსით იწყება; ადმინის ფორმა ასახელებს გვერდებს, სადაც ცვლილება აისახება |
| D52 | შეკვეთა და დიზაინები | ადმინში დიზაინის ZIP (HTML/CSS/JS) → `TemplateArchive` უსაფრთხოდ ხსნის (whitelist, traversal/ზომის/რაოდენობის ლიმიტები) `public/uploads/designs/{id}-{hash}/`-ში და ბარათის სურათს არქივიდან იღებს (მედიაში). დიზაინი ↔ კატეგორიები many-to-many (`design_category`). `order_form` ბლოკი: კატეგორიის chip-ები, ბარათები „მეტის ჩვენებით“, preview sandbox iframe-ში მოწყობილობის სიგანეებით. `POST /{lang}/order` → ვალიდაცია, honeypot, rate limit → Inbox (`status`: new / in_progress / done) + წერილი `orders.notify_email`-ზე (ცარიელი → კონტაქტის ელფოსტა). `/uploads/designs/`-ს აქვს CSP `sandbox` (ატვირთული JS უცხო origin-ზე მუშაობს). დიზაინის წაშლა შლის ჩანაწერს და საქაღალდეს, ბარათის სურათი მედიაში რჩება |
| D53 | კანონიკური მისამართი | `Core\Http\Middleware\CanonicalUrl`: production-ში 301 `APP_URL`-ის სქემაზე/host-ზე (http → https, www ↔ non-www) + HSTS |
| D54 | ფერები | ერთი ბლოკი `site.css`-ში: `--bs-primary` #E01677 (ვარდისფერი ფონი, თეთრ ტექსტთან 4.6:1), `--site-accent-text` #F0408F (ვარდისფერი ტექსტი მუქზე, 4.8:1), `--site-text-muted` #8C8C8C — WCAG AA. Bootstrap-ის hard-coded ვარდისფერი წესები ცვლადებზე მიუთითებს |
| D55 | Deploy (CI/CD) | GitHub (private `ITGuka/kursori`) → `.github/workflows/ci-cd.yml`: ყოველ push-ზე composer validate/install (`--no-dev`) და `php -l` PHP 8.2-ზე; `main`-ზე — build-ის rsync სერვერზე SSH-ით (`--delete`; გამორიცხულია `.env`, `public/uploads/`, `storage/cache`/`logs`, `.well-known`, `cgi-bin`, `docs/`, `.github/`, `resources/`), `resources/`-ის წაშლა სერვერიდან, `migrate`, `cache:clear`, smoke test (`/ka` → 200). ერთადერთი secret — `SSH_KEY`. Deploy = push `main`-ზე; seed-ები ავტომატურად არ ეშვება |
| D57 | სიახლეების იმპორტი | წყარო — dev.to API (Forem v1, `DevToClient`: 1 წმ პაუზა, 429-ზე ცდა, გასაღები არასავალდებულო). `news_feeds` (ტეგები, გამოსარიცხი ტეგები → ბლოგის კატეგორია, რეჟიმი auto/review, ფილტრები: დღეები, რეაქციები, ყდის სურათი) → `news_items` (unique source + external_id; new → approved → published / rejected / failed). გამოქვეყნება — არსებულ `blog` კოლექციაში: ინგლისური ორიგინალი სრული ტექსტით (`DevToHtml` + HtmlSanitizer, რომელიც ახლა img/pre/code/table-საც ინახავს), ყდა მედიაში (`news`, CDN-იდან JPEG), `source_name`/`source_url` — ატრიბუცია, `isBasedOn`, en ვერსიის canonical ორიგინალზე (ეს ვერსია hreflang-სა და sitemap-ში არ შედის, ენის გადამრთველში რჩება; ბლოგის ბარათზე — წყაროს სახელი). ქართული — Claude API-ით (`ClaudeTranslator`, Anthropic PHP SDK + Guzzle, JSON სქემა, stream; მოდელი, გასაღები, სიგრძის ლიმიტი — პარამეტრები → სიახლეები); თარგმანის შეცდომა გამოქვეყნებას არ აუქმებს. დღიური ლიმიტი `news.daily_limit` (0 — შეზღუდვის გარეშე) |
| D58 | ჯობების გრაფიკი | ერთი cron (`schedule:run` ყოველ 5 წთ-ში) → `scheduled_jobs` (ბრძანება მხოლოდ `config/schedule.php`-ის სიიდან, ოფციები ნიმუშით, ინტერვალი წუთებში, აქტიურობა). თითო ჯობი — ცალკე PHP პროცესი (`proc_open` მასივით, shell-ის გარეშე), lock — `running_since` (ატომური UPDATE; გათიშული პროცესი timeout + 5 წთ-ის შემდეგ თავისუფლდება), timeout 30 წთ, ჟურნალი `job_runs` (გამოსავალი, კოდი, ხანგრძლივობა; 30 დღე). **გაშვებამდე შემოწმება** (`JobChecks`, `config/schedule.php → check`): სამუშაო არ არის (აქტიური feed / დღიური ლიმიტი, დამტკიცებული სიახლე / დღიური ლიმიტი, უთარგმნელი სიახლე / გასაღები, გასაგზავნი კამპანია) → `skipped` + მიზეზი (`last_message`), პროცესისა და ჟურნალის გარეშე; `--job=ID --force` — შემოწმების გარეშე. **ჯაჭვი** (`after_job_id`, ერთი დონე, ადმინიდან): ჯობი ეშვება მშობლის შემდეგ იმავე ტიკში (მშობელი გაეშვა ან გამოტოვდა), ინტერვალი არ მოქმედებს; „ახლავე“ — დამოუკიდებლად. საწყისი ჯობები: news:fetch (6 სთ) → news:publish --limit=3 და news:translate --limit=3 (წამოღების შემდეგ), newsletter:send (10 წთ) |
| D56 | MariaDB-თან თავსებადობა | production-ის ბაზა MariaDB-ა: MySQL-ის სპეციფიკური სინტაქსი არ გამოიყენება (მაგ. `CAST(? AS JSON)` — `JSON_CONTAINS(col, ?)` ტექსტით მუშაობს ორივეზე). Composer: `php >= 8.2` |

## 3. დირექტორიების სტრუქტურა

✅ — უკვე არსებობს, ⏳ — დაგეგმილია.

```
KURSORI/
├── public/                  ← web root (kursori.test → public/)
│   ├── index.php            ✅ ერთადერთი front controller (ფრონტი + /admin)
│   ├── .htaccess            ✅ rewrite → index.php, უსაფრთხოების header-ები
│   ├── assets/front/        ✅ Kursori css/js/lib/img, fonts/ (FiraGO, D33), js/order-form.js
│   ├── assets/admin/        ✅ DASHMIN css/js/lib/img, js/dashboard.js
│   └── uploads/             ✅ მედია (git-ის გარეშე; სკრიპტები აკრძალულია), designs/ — დიზაინების შაბლონები (D52)
├── core/                    ← მრავალჯერადი ფრეიმვორკი (namespace Core\)
│   ├── App.php              ✅ კონტეინერი (autowiring), boot, HTTP დამუშავება, locale
│   ├── helpers.php          ✅ app, config, env, e, url, route, asset, view, redirect, back, abort,
│   │                          csrf_token, csrf_field, old, errors, is_route, auth, locale, t, setting
│   ├── Http/                ✅ Request, Response (with/withErrors/withInput), Router, Route, Middleware,
│   │                          HttpException, Middleware/VerifyCsrf, Middleware/CanonicalUrl (D53)
│   ├── Database/            ✅ DB (PDO), Migration, Migrator, Seeder · ⏳ QueryBuilder (საჭიროებისამებრ)
│   ├── View/                ✅ View, Template (layout, sections, insert)
│   ├── Debug/               ✅ ErrorHandler (debug გვერდი / errors/*.php, ლოგი)
│   ├── Support/             ✅ Env, Config, Session (flash), Csrf, Log, Validator, RateLimiter, Cache,
│   │                          Str (slug, limit), HtmlSanitizer (HTMLPurifier), Url (isSafeHref, resolve), Crypt · ⏳ Arr
│   ├── Console/             ✅ Console, Command, Output, Commands/*
│   ├── Auth/                ✅ Auth
│   ├── I18n/                ✅ Translator (loader-ით — წყარო პროექტი წყვეტს)
│   ├── Settings/            ✅ Settings (განსაზღვრებები კონფიგიდან, მნიშვნელობები ბაზიდან)
│   ├── Media/               ✅ Uploader, ImageResizer (GD), UploadException
│   └── Mail/                ✅ Mailer (PHPMailer, SMTP keep-alive პარტიებისთვის)
├── app/                     ← პროექტის კოდი (namespace App\)
│   ├── Controllers/Front/   ✅ Page, Collection, Form, Order, Newsletter, Seo (sitemap.xml, robots.txt)
│   ├── Controllers/Admin/   ✅ Auth, Dashboard, User, Language, Translation, Settings, Media, Page, Block, Menu,
│   │                          Collection, Category, Design, Inbox, Newsletter, Redirect
│   ├── Middleware/          ✅ PageCache (page_cache), FlushPageCache (flush_page_cache),
│   │                          SetLocale (locale), CheckMaintenance (maintenance), Authenticate (auth),
│   │                          RedirectIfAuthenticated (guest)
│   ├── Models/              ✅ AdminUser, ActivityLog, Language, Translation, Media, Page, PageBlock, Menu,
│   │                          Collection, Category, Design, FormSubmission, Subscriber, Campaign, Redirect,
│   │                          Dashboard (სტატისტიკა, დიაგრამები), Todo (ადმინის პირადი სია)
│   ├── Services/            ✅ Newsletter (დასტურის წერილი, ტესტი, პარტიის გაგზავნა), TemplateArchive (დიზაინის ZIP, D52)
│   ├── helpers.php          ✅ media(), media_url(), media_alt(), picture(), link_attrs(), menu(), link_target(),
│   │                          cta_link(), tel_href(), date_human(), reading_minutes()
│   ├── Console/Commands/    ✅ CreateAdminCommand (admin:create), NewsletterSendCommand (newsletter:send),
│   │                          ContentResetCommand (content:reset), MediaRegenerateCommand (media:regenerate)
│   ├── Blocks/              ✅ BlockRegistry (config/blocks.php), BlockInput (ფორმა → ვალიდაცია → JSON),
│   │                          BlockResolver (JSON → view-ის მონაცემები ენაზე)
│   └── routes/              ✅ admin.php (/admin), web.php (/{lang})
├── bootstrap/app.php        ✅ App-ის აწყობა: middleware, route-ები
├── views/
│   ├── front/               ✅ layouts/main (Kursori), partials/ (navbar, footer, title, faq, form-field, form-choice,
│   │                          form-consent, form-status), page, blocks/* (19 ტიპი, §4),
│   │                          collections/ (detail, service, post), message
│   ├── emails/              ✅ layout, newsletter-confirm, newsletter-campaign, order-admin
│   ├── admin/               ✅ layouts/*, partials/* (flash, pagination, upload-zone, media-input, media-picker),
│   │                          auth/login, dashboard, users/*, languages/*, translations/index, settings/*, media/*,
│   │                          pages/*, blocks/ (form, field — რეკურსიული, link), menus/ (index, item),
│   │                          collections/ (index, form), categories/ (index, form), designs/ (index, form), inbox/ (index, show),
│   │                          newsletter/ (campaigns, form, subscribers, tabs), redirects/ (index — + 404 ჩანართი, form)
│   └── errors/              ✅ default.php, 404.php (Kursori), maintenance.php
├── config/                  ✅ app, database (.env-იდან), auth, validation (ქართული შეტყობინებები), admin (sidebar),
│                              settings (პარამეტრების განსაზღვრებები), media (ტიპები, ლიმიტები, ვარიანტები),
│                              blocks (ბლოკების ტიპები და ველები), menus (მენიუს ადგილები და სიღრმე),
│                              collections (კოლექციების ტიპები და ველები), forms (საჯარო ფორმები, ანტი-სპამი)
├── database/
│   ├── migrations/          ✅ languages, admin_users, rate_limits, activity_log, translation_keys/values, settings,
│   │                          media + media_translations, pages + page_translations + page_blocks + page_block_translations,
│   │                          menus + menu_items + menu_item_translations, categories + category_translations,
│   │                          collection_items + collection_item_translations, form_submissions, subscribers,
│   │                          newsletter_campaigns + newsletter_sends, redirects, not_found_log, admin_todos,
│   │                          media.webp, admin_remember_tokens, designs + design_translations + design_category
│   └── seeds/               ✅ 01_languages (ka, en), 02_admin_user (.env ADMIN_*), 03_translations (Kursori UI ტექსტები),
│                              04_demo_media (resources/stock, საქაღალდე "demo"), 05_demo_pages (სტუდიის გვერდები),
│                              06_menus (header — გვერდების ხე, footer — ზედა დონე),
│                              07_demo_collections (სერვისები, გუნდი, შეფასებები, პორტფოლიო, ბლოგი),
│                              08_site_settings (სტუდიის კონტაქტები, SEO — მხოლოდ ცარიელ პარამეტრებში),
│                              09_privacy_page (კონფიდენციალურობის პოლიტიკა), 10_order_page (შეკვეთის გვერდი, დიზაინის კატეგორიები)
├── resources/               ✅ მხოლოდ ლოკალურად (production-ზე არ იტვირთება, D55): stock/ (seed 04-ის სურათები),
│                              templates/ — poseify/, dashmin/ საწყისი HTML (reference) + zip (git-ის გარეშე)
├── storage/                 ✅ cache/, logs/ (git-ის გარეშე)
├── bin/console              ✅ CLI
├── .env / .env.example      ✅
├── .github/workflows/       ✅ ci-cd.yml — lint/build + deploy kursori.dev-ზე (D55)
├── docs/                    ✅ ARCHITECTURE.md, architecture-map.html (ვიზუალური რუკა), audit/ (2026-10-27)
└── composer.json            ✅ PSR-4: Core\ → core/, App\ → app/; ezyang/htmlpurifier, phpmailer; php >= 8.2
```

Laragon-ის PHP CLI-ში `zip` გაფართოება გამორთულია, ამიტომ Composer-ის ინსტალაცია: `php -d extension=zip C:\laragon\bin\composer\composer.phar install`.

### ფენები და მოთხოვნის გზა

```
public/index.php → Core\App → Router → Middleware (VerifyCsrf, locale | auth/guest)
                → Controller → Model (PDO) → View (layout + blocks) → Response
                                  ↑
                              Cache (გვერდის HTML, ინვალიდაცია შენახვისას)
```

**`core/` წესი:** არ იცის არაფერი კონკრეტულ პროექტზე (ცხრილები, ბლოკები, Kursori). ყველაფერი პროექტ-სპეციფიკური `app/`-შია. ასე `core/` სხვა პროექტში გადააქვს ან მოგვიანებით ცალკე Composer პაკეტად გამოიყოფა.

### ბირთვის გამოყენება (მოკლე ცნობარი)

```php
// route-ები (app/routes/web.php)
$router->get('/', [HomeController::class, 'index'])->name('home');
$router->post('/contact', [FormController::class, 'store'])->name('contact.store');
$router->group(['prefix' => '/{lang}', 'where' => ['lang' => 'ka|en'], 'middleware' => ['locale'], 'name' => 'front.'], fn ($r) => ...);
$router->get('/items/{id}', ...)->where('id', '\d+');
route('home', ['lang' => 'ka']);              // "/ka" (+ base path); ზედმეტი პარამეტრები → query string

// controller: კონსტრუქტორის და მეთოდის კლასის ტიპის პარამეტრები კონტეინერიდან ივსება,
// დანარჩენი — route პარამეტრებიდან სახელით ("int" ტიპი გარდაიქმნება)
public function show(Request $request, string $lang, int $id): Response

// პასუხი: Response | array (→ JSON) | string (→ HTML) | null (→ 204)
return view('front/page', ['page' => $page]);
return redirect(route('home', ['lang' => 'ka']));
abort(404);

// ბაზა
$db->select('SELECT * FROM pages WHERE status = ?', ['published']);
$db->first(...); $db->value(...); $db->column(...);
$id = $db->insert('pages', ['status' => 'draft']);
$db->update('pages', ['status' => 'published'], 'id = ?', [$id]);
$db->transaction(fn (DB $db) => ...);

// შაბლონი (views/*.php; $this — Core\View\Template)
$this->layout('front/layouts/base', ['title' => '...']);
$this->start('scripts'); ?> <script>...</script> <?php $this->stop();
<?= $this->section('content') ?>  <?= $this->insert('front/partials/nav', [...]) ?>  <?= e($value) ?>

// ვალიდაცია და ფორმაში დაბრუნება (Post/Redirect/Get)
$v = Validator::make($data, ['email' => "required|email|max:190|unique:admin_users,email,$id"]);
if ($v->fails()) {
    return back(route('admin.users.edit', ['id' => $id]))->withErrors($v->errors())->withInput(['email' => $data['email']]);
}
return redirect(route('admin.users.index'))->with('success', 'შენახულია.');
// view-ში: old('email', $user['email']), errors('email'), Session::get('success')

// ავტორიზაცია და ლიმიტი
auth()->check(); auth()->user(); auth()->id();
$limiter->tooManyAttempts($key, 5); $limiter->hit($key, 900); $limiter->clear($key);

// თარგმანები და პარამეტრები (მიმდინარე ენაზე, fallback — ძირითადი ენა)
t('nav.contact');  t('form.sent');  t('greeting', ['name' => 'ანა']);   // ტექსტში :name
setting('contact.phone');  setting('general.site_name');  setting('general.maintenance_mode'); // toggle → bool
// ახალი პარამეტრი: config/settings.php-ში ერთი ხაზი → ადმინში ველი ავტომატურად ჩნდება
// ქეში: app(Cache::class)->remember('key', $ttl, fn () => ...); ->forget('key'); ->flush('prefix.')

// მედია: ბაზაში ინახება id; ვარიანტები: original | thumb | medium | large (აკლია → original)
<img src="<?= e(media_url($id, 'medium')) ?>" alt="<?= e(media_alt($id)) ?>" loading="lazy">
// ადმინის ფორმაში მედიის ველი (picker):
<?= $this->insert('admin/partials/media-input', ['name' => 'image_id', 'value' => $id, 'inputId' => 'image']) ?>
// ახალი მოდული, რომელიც მედიას იყენებს → Media::usages()-ში დაამატეთ შემოწმება (წაშლისგან დაცვა)

// ადმინის მოდული: route-ები app/routes/admin.php-ში ('auth' middleware-ით),
// sidebar-ის პუნქტი config/admin.php-ში, ქმედების ჟურნალი — ActivityLog::log('page.updated', 'page', $id)
```

ყველა POST/PUT/PATCH/DELETE მოთხოვნას სჭირდება `<?= csrf_field() ?>` ან `X-CSRF-TOKEN` header (სხვაგვარად 419). PUT/PATCH/DELETE HTML ფორმიდან: `<input type="hidden" name="_method" value="DELETE">`.

### CLI

```
php bin/console list                       # ყველა ბრძანება
php bin/console migrate [--seed]           # მიგრაციები (+ seed-ები)
php bin/console migrate:rollback [--step=N]
php bin/console migrate:status
php bin/console make:migration create_pages_table
php bin/console db:seed [name]             # ყველა ან სახელით ფილტრი
php bin/console cache:clear
php bin/console serve [--port=8000]        # Apache-ს გარეშე
php bin/console admin:create --email=... [--name=...] [--password=...]   # ადმინის შექმნა / პაროლის აღდგენა
php bin/console key:generate [--force]     # APP_KEY (საიდუმლოებების დაშიფვრა)
php bin/console newsletter:send [id] [--batch=50]   # „იგზავნება“ კამპანიების გაგრძელება (cron)
php bin/console content:reset --force      # გვერდები/მენიუ/კოლექციები → საწყისი კონტენტი (D49)
php bin/console media:regenerate [--missing]   # სურათების ვარიანტები და WebP ხელახლა (D24)
php bin/console news:fetch [feed id] [--dry-run]      # სიახლეები dev.to-დან რიგში (D57)
php bin/console news:publish [id] [--limit=N]         # დამტკიცებული სიახლეები ბლოგში + თარგმანი (დღიური ლიმიტით)
php bin/console news:translate [id] [--limit=5]       # უთარგმნელი სიახლეების ქართული ვერსია (Claude)
php bin/console schedule:run [--job=ID] [--list]      # cron-ის ბრძანება: ჯობები გრაფიკით (D58)
```

PHP PATH-ში არ არის — Laragon Terminal-იდან ან სრული გზით: `C:\laragon\bin\php\php-8.3.36-Win32-vs16-x64\php.exe bin\console ...`.

## 4. ბლოკების სისტემა (Page Builder)

გვერდი = ბლოკების დალაგებული სია. ბლოკის ყოველ ტიპს `config/blocks.php`-ში აქვს სქემა (ფორმა, ვალიდაცია და ფრონტის მონაცემები აქედან):

```php
'services' => [
    'label'  => 'სერვისები',
    'icon'   => 'fa fa-concierge-bell',
    'view'   => 'front/blocks/services',   // ხატავს $data-ს (უკვე მიმდინარე ენაზე)
    'header' => false,                     // true — გვერდის თავში, navbar-ის ქვეშ (D29)
    'fields' => [
        'subtitle' => ['type' => 'text', 'label' => 'ქვესათაური', 'translatable' => true, 'max' => 100],
        'items'    => ['type' => 'repeater', 'label' => 'სერვისები', 'max_items' => 20, 'fields' => [
            'image'       => ['type' => 'image', 'label' => 'სურათი'],
            'title'       => ['type' => 'text', 'label' => 'სახელი', 'translatable' => true],
            'button_link' => ['type' => 'link', 'label' => 'ბმული'],
        ]],
    ],
],
```

ველის ტიპები: `text`, `textarea`, `wysiwyg` (Jodit → HTMLPurifier), `image` (მედიის id), `link` (D30), `select`, `number`, `toggle`, `repeater` (ერთი დონე; გადათრევით დალაგება). ვარიანტები: `translatable`, `required` (მხოლოდ ძირითად ენაზე), `default`, `max`, `min`/`max` (number), `min_items`/`max_items`, `help`.
✅ `category` ველი და `show_if`; services/team/testimonials ბლოკები კოლექციიდანაც (D40).

| ბლოკი | Kursori-ის წყარო | შინაარსი |
|---|---|---|
| ✅ `hero_carousel` | index Header carousel | რეჟიმი `static` (პირველი სლაიდი, სიმაღლე შიგთავსით) ან `carousel` (ინტერვალი 2–30 წმ, ისრები); სლაიდები: სურათი, ქვესათაური, სათაური, ტექსტი, 2 ღილაკი (header) |
| ✅ `page_header` | შიდა გვერდების header | სათაური (ცარიელი → გვერდის სახელი), ფონის სურათი, breadcrumb (header) |
| ✅ `about` | About | სათაურები, WYSIWYG ტექსტი, ჩამონათვალი, 2 ღილაკი, სურათი, ფონი |
| ✅ `services` | Service | სერვისები, layout `alternating` (ზიგზაგი) ან `grid` (ორსვეტიანი ბარათები): სურათი, სახელი (ბმულით), აღწერა, ღილაკი |
| ✅ `stats` | — | trust bar: 1–6 „მნიშვნელობა + წარწერა“, ერთი ბარათი გამყოფებით |
| ✅ `process` | — | დანომრილი ნაბიჯები (სათაური + ტექსტი) |
| ✅ `pricing` | — (საერთო, D51) | პაკეტები, გამორჩეულის ნიშნით |
| ✅ `faq` | — (საერთო, D51) | კითხვა-პასუხი (`details`/`summary`) + FAQPage JSON-LD |
| ✅ `blog_list` | — | ბლოგის ბარათები (უახლესი პირველი), რაოდენობა, „ყველა სტატია“ ღილაკი; `per_page` — პაგინაცია `?page=N` (მე-2+ გვერდს საკუთარი canonical და სათაური, hreflang-ის გარეშე; გვერდების რაოდენობაზე მეტი → 404; გვერდის ქეში ამ პარამეტრს იღებს) |
| ✅ `order_form` | — | შეკვეთის ფორმა დიზაინის არჩევით (D52) |
| ✅ `banner_signup` | Banner | სათაური, ტექსტი, newsletter ფორმა (დამუშავება — ფაზა 7) |
| ✅ `team_grid` | Team | წევრები: ფოტო, სახელი, „დასახელება: მნიშვნელობა“ ხაზები (hover-ზე), ბმული |
| ✅ `testimonials` | Testimonial | შეფასებები (owl carousel, ფოტო — dot-ში) |
| ✅ `contact` | Contact | სათაური, ტექსტი, კონტაქტები და სოც. ქსელები პარამეტრებიდან, ფორმა (დამუშავება — ფაზა 7) |
| ✅ `map` | Google Map | embed URL (მხოლოდ google.com/maps/embed) ან პარამეტრებიდან, სიმაღლე |
| ✅ `rich_text` | — | სათაური + თავისუფალი WYSIWYG ტექსტი |
| ✅ `application_form` | — (Contact-ის სტილით) | განაცხადის ფორმა → Inbox (ტექსტის ველის და ღილაკის წარწერა — ბლოკიდან; სტუდიაში: „პროექტის მოთხოვნა“) |
| ✅ `portfolio_grid` | Team-ის ბარათები | პორტფოლიოს კოლექცია (`collection.always`), კატეგორია, რაოდენობა, კატეგორიების ფილტრი გვერდის გადატვირთვის გარეშე |

ახალი ბლოკის დამატება: ჩანაწერი `config/blocks.php`-ში + view `views/front/blocks/{type}.php` — ადმინის ფორმა ავტომატურად ჩნდება.

კოლექციები (`config/collections.php`): `services` (ფასი, ვადა, რა შედის, დაკავშირებული ქეისები, FAQ; შაბლონი `collections/service`), `team`, `testimonials`, `portfolio` (`collections/detail`), `blog` (`/ka/blogi`, `/en/blog`; ამონარიდი, ტექსტი, ავტორი, `order: newest`; შაბლონი `collections/post` — კითხვის დრო, BlogPosting JSON-LD). services / blog / portfolio ჩანაწერებს აქვს `header_image` (გვერდის header-ის ფონი; ცარიელი → თემის ნაგულისხმევი).

ბლოკის მონაცემები: D28. ერთი ბლოკის view-ის შეცდომა production-ში მთელ გვერდს არ აგდებს (ლოგში იწერება, ბლოკი გამოტოვდება).

## 5. ბაზის სქემა

ყველა ცხრილი: `utf8mb4_unicode_ci`, `created_at`/`updated_at`. თარგმნადი ობიექტები — მთავარი ცხრილი + `*_translations` (`{entity}_id`, `lang`, ...), unique (`{entity}_id`, `lang`).

| ჯგუფი | ცხრილები და მთავარი ველები |
|---|---|
| ენები | ✅ `languages` (code, name, native_name, is_default, is_active, sort) |
| პარამეტრები | ✅ `settings` (setting_key, lang — '' არათარგმნადისთვის, value) — განსაზღვრებები `config/settings.php`-ში (D20) |
| UI თარგმანები | ✅ `translation_keys` (group, key, description) + `translation_values` (key_id → CASCADE, lang, value) → `t('nav.contact')` |
| გვერდები | ✅ `pages` (parent_id → SET NULL, status draft/published, is_home, sort, og_image_id → media) + `page_translations` (page_id → CASCADE, lang, title, slug — unique(lang, slug), meta_title, meta_description) |
| ბლოკები | ✅ `page_blocks` (page_id → CASCADE, type, sort, is_active, settings JSON, media_ids JSON) + `page_block_translations` (block_id → CASCADE, lang, content JSON) |
| მენიუ | ✅ `menus` (location unique) + `menu_items` (menu_id → CASCADE, parent_id → CASCADE, type page/url, page_id → CASCADE, url, new_tab, is_active, sort) + `menu_item_translations` (item_id → CASCADE, lang, label, url). ⏳ შემდგომ: პუნქტი-ტიპი კოლექციის ჩანაწერი (ახლა — ბმულით) |
| კატეგორიები | ✅ `categories` (collection, parent_id → SET NULL, sort, is_active) + `category_translations` (category_id → CASCADE, lang, name, slug) |
| კოლექციები | ✅ `collection_items` (collection, category_id → SET NULL, image_id → media SET NULL, sort, is_active, settings JSON, media_ids JSON) + `collection_item_translations` (item_id → CASCADE, lang, title, slug, content JSON) |
| მედია | ✅ `media` (path, original_name, mime, extension, size, width, height, variants JSON, webp, folder, uploaded_by) + `media_translations` (media_id → CASCADE, lang, alt, caption) |
| ფორმები | ✅ `form_submissions` (form, lang, data JSON, page_url, ip, user_agent, is_read, status new/in_progress/done, created_at) |
| დიზაინები | ✅ `designs` (image_id → media SET NULL, folder, entry, files, size, sort, is_active) + `design_translations` (design_id → CASCADE, lang, title) + `design_category` (design_id, category_id → CASCADE) — D52 |
| Newsletter | ✅ `subscribers` (email unique, lang, status pending/active/unsubscribed, token unique, ip, confirmed_at, unsubscribed_at) · `newsletter_campaigns` (subject, body, lang '' = ყველა, status draft/sending/sent, created_by, started_at, sent_at) · `newsletter_sends` (campaign_id + subscriber_id → CASCADE, status pending/sent/failed, error, sent_at) |
| სისტემა | ✅ `admin_users` (name, email, password_hash, is_active, last_login_at, last_login_ip) · ✅ `admin_remember_tokens` (user_id → CASCADE, selector unique, validator_hash, fingerprint, expires_at) · ✅ `admin_todos` (user_id → CASCADE, text, is_done) · ✅ `rate_limits` (limiter_key sha256, hits, reset_at) · ✅ `activity_log` (user_id → SET NULL, action, entity, entity_id, data JSON, ip) · ✅ `redirects` (from_path unique, to_url, code 301/302, is_active, is_auto, hits, last_hit_at) · ✅ `not_found_log` (path unique, hits, referer, first/last_seen_at) · ✅ `migrations` |

კოლექციები ერთ ზოგად ცხრილშია: ახალი კოლექციის ტიპი (მაგ. `portfolio`) ემატება `app/` კონფიგში, მიგრაციის გარეშე. `attributes` ინახავს ტიპის სპეციფიკურ ველებს (მაგ. team-ისთვის პოზიცია, სოციალური ბმულები).

## 6. მრავალენოვნება

- URL: `/{lang}/{slug}`, `/{lang}/{collection}/{slug}`. Slug ყოველ ენაზე ცალკეა.
- `SetLocale` ამოიცნობს ენას პრეფიქსიდან; უცნობი/არააქტიური ენა → 404. View-ებს უზიარებს `$lang` და `$languages` (გადამრთველისთვის).
- `/` → 302 ძირითად ენაზე (ბაზიდან).
- ენების გადამრთველი გადადის **იმავე გვერდის** შესაბამის თარგმანზე.
- `<link rel="alternate" hreflang>` ყველა გვერდზე (მხოლოდ ენებზე, სადაც თარგმანი არსებობს), `x-default` → ძირითადი ენა; იგივე — sitemap-ში (D43). Canonical, hreflang, og:url — APP_URL-ით.
- თარგმანი აკლია → ჩანს ძირითადი ენის (`ka`) ვერსია; ადმინში ასეთი ჩანაწერები მონიშნულია.

## 7. ადმინი

- მისამართი `/admin`, DASHMIN layout. Sidebar: Dashboard, გვერდები, მენიუ, კატეგორიები, კოლექციები (თითოეული ცალკე), დიზაინები, მედია, Inbox, Newsletter, თარგმანები, ენები, პარამეტრები, მომხმარებლები.
- თარგმნად ფორმებში ენის გადამრთველი (დროშებით, KA / EN / ყველა) თითოეული ფორმის თავში — D50 (`partials/lang-switch`).
- სორტირება drag-drop-ით (SortableJS), სურათის არჩევა მედია ბიბლიოთეკიდან (modal).
- Dashboard: სტატისტიკის ბარათები (გვერდები, წაუკითხავი, გამომწერები, მედია, თითო კოლექცია), Chart.js დიაგრამები (შეტყობინებები და გამომწერები 30 დღეში, კონტენტი კოლექციებით), ბოლო ჩანაწერები, ბოლო ცვლილებები, Inbox, კალენდარი, აქტივობა, ადმინის პირადი to-do სია (`admin_todos`, AJAX).
- Inbox: შეკვეთებს აქვს სტატუსი (ახალი / მიმდინარე / დასრულებული).

## 8. ფორმები და Newsletter ✅

**ფორმები** (`config/forms.php`: contact, application) → `POST /{lang}/forms/{form}` → `form_submissions` → ადმინის Inbox (წაუკითხავის მრიცხველი sidebar-ში და Dashboard-ზე). Email არ იგზავნება (D7). შეკვეთის ფორმა (`order`) — ცალკე, `POST /{lang}/order`, D52.
- ველის ტიპი `options` → ღილაკებით არჩევა (`partials/form-choice`, სერვერზე `in:` წესი); `one_of` — საკმარისია ერთი (მაგ. email ან ტელეფონი). ფორმის ქვეშ — თანხმობის ტექსტი კონფიდენციალურობის გვერდის ბმულით (`legal.privacy_page`).
- ვალიდაცია სერვერზე, შეტყობინებები ენაზე (UI თარგმანები `validation.*`); შეცდომისას — იმავე გვერდზე, `#form-{form}` ღუზით, მხოლოდ იმ ფორმაში (flash `_form`), წინა მნიშვნელობებით.
- ანტი-სპამი: honeypot ველი (`website`, შევსებისას — ჩუმი „წარმატება“), 5 გაგზავნა / 10 წთ ერთ IP-ზე (`rate_limits`).
- დაბრუნება მხოლოდ იმავე host-ის Referer-ზე (open redirect-ის გარეშე).

**Newsletter:**
1. გამოწერა `banner_signup`-იდან → `subscribers` (`pending`) → დასტურის წერილი გამომწერის ენაზე → ბმული → `active` (double opt-in). უკვე აქტიურს იგივე პასუხი უჩანს (სტატუსი სხვისთვის არ ვლინდება).
2. ადმინში კამპანია (თემა, WYSIWYG ტექსტი — HTMLPurifier, ადრესატები: ყველა ან ერთი ენა) → ტესტური წერილი → გაგზავნა.
3. გაშვებისას ადრესატები ფიქსირდება `newsletter_sends`-ში; გაგზავნა პარტიებად (20) AJAX-ით ან `php bin/console newsletter:send` (cron). გაშვების შემდეგ გაუქმებულს არ ეგზავნება.
4. ყოველ წერილში პერსონალური გაუქმების ბმული + `List-Unsubscribe` / `List-Unsubscribe-Post` (one-click, RFC 8058). GET მხოლოდ ღილაკს აჩვენებს (ფოსტის სკანერები რომ არ აუქმებდნენ), POST აუქმებს — CSRF-ის გარეშე (`app.csrf_except`), დაცვა — 64-სიმბოლოიანი token.
5. წერილების ბმულები — მხოლოდ `APP_URL`-იდან (`absolute_url()`), არა Host header-იდან (link poisoning).
6. SMTP — პარამეტრები → ელ-ფოსტა; ლოკალურად Mailpit (127.0.0.1:1025, ნახვა: http://127.0.0.1:8025). პაროლი ინახება დაშიფრულად (D41).

## 9. უსაფრთხოება

- PDO prepared statements, `ATTR_EMULATE_PREPARES = false`.
- ყველა გამოტანა `e()`-ით; WYSIWYG HTML — HTMLPurifier-ით შენახვისას.
- CSRF token ყველა POST-ზე; `SameSite=Lax`, `HttpOnly` სესიის cookie; სესიის ID-ის განახლება login-ისას.
- `password_hash` / `password_verify`; login-ის ლიმიტი (`rate_limits`, D16); უცნობ email-ზეც სრულდება hash-ის შემოწმება (timing).
- ადმინის პასუხებს: `Cache-Control: no-store`, `X-Robots-Tag: noindex`; logout — მხოლოდ POST + CSRF; login/logout-ისას იცვლება სესიის ID და CSRF token.
- ადმინს არ შეუძლია საკუთარი ანგარიშის წაშლა/გათიშვა და ბოლო აქტიური ადმინის წაშლა/გათიშვა; საკუთარი პაროლის შეცვლას სჭირდება მიმდინარე პაროლი.
- ატვირთვა: D23; `uploads/.htaccess` — სკრიპტები 403, `php_flag engine off`, `nosniff`.
- `public/.htaccess` (D48): CSP `frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'` (სკრიპტებს არ ზღუდავს — CDN-ები და ანალიტიკის კოდი პარამეტრებიდან), `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`. ჩაანაცვლებს მშობელი `C:\laragon\www\.htaccess`-ის CSP-ს `localhost/KURSORI/`-ზე.
- საჯარო ფორმებზე honeypot + rate limit.
- `core/`, `app/`, `config/`, `storage/` web root-ის გარეთაა.

## 10. ქეში და წარმადობა ✅

- **გვერდის HTML ქეში** (D47): სტუმრის GET-ზე `home` / `page` / `collection.item` route-ების პასუხი ინახება `Cache`-ში (`pages.*`, 24 სთ); `X-Page-Cache: HIT | MISS | BYPASS`.
  - არ იქეშება: შესული ადმინი, query string (გარდა მხოლოდ `?page=N`-ისა — ბლოგის პაგინაცია), ფორმის შედეგის გვერდი (flash), 200-ის გარდა ნებისმიერი პასუხი, newsletter-ის გვერდები.
  - CSRF token ქეშში ჩანაცვლებულია ნიშნულით და ყოველ მოთხოვნაზე ვიზიტორის token ჩაისმება — ფორმები ქეშირებული გვერდიდანაც მუშაობს.
  - ტექნიკური რეჟიმი ქეშამდე მოწმდება.
  - **ინვალიდაცია:** ადმინში ნებისმიერი წარმატებული POST/PUT/PATCH/DELETE → ყველა `pages.*` (`FlushPageCache`). **კოდის / CSS-ის / შაბლონის შეცვლის შემდეგ:** `php bin/console cache:clear` (ქეშირებულ HTML-ში ძველი `?v=` ვერსიებია).
  - გამორთვა: `.env` → `PAGE_CACHE=false` (მაგ. ტესტებისას, როცა ბაზა პირდაპირ იცვლება).
- ენები, თარგმანები, პარამეტრები, მენიუები — ფაილურ ქეშში (D21, D35); კატეგორიების სახელები — მოთხოვნის განმავლობაში.
- კოლექციის სიები — 2 query (ჩანაწერები + თარგმანები), N+1-ის გარეშე (`Collection::findMany`).
- სურათები: ვარიანტები ატვირთვისას (D24), ფრონტში `loading="lazy"` (პირველი სლაიდის გარდა).
- სტატიკური ფაილები (`public/.htaccess`): `Cache-Control: max-age=30 დღე` (ვერსია URL-შია — `asset()` → `?v=filemtime`), gzip (`mod_deflate`).

## 11. ფაზები

| ფაზა | შინაარსი | სტატუსი |
|---|---|---|
| 0 | საფუძველი: სტრუქტურა, `core/` (Router, DB, View, Migrator, Session, Csrf), composer, console, Laragon vhost | ✅ 2026-10-24 |
| 1 | ადმინის გარსი: DASHMIN layout, login/logout, ადმინ-მომხმარებლები | ✅ 2026-10-24 |
| 2 | ენები, UI თარგმანები, საიტის პარამეტრები | ✅ 2026-10-24 |
| 3 | მედია ბიბლიოთეკა (+ ლოგო/favicon/OG ველების გადაყვანა მედიის არჩევაზე) | ✅ 2026-10-25 |
| 4 | გვერდები + ბლოკები, Kursori-ის სექციების გადატანა | ✅ 2026-10-25 |
| 5 | მენიუები | ✅ 2026-10-25 |
| 6 | კატეგორიები და კოლექციები | ✅ 2026-10-25 |
| 7 | ფორმები + Inbox, newsletter | ✅ 2026-10-25 |
| 8 | SEO: sitemap, robots, hreflang, redirects, 404 | ✅ 2026-10-25 |
| 9 | ქეში, ოპტიმიზაცია, ატრიბუციის მოხსნა, ვებ დიზაინ სტუდიის კონტენტი | ✅ 2026-10-25 |
| 10 | Pre-launch და ახალი ფუნქციები: WebP/`picture()`, კონფიდენციალურობა, CanonicalUrl, ბლოგი, ფასები/FAQ, FiraGO, „დამიმახსოვრე“, Dashboard, საერთო ბლოკები, შეკვეთა და დიზაინები, აუდიტი | ✅ 2026-10-27 |
| — | Deploy: kursori.dev, GitHub Actions CI/CD (D9, D55) | ✅ 2026-10-28 |

### Production-ის ჩამონათვალი

✅ — შესრულებულია kursori.dev-ზე, ⏳ — დარჩენილი.

1. ⏳ Credit Removal ლიცენზიები (Kursori + DASHMIN) ნაყიდია (D11).
2. ✅ Web root → `public/`; `core/`, `app/`, `config/`, `storage/`, `.env` — მის გარეთ.
3. ✅ `.env`: `APP_ENV=production`, `APP_DEBUG=false` (აუდიტი SEC-01), `APP_URL=https://kursori.dev`, ბაზის მონაცემები, APP_KEY (ლოკალურის შეცვლისას SMTP პაროლი თავიდან შესაყვანია).
4. ✅ `composer install --no-dev --optimize-autoloader`, `migrate`, `cache:clear` — CI/CD-ით ყოველ deploy-ზე (D55).
5. ✅ `storage/` და `public/uploads/` ჩაწერადი.
6. ⏳ პარამეტრები → ელ-ფოსტა: რეალური SMTP (TLS/SSL), გამგზავნი დომენის SPF/DKIM; `orders.notify_email` ✅ შევსებულია.
7. ⏳ ადმინის პაროლი ძლიერი (ბაზა ლოკალურის ასლია — თავდაპირველად ლოკალური პაროლით). ✅ production-ის `.env`-ში `ADMIN_PASSWORD` არ არის.
8. ✅ HTTPS (SSL.com, Namecheap) + CanonicalUrl/HSTS (D53). ⏳ cron (cPanel → Cron Jobs, ყოველ 5 წთ-ში): `cd ~/kursori.dev && php bin/console schedule:run >/dev/null 2>&1` — სიახლეები და newsletter (D58).
10. ⏳ სიახლეები (D57): feed-ები და ბლოგის კატეგორიები ადმინიდან (seed 11 production-ზე არ ეშვება), Claude API გასაღები და ბალანსი, `APP_KEY` `.env`-ში.
9. ⏳ გამოგონილი კონტენტი და stock სურათები ჩანაცვლებული (D49, აუდიტი CNT-01); ანალიტიკისთვის consent (ANL-01); Google Search Console-ში `sitemap.xml`. ✅ „საძიებოსთვის ღია“ ჩართულია.

## 12. ცვლილებების ისტორია

- **2026-10-29** — სიახლეების მოდული (D57): dev.to-ს 10 feed (seed 11 + ბლოგის კატეგორიები), `news:fetch`, `news:publish` (ყდა მედიაში, ატრიბუცია, canonical ორიგინალზე), ქართული თარგმანი Claude-ით (`news:translate`), პარამეტრები → სიახლეები. ჯობების გრაფიკი (D58): `schedule:run`, `scheduled_jobs`/`job_runs`, `config/schedule.php`. HtmlSanitizer ინახავს სურათებს, კოდს და ცხრილებს; სტატიის სტილები. Composer: anthropic-ai/sdk, guzzlehttp/guzzle. ადმინის ინტერფეისი (feed-ები, მოდერაცია, ჯობები) — შემდეგი ეტაპი.

- **2026-10-28** — WEBDEV → KURSORI (კოდი, seed-ები, cookie-ები, ბაზა `kursori_db`); Poseify-ის ხსენებები კომენტარებსა და დოკუმენტაციაში → Kursori (`resources/templates` უცვლელია). Deploy kursori.dev-ზე: GitHub Actions CI/CD (lint/build, rsync, migrate, cache:clear, smoke test); `resources/` production-ზე აღარ იტვირთება. გასწორდა: `Media::usages()` MariaDB-ზე სინტაქსის შეცდომით ეცემოდა (`CAST(? AS JSON)`) და production-ში მედია ვერ იშლებოდა. ციფრული აუდიტი — `docs/audit/AUDIT-2026-10-27.md`. დაემატა D55–D56; განახლდა D1, D9.
- **2026-10-27** — შეკვეთის გვერდი დიზაინის არჩევით: დიზაინები ადმინში (ZIP → `TemplateArchive`, კატეგორიები, რიგი), `order_form` ბლოკი sandbox preview-ით, `POST /{lang}/order` → Inbox სტატუსით + წერილი, seed 10. საერთო ბლოკები (pricing, FAQ). კოლექციის ჩანაწერის header-ის ფონი. ფერის ცვლადები და WCAG AA კონტრასტი. Composer: PHP 8.2. `docs/architecture-map.html`. დაემატა D51, D52, D54.
- **2026-10-26** — „დამიმახსოვრე“ ადმინის login-ზე (D15). ფრონტი: FiraGO მთელ საიტზე, მთავრული სათაურებსა და მენიუში (D33); hero slider static hero-ს დიზაინით, ინტერვალი და ისრები ადმინიდან; stats ბარათი; about ბლოკის ახალი layout; სერვისის სათაური ბმულით; მენიუს dropdown, ენის გადამრთველი (ერთი pill), CTA და ენის ღილაკების ერთნაირი სიმაღლე; ბლოკებს შორის ნაკლები დაშორება. ადმინის პარამეტრებში ნაგულისხმევი მნიშვნელობა თითო ენის ველის ქვეშ.
- **2026-10-25 (საღამო)** — ბლოგი (კოლექცია, სტატიის გვერდი, `blog_list`); სერვისის გვერდი (ფასი, ვადა, FAQ, ქეისები), ფასების გვერდი, ბლოკები `pricing`, `faq`, `stats`, `process`, hero `static` რეჟიმი, services `grid`; ფორმები: `options`, `one_of`, ახალი ველები, თანხმობა; mobile CTA ზოლი; footer-ის კონტაქტები. Pre-launch: WebP + `small` ვარიანტი, `picture()`, `media:regenerate`, CanonicalUrl + HSTS, meta description-ები, favicon, კონფიდენციალურობის გვერდი (seed 09). Unsplash stock სურათები (`resources/stock`), WOW.js → IntersectionObserver reveal, ერთი H1 გვერდზე, spinner ამოღებულია. Dashboard (დიაგრამები, to-do). ადმინის ენის გადამრთველი ფორმებში, დროშებით. აუდიტის გასწორებები: მასივის input-ზე 500, ზედმეტი ქეშის გასუფთავება, `rate_limits`-ის გასუფთავება. დაემატა D53; განახლდა D24.

- **2026-10-25** — ადმინის ფორმებში შესავსები ენის გადამრთველი (KA / EN / ყველა) ზედა ზოლში: გვერდები, ბლოკები, კოლექციები, კატეგორიები, მენიუს პუნქტები, მედია, პარამეტრები. გვერდის ფორმის ენის ჩანართები ჩანაცვლდა. დაემატა D50.

- **2026-10-25** — ფაზა 9: გვერდის HTML ქეში სტუმრებისთვის (CSRF-ის ჩანაცვლებით, ადმინის ცვლილებაზე გასუფთავებით), კოლექციის სიების N+1-ის მოხსნა, სტატიკური ფაილების ქეში და gzip, CSP და უსაფრთხოების header-ები, HTML Codex ატრიბუციის მოხსნა (ლიცენზიით), ახალი ბლოკი `portfolio_grid`, განაცხადის ფორმის წარწერები ბლოკიდან, ვებ სტუდიის საწყისი კონტენტი (seed-ები 05–08) და `content:reset`. ქართული სლაიდერის სათაური დიდ ეკრანზეც მცირდება. Production-ის ჩამონათვალი. დაემატა D47–D49.
- **2026-10-25** — ფაზა 8: `/sitemap.xml` (hreflang alternates), `/robots.txt` (პარამეტრებიდან), გადამისამართებები ადმინიდან (301/302, მხოლოდ 404-ზე, query-ის გადაცემით), ავტომატური 301 slug-ის შეცვლისას (ჯაჭვების გარეშე), 404 ჟურნალი, JSON-LD Organization, og:url/og:locale/twitter:card. `core/`: `App::onNotFound()`. გასწორდა: canonical/OG URL-ში `localhost/KURSORI`-ზე base path რჩებოდა. დაემატა D43–D46.
- **2026-10-25** — ფაზა 7: საკონტაქტო და განაცხადის ფორმები (ახალი ბლოკი `application_form`) → Inbox (წაუკითხავი/წაკითხული, ფილტრი, ძებნა, პასუხი mailto-თი); newsletter — double opt-in, დასტური/გაუქმება (one-click), კამპანიები ტესტით და პარტიებად გაგზავნით, გამომწერების სია და CSV; SMTP პარამეტრები ადმინში დაშიფრული პაროლით; `core/`: Mailer (PHPMailer), Crypt, Validator-ის შეტყობინებები, CSRF-ის გამონაკლისები, `absolute_url()`; `key:generate`, `newsletter:send`. დაემატა D41–D42.
- **2026-10-25** — ფაზა 6: კოლექციები (სერვისები, გუნდი, შეფასებები, პორტფოლიო) კონფიგიდან, საკუთარი ველებით; კატეგორიები (2 დონე); ადმინში სია (ძებნა, ფილტრი, გადათრევით რიგი, დამალვა) და ფორმა (ბლოკების ველების ძრავით); საჯარო გვერდები (სერვისი, პროექტი) hreflang-ით; ბლოკები „წყარო: კოლექცია“ (კატეგორია, რაოდენობა); `show_if`; მედიის გამოყენება კოლექციებშიც. დემო გვერდების ბლოკები გადავიდა კოლექციებზე. დაემატა D37–D40.
- **2026-10-25** — ფაზა 5: მართვადი მენიუები (header — 2 დონე, footer — 1 დონე): გვერდის/ბმულის პუნქტები, წარწერები და ბმულები ენებზე, ჩადგმული drag & drop, დამალვა, ახალ ფანჯარაში გახსნა; ფრონტის navbar და footer მენიუდან, ქეშით. `Core\Support\Url` — ბმულების ერთიანი შემოწმება (ახლა `//host` ბმულიც უარყოფილია). გასწორდა: ენის წაშლისას რჩებოდა მისი გვერდების, ბლოკებისა და მედიის თარგმანები. დაემატა D34–D36.
- **2026-10-25** — ფაზა 4: გვერდები (ხე, თარგმანები, slug ქართულიდან, SEO, OG სურათი, მონახაზი/წინასწარი ხედი, მთავარი გვერდი) და ბლოკების რედაქტორი (10 ტიპი სქემიდან, repeater, Jodit, link ველი, გადათრევით დალაგება, დამალვა, კოპირება). ფრონტი Kursori-ის layout-ზე: navbar (დროებით გვერდებიდან), ენების გადამრთველი, footer პარამეტრებიდან, hreflang/canonical/OG, Kursori-ის 404. Kursori-ის 6 გვერდი seed-ით. `core/`: Str, HtmlSanitizer; composer: HTMLPurifier. დაემატა D27–D33.
- **2026-10-25** — ფაზა 3: მედია ბიბლიოთეკა (ბადე, ფილტრები, drag & drop ატვირთვა პროგრესით, საქაღალდეები, alt/წარწერა ენებზე, URL-ის კოპირება, გამოყენების შემოწმება წაშლამდე), საერთო picker-ფანჯარა, პარამეტრების `image` ველები → მედიის id. `core/Media`: Uploader, ImageResizer. Kursori-ის დემო სურათები seed-ით. Uploader სურათს GD-ით სრულად ხსნის (დაზიანებული ფაილი არ მიიღება). გასწორდა: მშობელი პროექტის CSP `localhost/KURSORI/`-ზე jQuery-ს ბლოკავდა. დაემატა D23–D26.
- **2026-10-24** — ფაზა 2: ენების CRUD (ძირითადი ენის წესები), UI თარგმანების რედაქტორი (ფილტრი ჯგუფით, ძებნა, „მხოლოდ ნაკლული“, ერთად შენახვა, გასაღების დამატება/წაშლა), პარამეტრები ჩანართებით (ზოგადი, კონტაქტი, სოც. ქსელები, SEO, კოდი), ტექნიკური რეჟიმი. `core/`: Cache, Translator, Settings, Validator `regex`/`url`. გასწორდა: ნამდვილი PUT/PATCH/DELETE-ის form body არ იკითხებოდა. დაემატა D18–D22; `settings`/`translations` სქემა შეიცვალა D19–D20-ის მიხედვით.
- **2026-10-24** — ფაზა 1: DASHMIN layout (sidebar `config/admin.php`-იდან), login/logout, ადმინ-მომხმარებლების CRUD, Dashboard ბოლო აქტივობით, `Core\Auth\Auth`, `RateLimiter`, `Validator`, flash/old/errors helper-ები, `admin:create`. მიგრაციები: admin_users, rate_limits, activity_log. გასწორდა: Apache-ზე 419 → 500 (ახლა სრული status line იგზავნება); flash მონაცემი იშლებოდა, თუ ზედიზედ ორ მოთხოვნაზე flash-დებოდა. DASHMIN-ის `main.js` ჩანაცვლდა `admin.js`-ით (დემო დიაგრამების გარეშე). დაემატა D15–D17.
- **2026-10-24** — ფაზა 0: `core/` ფრეიმვორკი (App/კონტეინერი, Router, Request/Response, DB, Migrator, View/Template, Session, Csrf, ErrorHandler, Console), composer autoload, `.env`, languages მიგრაცია + seed, vhost `kursori.test`, თემფლეითები → `resources/templates/`, assets → `public/assets/`. დაემატა D12–D14.
- **2026-10-24** — პირველი ვერსია: თემფლეითების ანალიზი, გადაწყვეტილებები D1–D11, სქემა, ფაზები.
