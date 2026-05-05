<?
require 'init.php';

$result = lib::db_query(
  "SELECT f.*, l.api_log_user_id
     FROM " . FORM_TABLE . " f
     LEFT JOIN " . API_LOG_TABLE. " l ON f.recipe_id = l.api_log_form_id
     ORDER BY f.recipe_name ASC"
);
$rows = $result->fetchAll();
$num_rows = count($rows);

$security = false;
require 'ssi_top.php';
?>

<a href="affiliate_survey.php">Go to the data form</a>
<br><br>

<? if ($num_rows == 0) { ?>
  <b>No records were found in the database.</b><br><br>
<? } else { ?>
  <? if ($get_post['complete'] ?? '') { ?>
    Thank you for submitting the data form.<br><br>
  <? } ?>

  <b>Listing of Database Records:</b>

  <table border="1" cellspacing="0" cellpadding="5">
    <tr valign="top">
      <th>Recipe Name</th>
      <th>Email</th>
      <th>Preparation Date</th>
      <th>Total Cooking Time<br>(minutes)</th>
      <th>Difficulty Level<br>(1 = Easy, 10 = Hard)</th>
      <th>Meal Time</th>
      <th>Dish Type</th>
      <th>Main Cooking Method</th>
      <th>Proteins Used</th>
      <th>Additional Ingredients</th>
      <th>Cooking Instructions</th>
      <th>Submitted By</th>
    </tr>
    <? foreach ($rows as $row) { ?>
      <tr valign="top">
        <td><?= $row['recipe_name'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['preparation_date'] ?></td>
        <td><?= $row['total_cooking_time'] ?></td>
        <td><?= $row['difficulty_level'] ?></td>
        <td><?= $row['meal_time'] ?></td>
        <td><?= $row['dish_type'] ?></td>
        <td><?= $row['main_cooking_method'] ?></td>
        <td><?= $row['proteins_used'] ?></td>
        <td><?= $row['additional_ingredients'] ?></td>
        <td><?= $row['cooking_instructions'] ?></td>
        <td><?= $row['api_log_user_id'] ? 'API (Affiliate)' : 'In-house' ?></td>
      </tr>
    <? } ?>
  </table>

<? } ?>

<?
require 'ssi_bottom.php';
?>