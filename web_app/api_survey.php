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

?>