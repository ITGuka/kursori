<?php
/**
 * @var array $data
 * @var array $page გვერდის თარგმანი (title)
 * @var list<array{title: string, href: string}> $breadcrumbs წინაპრები (მიმდინარე გვერდის გარეშე)
 */
$heading = $data['title'] !== '' ? $data['title'] : ($page['title'] ?? '');
$background = media_url($data['image'], 'large');
$overlay = 'linear-gradient(rgba(0, 0, 0, .7), rgba(0, 0, 0, .7))';
$webp = ($media = media($data['image'])) !== null && !empty($media['webp']) ? app(App\Models\Media::class)->url($media, 'large', true) : '';
// image-set: WebP-ის მხარდამჭერი ბრაუზერი მას ჩატვირთავს, სხვა — პირველ (JPEG/PNG) წესს დატოვებს
$style = $background !== ''
    ? ' style="background-image: ' . $overlay . ', url(\'' . e($background) . '\')'
        . ($webp !== '' ? '; background-image: ' . $overlay . ', image-set(url(\'' . e($webp) . '\') type(\'image/webp\'), url(\'' . e($background) . '\') type(\'' . e($media['mime']) . '\'))' : '')
        . '" data-custom-bg'
    : '';
?>
<div class="page-header"<?= $style ?>>
    <div class="container text-center">
        <h1 class="display-4 text-uppercase mb-3 animated slideInDown"><?= e($heading) ?></h1>
        <?php if ($data['breadcrumb']): ?>
            <nav aria-label="breadcrumb" class="animated slideInDown">
                <ol class="breadcrumb justify-content-center text-uppercase mb-0">
                    <li class="breadcrumb-item"><a class="text-white" href="<?= e(url(locale())) ?>"><?= e(t('nav.home')) ?></a></li>
                    <?php foreach ($breadcrumbs ?? [] as $crumb): ?>
                        <li class="breadcrumb-item"><a class="text-white" href="<?= e($crumb['href'] ?: '#') ?>"><?= e($crumb['title']) ?></a></li>
                    <?php endforeach ?>
                    <li class="breadcrumb-item text-primary active" aria-current="page"><?= e($page['title'] ?? $heading) ?></li>
                </ol>
            </nav>
        <?php endif ?>
    </div>
</div>
