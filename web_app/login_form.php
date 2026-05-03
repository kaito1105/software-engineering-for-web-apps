<?
require_once 'init.php';

$error_message = '';
$logout_message = '';
$message = $get_post['message'] ?? '';
$task = $get_post['task'] ?? '';
$user_email = $_COOKIE['user_email'] ?? '';
$remember_checked = isset($_COOKIE['user_email']) ? 'checked' : '';

if (get_valid_logon()) {
  header("Location: dashboard.php");
  exit;
}

if ($message == 'logged_out') {
  $logout_message = "You have been logged out.";
} else if ($message == 'login_required') {
  $logout_message = "You must log in to access that page.";
} else if ($message == 'expired') {
  $logout_message = "Your login has expired.";
}

if ($task == 'submit') {

  $user_email = trim($get_post['user_email'] ?? '');
  $remember_checked = isset($_COOKIE['user_email']) ? 'checked' : '';

  $email_check = new user();
  $email_check->load($user_email, 'user_email');
  $user_id = $email_check->get_id_value();

  if (empty($user_id)) {
    $error_message = "User does not exist.";
  } else {
    $user = new user();
    $user->load($user_id);

    $input_password = hash('sha256', $get_post['password'] ?? '');
    if ($input_password !== $user->values['user_password']) {
      $error_message = "Incorrect password.";
    } else {
      login_user($user_id);

      if (isset($get_post['remember'])) {
        setcookie("user_email", $get_post['user_email'], time() + 60 * 60 * 24 * 365);
      } else {
        setcookie("user_email", '', time() - 60 * 60);
      }

      $_SESSION['user_id'] = $user_id;
      header("Location: dashboard.php");
      exit;
    }
  }
}

$security = false;
require 'ssi_top.php';
?>

<h2>Log In</h2>

<? if ($logout_message) { ?>
  <div><?= htmlspecialchars($logout_message) ?></div><br>
<? } ?>

<? if ($error_message) { ?>
  <div style="color:red;"><?= htmlspecialchars($error_message) ?></div><br>
<? } ?>

<form name="form" action="login_form.php" method="POST">
  <input type="hidden" name="task" value="submit">
  Email: <input type="text" name="user_email" 
    value="<?= htmlspecialchars($user_email) ?>"
    placeholder="abc@example.com" required><br><br>
  Password: <input type="password" name="password" 
    placeholder="Enter password" required><br><br>
  <input type="checkbox" name="remember" <?= $remember_checked ?>> Remember Email
  <br><br>
  <button type="submit"> Login </button>
</form>
<br><br>
Don't have an account? <a href="signup_form.php">Sign up here</a>

<?
require 'ssi_bottom.php';
?>