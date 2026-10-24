# WEBDEV — არქიტექტურა

> ცოცხალი დოკუმენტი: ახლდება ყოველი ფაზის ან არქიტექტურული გადაწყვეტილების შემდეგ.
> ბოლო განახლება: 2026-10-25 · მიმდინარე ფაზა: **6 (კატეგორიები და კოლექციები)** · ფაზები 0–5 დასრულდა

## 1. მიზანი

მრავალენოვანი საიტი, რომლის ფრონტში **ყველაფერი ადმინიდან იმართება**: გვერდები, ბლოკები, მენიუ, კატეგორიები, თარგმანები, საიტის პარამეტრები, მედია, ფორმები, newsletter.

- **ფრონტი:** Poseify (HTML Codex) — სტილები და markup უცვლელად რჩება, იცვლება მხოლოდ კონტენტი.
- **ადმინი:** DASHMIN — Bootstrap Admin Template Free (HTML Codex).
- **საბოლოო კონტენტი:** ვებ დიზაინ სტუდია. დემო-კონტენტი (მოდელინგის სააგენტო) ბოლო ეტაპზე ჩანაცვლდება — ამიტომ კოლექციები და ფორმები **ზოგადია**, არა მოდელინგზე მიბმული.
- **ფრეიმვორკი:** საკუთარი, მსუბუქი, მრავალჯერადი — სამომავლოდ სხვა პროექტებზეც გამოიყენება.

## 2. გადაწყვეტილებები

