<?php
$activePage = 'products';
require __DIR__ . '/../partials/header.php';
?>
<div class="box">
    <h2>Pending Product Approval</h2>
    <table>
        <thead><tr><th>Product</th><th>Seller</th><th>Price</th><th>Stock</th><th></th></tr></thead>
        <tbody>
        <?php if ($pending && mysqli_num_rows($pending) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($pending)): ?>
                <tr>
                    <td><?= esc($p['name']) ?><br><small><?= esc($p['description']) ?></small></td>
                    <td><?= esc($p['seller_name']) ?></td>
                    <td><?= CURRENCY ?> <?= number_format((float)$p['price'], 2) ?></td>
                    <td><?= (int)$p['stock_qty'] ?></td>
                    <td>
                        <a class="btn-sm" href="index.php?page=admin&action=product_approve&id=<?= (int)$p['id'] ?>&<?= csrf_url() ?>">Approve</a>
                        <a class="btn-sm" href="index.php?page=admin&action=product_reject&id=<?= (int)$p['id'] ?>&<?= csrf_url() ?>">Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Nothing waiting for approval.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="box">
    <h2>Recently Reviewed</h2>
    <table>
        <thead><tr><th>Product</th><th>Seller</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($reviewed && mysqli_num_rows($reviewed) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($reviewed)): ?>
                <tr>
                    <td><?= esc($p['name']) ?></td>
                    <td><?= esc($p['seller_name']) ?></td>
                    <td><span class="badge"><?= ucfirst($p['status']) ?></span></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No history yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
