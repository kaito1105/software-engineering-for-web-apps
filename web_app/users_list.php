<?
require_once 'init.php';
require_once 'class_pageable_list.php';

$query = " SELECT 
    user_id,
    user_name, 
    user_email, 
    user_created_at,
    COUNT(logon_token) AS login_count,
    MAX(logon_last_transaction) AS last_activity,
    user_api_token,
    user_is_admin
  FROM " . USERS_TABLE . " 
  LEFT JOIN " . LOGON_TABLE . " ON user_id = logon_id
  GROUP BY user_id ";

$listing = new pg_list($query, 'user_id', 'user_name', 'ASC', '', '', 1, 5, true, 4, 'even_row_css', 'odd_row_css', 'highlight_css');

$listing->add_column('user_name', 'Name');
$listing->add_column('user_email', 'Email');
$listing->add_column('user_created_at', 'Signup Date', 'mysql_timestamp');
$listing->add_column('login_count', 'Login Count');
$listing->add_column('last_activity', 'Last Activity', 'mysql_timestamp');
$listing->add_column('user_api_token', 'Has API Token', 'yesno');
$listing->add_column('user_is_admin', 'Is Admin', 'yesno');
$listing->add_column('', 'actions', '', '', '', '', false, '', 'column_action_links', 'signup_form.php');

$listing->init_list();

$page_title = "Users List";
require "ssi_top.php";
?>

<?
if (!$is_admin) {
  header("Location: dashboard.php");
  exit;
}
?>

<style>
  .even_row_css {
    background-color: #EEE;
    font-size: 10pt;
  }

  .odd_row_css {
    background-color: #DDD;
    font-size: 10pt;
  }

  .highlight_css {
    background-color: #DDF;
    font-size: 10pt;
  }

  tbody th {
    text-align: left;
    font-size: 8pt;
  }
</style>

<h2>Manage Users</h2>
<? if (isset($get_post['deleted_message'])) { ?>
  <b>The DB record was successfully deleted.</b>
  <br><br>
<? } ?>

<? if (isset($get_post['updated'])) { ?>
  <b>The DB record was successfully updated.</b>
  <br><br>
<? } ?>

<b>Listing of User Records:</b>

<?= $listing->get_html() ?>

<br><br>
<a href="dashboard.php">Go to Dashboard</a>

<script type="text/javascript">

  function confirm_delete(user_id, user_name) {
    var choice = confirm("Are you sure you want to delete " + user_name + "?");

    if (choice == true) {
      window.location.href = "signup_form.php?task=delete&user_id=" + user_id;
    }
  }

</script>

<br><br><br><br><br><br>

<? require "ssi_bottom.php"; ?>