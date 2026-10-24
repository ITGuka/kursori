<ul class="nav nav-tabs mb-0">
    <li class="nav-item"><a class="nav-link<?= is_route('admin.newsletter.campaigns', 'admin.newsletter.create', 'admin.newsletter.edit') ? ' active' : '' ?>" href="<?= e(route('admin.newsletter.campaigns')) ?>">კამპანიები</a></li>
    <li class="nav-item"><a class="nav-link<?= is_route('admin.newsletter.subscribers') ? ' active' : '' ?>" href="<?= e(route('admin.newsletter.subscribers')) ?>">გამომწერები</a></li>
</ul>