| # | თემა | გადაწყვეტილება |
|---|---|---|
| D1 | სტეკი | PHP 8.3 + MySQL 8.4 (Laragon), Bootstrap 5 + jQuery (თემფლეითებიდან) |
| D2 | ფრეიმვორკი | საკუთარი MVC: `core/` პროექტისგან დამოუკიდებელია, `app/` — პროექტის კოდი. Composer მხოლოდ autoload-ისა და რამდენიმე ბიბლიოთეკისთვის |
| D3 | ენები | `ka` (ძირითადი) და `en`. URL-ს **ყველა ენაზე** აქვს პრეფიქსი: `/ka/...`, `/en/...`; `/` → 302 `/ka/` |
| D4 | კონტენტის რედაქტირება | ფიქსირებული ბლოკის ტიპები (Poseify-ის სექციები) + WYSIWYG textarea ველებში და ცალკე `rich_text` ბლოკი |
| D5 | WYSIWYG | Jodit (MIT, jQuery-ს არ საჭიროებს); შენახვისას HTML იწმინდება HTMLPurifier-ით |
| D6 | როლები | ამ ეტაპზე ერთი როლი (ადმინი). სქემა ისეა, რომ როლები მოგვიანებით დაემატოს |
| D7 | ფორმები | Contact და განაცხადის ფორმა (ყოფილი Casting) — ფოტოების გარეშე, ინახება მხოლოდ ადმინის Inbox-ში, email არ იგზავნება |
| D8 | Newsletter | გამოწერების შეგროვება + კამპანიის შექმნა და გაგზავნა ადმინიდან (PHPMailer/SMTP, პარტიებად) |
| D9 | გარემო | ამ ეტაპზე მხოლოდ ლოკალურად (Laragon). Deploy მოგვიანებით |
| D10 | დიზაინის პარამეტრები | ადმინიდან იმართება მხოლოდ კონტენტი; ფერები/ფონტები — CSS-ში |
| D11 | HTML Codex ატრიბუცია | იშლება — მფლობელი ყიდულობს Credit Removal ლიცენზიას (ორივე თემფლეითზე) |
| D12 | Front controller | ერთი `public/index.php` ფრონტისა და ადმინისთვის; ადმინი — `/admin` route-ების ჯგუფი |
| D13 | გარემოს პარამეტრები | `.env` (git-ის გარეშე, ასლი `.env.example`) → `config/*.php` → `config('app.name')` |
| D14 | ლოკალური მისამართი | `http://webdev.test` (vhost → `public/`); სარეზერვო `http://localhost/WEBDEV/` root `.htaccess`-ით |
| D15 | ადმინის ავტორიზაცია | სესია + `password_hash`; მომხმარებელი ყოველ მოთხოვნაზე ბაზიდან იკითხება; პაროლის შეცვლა ან გათიშვა ხურავს მის ყველა სესიას; 2 სთ უმოქმედობის შემდეგ — გამოსვლა. „Remember me“ და პაროლის აღდგენა email-ით — არ არის (აღდგენა: `admin:create`) |
| D16 | Login-ის ლიმიტი | 5 მცდელობა email+IP-ზე და 20 — IP-ზე, 15 წუთში; ზოგადი `rate_limits` ცხრილი (`login_attempts`-ის ნაცვლად), საჯარო ფორმებიც მას გამოიყენებს |
| D17 | ადმინის ენა | ადმინის ინტერფეისი ქართულადაა (ტექსტები view-ებში); თარგმანების სისტემა (ფაზა 2) ფრონტისთვისაა |
| D18 | ენების წყარო | `languages` ცხრილი (ადმინიდან); route-ში `{lang}` = `[a-z]{2}`, აქტიურობას `SetLocale` ამოწმებს. კოდი შექმნის შემდეგ არ იცვლება. ძირითადი ენა ყოველთვის ერთია და აქტიური; მისი წაშლა/გათიშვა აკრძალულია. ენის წაშლა შლის მის UI თარგმანებსა და პარამეტრებს |
| D19 | UI თარგმანები | `translation_keys` (group, key) + `translation_values` (key_id, lang) — ცალკე გასაღებები, რომ ნაკლული თარგმანები ჩანდეს. `t('group.key')`; აკლია → ძირითადი ენა → გასაღები. Seed (`03_translations`) ადმინში შეცვლილ ტექსტებს არ ეხება |
| D20 | პარამეტრები | განსაზღვრებები კოდში (`config/settings.php`: ჯგუფი → ველები, ტიპი, თარგმნადობა, default), მნიშვნელობები ბაზაში (`settings`: setting_key, lang — '' არათარგმნადისთვის). ადმინის ფორმა განსაზღვრებებიდან გენერირდება. ახალი პარამეტრი = ერთი ხაზი კონფიგში, მიგრაციის გარეშე |
| D21 | ქეში | `Core\Support\Cache` — ფაილური (`storage/cache/data`, PHP ფაილები opcache-ისთვის). ენები, თარგმანები (ენაზე), პარამეტრები ქეშირდება ვადის გარეშე და იშლება ადმინში შენახვისას |
| D22 | ტექნიკური რეჟიმი | `general.maintenance_mode` — სტუმრებს 503 (`errors/maintenance`), შესულ ადმინს — საიტი |
| D23 | მედიის შენახვა | `public/uploads/YYYY/MM/{20 hex}.{ext}`; ტიპი — შიგთავსით (finfo), გაფართოება — დაშვებული MIME-ის რუკიდან; სურათი მოწმდება getimagesize-ით, პიქსელების ლიმიტით (40 MP) და GD-ით სრული გახსნით. დაშვებულია JPG, PNG, WebP, GIF, PDF, მაქს. 10 MB. SVG აკრძალულია (JavaScript-ის რისკი) |
| D24 | სურათის ვარიანტები | ატვირთვისას: `thumb` 400×400 (crop), `medium` 1024 და `large` 1920 (fit), `-{variant}` სუფიქსით. პატარა სურათი არ დიდდება; ორიგინალის ტოლი ვარიანტი არ ინახება; GIF (ანიმაცია) და PDF — ვარიანტების გარეშე. ხელახალი კოდირება EXIF/GPS-ს შლის, JPEG-ის ორიენტაცია სწორდება |
| D25 | მედიაზე მითითება | ყველგან ინახება მედიის **id** (არა ბილიკი): `media_url($id, 'medium')`, `media_alt($id)`. Alt/წარწერა — თითო ენაზე (`media_translations`). გამოყენებული ფაილი არ იშლება (`Media::usages()` — ყოველი ახალი მოდული აქ ემატება) |
| D26 | ატვირთვა ადმინში | ერთი ფაილი თითო AJAX მოთხოვნაზე (რიგრიგობით, პროგრესით, CSRF header-ით), drag & drop; ერთი საერთო picker-ფანჯარა ყველა ადმინ-გვერდზე (`partials/media-input` + `media.js`) |
| D27 | გვერდის URL | ბრტყელი: `/{lang}/{slug}` (slug უნიკალურია ენის ფარგლებში; `[a-z0-9-]`, ქართულიდან ავტომატური ტრანსლიტერაციით — `Str::slug`), მთავარი — `/{lang}`. მშობელი გვერდი URL-ზე არ მოქმედებს — მხოლოდ breadcrumb-სა და მენიუზე. თარგმანი ენაზე არ არის → გვერდი იმ ენაზე 404, ენების გადამრთველი → იმ ენის მთავარი |
| D28 | ბლოკის შენახვა | `page_blocks.settings` — არათარგმნადი ველები და repeater-ის სტრუქტურა (`_id`-ით), `page_block_translations.content` — თარგმნადი ველები ენაზე (repeater: `content[field][_id][sub]`), `media_ids` — გამოყენებული მედია (`JSON_CONTAINS` — წაშლისგან დაცვა). ცარიელი თარგმნადი ველი → ძირითადი ენა → default |
| D29 | Header ბლოკი | Poseify-ის navbar absolute-ია, ამიტომ პირველი ბლოკი თუ `header` ტიპი არ არის (სლაიდერი / გვერდის სათაური), ავტომატურად ემატება გვერდის სათაური |
| D30 | Link ველი | `page` (id → URL მიმდინარე ენაზე ავტომატურად) ან `url` (მხოლოდ http(s), `/...`, `#...`, `mailto:`, `tel:`); ახალ ფანჯარაში → `rel="noopener"` |
| D31 | მონახაზი | `draft` გვერდი სტუმრისთვის 404, შესული ადმინისთვის — წინასწარი ხედი ზოლით და `noindex`-ით. დამალული ბლოკი არ იხატება |
| D32 | ნავიგაცია მენიუს გარეშე | ~~ფაზა 5-მდე~~ ახლა მხოლოდ სარეზერვო: header მენიუში არცერთი პუნქტი არ არის (ახალი საიტი) → ზედა დონის გამოქვეყნებული გვერდები (`Page::navigation`). ჩანაცვლდა D34-ით |
| D34 | მენიუები | ადგილები კოდში (`config/menus.php`: `header` — 2 დონე, `footer` — 1 დონე), პუნქტები ბაზაში. პუნქტი = გვერდი (წარწერა და URL ყველა ენაზე გვერდიდან, წარწერის შეცვლა შეიძლება) ან ბმული (წარწერა ენაზე, აკლია → ძირითადი; ბმული ენაზე — არასავალდებულო). ახალი გვერდი მენიუში ავტომატურად **არ** ემატება. გვერდი ენაზე თარგმნილი/გამოქვეყნებული არ არის ან პუნქტი დამალულია → პუნქტი შვილებით არ ჩანს. გვერდის წაშლა მის პუნქტებს შლის (CASCADE) |
| D35 | მენიუს ქეში | `menus.{location}.{lang}.{base path hash}` — ქეში იშლება პუნქტის, გვერდის (სათაური/slug/სტატუსი) და ენის ცვლილებისას |
| D36 | ბმულების უსაფრთხოება | ერთი წესი ყველგან (`Core\Support\Url::isSafeHref`): http(s), `/ბილიკი` (არა `//host`), `#ღუზა`, `mailto:`, `tel:` |
| D33 | ქართული შრიფტი | Poseify-ის შრიფტებში ქართული არ არის — `site.css`-ში Noto Sans Georgian სარეზერვოდ, მობილურზე სათაურების ზომა მცირდება. `style.css` უცვლელია |

