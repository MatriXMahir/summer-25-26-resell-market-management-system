<?php
$activePage = 'review';
require __DIR__ . '/../partials/header.php';
$selectedOrder = (int)($_GET['order_id'] ?? 0);
?>
<div class="box" style="max-width:460px">
    <h2>Write a Review</h2>
    <?php if (!empty($reviewErrors)): ?>
        <div class="alert alert-error"><?php foreach ($reviewErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=buyer&action=review_save"
          onsubmit="return validateForm(this, {
              order_id: [{type:'required', message:'Please select an order.'}],
              comment:  [{type:'required', message:'Please write a short comment.'}]
          });">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Order</label>
            <select name="order_id">
                <option value="">-- select an order --</option>
                <?php if ($eligibleOrders && mysqli_num_rows($eligibleOrders) > 0): mysqli_data_seek($eligibleOrders, 0);
                    while ($o = mysqli_fetch_assoc($eligibleOrders)): ?>
                    <option value="<?= (int)$o['id'] ?>" <?= $selectedOrder === (int)$o['id'] ? 'selected' : '' ?>>
                        #<?= (int)$o['id'] ?> - <?= esc($o['name']) ?>
                    </option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Rating (1-5)</label>
            <select name="rating">
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Terrible</option>
            </select>
        </div>
        <div class="form-group">
            <label>Comment</label>
            <textarea name="comment" rows="4"></textarea>
        </div>
        <button class="btn-primary" type="submit">Submit Review</button>
    </form>
</div>

<div class="box">
    <h2>My Reviews</h2>
    <table>
        <thead><tr><th>Product</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
        <tbody>
        <?php if ($myReviews && mysqli_num_rows($myReviews) > 0): ?>
            <?php while ($r = mysqli_fetch_assoc($myReviews)): ?>
                <tr>
                    <td><?= esc($r['product_name']) ?></td>
                    <td><?= str_repeat('*', (int)$r['rating']) ?></td>
                    <td><?= esc($r['comment']) ?></td>
                    <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No reviews yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
