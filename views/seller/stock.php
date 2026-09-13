<?php
$activePage = 'stock';
require __DIR__ . '/../partials/header.php';
?>
<div class="box" style="max-width:460px">
    <h2>Update Stock Status</h2>

    <?php if (!empty($stockErrors)): ?>
        <div class="alert alert-error"><?php foreach ($stockErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=seller&action=stock_save"
          onsubmit="return validateForm(this, {
              new_qty: [{type:'required', message:'Enter a quantity.'}, {type:'number', message:'Quantity must be a number.'}]
          });">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Product</label>
            <select name="product_id">
                <?php if ($products && mysqli_num_rows($products) > 0): mysqli_data_seek($products, 0);
                    while ($p = mysqli_fetch_assoc($products)): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= esc($p['name']) ?> (current: <?= (int)$p['stock_qty'] ?>)</option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="newQty">New Quantity</label>
            <input type="text" id="newQty" name="new_qty" placeholder="e.g. 12">
            <!-- DOM manipulation feature: live badge, no page reload -->
            <div id="stockIndicator">Enter a quantity</div>
        </div>
        <button class="btn-primary" type="submit">Update Stock</button>
    </form>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