## 3. დირექტორიების სტრუქტურა

✅ — უკვე არსებობს, ⏳ — დაგეგმილია.

```
WEBDEV/
├── public/                  ← web root (webdev.test → public/)
│   ├── index.php            ✅ ერთადერთი front controller (ფრონტი + /admin)
│   ├── .htaccess            ✅ rewrite → index.php, უსაფრთხოების header-ები
│   ├── assets/front/        ✅ Poseify css/js/lib/img
│   ├── assets/admin/        ✅ DASHMIN css/js/lib/img
│   └── uploads/             ✅ მედია (git-ის გარეშე; სკრიპტები აკრძალულია)
├── core/                    ← მრავალჯერადი ფრეიმვორკი (namespace Core\)
│   ├── App.php              ✅ კონტეინერი (autowiring), boot, HTTP დამუშავება, locale
│   ├── helpers.php          ✅ app, config, env, e, url, route, asset, view, redirect, back, abort,
│   │                          csrf_token, csrf_field, old, errors, is_route, auth, locale, t, setting
│   ├── Http/                ✅ Request, Response (with/withErrors/withInput), Router, Route, Middleware,
│   │                          HttpException, Middleware/VerifyCsrf
│   ├── Database/            ✅ DB (PDO), Migration, Migrator, Seeder · ⏳ QueryBuilder (საჭიროებისამებრ)
│   ├── View/                ✅ View, Template (layout, sections, insert)
│   ├── Debug/               ✅ ErrorHandler (debug გვერდი / errors/*.php, ლოგი)
│   ├── Support/             ✅ Env, Config, Session (flash), Csrf, Log, Validator, RateLimiter, Cache,
│   │                          Str (slug, limit), HtmlSanitizer (HTMLPurifier), Url (isSafeHref, resolve) · ⏳ Arr
│   ├── Console/             ✅ Console, Command, Output, Commands/*
│   ├── Auth/                ✅ Auth
│   ├── I18n/                ✅ Translator (loader-ით — წყარო პროექტი წყვეტს)
│   ├── Settings/            ✅ Settings (განსაზღვრებები კონფიგიდან, მნიშვნელობები ბაზიდან)
│   ├── Media/               ✅ Uploader, ImageResizer (GD), UploadException
│   └── Mail/                ⏳ Mailer (PHPMailer wrapper)
├── app/                     ← პროექტის კოდი (namespace App\)
│   ├── Controllers/Front/   ✅ Page · ⏳ Collection, Form, Newsletter, Sitemap
│   ├── Controllers/Admin/   ✅ Auth, Dashboard, User, Language, Translation, Settings, Media, Page, Block, Menu ·
│   │                          ⏳ Categories, Collections, Inbox, Newsletter
│   ├── Middleware/          ✅ SetLocale (locale), CheckMaintenance (maintenance), Authenticate (auth),
│   │                          RedirectIfAuthenticated (guest)
│   ├── Models/              ✅ AdminUser, ActivityLog, Language, Translation, Media, Page, PageBlock, Menu · ⏳ დანარჩენი
│   ├── helpers.php          ✅ media(), media_url(), media_alt(), link_attrs(), menu(), link_target()
│   ├── Console/Commands/    ✅ CreateAdminCommand (admin:create)
│   ├── Blocks/              ✅ BlockRegistry (config/blocks.php), BlockInput (ფორმა → ვალიდაცია → JSON),
│   │                          BlockResolver (JSON → view-ის მონაცემები ენაზე)
│   └── routes/              ✅ admin.php (/admin), web.php (/{lang})
├── bootstrap/app.php        ✅ App-ის აწყობა: middleware, route-ები
├── views/
│   ├── front/               ✅ layouts/main (Poseify), partials/ (navbar, footer, title), page, blocks/* (10 ტიპი)
│   ├── admin/               ✅ layouts/*, partials/* (flash, pagination, upload-zone, media-input, media-picker),
│   │                          auth/login, dashboard, users/*, languages/*, translations/index, settings/*, media/*,
│   │                          pages/*, blocks/ (form, field — რეკურსიული, link), menus/ (index, item) · ⏳ მოდულები
│   └── errors/              ✅ default.php, 404.php (Poseify), maintenance.php
├── config/                  ✅ app, database (.env-იდან), auth, validation (ქართული შეტყობინებები), admin (sidebar),
│                              settings (პარამეტრების განსაზღვრებები), media (ტიპები, ლიმიტები, ვარიანტები),
│                              blocks (ბლოკების ტიპები და ველები), menus (მენიუს ადგილები და სიღრმე)
├── database/
│   ├── migrations/          ✅ languages, admin_users, rate_limits, activity_log, translation_keys/values, settings,
│   │                          media + media_translations, pages + page_translations + page_blocks + page_block_translations,
│   │                          menus + menu_items + menu_item_translations
│   └── seeds/               ✅ 01_languages (ka, en), 02_admin_user (.env ADMIN_*), 03_translations (Poseify UI ტექსტები),
│                              04_demo_media (Poseify-ის 19 სურათი, საქაღალდე "demo"), 05_demo_pages (6 გვერდი ბლოკებით),
│                              06_menus (header — გვერდების ხე, footer — ზედა დონე)
├── resources/templates/     ✅ poseify/, dashmin/ — საწყისი HTML (reference) + zip (git-ის გარეშე)
├── storage/                 ✅ cache/, logs/ (git-ის გარეშე)
├── bin/console              ✅ CLI
├── .env / .env.example      ✅
├── docs/ARCHITECTURE.md
└── composer.json            ✅ PSR-4: Core\ → core/, App\ → app/; ezyang/htmlpurifier
```

