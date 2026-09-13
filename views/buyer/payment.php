<?php
$activePage = 'payment';
require __DIR__ . '/../partials/header.php';
$selectedOrder = (int)($_GET['order_id'] ?? 0);
?>
<div class="box" style="max-width:460px">
    <h2>Make a Payment</h2>
    <?php if (!empty($paymentErrors)): ?>
        <div class="alert alert-error"><?php foreach ($paymentErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>

    
    <form method="post" action="index.php?page=buyer&action=payment_save"
          onsubmit="return validateForm(this, {
              order_id: [{type:'required', message:'Please select an order.'}],
              method:   [{type:'required', message:'Please choose a payment method.'}]
          });">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Order</label>
            <select name="order_id">
                <option value="">-- select a pending order --</option>
                <?php if ($pendingOrders && mysqli_num_rows($pendingOrders) > 0): mysqli_data_seek($pendingOrders, 0);
                    while ($o = mysqli_fetch_assoc($pendingOrders)): ?>
                    <option value="<?= (int)$o['id'] ?>" <?= $selectedOrder === (int)$o['id'] ? 'selected' : '' ?>>
                        #<?= (int)$o['id'] ?> - <?= esc($o['product_name']) ?> (<?= CURRENCY ?> <?= number_format((float)$o['total_price'], 2) ?>)
                    </option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Payment Method</label>
            <select name="method">
                <option value="bkash">bKash</option>
                <option value="nagad">Nagad</option>
                <option value="card">Card</option>
                <option value="cod">Cash on Delivery</option>
            </select>
        </div>
        <button class="btn-primary" type="submit">Pay Now</button>
    </form>
</div>

<div class="box">
    <h2>Payment History</h2>
    <table>
        <thead><tr><th>Order</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php if ($paymentHistory && mysqli_num_rows($paymentHistory) > 0): ?>
            <?php while ($h = mysqli_fetch_assoc($paymentHistory)): ?>
                <tr>
                    <td><?= esc($h['product_name']) ?></td>
                    <td><?= CURRENCY ?> <?= number_format((float)$h['amount'], 2) ?></td>
                    <td><?= strtoupper($h['method']) ?></td>
                    <td><span class="badge"><?= ucfirst($h['payment_status']) ?></span></td>
                    <td><?= date('d M Y, h:i A', strtotime($h['paid_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No payments yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
