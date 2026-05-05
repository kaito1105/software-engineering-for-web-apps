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
?>

<!DOCTYPE html>
<html>

<head>
  <title>Non-Fancy Listing of People Table</title>
  <script type="text/javascript">

    function confirm_delete(recipe_id, recipe_name) {
      var choice = confirm("Are you sure you want to delete " + recipe_name + "?");

      if (choice == true) {
        window.location.href = "page_form.php?task=delete&recipe_id=" + recipe_id;
      }
    }
  </script>

</head>

<body>
  <a href="page_form.php">Go to the data form. </a>
  <br><br>

  <? if (isset($get_post['deleted_message'])) { ?>
    <b>The DB record was successfully deleted.</b>
    <br><br>
  <? } ?>

  <? if ($num_rows == 0) { ?>
    <b>No records were found in the database.</b>
  <? } else { ?>

    <b>Listing of Database Records:</b>

    <table width="" border="1" cellspacing="0" cellpadding="5">
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
        <td>&nbsp;</td>
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
          <td>
            <a href="page_form.php?task=edit&recipe_id=<?= $row['recipe_id'] ?>">Edit</a>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="#null" onclick="confirm_delete(<?= $row['recipe_id'] ?> , '<?= $row['recipe_name'] ?>')">Delete</a>
          </td>
        </tr>
      <? } ?>
    </table>

  <? } ?>

</body>

</html>