Laragon-ის PHP CLI-ში `zip` გაფართოება გამორთულია, ამიტომ Composer-ის ინსტალაცია: `php -d extension=zip C:\laragon\bin\composer\composer.phar install`.

### ფენები და მოთხოვნის გზა

```
public/index.php → Core\App → Router → Middleware (VerifyCsrf, locale | auth/guest)
                → Controller → Model (PDO) → View (layout + blocks) → Response
                                  ↑
                              Cache (გვერდის HTML, ინვალიდაცია შენახვისას)
```

**`core/` წესი:** არ იცის არაფერი კონკრეტულ პროექტზე (ცხრილები, ბლოკები, Poseify). ყველაფერი პროექტ-სპეციფიკური `app/`-შია. ასე `core/` სხვა პროექტში გადააქვს ან მოგვიანებით ცალკე Composer პაკეტად გამოიყოფა.

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
⏳ ფაზა 6: `category` / `collection_items` — services/team/testimonials ბლოკები კოლექციიდანაც.

| ბლოკი | Poseify-ის წყარო | შინაარსი |
|---|---|---|
| ✅ `hero_carousel` | index Header carousel | სლაიდები: სურათი, ქვესათაური, სათაური, ტექსტი, ღილაკი (header) |
| ✅ `page_header` | შიდა გვერდების header | სათაური (ცარიელი → გვერდის სახელი), ფონის სურათი, breadcrumb (header) |
| ✅ `about` | About | სათაურები, WYSIWYG ტექსტი, ჩამონათვალი, 2 ღილაკი, სურათი, ფონი |
| ✅ `services` | Service | სერვისები (ზიგზაგ layout): სურათი, სახელი, აღწერა, ღილაკი |
| ✅ `banner_signup` | Banner | სათაური, ტექსტი, newsletter ფორმა (დამუშავება — ფაზა 7) |
| ✅ `team_grid` | Team | წევრები: ფოტო, სახელი, „დასახელება: მნიშვნელობა“ ხაზები (hover-ზე), ბმული |
| ✅ `testimonials` | Testimonial | შეფასებები (owl carousel, ფოტო — dot-ში) |
| ✅ `contact` | Contact | სათაური, ტექსტი, კონტაქტები და სოც. ქსელები პარამეტრებიდან, ფორმა (დამუშავება — ფაზა 7) |
| ✅ `map` | Google Map | embed URL (მხოლოდ google.com/maps/embed) ან პარამეტრებიდან, სიმაღლე |
| ✅ `rich_text` | — | სათაური + თავისუფალი WYSIWYG ტექსტი |
| ⏳ `application_form` | — (Banner-ის სტილით) | განაცხადის ფორმა — ფაზა 7 |

