<?php
/** @var Core\View\Template $this @var array $data */
$members = array_values(array_filter($data['members'], fn ($member) => $member['name'] !== ''));
?>
<div class="container-fluid py-5">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <div class="row g-4">
            <?php foreach ($members as $i => $member): ?>
                <?php
                // „დასახელება: მნიშვნელობა“ ხაზები → hover-ზე მარცხნივ დასახელებები, მარჯვნივ მნიშვნელობები
                $attributes = [];
                foreach (preg_split('/\R/u', $member['attributes']) ?: [] as $line) {
                    if (str_contains($line, ':')) {
                        [$label, $value] = array_map('trim', explode(':', $line, 2));
                        $attributes[] = [$label, $value];
                    }
                }
                $image = media_url($member['image'], 'medium');
                ?>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="<?= e(0.1 + ($i % 4) * 0.2) ?>s">
                    <div class="team-item">
                        <div class="team-body">
                            <?php if ($attributes !== []): ?>
                                <div class="team-before"><?php foreach ($attributes as [$label]): ?><span><?= e($label) ?></span><?php endforeach ?></div>
                            <?php endif ?>
                            <?php if ($image !== ''): ?>
                                <img class="img-fluid" src="<?= e($image) ?>" alt="<?= e(media_alt($member['image']) ?: $member['name']) ?>" loading="lazy">
                            <?php endif ?>
                            <?php if ($attributes !== []): ?>
                                <div class="team-after"><?php foreach ($attributes as [, $value]): ?><span><?= e($value) ?></span><?php endforeach ?></div>
                            <?php endif ?>
                        </div>
                        <a class="team-name"<?= link_attrs($member['link']) ?>>
                            <h5 class="text-uppercase mb-0"><?= e($member['name']) ?></h5>
                        </a>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
