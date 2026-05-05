<?
require 'init.php'; // database connection, etc

function radio_button($id, $name, $value, $label, $selected)
{
  $checked = ($selected == $value) ? "checked" : "";
  $buttons = "<input type='radio' id=\"$id\" name=\"$name\" value=\"$value\" $checked required>\n";
  $buttons .= "<label for=\"$id\">{$label}</label><br>\n";
  return $buttons;
}

$main_cooking_methods = array(
  "boiling" => "Boiling",
  "baking" => "Baking",
  "frying" => "Frying",
  "steaming" => "Steaming",
  "grilling" => "Grilling"
);

$additional_ingredients = array(
  "onion" => "Onion",
  "carrot" => "Carrot",
  "potato" => "Potato",
  "lettuce" => "Lettuce",
  "cabbage" => "Cabbage",
  "tomato" => "Tomato",
  "none" => "None"
);

$proteins_used_array = array(
  "beef" => "Beef",
  "pork" => "Pork",
  "chicken" => "Chicken",
  "fish" => "Fish",
  "egg" => "Egg"
);

$proteins_used = [];
$selected_additional_ingredients = [];

$task = $get_post['task'];
switch ($task) {

  case 'save':
    // Create a database record From Form Submission

    if (isset($get_post['recipe_id']) && $get_post['recipe_id'] > 0) {
      $recipe_id = $get_post['recipe_id'];  // need to update existing person DB record
    } else {
      $recipe_id = 0;                    // need to create new person DB record
    }

    // With PDO prepared statements, values are passed separately from the SQL
    // so no escaping is needed -- PDO handles quoting automatically.
    $recipe_name = $get_post['recipe_name']; // already trimmed in init.php
    $email = $get_post['email'];
    $preparation_date = $get_post['preparation_date'];
    $total_cooking_time = $get_post['total_cooking_time'];
    $difficulty_level = $get_post['difficulty_level'];
    $meal_time = $get_post['meal_time'];
    $dish_type = $get_post['dish_type'];
    $main_cooking_method = $get_post['main_cooking_method'];
    $proteins_used = implode(', ', $get_post['proteins_used'] ?? []);
    $additional_ingredients = implode(', ', $get_post['additional_ingredients'] ?? []);
    $cooking_instructions = $get_post['cooking_instructions'];

    // Server-Side Validation
    $cooking_time_valid = ctype_digit($total_cooking_time) && (int) $total_cooking_time > 0;

    if (
      !$recipe_name || !$email || !$preparation_date || !$cooking_time_valid ||
      !$difficulty_level || !$meal_time || !$dish_type || !$main_cooking_method ||
      !$additional_ingredients || !$cooking_instructions
    ) {
      // reload this page with error message
      // Ideally, client-side form validation would have caught this first
      // But client-side validation can be cheated, so server-side is the only sure way.

      if ($recipe_id > 0) {
        $transfer_url = "page_form.php?incomplete=yes&task=edit&recipe_id=$recipe_id";
      } else {
        $transfer_url = "page_form.php?incomplete=yes";
      }

      header("Location: $transfer_url");
      exit;

      // The PHP header() function adds the following to the HTTP response header sent back to the browser.
      // Location: page_form.php?incomplete=yes
      // The browser then does the transfer.
    }

    if ($recipe_id > 0) {
      // UPDATE existing record
      $sql = "UPDATE " . FORM_TABLE . " SET 
        recipe_name = :recipe_name, 
        email = :email, 
        preparation_date = :preparation_date, 
        total_cooking_time = :total_cooking_time, 
        difficulty_level = :difficulty_level, 
        meal_time = :meal_time, 
        dish_type = :dish_type, 
        main_cooking_method = :main_cooking_method, 
        proteins_used = :proteins_used, 
        additional_ingredients = :additional_ingredients, 
        cooking_instructions = :cooking_instructions WHERE recipe_id = :recipe_id";
      $placehold = [
        ':recipe_name' => $recipe_name,
        ':email' => $email,
        ':preparation_date' => $preparation_date,
        ':total_cooking_time' => $total_cooking_time,
        ':difficulty_level' => $difficulty_level,
        ':meal_time' => $meal_time,
        ':dish_type' => $dish_type,
        ':main_cooking_method' => $main_cooking_method,
        ':proteins_used' => $proteins_used,
        ':additional_ingredients' => $additional_ingredients,
        ':cooking_instructions' => $cooking_instructions,
        ':recipe_id' => $recipe_id
      ];
      lib::db_query($sql, $placehold);
    } else {
      // INSERT new record
      $sql = "INSERT INTO " . FORM_TABLE . " VALUES (
        NULL, 
        :recipe_name, 
        :email, 
        :preparation_date, 
        :total_cooking_time, 
        :difficulty_level, 
        :meal_time, 
        :dish_type, 
        :main_cooking_method, 
        :proteins_used, 
        :additional_ingredients, 
        :cooking_instructions)";
      $placehold = [
        ':recipe_name' => $recipe_name,
        ':email' => $email,
        ':preparation_date' => $preparation_date,
        ':total_cooking_time' => $total_cooking_time,
        ':difficulty_level' => $difficulty_level,
        ':meal_time' => $meal_time,
        ':dish_type' => $dish_type,
        ':main_cooking_method' => $main_cooking_method,
        ':proteins_used' => $proteins_used,
        ':additional_ingredients' => $additional_ingredients,
        ':cooking_instructions' => $cooking_instructions
      ];
      lib::db_query($sql, $placehold);
    }

    // Transfer to the listing page -- not good to leave the browser sitting on a post transaction
    header("Location: page_listing.php");
    exit;
    break;
  //////////////////////////////////////////////////////////
  // End Save Case
  //////////////////////////////////////////////////////////

  case 'delete':
    // Just delete that puppy

    if (isset($get_post['recipe_id']) && $get_post['recipe_id'] > 0) {
      $recipe_id = $get_post['recipe_id'];
    }

    // DELETE the record
    $sql = "DELETE FROM " . FORM_TABLE . " WHERE recipe_id = :recipe_id";
    $placehold = [':recipe_id' => $recipe_id];
    lib::db_query($sql, $placehold);

    header("Location: page_listing.php?deleted_message=yes");
    exit;
    break;

  //////////////////////////////////////////////////////////
  // End delete Case
  //////////////////////////////////////////////////////////

  case 'edit':

    if (!isset($get_post['recipe_id']) || $get_post['recipe_id'] <= 0) {
      // if no incoming recipe_id, just give blank form
      break;
    }

    $recipe_id = $get_post['recipe_id'];
    $sql = "SELECT * FROM " . FORM_TABLE . " WHERE recipe_id = :recipe_id";
    $placehold = [':recipe_id' => $recipe_id];
    $result = lib::db_query($sql, $placehold);
    $row = $result->fetch();  // will only be one row

    /*
      Certain characters like " and < and > are reserved in HTML,
      so will break the HTML if present in the data. The htmlspecialchars()function
      converts them all into HTML character entities &quot; and &lt; and &gt;
    */
    $recipe_name = htmlspecialchars($row['recipe_name']);
    $email = htmlspecialchars($row['email']);
    $preparation_date = htmlspecialchars($row['preparation_date']);
    $total_cooking_time = htmlspecialchars($row['total_cooking_time']);
    $difficulty_level = $row['difficulty_level'];
    $meal_time = $row['meal_time'];
    $dish_type = $row['dish_type'];
    $main_cooking_method = $row['main_cooking_method'];
    $proteins_used = explode(", ", $row['proteins_used']);
    $selected_additional_ingredients = explode(", ", $row['additional_ingredients']);
    $cooking_instructions = htmlspecialchars($row['cooking_instructions']);

    /*
      It would be tempting to apply htmlspecialchars() to every column from the DB like below

      foreach ($row as $key => $value) {
        $row[$key] = htmlspecialchars($value);
      }

      But if a column contains serialized data, htmlspecialchars() can mess that up.
      Plus, the radio/checkbox/menu values are not user entered, so shouldn't need cleaned up anyway.
    */

    break;

  //////////////////////////////////////////////////////////
  // End edit Case
  //////////////////////////////////////////////////////////

  default:
  // switch statement default - no task submitted.
  // just drops into the page with blank HTML form - no data processing first.
}

