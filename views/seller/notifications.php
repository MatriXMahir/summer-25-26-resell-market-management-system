<?php
$activePage = 'notifications';
require __DIR__ . '/../partials/header.php';
?>
<div class="box">
    <h2>Notifications</h2>
    <p>Low-stock alerts are created automatically whenever a product's stock drops below <?= LOW_STOCK ?> units.</p>

    <?php if ($notifications && mysqli_num_rows($notifications) > 0): ?>
        <?php while ($n = mysqli_fetch_assoc($notifications)): ?>
            <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                <div>
                    <?= esc($n['message']) ?>
                    <br><small><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></small>
                </div>
                <?php if (!$n['is_read']): ?>
                    <form method="post" action="index.php?page=seller&action=notif_read">
                        <?= csrf_field() ?>
                        <input type="hidden" name="notif_id" value="<?= (int)$n['id'] ?>">
                        <button class="btn-sm" type="submit">Mark read</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No notifications yet.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
