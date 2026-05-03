<?
require_once 'init.php';

if (($get_post['logout'] ?? '') === 'yes') {
  setcookie('logon_token', '', time() - 60 * 60);
  session_start();
  session_destroy();

  header("Location: login_form.php?message=logged_out");
  exit;
}

$logon = get_valid_logon($reason);

if (!$logon) {
  header("Location: login_form.php?message=" . $reason);
  exit;
}

$logon->values['logon_last_transaction'] = time();
$logon->save();

$login_user = new user();
$login_user->load($logon->values['logon_id']);

$is_admin = is_admin($login_user);