ახალი ბლოკის დამატება: ჩანაწერი `config/blocks.php`-ში + view `views/front/blocks/{type}.php` — ადმინის ფორმა ავტომატურად ჩნდება.

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
| მენიუ | ✅ `menus` (location unique) + `menu_items` (menu_id → CASCADE, parent_id → CASCADE, type page/url, page_id → CASCADE, url, new_tab, is_active, sort) + `menu_item_translations` (item_id → CASCADE, lang, label, url). ⏳ ფაზა 6: type `category` / `item` |
| კატეგორიები | `categories` (collection, parent_id, sort, is_active) + `category_translations` (name, slug, description) |
| კოლექციები | `collection_items` (collection: services/team/testimonials/…, category_id, image_id, attributes JSON, sort, is_active) + `collection_item_translations` (title, slug, subtitle, excerpt, body, attributes JSON) |
| მედია | ✅ `media` (path, original_name, mime, extension, size, width, height, variants JSON, folder, uploaded_by) + `media_translations` (media_id → CASCADE, lang, alt, caption) |
| ფორმები | `form_submissions` (form: contact/application, data JSON, ip, is_read, created_at) |
| Newsletter | `subscribers` (email, lang, status, token, confirmed_at, unsubscribed_at) · `newsletter_campaigns` (subject, body, lang, status, sent_at) · `newsletter_sends` (campaign_id, subscriber_id, status, error, sent_at) |
| სისტემა | ✅ `admin_users` (name, email, password_hash, is_active, last_login_at, last_login_ip) · ✅ `rate_limits` (limiter_key sha256, hits, reset_at) · ✅ `activity_log` (user_id → SET NULL, action, entity, entity_id, data JSON, ip) · `redirects` (from, to, code) · ✅ `migrations` |

