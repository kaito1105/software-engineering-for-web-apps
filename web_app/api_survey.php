<?
require_once 'init.php';

header('Content-Type: application/json');

$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
  ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '') ?? '';
$bearer_token = str_replace('Bearer ', '', $auth_header);

if (!$bearer_token) {
  http_response_code(401);
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized — Bearer token required']);
  exit;
}

$user_id = user::get_user_from_api_token($bearer_token);

if ($user_id === -1) {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => 'Forbidden — invalid token']);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
  $result = lib::db_query(
    "SELECT * FROM " . FORM_TABLE . " ORDER BY recipe_name ASC"
  );
  $rows = $result->fetchAll();

  $api_log = new api_log();
  $api_log->values['api_log_user_id'] = $user_id;
  $api_log->values['api_log_form_id'] = null;
  $api_log->values['api_log_timestamp'] = time();
  $api_log->values['api_log_method'] = 'GET';
  $api_log->values['api_log_http_code'] = 200;
  $api_log->values['api_log_token'] = $bearer_token;
  $api_log->save();

  $response = array(
    "status" => "success",
    "count" => count($rows),
    "data" => $rows
  );

  http_response_code(200);
  echo json_encode($response);
  exit;

} else if ($method === 'POST') {
  // implement POST endpoint
} else {
  http_response_code(405);
  echo json_encode(['status' => 'error', 'message' => 'Request Method not allowed']);
  exit;
}

?>