?>
<!DOCTYPE html>
<html>

<head>
  <title>PHP/MySQL CRUD Example</title>
</head>

<body>
  <? if ($get_post['incomplete']) { ?>
    Your Form Submission was Missing Data
    <br><br>
  <? } ?>
  <br>

  <a href="page_listing.php">Go To CRUD Listing Page</a> or create a new Database Record below.

  <br><br><br>

  <!--
      This form Submits to THIS file regardless of what this file's name.
      But that causes a vulnerability since $_SERVER['PHP_SELF'] is populated from the browser's address field
      which means the file name effectively becomes a form of user input.
      See more info in the comment further below.
     -->

  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
    <input type="hidden" name="task" value="save">
    <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">

    <label for="recipe_name">Recipe Name:</label><br>
    <input type="text" id="recipe_name" name="recipe_name" value="<?= $recipe_name ?>"
      placeholder="e.g., Spaghetti Carbonara" required>
    <br><br>

    <label for="email">Your Email:</label><br>
    <input type="email" id="email" name="email" value="<?= $email ?>" placeholder="abc@example.com" required>
    <br><br>

    <label for="preparation_date">Preparation Date:</label><br>
    <input type="date" id="preparation_date" name="preparation_date" value="<?= $preparation_date ?>" required>
    <br><br>

    <label for="total_cooking_time">Total Cooking Time (minutes):</label><br>
    <input type="number" id="total_cooking_time" name="total_cooking_time" value="<?= $total_cooking_time ?>" min="1"
      max="600" required>
    <br><br>

    <label for="difficulty_level">Difficulty Level (1 = Easy, 10 = Hard):</label><br>
    <input type="range" id="difficulty_level" name="difficulty_level" value="<?= $difficulty_level ?>" min="1" max="10"
      required>
    <br><br>

    Meal Time:<br>
    <?= radio_button("breakfast", "meal_time", "breakfast", "Breakfast", $meal_time); ?>
    <?= radio_button("lunch", "meal_time", "lunch", "Lunch", $meal_time); ?>
    <?= radio_button("dinner", "meal_time", "dinner", "Dinner", $meal_time); ?>
    <?= radio_button("snack", "meal_time", "snack", "Snack", $meal_time); ?>
    <br>

    Dish Type:<br>
    <?= radio_button("main_course", "dish_type", "main", "Main Course", $dish_type); ?>
    <?= radio_button("side_dish", "dish_type", "side", "Side Dish", $dish_type); ?>
    <?= radio_button("appetizer", "dish_type", "appetizer", "Appetizer", $dish_type); ?>
    <?= radio_button("soup", "dish_type", "soup", "Soup", $dish_type); ?>
    <?= radio_button("dessert", "dish_type", "dessert", "Dessert", $dish_type); ?>
    <br>

    <label for="main_cooking_method">Main Cooking Method:</label><br>
    <?= lib::menu_from_assoc_array(
      "main_cooking_method",
      $main_cooking_methods,
      "Select one",
      $main_cooking_method
    ); ?>
    <br><br>

    Proteins Used (Select all that apply):<br>
    <? foreach ($proteins_used_array as $value => $label) { ?>
      <input type="checkbox" id="<?= $value ?>" name="proteins_used[]" value="<?= $value ?>" <?= in_array($value, $proteins_used) ? "checked" : "" ?>>
      <label for="<?= $value ?>"><?= $label ?></label><br>
    <? } ?>
    <br>

    <label for="additional_ingredients">
      Additional Ingredients (Select one or more):
    </label><br>
    <?= lib::menu_from_assoc_array(
      "additional_ingredients[]",
      $additional_ingredients,
      "",
      $selected_additional_ingredients,
      "multiple",
      "required"
    ); ?>
    <br><br>

    <label for="cooking_instructions">Cooking Instructions:</label><br>
    <textarea id="cooking_instructions" name="cooking_instructions" rows="5" cols="40"
      placeholder="1. Preheat oven to 180°C...&#10;2. Chop vegetables..."
      required><?= $cooking_instructions ?></textarea>
    <br><br>

    <button type="reset"> Reset </button>
    <button type="submit"> Submit </button>
  </form>

  <!--
      htmlspecialchars() escapes to neutralize malicious JavaScript (key logging, cookie reading, ...)
      If someone changes the URL in the browser to:

      ...page_form.php"><script>malicious code</script>

      Then without htmlspecialchars() it gets written into your HTML as:

      <form action="page_form.php"><script>malicious code</script>

      htmlspecialchars() converts the < and > into html entities

      &lt;script&gt;malicious code&lt;/script&gt;

      so the script never executes.
     -->

</body>

</html>