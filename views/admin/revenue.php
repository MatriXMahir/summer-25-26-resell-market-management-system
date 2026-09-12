<?php
$activePage = 'revenue';
require __DIR__ . '/../partials/header.php';
?>
<div class="box">
    <h2>Monthly Revenue</h2>
    <p>This month: <b><?= CURRENCY ?> <?= number_format($thisMonthTotal, 2) ?></b> &nbsp;|&nbsp; All-time: <b><?= CURRENCY ?> <?= number_format($grandTotal, 2) ?></b></p>

    <?php if (!empty($monthlyData)): ?>
        <table>
            <thead><tr><th>Month</th><th>Revenue</th></tr></thead>
            <tbody>
            <?php foreach ($monthlyData as $d): ?>
                <tr>
                    <td><?= date('M Y', strtotime($d['ym'] . '-01')) ?></td>
                    <td><?= CURRENCY ?> <?= number_format((float)$d['total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No successful payments recorded yet.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
