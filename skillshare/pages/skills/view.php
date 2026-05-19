<?php
define('BASE_URL', '/skillshare/');
define('SITE_NAME', 'SkillBridge');
require_once '../../config/db.php';
require_once '../../config/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT s.*, u.name AS uname, u.department, u.bio AS ubio, u.semester, u.id AS uid FROM skills s JOIN users u ON s.user_id=u.id WHERE s.id=?");
$stmt->execute([$id]);
$skill = $stmt->fetch();

if (!$skill) { setFlash('danger','Skill not found.'); redirect(BASE_URL.'pages/skills/browse.php'); }

// Increment views
$pdo->prepare("UPDATE skills SET views=views+1 WHERE id=?")->execute([$id]);

// Reviews
$reviews = $pdo->prepare("SELECT r.*, u.name AS rname, u.department AS rdept FROM reviews r JOIN users u ON r.reviewer_id=u.id WHERE r.skill_id=? ORDER BY r.created_at DESC");
$reviews->execute([$id]);
$reviewList = $reviews->fetchAll();
$avgRating = count($reviewList) ? round(array_sum(array_column($reviewList,'rating'))/count($reviewList),1) : 0;

// Has current user already requested?
$alreadyRequested = false;
$alreadyReviewed  = false;
if (isLoggedIn()) {
    $rq = $pdo->prepare("SELECT id FROM requests WHERE skill_id=? AND sender_id=?");
    $rq->execute([$id, currentUserId()]);
    $alreadyRequested = (bool)$rq->fetch();

    $rv = $pdo->prepare("SELECT id FROM reviews WHERE skill_id=? AND reviewer_id=?");
    $rv->execute([$id, currentUserId()]);
    $alreadyReviewed = (bool)$rv->fetch();
}

// Handle interest request
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    requireLogin();
    if ($_POST['action']==='request' && !$alreadyRequested && currentUserId()!==$skill['uid']) {
        $msg = trim($_POST['message'] ?? '');
        $pdo->prepare("INSERT INTO requests (skill_id,sender_id,message) VALUES (?,?,?)")->execute([$id,currentUserId(),$msg]);
        // Notify owner
        $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")->execute([
            $skill['uid'],
            currentUser()['name'] . ' is interested in your skill: ' . $skill['title'],
            'pages/skills/view.php?id=' . $id
        ]);
        setFlash('success','Interest sent successfully!');
        redirect(BASE_URL.'pages/skills/view.php?id='.$id);
    }
    if ($_POST['action']==='review' && !$alreadyReviewed && currentUserId()!==$skill['uid']) {
        $rating  = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        if ($rating>=1 && $rating<=5) {
            $pdo->prepare("INSERT INTO reviews (reviewer_id,reviewed_id,skill_id,rating,comment) VALUES (?,?,?,?,?)")
                ->execute([currentUserId(), $skill['uid'], $id, $rating, $comment]);
            setFlash('success','Review submitted!');
            redirect(BASE_URL.'pages/skills/view.php?id='.$id);
        }
    }
}

// Related skills
$related = $pdo->prepare("SELECT s.*, u.name AS uname FROM skills s JOIN users u ON s.user_id=u.id WHERE s.category=? AND s.id!=? AND s.status='active' LIMIT 3");
$related->execute([$skill['category'], $id]);
$relatedList = $related->fetchAll();

