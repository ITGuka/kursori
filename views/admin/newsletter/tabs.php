<div class="nav-align-top">
    <ul class="nav nav-pills flex-wrap gap-2 mb-6">
        <li class="nav-item"><a class="nav-link<?= is_route('admin.newsletter.campaigns', 'admin.newsletter.create', 'admin.newsletter.edit') ? ' active' : '' ?>" href="<?= e(route('admin.newsletter.campaigns')) ?>"><i class="icon-base bx bx-paper-plane icon-sm me-1"></i>კამპანიები</a></li>
        <li class="nav-item"><a class="nav-link<?= is_route('admin.newsletter.subscribers') ? ' active' : '' ?>" href="<?= e(route('admin.newsletter.subscribers')) ?>"><i class="icon-base bx bx-group icon-sm me-1"></i>გამომწერები</a></li>
    </ul>
</div>
