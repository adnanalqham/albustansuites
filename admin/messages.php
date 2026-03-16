<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'messages';
$adminPageTitle = 'Messages';

// Mark as read
if(isset($_GET['read']) && is_numeric($_GET['read'])) {
    $db->prepare("UPDATE messages SET is_read=1 WHERE id=?")->execute([(int)$_GET['read']]);
}
// Delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM messages WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: messages.php'); exit;
}
// View single
$viewMsg = null;
if(isset($_GET['view']) && is_numeric($_GET['view'])) {
    $s=$db->prepare("SELECT * FROM messages WHERE id=?");$s->execute([(int)$_GET['view']]);$viewMsg=$s->fetch();
    if($viewMsg) $db->prepare("UPDATE messages SET is_read=1 WHERE id=?")->execute([$viewMsg['id']]);
}

$messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 100")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<?php if($viewMsg): ?>
<div class="card">
  <div class="card-header">
    <span class="card-title">Message from <?= e($viewMsg['name']) ?></span>
    <a href="messages.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
  <table><tbody>
    <tr><td style="color:var(--gray);padding:8px 0;width:120px;">From</td><td><strong><?= e($viewMsg['name']) ?></strong></td></tr>
    <tr><td style="color:var(--gray);padding:8px 0;">Email</td><td><a href="mailto:<?= e($viewMsg['email']) ?>"><?= e($viewMsg['email']) ?></a></td></tr>
    <tr><td style="color:var(--gray);padding:8px 0;">Phone</td><td><?= e($viewMsg['phone']??'-') ?></td></tr>
    <tr><td style="color:var(--gray);padding:8px 0;">Subject</td><td><?= e($viewMsg['subject']??'-') ?></td></tr>
    <tr><td style="color:var(--gray);padding:8px 0;">Type</td><td><?= e($viewMsg['type']) ?></td></tr>
    <tr><td style="color:var(--gray);padding:8px 0;">Date</td><td><?= formatDate($viewMsg['created_at']) ?></td></tr>
  </tbody></table>
  <div style="background:var(--admin-darker);border-radius:8px;padding:20px;margin-top:16px;color:var(--cream);line-height:1.8;"><?= nl2br(e($viewMsg['message'])) ?></div>
  <div style="margin-top:16px;display:flex;gap:10px;">
    <a href="mailto:<?= e($viewMsg['email']) ?>?subject=Re: <?= e($viewMsg['subject']) ?>" class="btn btn-primary"><i class="fas fa-reply"></i> Reply via Email</a>
    <a href="messages.php?delete=<?= $viewMsg['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i> Delete</a>
  </div>
</div>
<?php else: ?>
<div class="card">
  <div class="card-header"><span class="card-title">All Messages (<?= count($messages) ?>)</span></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Status</th><th>From</th><th>Subject</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($messages as $m): ?>
        <tr style="<?= !$m['is_read']?'background:rgba(201,168,76,0.04);':'' ?>">
          <td><?= !$m['is_read']?'<span class="badge badge-warning">New</span>':'<span class="badge badge-secondary">Read</span>' ?></td>
          <td><strong><?= e($m['name']) ?></strong><br><small style="color:var(--gray);"><?= e($m['email']) ?></small></td>
          <td><?= e($m['subject']??'—') ?></td>
          <td><small><?= e($m['type']) ?></small></td>
          <td><small><?= formatDate($m['created_at']) ?></small></td>
          <td style="display:flex;gap:6px;">
            <a href="?view=<?= $m['id'] ?>" class="btn-xs btn-info"><i class="fas fa-eye"></i></a>
            <a href="mailto:<?= e($m['email']) ?>" class="btn-xs btn-success"><i class="fas fa-reply"></i></a>
            <a href="?delete=<?= $m['id'] ?>" class="btn-xs btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
</div></div></div></body></html>
