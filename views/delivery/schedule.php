<?php
$activePage = 'schedule';
require __DIR__ . '/../partials/header.php';
?>
<div class="box">
    <h2>Delivery Schedule</h2>
    <p>Click a date to expand or collapse the deliveries for that day.</p>

    <?php if (!empty($groups)): ?>
        <?php $i = 0; foreach ($groups as $date => $items): $i++; $bodyId = "sched-" . $i; ?>
            <div class="date-group">
                <div class="date-header" data-body="<?= $bodyId ?>">
                    <span><?= date('l, d M Y', strtotime($date)) ?></span>
                    <span><?= count($items) ?> delivery(ies)</span>
                </div>
                <div class="date-body" id="<?= $bodyId ?>">
                    <table>
                        <thead><tr><th>Order</th><th>Product</th><th>Driver</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td>#<?= (int)$it['order_id'] ?></td>
                                <td><?= esc($it['product_name']) ?></td>
                                <td><?= $it['driver_name'] ? esc($it['driver_name']) : 'unassigned' ?></td>
                                <td><span class="badge"><?= ucfirst(str_replace('_',' ',$it['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No deliveries scheduled yet.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
