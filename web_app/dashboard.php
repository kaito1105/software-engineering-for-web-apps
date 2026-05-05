<?
require_once 'init.php';

require 'ssi_top.php';

$task = $get_post['task'] ?? '';

if ($task == 'generate_token') {

  $login_user->values['user_api_token'] = bin2hex(random_bytes(32));
  $login_user->save();

  header("Location: dashboard.php");
  exit;
}

?>

<h2>Dashboard</h2>

<? if (($get_post['created'] ?? '') === 'yes') { ?>
  Your account has been successfully created.
  <br><br>
<? } else if (($get_post['updated'] ?? '') === 'yes') { ?>
  Your account has been successfully updated.
  <br><br>
<? } ?>

<? if ($login_user->get_id_value()) { ?>

  <h3>Your Profile Information</h3>
  Name: <?= htmlspecialchars($login_user->values['user_name']) ?><br><br>
  Email: <?= htmlspecialchars($login_user->values['user_email']) ?><br><br>
  Account Created At: <?= lib::nice_date($login_user->values['user_created_at'], 'military_datetime') ?><br><br>
  Your IP Address: <?= htmlspecialchars($login_user->values['user_ip_address']) ?><br><br>
  
  <h3>API Access Token</h3>
  <? if (!empty($login_user->values['user_api_token'])) { ?>
    <?= htmlspecialchars($login_user->values['user_api_token']) ?>
    <br><br>
    <a href="affiliate_survey.zip" download>Download Affiliate Client</a>
  <? } else { ?>
    You do not have an API Access Token yet. Click below to generate one and download the affiliate client.
  <? } ?>
  <br><br>
  <form method="POST">
    <input type="hidden" name="task" value="generate_token">
    <button type="submit">Generate / Re-Generate Token</button>
  </form>
  <br>

  <? if ($is_admin) { ?>
    <h3>Admin Portal</h3>
    <a href="users_list.php">Manage Users</a>
    <br><br>
    <a href="api_log_list.php">API Log List</a>
  <? } ?>

<? } else { ?>
  <h3>User not found.</h3>
<? } ?>

<br><br><br><br><br><br>

<?
require 'ssi_bottom.php';
?>