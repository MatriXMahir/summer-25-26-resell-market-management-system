<?php
$activePage = 'dashboard';
require __DIR__ . '/../partials/header.php';
?>

<?php if ($action === 'create'): ?>
<div class="box" style="max-width:460px">
    <h2>Assign New Delivery</h2>
    <?php if (!empty($createErrors)): ?>
        <div class="alert alert-error"><?php foreach ($createErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=delivery&action=save"
          onsubmit="return validateForm(this, {
              order_id:       [{type:'required', message:'Please select an order.'}],
              scheduled_date: [{type:'required', message:'Please enter a scheduled date.'}]
          });">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Order (paid, awaiting delivery)</label>
            <select name="order_id">
                <option value="">-- select an order --</option>
                <?php if ($availableOrders && mysqli_num_rows($availableOrders) > 0): mysqli_data_seek($availableOrders, 0);
                    while ($o = mysqli_fetch_assoc($availableOrders)): ?>
                    <option value="<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?> - <?= esc($o['name']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Driver</label>
            <select name="driver_id">
                <option value="">-- unassigned for now --</option>
                <?php if ($availableDrivers && mysqli_num_rows($availableDrivers) > 0): mysqli_data_seek($availableDrivers, 0);
                    while ($dr = mysqli_fetch_assoc($availableDrivers)): ?>
                    <option value="<?= (int)$dr['id'] ?>"><?= esc($dr['name']) ?> (<?= esc($dr['vehicle']) ?>)</option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Scheduled Date</label>
            <input type="text" name="scheduled_date" placeholder="YYYY-MM-DD">
        </div>
        <button class="btn-primary" type="submit">Assign Delivery</button>
        <a class="btn-sm" href="index.php?page=delivery" style="margin-left:6px">Cancel</a>
    </form>
</div>
<?php endif; ?>

<?php if ($editDelivery): ?>
<div class="box" style="max-width:460px">
    <h2>Update Delivery: <?= esc($editDelivery['product_name']) ?></h2>
    <form method="post" action="index.php?page=delivery&action=update"
          onsubmit="return validateForm(this, {
              scheduled_date: [{type:'required', message:'Please enter a scheduled date.'}]
          });">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$editDelivery['id'] ?>">
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (['assigned','in_transit','delivered'] as $s): ?>
                    <option value="<?= $s ?>" <?= $editDelivery['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Scheduled Date</label>
            <input type="text" name="scheduled_date" value="<?= esc($editDelivery['scheduled_date']) ?>">
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3"><?= esc($editDelivery['notes']) ?></textarea>
        </div>
        <button class="btn-primary" type="submit">Save Changes</button>
        <a class="btn-sm" href="index.php?page=delivery" style="margin-left:6px">Cancel</a>
    </form>
</div>
<?php endif; ?>

<div class="box">
    <h2>My Deliveries (Create, Read, Update, Delete, Search)</h2>
    <p><a class="btn-sm" href="index.php?page=delivery&action=create">+ Assign New Delivery</a></p>
    <div class="search-wrap" style="position:static">
        <input type="text" id="deliverySearch" placeholder="Search by product name..." value="<?= esc($searchTerm) ?>">
    </div>
    <table>
        <thead><tr><th>Order</th><th>Product</th><th>Driver</th><th>Scheduled</th><th>Status</th><th></th></tr></thead>
        <tbody id="deliveryTableBody">
        <?php if ($deliveries && mysqli_num_rows($deliveries) > 0): ?>
            <?php while ($d = mysqli_fetch_assoc($deliveries)): ?>
                <tr>
                    <td>#<?= (int)$d['order_num'] ?></td>
                    <td><?= esc($d['product_name']) ?></td>
                    <td><?= $d['driver_name'] ? esc($d['driver_name']) : 'unassigned' ?></td>
                    <td><?= date('d M Y', strtotime($d['scheduled_date'])) ?></td>
                    <td><span class="badge"><?= ucfirst(str_replace('_', ' ', $d['status'])) ?></span></td>
                    <td>
                        <?php if ($d['status'] !== 'delivered' && $d['status'] !== 'cancelled'): ?>
                            <a class="btn-sm" href="index.php?page=delivery&action=edit&id=<?= (int)$d['id'] ?>">Update</a>
                        <?php endif; ?>
                        <a class="btn-sm" href="index.php?page=delivery&action=delete&id=<?= (int)$d['id'] ?>&<?= csrf_url() ?>" onclick="return confirm('Delete this delivery?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No deliveries assigned yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
setupLiveSearch("deliverySearch", "deliveryTableBody", "search_deliveries", function (row) {
    var tr = document.createElement("tr");
    var o = document.createElement("td"); o.textContent = "#" + row.order_num; tr.appendChild(o);
    var p = document.createElement("td"); p.textContent = row.product_name; tr.appendChild(p);
    var dr = document.createElement("td"); dr.textContent = row.driver_name || "unassigned"; tr.appendChild(dr);
    var sd = document.createElement("td"); sd.textContent = row.scheduled_date; tr.appendChild(sd);
    var s = document.createElement("td");
    var badge = document.createElement("span"); badge.className = "badge";
    badge.textContent = row.status.replace("_", " ");
    s.appendChild(badge); tr.appendChild(s);
    var a = document.createElement("td"); tr.appendChild(a);
    return tr;
}, 6);
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
