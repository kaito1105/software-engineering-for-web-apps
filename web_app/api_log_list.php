<?
require_once 'init.php';
require_once 'class_pageable_list.php';

$query = " SELECT 
    api_log_id,
    api_log_user_id, 
    api_log_form_id, 
    api_log_timestamp,
    api_log_method,
    api_log_http_code,
    api_log_token
  FROM " . API_LOG_TABLE . " 
  WHERE 1 ";

$listing = new pg_list($query, 'api_log_id', 'api_log_user_id', 'ASC', '', '', 1, 5, true, 4, 'even_row_css', 'odd_row_css', 'highlight_css');

$listing->add_column('api_log_user_id', 'User ID');
$listing->add_column('api_log_form_id', 'Form ID');
$listing->add_column('api_log_timestamp', 'Timestamp', 'mysql_timestamp');
$listing->add_column('api_log_method', 'Method');
$listing->add_column('api_log_http_code', 'HTTP Code');
$listing->add_column('api_log_token', 'Has API Token', 'yesno');

$listing->init_list();

$page_title = "API Log List";
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
    font-size: 9pt;
  }
</style>

<h2>API Log List</h2>
<b>Listing of API Log Records:</b>

<?= $listing->get_html() ?>

<br><br>
<a href="dashboard.php">Go to Dashboard</a>

<br><br><br><br><br><br>

<? require "ssi_bottom.php"; ?>