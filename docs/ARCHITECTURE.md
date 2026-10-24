# WEBDEV — არქიტექტურა

> ცოცხალი დოკუმენტი: ახლდება ყოველი ფაზის ან არქიტექტურული გადაწყვეტილების შემდეგ.
> ბოლო განახლება: 2026-10-24 · მიმდინარე ფაზა: **3 (მედია ბიბლიოთეკა)** · ფაზები 0–2 დასრულდა

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
│   ├── Support/             ✅ Env, Config, Session (flash), Csrf, Log, Validator, RateLimiter, Cache · ⏳ Str, Arr
│   ├── Console/             ✅ Console, Command, Output, Commands/*
│   ├── Auth/                ✅ Auth
│   ├── I18n/                ✅ Translator (loader-ით — წყარო პროექტი წყვეტს)
│   ├── Settings/            ✅ Settings (განსაზღვრებები კონფიგიდან, მნიშვნელობები ბაზიდან)
│   ├── Media/               ⏳ Uploader, ImageResizer (GD)
│   └── Mail/                ⏳ Mailer (PHPMailer wrapper)
├── app/                     ← პროექტის კოდი (namespace App\)
│   ├── Controllers/Front/   ✅ Home (დროებითი) · ⏳ Page, Collection, Form, Newsletter, Sitemap
│   ├── Controllers/Admin/   ✅ Auth, Dashboard, User, Language, Translation, Settings · ⏳ Pages, Blocks,
│   │                          Menus, Categories, Collections, Media, Inbox, Newsletter
│   ├── Middleware/          ✅ SetLocale (locale), CheckMaintenance (maintenance), Authenticate (auth),
│   │                          RedirectIfAuthenticated (guest)
│   ├── Models/              ✅ AdminUser, ActivityLog, Language, Translation · ⏳ დანარჩენი ცხრილები
│   ├── Console/Commands/    ✅ CreateAdminCommand (admin:create)
│   ├── Blocks/              ⏳ ბლოკების რეესტრი და სქემები
│   └── routes/              ✅ admin.php (/admin), web.php (/{lang})
├── bootstrap/app.php        ✅ App-ის აწყობა: middleware, route-ები
├── views/
│   ├── front/               ✅ layouts/base, home (დროებითი) · ⏳ Poseify partials, blocks/*
│   ├── admin/               ✅ layouts/*, partials/* (flash, pagination), auth/login, dashboard, users/*,
│   │                          languages/*, translations/index, settings/index + field · ⏳ მოდულები
│   └── errors/              ✅ default.php, maintenance.php
├── config/                  ✅ app, database (.env-იდან), auth, validation (ქართული შეტყობინებები), admin (sidebar),
│                              settings (პარამეტრების განსაზღვრებები)
├── database/
│   ├── migrations/          ✅ languages, admin_users, rate_limits, activity_log, translation_keys/values, settings
│   └── seeds/               ✅ 01_languages (ka, en), 02_admin_user (.env ADMIN_*), 03_translations (Poseify UI ტექსტები)
├── resources/templates/     ✅ poseify/, dashmin/ — საწყისი HTML (reference) + zip (git-ის გარეშე)
├── storage/                 ✅ cache/, logs/ (git-ის გარეშე)
├── bin/console              ✅ CLI
├── .env / .env.example      ✅
├── docs/ARCHITECTURE.md
└── composer.json            ✅ PSR-4: Core\ → core/, App\ → app/
```

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

გვერდი = ბლოკების დალაგებული სია. ბლოკის ყოველ ტიპს `app/Blocks/`-ში აქვს სქემა:

```php
'services' => [
    'view'   => 'blocks/services',
    'fields' => [
        'subtitle' => ['type' => 'text',  'translatable' => true],
        'title'    => ['type' => 'text',  'translatable' => true],
        'source'   => ['type' => 'select', 'options' => ['collection', 'manual']],
        'category' => ['type' => 'category', 'collection' => 'services'],
        'limit'    => ['type' => 'number', 'default' => 4],
    ],
],
```

ადმინი სქემიდან ფორმას ავტომატურად აგენერირებს. ველის ტიპები: `text`, `textarea`, `wysiwyg`, `image`, `gallery`, `link` (url + ტექსტი + target), `select`, `number`, `toggle`, `repeater` (ჩამონათვალი, მაგ. სლაიდები/bullet-ები), `category`, `collection_items`.

| ბლოკი | Poseify-ის წყარო | შინაარსი |
|---|---|---|
| `hero_carousel` | index Header carousel | სლაიდები: სურათი, ქვესათაური, სათაური, ტექსტი, ღილაკი |
| `page_header` | შიდა გვერდების header | სათაური + breadcrumb (ავტომატური) |
| `about` | About | სათაურები, ტექსტი, bullet-ები, 2 ღილაკი, სურათი |
| `services` | Service | კოლექციიდან `services` (ზიგზაგ layout) |
| `banner_signup` | Banner | სათაური, ტექსტი, newsletter ფორმა |
| `team_grid` | Team | კოლექციიდან `team` + ატრიბუტები (hover-ზე) |
| `testimonials` | Testimonial | კოლექციიდან `testimonials` (owl carousel) |
| `contact` | Contact | კონტაქტები settings-იდან + contact ფორმა |
| `map` | Google Map | embed URL |
| `application_form` | — (Banner-ის სტილით) | განაცხადის ფორმა (ყოფილი Casting) |
| `rich_text` | — | თავისუფალი WYSIWYG კონტენტი |

ბლოკის მონაცემები: არათარგმნადი ველები `page_blocks.settings` (JSON), თარგმნადი — `page_block_translations.content` (JSON, თითო ენაზე).

## 5. ბაზის სქემა

ყველა ცხრილი: `utf8mb4_unicode_ci`, `created_at`/`updated_at`. თარგმნადი ობიექტები — მთავარი ცხრილი + `*_translations` (`{entity}_id`, `lang`, ...), unique (`{entity}_id`, `lang`).

| ჯგუფი | ცხრილები და მთავარი ველები |
|---|---|
| ენები | ✅ `languages` (code, name, native_name, is_default, is_active, sort) |
| პარამეტრები | ✅ `settings` (setting_key, lang — '' არათარგმნადისთვის, value) — განსაზღვრებები `config/settings.php`-ში (D20) |
| UI თარგმანები | ✅ `translation_keys` (group, key, description) + `translation_values` (key_id → CASCADE, lang, value) → `t('nav.contact')` |
| გვერდები | `pages` (parent_id, status draft/published, is_home, sort, published_at) + `page_translations` (title, slug, meta_title, meta_description, og_image_id) |
| ბლოკები | `page_blocks` (page_id, type, sort, is_active, settings JSON) + `page_block_translations` (content JSON) |
| მენიუ | `menus` (location: header/footer) + `menu_items` (menu_id, parent_id, type page/category/item/url, target_id, url, target, icon, sort, is_active) + `menu_item_translations` (label, url) |
| კატეგორიები | `categories` (collection, parent_id, sort, is_active) + `category_translations` (name, slug, description) |
| კოლექციები | `collection_items` (collection: services/team/testimonials/…, category_id, image_id, attributes JSON, sort, is_active) + `collection_item_translations` (title, slug, subtitle, excerpt, body, attributes JSON) |
| მედია | `media` (path, mime, size, width, height, folder) + `media_translations` (alt, caption) |
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
- ატვირთვა: MIME + გაფართოების whitelist, ახალი ფაილის სახელი, `uploads/`-ში PHP-ის შესრულება აკრძალულია.
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
| 3 | მედია ბიბლიოთეკა (+ ლოგო/favicon/OG ველების გადაყვანა მედიის არჩევაზე) | ⏳ შემდეგი |
| 4 | გვერდები + ბლოკები, Poseify-ის სექციების გადატანა | — |
| 5 | მენიუები | — |
| 6 | კატეგორიები და კოლექციები | — |
| 7 | ფორმები + Inbox, newsletter | — |
| 8 | SEO: sitemap, robots, hreflang, redirects, 404 | — |
| 9 | ქეში, ოპტიმიზაცია, ატრიბუციის მოხსნა, ვებ დიზაინ სტუდიის კონტენტი | — |
| — | Deploy (მოგვიანებით, D9) | — |

## 12. ცვლილებების ისტორია

- **2026-10-24** — ფაზა 2: ენების CRUD (ძირითადი ენის წესები), UI თარგმანების რედაქტორი (ფილტრი ჯგუფით, ძებნა, „მხოლოდ ნაკლული“, ერთად შენახვა, გასაღების დამატება/წაშლა), პარამეტრები ჩანართებით (ზოგადი, კონტაქტი, სოც. ქსელები, SEO, კოდი), ტექნიკური რეჟიმი. `core/`: Cache, Translator, Settings, Validator `regex`/`url`. გასწორდა: ნამდვილი PUT/PATCH/DELETE-ის form body არ იკითხებოდა. დაემატა D18–D22; `settings`/`translations` სქემა შეიცვალა D19–D20-ის მიხედვით.
- **2026-10-24** — ფაზა 1: DASHMIN layout (sidebar `config/admin.php`-იდან), login/logout, ადმინ-მომხმარებლების CRUD, Dashboard ბოლო აქტივობით, `Core\Auth\Auth`, `RateLimiter`, `Validator`, flash/old/errors helper-ები, `admin:create`. მიგრაციები: admin_users, rate_limits, activity_log. გასწორდა: Apache-ზე 419 → 500 (ახლა სრული status line იგზავნება); flash მონაცემი იშლებოდა, თუ ზედიზედ ორ მოთხოვნაზე flash-დებოდა. DASHMIN-ის `main.js` ჩანაცვლდა `admin.js`-ით (დემო დიაგრამების გარეშე). დაემატა D15–D17.
- **2026-10-24** — ფაზა 0: `core/` ფრეიმვორკი (App/კონტეინერი, Router, Request/Response, DB, Migrator, View/Template, Session, Csrf, ErrorHandler, Console), composer autoload, `.env`, languages მიგრაცია + seed, vhost `webdev.test`, თემფლეითები → `resources/templates/`, assets → `public/assets/`. დაემატა D12–D14.
- **2026-10-24** — პირველი ვერსია: თემფლეითების ანალიზი, გადაწყვეტილებები D1–D11, სქემა, ფაზები.
