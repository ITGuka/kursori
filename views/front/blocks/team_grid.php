<?php
/** @var Core\View\Template $this @var array $data */
$members = array_values(array_filter($data['members'], fn ($member) => $member['name'] !== ''));
$agents = config('blocks.team_grid.fields.members.fields.agent.options', []);
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
                $agent = (string)($member['agent'] ?? '');
                $icon = (string)($member['icon'] ?? '');
                ?>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="<?= e(0.1 + ($i % 4) * 0.2) ?>s">
                    <div class="team-item">
                        <div class="team-body">
                            <?php if ($attributes !== []): ?>
                                <div class="team-before"><?php foreach ($attributes as [$label]): ?><span><?= e($label) ?></span><?php endforeach ?></div>
                            <?php endif ?>
                            <?php if ($image !== ''): ?>
                                <?= picture($member['image'], 'medium', ['class' => 'img-fluid', 'alt' => media_alt($member['image']) ?: $member['name']], '(min-width: 992px) 25vw, (min-width: 576px) 50vw, 100vw') ?>
                            <?php elseif ($icon !== ''): ?>
                                <!-- ფოტოს ნაცვლად როლის აიკონი (მაგ. AI აგენტი) -->
                                <div class="team-icon" aria-hidden="true"><i class="<?= e($icon) ?>"></i></div>
                            <?php endif ?>
                            <?php if (isset($agents[$agent]) && $agent !== ''): ?>
                                <span class="team-agent team-agent-<?= e($agent) ?>"><?= e($agents[$agent]) ?></span>
                            <?php endif ?>
                            <?php if ($attributes !== []): ?>
                                <div class="team-after"><?php foreach ($attributes as [, $value]): ?><span><?= e($value) ?></span><?php endforeach ?></div>
                            <?php endif ?>
                        </div>
                        <a class="team-name<?= ($member['position'] ?? '') !== '' ? ' flex-column' : '' ?>"<?= link_attrs($member['link']) ?>>
                            <h5 class="text-uppercase mb-0"><?= e($member['name']) ?></h5>
                            <?php if (($member['position'] ?? '') !== ''): ?><small class="text-primary"><?= e($member['position']) ?></small><?php endif ?>
                        </a>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
        <?php if (($data['button_label'] ?? '') !== '' && ($data['button_link'] ?? null)): ?>
            <div class="text-center mt-5">
                <a<?= link_attrs($data['button_link']) ?> class="btn btn-outline-primary border-2 py-3 px-5"><?= e($data['button_label']) ?></a>
            </div>
        <?php endif ?>
    </div>
</div>
