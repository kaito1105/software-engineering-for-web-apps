<?
require_once 'init.php';

require 'ssi_top.php';

$user_id = $login_user->get_id_value();
$logon_data = $user_id ? logon::get_logon($user_id) : [];

?>

<h2>Logon History</h2>
<? if (!$user_id) { ?>
 <b>User not found.</b>
<? } else if (count($logon_data) == 0) { ?>
  <b>No records were found in the database.</b>
<? } else { ?>

  <b>Listing of Database Records:</b>

  <table width="" border="1" cellspacing="0" cellpadding="5">
    <tr valign="top">
      <td>Login Time</td>
      <td>Last Activity</td>
      <td>IP Address</td>
    </tr>
    <? foreach ($logon_data as $logon_token => $logon) { ?>
      <tr valign="top">
        <td><?= lib::nice_date($logon['logon_created_at'], 'military_datetime') ?></td>
        <td><?= lib::nice_date($logon['logon_last_transaction'], 'military_datetime') ?></td>
        <td><?= htmlspecialchars($logon['logon_ip_address']) ?></td>
      </tr>
    <? } ?>
  </table>

<? } ?>

<br><br>
<a href="dashboard.php">Go to Dashboard</a>

<?
require 'ssi_bottom.php';
?>