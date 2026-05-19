// Mark notifications as read when dropdown opens
document.addEventListener('DOMContentLoaded', function () {
    const notifDropdown = document.querySelector('.notif-dropdown');
    if (notifDropdown) {
        notifDropdown.closest('.dropdown')?.addEventListener('shown.bs.dropdown', function () {
            fetch(BASE_URL + 'pages/profile/mark_read.php', { method: 'POST' })
                .then(() => {
                    const badge = document.querySelector('.notif-badge');
                    if (badge) badge.remove();
                });
        });
    }
});
