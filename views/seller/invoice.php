<?php
$activePage = 'invoice';
require __DIR__ . '/../partials/header.php';
?>
<div class="box" style="max-width:460px">
    <h2>Generate Invoice</h2>
    <?php if (!empty($invoiceErrors)): ?>
        <div class="alert alert-error"><?php foreach ($invoiceErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=seller&action=invoice_save">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Paid Order</label>
            <select name="order_id">
                <option value="">-- select a paid order --</option>
                <?php if ($invoiceableOrders && mysqli_num_rows($invoiceableOrders) > 0): mysqli_data_seek($invoiceableOrders, 0);
                    while ($o = mysqli_fetch_assoc($invoiceableOrders)): ?>
                    <option value="<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?> - <?= esc($o['name']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <button class="btn-primary" type="submit">Generate Invoice</button>
    </form>
</div>

<div class="box">
    <h2>My Invoices</h2>
    <?php if ($invoices && mysqli_num_rows($invoices) > 0): ?>
        <?php while ($i = mysqli_fetch_assoc($invoices)): ?>
            <div class="invoice-box">
                <strong><?= esc($i['invoice_number']) ?></strong><br>
                Product: <?= esc($i['product_name']) ?> x <?= (int)$i['quantity'] ?><br>
                Amount: <?= CURRENCY ?> <?= number_format((float)$i['amount'], 2) ?><br>
                Issued: <?= date('d M Y, h:i A', strtotime($i['created_at'])) ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No invoices generated yet.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