კოლექციები ერთ ზოგად ცხრილშია: ახალი კოლექციის ტიპი (მაგ. `portfolio`) ემატება `app/` კონფიგში, მიგრაციის გარეშე. `attributes` ინახავს ტიპის სპეციფიკურ ველებს (მაგ. team-ისთვის პოზიცია, სოციალური ბმულები).

## 6. მრავალენოვნება

- URL: `/{lang}/{slug}`, `/{lang}/{collection}/{slug}`. Slug ყოველ ენაზე ცალკეა.
- `SetLocale` ამოიცნობს ენას პრეფიქსიდან; უცნობი/არააქტიური ენა → 404. View-ებს უზიარებს `$lang` და `$languages` (გადამრთველისთვის).
- `/` → 302 ძირითად ენაზე (ბაზიდან).
- ენების გადამრთველი გადადის **იმავე გვერდის** შესაბამის თარგმანზე.
- `<link rel="alternate" hreflang>` ყველა გვერდზე, `x-default` → `ka`.
- თარგმანი აკლია → ჩანს ძირითადი ენის (`ka`) ვერსია; ადმინში ასეთი ჩანაწერები მონიშნულია.

## 7. ადმინი

- მისამართი `/admin`, DASHMIN layout. Sidebar: Dashboard, გვერდები, მენიუ, კატეგორიები, კოლექციები (თითოეული ცალკე), მედია, Inbox, Newsletter, თარგმანები, ენები, პარამეტრები, მომხმარებლები.
- თარგმნად ფორმებში ენების ჩანართები (KA / EN).
- სორტირება drag-drop-ით (SortableJS), სურათის არჩევა მედია ბიბლიოთეკიდან (modal).
- Dashboard: წაუკითხავი შეტყობინებები, გამომწერები, ბოლო ცვლილებები.

## 8. Newsletter

1. გამოწერა `banner_signup`-იდან → `subscribers` (status `pending`), დასტურის წერილი (double opt-in) → `active`.
2. ადმინში კამპანია (subject + WYSIWYG body, ენა) → ტესტ-გაგზავნა → გაგზავნა.
3. გაგზავნა პარტიებად (მაგ. 50 წერილი): ადმინის გვერდი AJAX-ით უშვებს პარტიებს, ან `bin/console newsletter:send`.
4. ყოველ წერილში unsubscribe ბმული (`token`).
5. ლოკალურად SMTP → Mailpit (წერილები რეალურად არ იგზავნება). SMTP პარამეტრები `settings`-შია.

