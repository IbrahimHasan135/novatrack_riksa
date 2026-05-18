<?php
// navbar.php — render sidebar untuk halaman yang ter-autentikasi
// Login page sendiri-sendiri dengan self-contained HTML
$curUser = \Core\Auth::getInstance()->check() ? \Core\Auth::getInstance()->user() : null;
?>

<?php if (!(isset($isLoginPage) && $isLoginPage)): ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ─── SIDEBAR ─── -->
<?php
$registry = \Core\ModuleRegistry::getInstance();
$sidebar  = new \Core\Sidebar($registry);
echo $sidebar->render();
?>

<!-- ─── Sidebar JS ─── -->
<script>
(function () {
    const toggle   = document.getElementById('sidebar_toggle');
    const overlay  = document.getElementById('sidebar_overlay');

    if (!toggle) return;

    function open()  { document.body.classList.add('sidebar-open'); }
    function close() { document.body.classList.remove('sidebar-open'); }

    toggle.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    overlay?.addEventListener('click', close);

    // Highlight active nav item
    const here = window.location.pathname.replace(/[?#].*$/, '').replace(/\/$/, '') || '/';
    document.querySelectorAll('.nav-link').forEach(a => {
        if ((a.getAttribute('href') || '').replace(/\/$/, '') === here) {
            a.classList.add('active');
        }
    });

    // Sidebar search filter
    const searchInput = document.querySelector('.sidebar-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.nav-link').forEach(a => {
                const label = (a.querySelector('span')?.textContent || '').toLowerCase();
                a.style.display  = label.includes(q) ? '' : 'none';
            });
        });
    }

    // Confirm logout
    document.querySelectorAll('a[href="<?= app_url('logout'); ?>"]').forEach(a =>
        a.addEventListener('click', e => { if (!confirm('Keluar dari NovaTrack Riksa?')) e.preventDefault(); })
    );
})();
</script>
<?php endif; ?>
