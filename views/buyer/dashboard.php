<?php
$activePage = 'dashboard';
require __DIR__ . '/../partials/header.php';
?>


<?php if ($editOrder): ?>
<div class="box" style="max-width:460px">
    <h2>Edit Order: <?= esc($editOrder['product_name']) ?></h2>
    <?php if (!empty($orderEditErrors)): ?>
        <div class="alert alert-error"><?php foreach ($orderEditErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=buyer&action=update"
          onsubmit="return validateForm(this, {
              quantity: [{type:'required', message:'Quantity is required.'}, {type:'number', message:'Quantity must be a number.'}]
          });">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$editOrder['id'] ?>">
        <div class="form-group">
            <label>Quantity</label>
            <input type="text" name="quantity" value="<?= (int)$editOrder['quantity'] ?>">
        </div>
        <button class="btn-primary" type="submit">Save Changes</button>
        <a class="btn-sm" href="index.php?page=buyer" style="margin-left:6px">Cancel</a>
    </form>
</div>
<?php endif; ?>

<div class="box">
    <h2>My Orders (Create, Read, Update, Delete, Search)</h2>
    <p>To create a new order, go to <a href="index.php?page=buyer&action=browse">Browse Products</a> and click Buy.</p>
    <div class="search-wrap" style="position:static">
        <input type="text" id="orderSearch" placeholder="Search my orders by product name..." value="<?= esc($searchTerm) ?>">
    </div>
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody id="orderTableBody">
        <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
            <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= esc($o['product_name']) ?></td>
                    <td><?= (int)$o['quantity'] ?></td>
                    <td><?= CURRENCY ?> <?= number_format((float)$o['total_price'], 2) ?></td>
                    <td><span class="badge"><?= ucfirst($o['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                    <td>
                        <?php if ($o['status'] === 'pending'): ?>
                            <a class="btn-sm" href="index.php?page=buyer&action=edit&id=<?= (int)$o['id'] ?>">Edit</a>
                            <a class="btn-sm" href="index.php?page=buyer&action=payment&order_id=<?= (int)$o['id'] ?>">Pay</a>
                            <a class="btn-sm" href="index.php?page=buyer&action=delete&id=<?= (int)$o['id'] ?>&<?= csrf_url() ?>" onclick="return confirm('Cancel this order?');">Cancel</a>
                        <?php elseif (in_array($o['status'], ['paid','shipped','completed'])): ?>
                            <a class="btn-sm" href="index.php?page=buyer&action=review&order_id=<?= (int)$o['id'] ?>">Review</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">You have not placed any orders yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<script>
setupLiveSearch("orderSearch", "orderTableBody", "search_orders", function (row) {
    var tr = document.createElement("tr");
    var p = document.createElement("td"); p.textContent = row.product_name; tr.appendChild(p);
    var q = document.createElement("td"); q.textContent = row.quantity; tr.appendChild(q);
    var t = document.createElement("td"); t.textContent = "<?= CURRENCY ?> " + row.total_price.toFixed(2); tr.appendChild(t);
    var s = document.createElement("td");
    var badge = document.createElement("span"); badge.className = "badge";
    badge.textContent = row.status.charAt(0).toUpperCase() + row.status.slice(1);
    s.appendChild(badge); tr.appendChild(s);
    var d = document.createElement("td"); d.textContent = ""; tr.appendChild(d);
    var a = document.createElement("td"); tr.appendChild(a);
    return tr;
}, 6);
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