require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Skill Header -->
        <div class="skill-detail-header">
            <div class="d-flex gap-2 mb-3">
                <span class="category-pill" style="background:rgba(255,255,255,0.2);color:#fff;"><?= getCategoryIcon($skill['category']) ?> <?= sanitize($skill['category']) ?></span>
                <span class="category-pill type-<?= $skill['skill_type'] ?>"><?= $skill['skill_type']==='offer'?'✅ Offering':'🔍 Wanted' ?></span>
            </div>
            <h2 style="font-family:'Syne',sans-serif;font-weight:800;"><?= sanitize($skill['title']) ?></h2>
            <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-sm"><?= strtoupper(substr($skill['uname'],0,1)) ?></div>
                    <div>
                        <div class="fw-bold"><?= sanitize($skill['uname']) ?></div>
                        <div style="font-size:0.8rem;opacity:0.7;"><?= sanitize($skill['department']) ?> · <?= sanitize($skill['semester']) ?> sem</div>
                    </div>
                </div>
                <div style="opacity:0.7;font-size:0.85rem;"><i class="bi bi-eye me-1"></i><?= $skill['views'] ?> views</div>
                <div style="opacity:0.7;font-size:0.85rem;"><i class="bi bi-clock me-1"></i><?= timeAgo($skill['created_at']) ?></div>
            </div>
        </div>

        <!-- Description -->
        <div class="skill-card mb-4">
            <div class="skill-card-body">
                <h5 class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">About this Skill</h5>
                <p style="line-height:1.8;"><?= nl2br(sanitize($skill['description'])) ?></p>
                <?php if ($skill['tags']): ?>
                <div class="mt-3">
                    <?php foreach (explode(',', $skill['tags']) as $tag): ?>
                    <span class="tag-pill">#<?= sanitize(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews -->
        <div class="skill-card mb-4">
            <div class="skill-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-family:'Syne',sans-serif;">Reviews (<?= count($reviewList) ?>)</h5>
                    <?php if ($avgRating): ?>
                    <div class="d-flex align-items-center gap-2">
                        <span class="rating-avg" style="font-size:1.5rem;"><?= $avgRating ?></span>
                        <div class="stars"><?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆',5-round($avgRating)) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php foreach ($reviewList as $rv): ?>
                <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                    <div class="avatar-xs flex-shrink-0"><?= strtoupper(substr($rv['rname'],0,1)) ?></div>
                    <div>
                        <div class="fw-bold small"><?= sanitize($rv['rname']) ?> <span class="text-muted fw-normal"><?= sanitize($rv['rdept']) ?></span></div>
                        <div class="stars small"><?= str_repeat('★',$rv['rating']) ?><?= str_repeat('☆',5-$rv['rating']) ?></div>
                        <p class="mb-0 small mt-1"><?= sanitize($rv['comment']) ?></p>
                        <div class="text-muted" style="font-size:0.72rem;"><?= timeAgo($rv['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($reviewList)): ?>
                <p class="text-muted small">No reviews yet. Be the first!</p>
                <?php endif; ?>

                <!-- Leave review -->
                <?php if (isLoggedIn() && currentUserId()!==$skill['uid'] && !$alreadyReviewed): ?>
                <div class="mt-3 pt-3 border-top">
                    <div class="fw-bold small mb-2">Leave a Review</div>
                    <form method="POST">
                        <input type="hidden" name="action" value="review">
                        <div class="mb-2">
                            <select name="rating" class="form-select form-select-sm" style="width:auto;" required>
                                <option value="">Rating</option>
                                <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                <option value="4">⭐⭐⭐⭐ (4)</option>
                                <option value="3">⭐⭐⭐ (3)</option>
                                <option value="2">⭐⭐ (2)</option>
                                <option value="1">⭐ (1)</option>
                            </select>
                        </div>
                        <textarea name="comment" class="form-control form-control-sm mb-2" rows="2" placeholder="Share your experience..."></textarea>
                        <button type="submit" class="btn btn-sm" style="background:var(--primary);color:#fff;border-radius:8px;">Submit Review</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Action Card -->
        <div class="skill-card mb-4 p-4">
            <?php if (!isLoggedIn()): ?>
            <p class="text-muted small">Log in to express interest in this skill.</p>
            <a href="<?= BASE_URL ?>pages/auth/login.php" class="btn btn-accent w-100">Login to Connect</a>
            <?php elseif (currentUserId()===$skill['uid']): ?>
            <div class="text-center text-muted small mb-3">This is your skill</div>
            <a href="edit.php?id=<?= $id ?>" class="btn w-100 mb-2" style="background:var(--primary);color:#fff;border-radius:10px;"><i class="bi bi-pencil me-1"></i>Edit Skill</a>
            <a href="<?= BASE_URL ?>pages/profile/my_skills.php" class="btn btn-outline-secondary w-100">My Skills</a>
            <?php elseif ($alreadyRequested): ?>
            <div class="text-center py-2">
                <div style="font-size:2rem;">✅</div>
                <div class="fw-bold">Interest Sent!</div>
                <div class="text-muted small">Waiting for response</div>
            </div>
            <?php else: ?>
            <div class="fw-bold mb-1" style="font-family:'Syne',sans-serif;">Interested?</div>
            <p class="text-muted small mb-3">Send a message to <?= sanitize(explode(' ',$skill['uname'])[0]) ?> and get started.</p>
            <form method="POST">
                <input type="hidden" name="action" value="request">
                <textarea name="message" class="form-control mb-2" rows="3" placeholder="Introduce yourself and explain why you're interested..."></textarea>
                <button type="submit" class="btn-submit">Send Interest</button>
            </form>
            <?php endif; ?>
        </div>

        <!-- About the teacher -->
        <div class="skill-card mb-4 p-4">
            <div class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">About the Student</div>
            <div class="d-flex gap-3 align-items-center mb-3">
                <div class="avatar-lg" style="width:50px;height:50px;font-size:1.2rem;"><?= strtoupper(substr($skill['uname'],0,1)) ?></div>
                <div>
                    <div class="fw-bold"><?= sanitize($skill['uname']) ?></div>
                    <div class="text-muted small"><?= sanitize($skill['department']) ?></div>
                    <div class="text-muted small"><?= sanitize($skill['semester']) ?> Semester</div>
                </div>
            </div>
            <?php if ($skill['ubio']): ?>
            <p class="small text-muted"><?= sanitize($skill['ubio']) ?></p>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>pages/profile/view.php?id=<?= $skill['uid'] ?>" class="btn btn-outline-secondary btn-sm w-100">View Full Profile</a>
        </div>

        <!-- Related Skills -->
        <?php if (!empty($relatedList)): ?>
        <div class="skill-card p-4">
            <div class="fw-bold mb-3" style="font-family:'Syne',sans-serif;">Related Skills</div>
            <?php foreach ($relatedList as $r): ?>
            <a href="view.php?id=<?= $r['id'] ?>" class="d-flex gap-2 mb-3 text-decoration-none text-dark align-items-start">
                <span style="font-size:1.2rem;"><?= getCategoryIcon($r['category']) ?></span>
                <div>
                    <div class="small fw-semibold"><?= sanitize($r['title']) ?></div>
                    <div class="small text-muted"><?= sanitize($r['uname']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