> შენიშვნა: D7-ის მიხედვით ფორმების შეტყობინებები email-ით არ იგზავნება, მაგრამ newsletter-ისა და double opt-in-ისთვის mailer მაინც საჭიროა.

## 9. უსაფრთხოება

- PDO prepared statements, `ATTR_EMULATE_PREPARES = false`.
- ყველა გამოტანა `e()`-ით; WYSIWYG HTML — HTMLPurifier-ით შენახვისას.
- CSRF token ყველა POST-ზე; `SameSite=Lax`, `HttpOnly` სესიის cookie; სესიის ID-ის განახლება login-ისას.
- `password_hash` / `password_verify`; login-ის ლიმიტი (`rate_limits`, D16); უცნობ email-ზეც სრულდება hash-ის შემოწმება (timing).
- ადმინის პასუხებს: `Cache-Control: no-store`, `X-Robots-Tag: noindex`; logout — მხოლოდ POST + CSRF; login/logout-ისას იცვლება სესიის ID და CSRF token.
- ადმინს არ შეუძლია საკუთარი ანგარიშის წაშლა/გათიშვა და ბოლო აქტიური ადმინის წაშლა/გათიშვა; საკუთარი პაროლის შეცვლას სჭირდება მიმდინარე პაროლი.
- ატვირთვა: D23; `uploads/.htaccess` — სკრიპტები 403, `php_flag engine off`, `nosniff`.
- `public/.htaccess` შლის მშობელი `C:\laragon\www\.htaccess`-ის (სხვა პროექტის) CSP-ს, რომელიც `localhost/WEBDEV/`-ზე jQuery-ს CDN-ს ბლოკავდა. საკუთარი CSP — ფაზა 9.
- საჯარო ფორმებზე honeypot + rate limit.
- `core/`, `app/`, `config/`, `storage/` web root-ის გარეთაა.

## 10. ქეში და წარმადობა

- გვერდის სრული HTML იქეშება `storage/cache/pages/{lang}/{slug}.html`-ში; ადმინში ნებისმიერი შენახვა შესაბამის ქეშს შლის (ზოგად პარამეტრებზე — მთელს).
- settings და translations იტვირთება ერთი query-ით და ინახება მოთხოვნის განმავლობაში.
- სურათებს ატვირთვისას ეწყობა thumbnail-ები; ფრონტში `loading="lazy"`.

## 11. ფაზები

| ფაზა | შინაარსი | სტატუსი |
|---|---|---|
| 0 | საფუძველი: სტრუქტურა, `core/` (Router, DB, View, Migrator, Session, Csrf), composer, console, Laragon vhost | ✅ 2026-10-24 |
| 1 | ადმინის გარსი: DASHMIN layout, login/logout, ადმინ-მომხმარებლები | ✅ 2026-10-24 |
| 2 | ენები, UI თარგმანები, საიტის პარამეტრები | ✅ 2026-10-24 |
| 3 | მედია ბიბლიოთეკა (+ ლოგო/favicon/OG ველების გადაყვანა მედიის არჩევაზე) | ✅ 2026-10-25 |
| 4 | გვერდები + ბლოკები, Poseify-ის სექციების გადატანა | ✅ 2026-10-25 |
| 5 | მენიუები | ✅ 2026-10-25 |
| 6 | კატეგორიები და კოლექციები | ⏳ შემდეგი |
| 7 | ფორმები + Inbox, newsletter | — |
| 8 | SEO: sitemap, robots, hreflang, redirects, 404 | — |
| 9 | ქეში, ოპტიმიზაცია, ატრიბუციის მოხსნა, ვებ დიზაინ სტუდიის კონტენტი | — |
| — | Deploy (მოგვიანებით, D9) | — |

## 12. ცვლილებების ისტორია

