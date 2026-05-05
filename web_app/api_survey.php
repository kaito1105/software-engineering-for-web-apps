<?
require_once 'init.php';

header('Content-Type: application/json');

function log_api_request($user_id, $form_id, $method, $http_code, $token)
{
  $log = new api_log();
  $log->values['api_log_user_id'] = $user_id;
  $log->values['api_log_form_id'] = $form_id;
  $log->values['api_log_timestamp'] = time();
  $log->values['api_log_method'] = $method;
  $log->values['api_log_http_code'] = $http_code;
  $log->values['api_log_token'] = $token;
  $log->save();
}

$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
  ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '') ?? '';
$bearer_token = str_replace('Bearer ', '', $auth_header);

if (!$bearer_token) {
  log_api_request(null, null, $_SERVER['REQUEST_METHOD'], 401, '');
  http_response_code(401);
  echo json_encode([
    'status' => 'error',
    'message' => 'Unauthorized — Bearer token required'
  ]);
  exit;
}

$user_id = user::get_user_from_api_token($bearer_token);

if ($user_id === -1) {
  log_api_request(null, null, $_SERVER['REQUEST_METHOD'], 403, $bearer_token);
  http_response_code(403);
  echo json_encode([
    'status' => 'error',
    'message' => 'Forbidden — invalid token'
  ]);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
  $result = lib::db_query(
    "SELECT * FROM " . FORM_TABLE . " ORDER BY recipe_name ASC"
  );
  $rows = $result->fetchAll();

  log_api_request($user_id, null, 'GET', 200, $bearer_token);

  $response = array(
    "status" => "success",
    "count" => count($rows),
    "data" => $rows
  );

  http_response_code(200);
  echo json_encode($response);
  exit;

} else if ($method === 'POST') {
  $recipe_name = $_POST['recipe_name'] ?? '';
  $email = $_POST['email'] ?? '';
  $preparation_date = $_POST['preparation_date'] ?? '';
  $total_cooking_time = $_POST['total_cooking_time'] ?? '';
  $difficulty_level = $_POST['difficulty_level'] ?? '';
  $meal_time = $_POST['meal_time'] ?? '';
  $dish_type = $_POST['dish_type'] ?? '';
  $main_cooking_method = $_POST['main_cooking_method'] ?? '';
  $proteins_used = implode(', ', $_POST['proteins_used'] ?? []);
  $additional_ingredients = implode(', ', $_POST['additional_ingredients'] ?? []);
  $cooking_instructions = $_POST['cooking_instructions'] ?? '';

  $cooking_time_valid = ctype_digit($total_cooking_time) && (int) $total_cooking_time > 0;

  if (
    !$recipe_name || !$email || !$preparation_date || !$cooking_time_valid ||
    !$difficulty_level || !$meal_time || !$dish_type || !$main_cooking_method ||
    !$additional_ingredients || !$cooking_instructions
  ) {
    log_api_request($user_id, null, 'POST', 422, $bearer_token);
    http_response_code(422);
    echo json_encode([
      'status' => 'error',
      'message' => 'Validation failed — required fields missing or invalid'
    ]);
    exit;
  }

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
  $new_form_id = $pdo->lastInsertId();

  log_api_request($user_id, $new_form_id, 'POST', 201, $bearer_token);

  http_response_code(201);
  echo json_encode(['status' => 'success', 'message' => 'Survey record created']);
  exit;

} else {
  log_api_request($user_id, null, $_SERVER['REQUEST_METHOD'], 405, $bearer_token);
  http_response_code(405);
  echo json_encode(['status' => 'error', 'message' => 'Request Method not allowed']);
  exit;
}

?>