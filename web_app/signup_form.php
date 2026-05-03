<?
require_once 'init.php';

$error_message = '';
$task = $get_post['task'] ?? '';
$user = new user();

function get_logon_and_admin(): array
{
    $logon = get_valid_logon();
    $is_admin_user = false;
    if ($logon) {
        $current_user = new user();
        $current_user->load($logon->values['logon_id']);
        $is_admin_user = is_admin($current_user);
    }
    return [$logon, $is_admin_user];
}

switch ($task) {
   case 'save':

      // $user = new user();
      $user->load_from_form_submit();

      [$logon, $is_admin_user] = get_logon_and_admin();

      $submitted_user_id = $get_post['user_id'] ?? 0;
      $is_new_user = $submitted_user_id == 0;

      if ($logon) {
         $logged_in_user_id = $logon->values['logon_id'];

         if (!$is_admin_user && ($logged_in_user_id !== $submitted_user_id)) {
            die("Unauthorized");
         }
      }

      $email_check = new user();
      $email_check->load($user->values['user_email'], 'user_email');
      $email_check_id = $email_check->get_id_value();

      if (strlen(trim($user->values['user_name'])) < 2) {
         $error_message = "Name must be at least 2 characters.";
         break;
      }

      if (!empty($email_check_id) && ($email_check_id != $get_post['user_id'])) {
         $error_message = "Email is already in use.";
         break;
      }

      $new_password = trim($user->values['user_password'] ?? '');

      if ($is_new_user || $new_password !== '') {
         if (strlen($new_password) < 5) {
            $error_message = "Password must be at least 5 characters.";
            break;
         }

         if ($new_password !== trim($get_post['user_password_verify'])) {
            $error_message = "Passwords do not match.";
            break;
         }

         $user->values["user_password"] = hash('sha256', $new_password);
      } else {
         $existing_user = new user();
         $existing_user->load($get_post['user_id']);
         $user->values['user_password'] = $existing_user->values['user_password'];
      }

      if ($is_new_user) {
         $user->values["user_created_at"] = time();
         $user->values["user_ip_address"] = $_SERVER['REMOTE_ADDR'];
         $user->values['user_api_token'] = null;
         $user->values["user_is_admin"] = 0;
      } else {
         $existing_user = $existing_user ?? new user();
         $existing_user->load($get_post['user_id']);
         $user->values['user_created_at'] = $existing_user->values['user_created_at'];
         $user->values['user_ip_address'] = $existing_user->values['user_ip_address'];
         $user->values['user_api_token'] = $existing_user->values['user_api_token'];
         $user->values["user_is_admin"] = $existing_user->values['user_is_admin'];
      }

      $user->save();
      $user_id = $user->get_id_value();

      if ($logon) {
         $logged_in_user_id = $logon->values['logon_id'];

         if ($is_admin_user && ($logged_in_user_id !== $submitted_user_id)) {
            header("Location: users_list.php?updated=yes");
         } else {
            header("Location: dashboard.php?updated=yes");
         }
      } else {
         $_SESSION['user_id'] = $user_id;
         login_user($user_id);
         header("Location: dashboard.php?created=yes");
      }

      exit;

   case 'delete':

      [$logon, $is_admin_user] = get_logon_and_admin();

      if ($is_admin_user) {
         $user_id = $get_post['user_id'] ?? 0;
         if ($user_id > 0) {
            $logon_record = new logon();
            $logon_record->delete($user_id, true, 'logon_id');
            $delete_user = new user();
            $delete_user->delete($user_id);
         }
         header("Location: users_list.php?deleted_message=yes");
      } else {
         header("Location: dashboard.php");
      }

      exit;

   case 'edit':

      [$logon, $is_admin_user] = get_logon_and_admin();

      if (!$logon) {
         break;
      }

      if ($is_admin_user) {
         $user_id = $get_post['user_id'] ?? $logon->values['logon_id'];
      } else {
         $user_id = $logon->values['logon_id'];
      }

      // $user = new user();
      $user->load($user_id);
      $user->html_safe();
      break;

   default:
      // $user = new user();
      break;
}

$logon = get_valid_logon();
if ($logon) {
   $task = 'edit';
} else {
   $security = false;
}

require 'ssi_top.php';

$is_admin_user = $is_admin;
?>

<? if ($task == 'edit') { ?>
   <h2>Update Profile</h2>
<? } else { ?>
   <h2>Sign Up</h2>
   Please fill out the information below to create your account.
   <br><br>
<? } ?>

<? if ($error_message) { ?>
   <div style="color:red;"><?= htmlspecialchars($error_message) ?></div>
<? } ?>

<br>
<form name="form" action="signup_form.php" method="POST">
   <input type="hidden" name="task" value="save">
   <input type="hidden" name="user_id" value="<?= $user->get_id_value() ?>">

   Name: <input type="text" name="user_name" value="<?= htmlspecialchars($user->values['user_name'] ?? '') ?>" required>
   <br><br>
   Email: <input type="email" name="user_email" value="<?= htmlspecialchars($user->values['user_email'] ?? '') ?>"
      required>
   <br><br>
   Password: <input type="password" name="user_password" <?= ($task == 'edit') ? '' : 'required' ?>>
   <? if ($task == 'edit') { ?>
      <small>(Leave blank to keep current password)</small>
   <? } ?>
   <br><br>
   Password Verify: <input type="password" name="user_password_verify" <?= ($task == 'edit') ? '' : 'required' ?>>
   <br><br>
   <button type="submit"><?= ($task == 'edit') ? ' Update ' : ' Submit ' ?></button>
</form>
<br><br>

<? if ($task != 'edit') { ?>
   Already have an account? <a href="login_form.php">Log in here</a>
<? } else { ?>
   <? if ($get_post['user_id']) { ?>
      <a href="users_list.php">Go to Users List</a>
   <? } else { ?>
      <a href="dashboard.php">Go to Dashboard</a>
   <? } ?>
<? } ?>

<?
require 'ssi_bottom.php';
?>