- **2026-10-25** — ფაზა 5: მართვადი მენიუები (header — 2 დონე, footer — 1 დონე): გვერდის/ბმულის პუნქტები, წარწერები და ბმულები ენებზე, ჩადგმული drag & drop, დამალვა, ახალ ფანჯარაში გახსნა; ფრონტის navbar და footer მენიუდან, ქეშით. `Core\Support\Url` — ბმულების ერთიანი შემოწმება (ახლა `//host` ბმულიც უარყოფილია). გასწორდა: ენის წაშლისას რჩებოდა მისი გვერდების, ბლოკებისა და მედიის თარგმანები. დაემატა D34–D36.
- **2026-10-25** — ფაზა 4: გვერდები (ხე, თარგმანები, slug ქართულიდან, SEO, OG სურათი, მონახაზი/წინასწარი ხედი, მთავარი გვერდი) და ბლოკების რედაქტორი (10 ტიპი სქემიდან, repeater, Jodit, link ველი, გადათრევით დალაგება, დამალვა, კოპირება). ფრონტი Poseify-ის layout-ზე: navbar (დროებით გვერდებიდან), ენების გადამრთველი, footer პარამეტრებიდან, hreflang/canonical/OG, Poseify-ის 404. Poseify-ის 6 გვერდი seed-ით. `core/`: Str, HtmlSanitizer; composer: HTMLPurifier. დაემატა D27–D33.
- **2026-10-25** — ფაზა 3: მედია ბიბლიოთეკა (ბადე, ფილტრები, drag & drop ატვირთვა პროგრესით, საქაღალდეები, alt/წარწერა ენებზე, URL-ის კოპირება, გამოყენების შემოწმება წაშლამდე), საერთო picker-ფანჯარა, პარამეტრების `image` ველები → მედიის id. `core/Media`: Uploader, ImageResizer. Poseify-ის დემო სურათები seed-ით. Uploader სურათს GD-ით სრულად ხსნის (დაზიანებული ფაილი არ მიიღება). გასწორდა: მშობელი პროექტის CSP `localhost/WEBDEV/`-ზე jQuery-ს ბლოკავდა. დაემატა D23–D26.
- **2026-10-24** — ფაზა 2: ენების CRUD (ძირითადი ენის წესები), UI თარგმანების რედაქტორი (ფილტრი ჯგუფით, ძებნა, „მხოლოდ ნაკლული“, ერთად შენახვა, გასაღების დამატება/წაშლა), პარამეტრები ჩანართებით (ზოგადი, კონტაქტი, სოც. ქსელები, SEO, კოდი), ტექნიკური რეჟიმი. `core/`: Cache, Translator, Settings, Validator `regex`/`url`. გასწორდა: ნამდვილი PUT/PATCH/DELETE-ის form body არ იკითხებოდა. დაემატა D18–D22; `settings`/`translations` სქემა შეიცვალა D19–D20-ის მიხედვით.
- **2026-10-24** — ფაზა 1: DASHMIN layout (sidebar `config/admin.php`-იდან), login/logout, ადმინ-მომხმარებლების CRUD, Dashboard ბოლო აქტივობით, `Core\Auth\Auth`, `RateLimiter`, `Validator`, flash/old/errors helper-ები, `admin:create`. მიგრაციები: admin_users, rate_limits, activity_log. გასწორდა: Apache-ზე 419 → 500 (ახლა სრული status line იგზავნება); flash მონაცემი იშლებოდა, თუ ზედიზედ ორ მოთხოვნაზე flash-დებოდა. DASHMIN-ის `main.js` ჩანაცვლდა `admin.js`-ით (დემო დიაგრამების გარეშე). დაემატა D15–D17.
- **2026-10-24** — ფაზა 0: `core/` ფრეიმვორკი (App/კონტეინერი, Router, Request/Response, DB, Migrator, View/Template, Session, Csrf, ErrorHandler, Console), composer autoload, `.env`, languages მიგრაცია + seed, vhost `webdev.test`, თემფლეითები → `resources/templates/`, assets → `public/assets/`. დაემატა D12–D14.
- **2026-10-24** — პირველი ვერსია: თემფლეითების ანალიზი, გადაწყვეტილებები D1–D11, სქემა, ფაზები.
