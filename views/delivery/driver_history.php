// 23-51598-2 (Arfan Rahman) - Summer 2025-26 Resell Market Management System
<?php
$activePage = 'history';
require __DIR__ . '/../partials/header.php';
?>
<div class="box" style="max-width:460px">
    <h2>Driver History</h2>
    <form method="get" action="index.php">
        <input type="hidden" name="page" value="delivery">
        <input type="hidden" name="action" value="history">
        <div class="form-group">
            <label>Choose a driver</label>
            <select name="driver_id" onchange="this.form.submit()">
                <option value="">-- select a driver --</option>
                <?php if ($allDrivers && mysqli_num_rows($allDrivers) > 0): mysqli_data_seek($allDrivers, 0);
                    while ($dr = mysqli_fetch_assoc($allDrivers)): ?>
                    <option value="<?= (int)$dr['id'] ?>" <?= $selectedDriver === (int)$dr['id'] ? 'selected' : '' ?>><?= esc($dr['name']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <noscript><button class="btn-primary" type="submit">View</button></noscript>
    </form>
</div>

<?php if ($selectedDriver > 0): ?>
<div class="box">
    <h2>Completed Deliveries</h2>
    <table>
        <thead><tr><th>Order</th><th>Product</th><th>Delivered On</th><th>Notes</th></tr></thead>
        <tbody>
        <?php if ($history && mysqli_num_rows($history) > 0): ?>
            <?php while ($h = mysqli_fetch_assoc($history)): ?>
                <tr>
                    <td>#<?= (int)$h['order_id'] ?></td>
                    <td><?= esc($h['product_name']) ?></td>
                    <td><?= date('d M Y', strtotime($h['scheduled_date'])) ?></td>
                    <td><?= esc($h['notes'] ?: '-') ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No completed deliveries for this driver yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
