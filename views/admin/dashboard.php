<?php
$activePage = 'dashboard';
require __DIR__ . '/../partials/header.php';
?>
<div class="box" style="max-width:460px">
    <h2><?= $editUser ? 'Edit User' : 'Add a User Manually' ?></h2>

    <?php if (!empty($adminErrors)): ?>
        <div class="alert alert-error"><?php foreach ($adminErrors as $err) echo esc($err) . "<br>"; ?></div>
    <?php endif; ?>

    <form id="userForm" method="post" action="index.php?page=admin&action=save"
          onsubmit="return validateForm(this, {
              full_name: [{type:'required', message:'Name is required.'}],
              email:     [{type:'required', message:'Email is required.'}, {type:'email', message:'Enter a valid email address.'}]
              <?= $editUser ? '' : ", password: [{type:'required', message:'Password is required.'}, {type:'min', value:6, message:'Password must be at least 6 characters.'}]" ?>
          });">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$oldValues['id'] ?>">
        <div class="form-group"><label>Name</label><input type="text" name="full_name" value="<?= esc($oldValues['full_name']) ?>"></div>
        <div class="form-group"><label>Email</label><input type="text" name="email" value="<?= esc($oldValues['email']) ?>"></div>
        <?php if (!$editUser): ?>
        <div class="form-group"><label>Password</label><input type="password" name="password"></div>
        <?php endif; ?>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <?php foreach (['buyer','seller','delivery','admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= $oldValues['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn-primary" type="submit"><?= $editUser ? 'Save Changes' : 'Create User' ?></button>
        <?php if ($editUser): ?><a class="btn-sm" href="index.php?page=admin" style="margin-left:6px">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="box">
    <h2>Registered Members (Create, Read, Update, Delete, Search)</h2>
    <div class="search-wrap" style="position:static">
        <input type="text" id="userSearch" placeholder="Search by name or email..." value="<?= esc($searchTerm) ?>">
    </div>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
        <tbody id="userTableBody">
        <?php if ($users && mysqli_num_rows($users) > 0): ?>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
                <tr class="clickable-row" data-detail="detail-<?= (int)$u['id'] ?>">
                    <td><?= esc($u['full_name']) ?></td>
                    <td><?= esc($u['email']) ?></td>
                    <td><?= ucfirst($u['role']) ?></td>
                    <td><span class="badge"><?= ucfirst($u['status']) ?></span></td>
                    <td>
                        <?php if ($u['status'] === 'pending'): ?>
                            <a class="btn-sm" href="index.php?page=admin&action=approve&id=<?= (int)$u['id'] ?>&<?= csrf_url() ?>">Approve</a>
                        <?php endif; ?>
                        <a class="btn-sm" href="index.php?page=admin&action=edit&id=<?= (int)$u['id'] ?>">Edit</a>
                        <?php if ($u['role'] !== 'admin'): ?>
                            <a class="btn-sm" href="index.php?page=admin&action=delete&id=<?= (int)$u['id'] ?>&<?= csrf_url() ?>" onclick="return confirm('Delete this user?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr id="detail-<?= (int)$u['id'] ?>" class="detail-row">
                    <td colspan="5">
                        Joined: <?= date('d M Y, h:i A', strtotime($u['created_at'])) ?>
                        <?php if ($u['status'] === 'banned'): ?>
                            &nbsp;|&nbsp; Banned until: <?= $u['ban_until'] ? date('d M Y, h:i A', strtotime($u['ban_until'])) : 'further notice' ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
setupLiveSearch("userSearch", "userTableBody", "search_users", function (row) {
    var tr = document.createElement("tr");
    ["full_name", "email"].forEach(function (key) {
        var td = document.createElement("td");
        td.textContent = row[key];
        tr.appendChild(td);
    });
    var roleTd = document.createElement("td");
    roleTd.textContent = row.role.charAt(0).toUpperCase() + row.role.slice(1);
    tr.appendChild(roleTd);
    var statusTd = document.createElement("td");
    var badge = document.createElement("span");
    badge.className = "badge";
    badge.textContent = row.status.charAt(0).toUpperCase() + row.status.slice(1);
    statusTd.appendChild(badge);
    tr.appendChild(statusTd);
    var actionTd = document.createElement("td");
    var editLink = document.createElement("a");
    editLink.className = "btn-sm";
    editLink.href = "index.php?page=admin&action=edit&id=" + row.id;
    editLink.textContent = "Edit";
    actionTd.appendChild(editLink);
    tr.appendChild(actionTd);
    return tr;
}, 5);
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
