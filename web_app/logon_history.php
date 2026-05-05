<?
require_once 'init.php';
require_once 'class_pageable_list.php';

require "ssi_top.php";

$user_id = $login_user->get_id_value();

$query = " SELECT 
    logon_id,
    logon_created_at, 
    logon_last_transaction, 
    logon_ip_address
  FROM " . LOGON_TABLE . " 
  WHERE logon_id = " . $user_id;

$listing = new pg_list($query, 'logon_id', 'logon_created_at', 'ASC', '', '', 1, 5, true, 10, 'even_row_css', 'odd_row_css', 'highlight_css');

$listing->add_column('logon_created_at', 'Login Time', 'mysql_timestamp');
$listing->add_column('logon_last_transaction', 'Last Activity', 'mysql_timestamp');
$listing->add_column('logon_ip_address', 'IP Address');

$listing->init_list();

$page_title = "Logon History";
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
    font-size: 9pt;
  }
</style>

<h2>Logon History</h2>
<b>Listing of Database Records:</b>

<?= $listing->get_html() ?>

<br><br>
<a href="dashboard.php">Go to Dashboard</a>

<br><br><br><br><br><br>

<? require "ssi_bottom.php"; ?>