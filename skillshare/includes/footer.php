</div><!-- end container -->

<footer class="site-footer mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="footer-brand"><span>⚡</span> SkillBridge</div>
                <p class="footer-desc">A platform where students teach, learn, and grow together by sharing their unique skills.</p>
            </div>
            <div class="col-md-2 mb-4">
                <div class="footer-heading">Explore</div>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>pages/skills/browse.php">Browse Skills</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4">
                <div class="footer-heading">Account</div>
                <ul class="footer-links">
                    <?php if (isLoggedIn()): ?>
                    <li><a href="<?= BASE_URL ?>pages/profile/view.php?id=<?= currentUserId() ?>">My Profile</a></li>
                    <li><a href="<?= BASE_URL ?>pages/auth/logout.php">Logout</a></li>
                    <?php else: ?>
                    <li><a href="<?= BASE_URL ?>pages/auth/login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>pages/auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <div class="footer-heading">Categories</div>
                <div class="footer-tags">
                    <?php
                    $cats = ['Programming','Data Science','Design','Mobile','Language','Photography','Mathematics','Music'];
                    foreach ($cats as $c):
                    ?>
                    <a href="<?= BASE_URL ?>pages/skills/browse.php?category=<?= urlencode($c) ?>" class="footer-tag"><?= $c ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> SkillBridge — Built with PHP & MySQL</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
