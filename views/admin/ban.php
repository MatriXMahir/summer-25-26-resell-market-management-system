<?php
$activePage = 'ban';
require __DIR__ . '/../partials/header.php';
?>
<div class="box" style="max-width:460px">
    <h2>Ban a User Temporarily</h2>
    <?php if (!empty($banErrors)): ?>
        <div class="alert alert-error"><?php foreach ($banErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=admin&action=ban_save"
          onsubmit="return validateForm(this, {
              days: [{type:'required', message:'Enter the number of days.'}, {type:'number', message:'Days must be a number.'}]
          });">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>User</label>
            <select name="user_id">
                <?php if ($activeUsers && mysqli_num_rows($activeUsers) > 0): mysqli_data_seek($activeUsers, 0);
                    while ($u = mysqli_fetch_assoc($activeUsers)): ?>
                    <option value="<?= (int)$u['id'] ?>"><?= esc($u['full_name']) ?> (<?= ucfirst($u['role']) ?>)</option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Ban duration (days)</label>
            <input type="text" name="days" placeholder="e.g. 7">
        </div>
        <button class="btn-primary" type="submit">Ban User</button>
    </form>
</div>

<div class="box">
    <h2>Currently Banned</h2>
    <table>
        <thead><tr><th>Name</th><th>Role</th><th>Banned until</th><th></th></tr></thead>
        <tbody>
        <?php if ($bannedUsers && mysqli_num_rows($bannedUsers) > 0): ?>
            <?php while ($b = mysqli_fetch_assoc($bannedUsers)): ?>
                <tr>
                    <td><?= esc($b['full_name']) ?></td>
                    <td><?= ucfirst($b['role']) ?></td>
                    <td><?= $b['ban_until'] ? date('d M Y, h:i A', strtotime($b['ban_until'])) : 'Indefinite' ?></td>
                    <td><a class="btn-sm" href="index.php?page=admin&action=unban&id=<?= (int)$b['id'] ?>&<?= csrf_url() ?>">Unban now</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No one is currently banned.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
