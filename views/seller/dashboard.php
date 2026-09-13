<?php
$activePage = 'dashboard';
require __DIR__ . '/../partials/header.php';
?>
<div class="box" style="max-width:460px">
    <h2><?= $editProduct ? 'Edit Product' : 'Add a New Product' ?></h2>

    <?php if (!empty($productErrors)): ?>
        <div class="alert alert-error"><?php foreach ($productErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>

    <form id="productForm" method="post" action="index.php?page=seller&action=save"
          onsubmit="return validateForm(this, {
              name:      [{type:'required', message:'Product name is required.'}],
              price:     [{type:'required', message:'Price is required.'}, {type:'number', message:'Price must be a number.'}],
              stock_qty: [{type:'required', message:'Stock quantity is required.'}, {type:'number', message:'Stock must be a number.'}]
          });">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$oldValues['id'] ?>">
        <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= esc($oldValues['name']) ?>"></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="2"><?= esc($oldValues['description']) ?></textarea></div>
        <div class="form-group"><label>Price (<?= CURRENCY ?>)</label><input type="text" name="price" value="<?= esc((string)$oldValues['price']) ?>"></div>
        <div class="form-group"><label>Stock Quantity</label><input type="text" name="stock_qty" value="<?= esc((string)$oldValues['stock_qty']) ?>"></div>
        <button class="btn-primary" type="submit"><?= $editProduct ? 'Save Changes' : 'Submit for Approval' ?></button>
        <?php if ($editProduct): ?><a class="btn-sm" href="index.php?page=seller" style="margin-left:6px">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="box">
    <h2>My Products (Create, Read, Update, Delete, Search)</h2>
    <div class="search-wrap" style="position:static">
        <input type="text" id="productSearch" placeholder="Search my products..." value="<?= esc($searchTerm) ?>">
    </div>
    <table>
        <thead><tr><th>Name</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
        <tbody id="productTableBody">
        <?php if ($products && mysqli_num_rows($products) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td><?= esc($p['name']) ?></td>
                    <td><?= CURRENCY ?> <?= number_format((float)$p['price'], 2) ?></td>
                    <td><?= (int)$p['stock_qty'] ?></td>
                    <td><span class="badge"><?= ucfirst($p['status']) ?></span></td>
                    <td>
                        <a class="btn-sm" href="index.php?page=seller&action=edit&id=<?= (int)$p['id'] ?>">Edit</a>
                        <a class="btn-sm" href="index.php?page=seller&action=delete&id=<?= (int)$p['id'] ?>&<?= csrf_url() ?>" onclick="return confirm('Delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">You haven't listed any products yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
setupLiveSearch("productSearch", "productTableBody", "search_products", function (row) {
    var tr = document.createElement("tr");
    var nameTd = document.createElement("td"); nameTd.textContent = row.name; tr.appendChild(nameTd);
    var priceTd = document.createElement("td"); priceTd.textContent = "<?= CURRENCY ?> " + row.price.toFixed(2); tr.appendChild(priceTd);
    var stockTd = document.createElement("td"); stockTd.textContent = row.stock_qty; tr.appendChild(stockTd);
    var statusTd = document.createElement("td");
    var badge = document.createElement("span"); badge.className = "badge";
    badge.textContent = row.status.charAt(0).toUpperCase() + row.status.slice(1);
    statusTd.appendChild(badge); tr.appendChild(statusTd);
    var actionTd = document.createElement("td");
    var editLink = document.createElement("a"); editLink.className = "btn-sm";
    editLink.href = "index.php?page=seller&action=edit&id=" + row.id;
    editLink.textContent = "Edit";
    actionTd.appendChild(editLink); tr.appendChild(actionTd);
    return tr;
}, 5);
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
