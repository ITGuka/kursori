<?php
/**
 * ხშირი კითხვები: გასაშლელი სია (<details>, JS-ის გარეშე) + FAQPage სტრუქტურირებული მონაცემები Google-ისთვის.
 * @var list<array{question: string, answer: string}> $items
 */
$items = array_values(array_filter($items, fn ($item) => $item['question'] !== '' && $item['answer'] !== ''));
if ($items === []) {
    return;
}
$schema = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn ($item) => [
        '@type'          => 'Question',
        'name'           => $item['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
    ], $items),
];
?>
<div class="faq-list">
    <?php foreach ($items as $i => $item): ?>
        <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
            <summary><?= e($item['question']) ?></summary>
            <p class="mb-0"><?= nl2br(e($item['answer'])) ?></p>
        </details>
    <?php endforeach ?>
</div>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
