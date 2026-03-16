<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'users';
$adminPageTitle = 'admin_guest_accounts';

$users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM bookings WHERE user_id=u.id) as booking_count FROM users u ORDER BY u.created_at DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_all_guests') ?> (<?= count($users) ?>)</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th><?= t('admin_id') ?></th><th><?= t('admin_name') ?></th><th><?= t('admin_email') ?></th><th><?= t('admin_phone') ?></th><th><?= t('admin_bookings') ?></th><th><?= t('admin_joined') ?></th></tr></thead>
      <tbody>
        <?php foreach($users as $u):?>
        <tr>
          <td>#<?=$u['id']?></td>
          <td><strong><?=e($u['name'])?></strong></td>
          <td><?=e($u['email'])?></td>
          <td><?=e($u['phone']??'—')?></td>
          <td><a href="bookings.php?q=<?=e($u['email'])?>" class="badge badge-info" style="cursor:pointer;"><?=$u['booking_count']?> <?= t('admin_bookings') ?></a></td>
          <td><small><?=formatDate($u['created_at'])?></small></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
</div></div></div></body></html>
