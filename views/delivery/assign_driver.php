<?php
$activePage = 'assign';
require __DIR__ . '/../partials/header.php';
?>
<div class="box" style="max-width:460px">
    <h2>Assign a Driver</h2>
    <?php if (!empty($assignErrors)): ?>
        <div class="alert alert-error"><?php foreach ($assignErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=delivery&action=assign_save"
          onsubmit="return validateForm(this, {
              delivery_id: [{type:'required', message:'Please select a delivery.'}],
              driver_id:   [{type:'required', message:'Please select a driver.'}]
          });">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Delivery</label>
            <select name="delivery_id">
                <option value="">-- select a delivery --</option>
                <?php if ($assignableDeliveries && mysqli_num_rows($assignableDeliveries) > 0): mysqli_data_seek($assignableDeliveries, 0);
                    while ($d = mysqli_fetch_assoc($assignableDeliveries)): ?>
                    <option value="<?= (int)$d['id'] ?>">#<?= (int)$d['id'] ?> - <?= esc($d['product_name']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Driver</label>
            <select name="driver_id">
                <option value="">-- select a driver --</option>
                <?php if ($allDrivers && mysqli_num_rows($allDrivers) > 0): mysqli_data_seek($allDrivers, 0);
                    while ($dr = mysqli_fetch_assoc($allDrivers)): ?>
                    <option value="<?= (int)$dr['id'] ?>"><?= esc($dr['name']) ?> (<?= esc($dr['vehicle']) ?>) - <?= ucfirst($dr['status']) ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <button class="btn-primary" type="submit">Assign Driver</button>
    </form>
</div>

<div class="box">
    <h2>All Drivers</h2>
    <table>
        <thead><tr><th>Name</th><th>Phone</th><th>Vehicle</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($allDrivers && mysqli_num_rows($allDrivers) > 0): mysqli_data_seek($allDrivers, 0);
            while ($dr = mysqli_fetch_assoc($allDrivers)): ?>
            <tr>
                <td><?= esc($dr['name']) ?></td>
                <td><?= esc($dr['phone']) ?></td>
                <td><?= esc($dr['vehicle']) ?></td>
                <td><span class="badge"><?= ucfirst($dr['status']) ?></span></td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
