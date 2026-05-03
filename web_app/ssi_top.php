<?
if (!isset($security) || $security !== false) {
  require "ssi_security.php";
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>My Recipe Book</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" type="text/css" href="css/normalize.css">
  <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>

<body>
  <div id="page_wrapper">
    <div id="header">
      <h2>My Recipe Book</h2>
      <? if (isset($login_user) && $login_user->get_id_value()) { ?>
        <?= htmlspecialchars($login_user->values['user_email']) ?> is logged in. | 
        <a href="ssi_security.php?logout=yes">Log Out</a>
        <br>
        <a href="dashboard.php">Dashboard</a> | 
        <a href="signup_form.php?task=edit">Edit Profile</a> | 
        <a href="logon_history.php">Logon History</a>
        <? if ($is_admin) { ?>
          | <a href="users_list.php">Manage Users</a>
        <? } ?>
      <? } ?>
    </div>

    <div id="content">