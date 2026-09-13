<?php
$activePage = 'browse';
require __DIR__ . '/../partials/header.php';
?>
<div class="box">
    <h2>Browse Products</h2>

    <form id="searchForm" class="search-wrap" method="get" action="index.php" autocomplete="off">
        <input type="hidden" name="page" value="buyer">
        <input type="hidden" name="action" value="browse">
        <input type="text" id="searchBox" name="q" placeholder="Type a product name..." value="<?= esc($_GET['q'] ?? '') ?>">
        <ul id="suggestions"></ul>
    </form>

    
    <table>
        <thead><tr><th>Product</th><th>Seller</th><th>Price</th><th>Stock</th><th></th></tr></thead>
        <tbody>
        <?php if ($products && mysqli_num_rows($products) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td><?= esc($p['name']) ?><br><small><?= esc($p['description']) ?></small></td>
                    <td><?= esc($p['seller_name']) ?></td>
                    <td><?= CURRENCY ?> <?= number_format((float)$p['price'], 2) ?></td>
                    <td><?= (int)$p['stock_qty'] ?></td>
                    <td>
                        <?php if ($p['stock_qty'] > 0): ?>
                            <a class="btn-sm" href="index.php?page=buyer&action=browse&product_id=<?= (int)$p['id'] ?>">Buy</a>
                        <?php else: ?>
                            <span class="badge">Out of stock</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No approved products found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<?php if ($selectedProduct): ?>
<div class="box" style="max-width:420px">
    <h2>Buy: <?= esc($selectedProduct['name']) ?></h2>
    <p>Price: <?= CURRENCY ?> <?= number_format((float)$selectedProduct['price'], 2) ?> &nbsp;|&nbsp; In stock: <?= (int)$selectedProduct['stock_qty'] ?></p>

    <?php if (!empty($orderErrors)): ?>
        <div class="alert alert-error"><?php foreach ($orderErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=buyer&action=save"
          onsubmit="return validateForm(this, {
              quantity: [{type:'required', message:'Quantity is required.'}, {type:'number', message:'Quantity must be a number.'}]
          });">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int)$selectedProduct['id'] ?>">
        <div class="form-group">
            <label>Quantity</label>
            <input type="text" name="quantity" value="1">
        </div>
        <button class="btn-primary" type="submit">Place Order</button>
    </form>
</div>
<?php endif; ?>


<script>
setupAutocomplete("searchBox", "suggestions", "searchForm");
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
