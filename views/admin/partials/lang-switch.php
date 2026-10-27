<?php
/**
 * ფორმის შესავსები ენის გადამრთველი: [data-lang="xx"] ნაწილები ჩანს მხოლოდ არჩეულ ენაზე (ან „ყველა“).
 * დამალვის CSS და საწყისი ენა — admin/layouts/app, ლოგიკა — admin.js.
 * @var list<array> $languages
 */
// ენის კოდი → დროშის ქვეყანა (flag-icons); სიაში არ არის → თავად კოდი
$flags = ['ka' => 'ge', 'en' => 'gb', 'uk' => 'ua', 'el' => 'gr', 'hy' => 'am', 'he' => 'il', 'ja' => 'jp', 'ko' => 'kr',
    'zh' => 'cn', 'cs' => 'cz', 'da' => 'dk', 'sv' => 'se', 'et' => 'ee', 'sl' => 'si', 'sr' => 'rs', 'kk' => 'kz',
    'be' => 'by', 'fa' => 'ir', 'ar' => 'sa', 'hi' => 'in', 'vi' => 'vn', 'nb' => 'no', 'ms' => 'my'];
?>
<?php if (count($languages ?? []) > 1): ?>
    <div class="lang-switch d-none" role="group" aria-label="შესავსები ენა" data-lang-switch>
        <?php foreach ($languages as $language): ?>
            <button type="button" class="lang-switch-btn" data-lang-option="<?= e($language['code']) ?>" title="<?= e($language['native_name']) ?>">
                <span class="fi fis fi-<?= e($flags[$language['code']] ?? $language['code']) ?>"></span><?= e(strtoupper($language['code'])) ?>
            </button>
        <?php endforeach ?>
        <button type="button" class="lang-switch-btn" data-lang-option="all" title="ყველა ენა ერთად"><i class="icon-base bx bx-layer"></i>ყველა</button>
    </div>
<?php endif ?>
