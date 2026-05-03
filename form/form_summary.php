<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Server-Side Form Processing</title>
  <style>
    table,
    td,
    th {
      border-collapse: collapse;
      border: 1px solid black;
    }
  </style>
</head>

<body>
  <h2>Server-Side Form Processing</h2>
  <table>
    <tr>
      <th>Recipe Name</th>
      <th>Your Email</th>
      <th>Preparation Date</th>
      <th>Total Cooking Time<br>(minutes)</th>
      <th>Difficulty Level<br>(1 = Easy, 10 = Hard)</th>
      <th>Meal Time</th>
      <th>Dish Type</th>
      <th>Main Cooking Method</th>
      <th>Proteins Used</th>
      <th>Additional Ingredients</th>
      <th>Cooking Instructions</th>
      <th>Hidden Value</th>
    </tr>
    <tr>
      <td><?= $_GET["recipe"] ?></td>
      <td><?= $_GET["email"] ?></td>
      <td><?= $_GET["preparation_date"] ?></td>
      <td><?= $_GET["time"] ?></td>
      <td><?= $_GET["difficulty"] ?></td>
      <td><?= $_GET["meal_time"] ?></td>
      <td><?= $_GET["dish_type"] ?></td>
      <td><?= $_GET["method"] ?></td>
      <td>
        <? if (is_array($_GET["proteins"])) {
          foreach ($_GET["proteins"] as $protein) {
            ?>
            <?= $protein ?><br>
            <?
          }
        }
        ?>
      </td>
      <td>
        <? if (is_array($_GET["ingredients"])) {
          foreach ($_GET["ingredients"] as $ingredient) {
            ?>
            <?= $ingredient ?><br>
            <?
          }
        }
        ?>
      </td>
      <td><?= $_GET["instructions"] ?></td>
      <td><?= $_GET["recipe_id"] ?></td>
    </tr>
  </table>

</body>

</html>