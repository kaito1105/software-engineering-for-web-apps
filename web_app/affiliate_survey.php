<?
require 'init.php';

define('SURVEY_API_URL', 'https://csci.lakeforest.edu/~miyamotok/csci488/web_app/api_survey.php');
define('SURVEY_API_TOKEN', '8f9d8313cbc55922b6ba34194ab5599d64ed12f3161813193c63ff724cee9f13');

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
$error_message = '';

$task = $get_post['task'];

if ($task == 'save') {

  $recipe_name = $get_post['recipe_name'] ?? '';
  $email = $get_post['email'] ?? '';
  $preparation_date = $get_post['preparation_date'] ?? '';
  $total_cooking_time = $get_post['total_cooking_time'] ?? '';
  $difficulty_level = $get_post['difficulty_level'] ?? '';
  $meal_time = $get_post['meal_time'] ?? '';
  $dish_type = $get_post['dish_type'] ?? '';
  $main_cooking_method = $get_post['main_cooking_method'] ?? '';
  $proteins_used = $get_post['proteins_used'] ?? [];
  $selected_additional_ingredients = $get_post['additional_ingredients'] ?? [];
  $cooking_instructions = $get_post['cooking_instructions'] ?? '';

  $cooking_time_valid = ctype_digit($total_cooking_time) && (int) $total_cooking_time > 0;

  if (
    !$recipe_name || !$email || !$preparation_date || !$cooking_time_valid ||
    !$difficulty_level || !$meal_time || !$dish_type || !$main_cooking_method ||
    !$additional_ingredients || !$cooking_instructions
  ) {
    header("Location: affiliate_survey.php?incomplete=yes");
    exit;
  }

  $response = HTTP::curl(
    SURVEY_API_URL,
    'POST',
    $_POST,
    [],
    ['bearer' => SURVEY_API_TOKEN]
  );

  if ($response['error']) {
    $error_message = "Could not reach the survey API: " . $response['error'];
  } else if ($response['status'] == 201) {
    header("Location: survey_list.php?complete=yes");
    exit;
  } else {
    $error_message = "API error (status " . $response['status'] . "): "
      . ($response['json']['message'] ?? $response['body']);
  }
}

$security = false;
require 'ssi_top.php';
?>

<? if ($get_post['incomplete'] ?? '') { ?>
  Your Form Submission was Missing Data<br><br>
<? } ?>

<? if ($error_message) { ?>
  <div style="color:red;"><?= htmlspecialchars($error_message) ?></div><br>
<? } ?>
<br>

<form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
  <input type="hidden" name="task" value="save">

  <label for="recipe_name">Recipe Name:</label><br>
  <input type="text" id="recipe_name" name="recipe_name" 
    value="<?= htmlspecialchars($recipe_name ?? '') ?>"
    placeholder="e.g., Spaghetti Carbonara" required>
  <br><br>

  <label for="email">Your Email:</label><br>
  <input type="email" id="email" name="email" 
    value="<?= htmlspecialchars($email ?? '') ?>" 
    placeholder="abc@example.com" required>
  <br><br>

  <label for="preparation_date">Preparation Date:</label><br>
  <input type="date" id="preparation_date" name="preparation_date" 
    value="<?= htmlspecialchars($preparation_date ?? '') ?>" required>
  <br><br>

  <label for="total_cooking_time">Total Cooking Time (minutes):</label><br>
  <input type="number" id="total_cooking_time" name="total_cooking_time" 
    value="<?= htmlspecialchars($total_cooking_time ?? '') ?>" 
    min="1" max="600" required>
  <br><br>

  <label for="difficulty_level">Difficulty Level (1 = Easy, 10 = Hard):</label><br>
  <input type="range" id="difficulty_level" name="difficulty_level" 
    value="<?= htmlspecialchars($difficulty_level ?? '') ?>" min="1" max="10" required>
  <br><br>

  Meal Time:<br>
  <?= radio_button("breakfast", "meal_time", "breakfast", "Breakfast", $meal_time ?? ''); ?>
  <?= radio_button("lunch", "meal_time", "lunch", "Lunch", $meal_time ?? ''); ?>
  <?= radio_button("dinner", "meal_time", "dinner", "Dinner", $meal_time ?? ''); ?>
  <?= radio_button("snack", "meal_time", "snack", "Snack", $meal_time ?? ''); ?>
  <br>

  Dish Type:<br>
  <?= radio_button("main_course", "dish_type", "main", "Main Course", $dish_type ?? ''); ?>
  <?= radio_button("side_dish", "dish_type", "side", "Side Dish", $dish_type ?? ''); ?>
  <?= radio_button("appetizer", "dish_type", "appetizer", "Appetizer", $dish_type ?? ''); ?>
  <?= radio_button("soup", "dish_type", "soup", "Soup", $dish_type ?? ''); ?>
  <?= radio_button("dessert", "dish_type", "dessert", "Dessert", $dish_type ?? ''); ?>
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
    <input type="checkbox" id="<?= $value ?>" name="proteins_used[]" 
      value="<?= $value ?>" <?= in_array($value, $proteins_used) ? "checked" : "" ?>>
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
    required><?= htmlspecialchars($cooking_instructions ?? '') ?></textarea>
  <br><br>

  <button type="reset"> Reset </button>
  <button type="submit"> Submit </button>
</form>

<?
require 'ssi_bottom.php';
?>