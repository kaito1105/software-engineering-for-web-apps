<?
require 'init.php';
// This assumes that class_HTTP.php has been required by the init file. 

// ============================================================
// API Test Client
// Tests the key outcomes your API must handle correctly.
// Change these two values to point at the API you are testing.
// ============================================================
$api_url   = 'https://csci.lakeforest.edu/~miyamotok/csci488/web_app/survey_api.php';
$api_token = '8f9d8313cbc55922b6ba34194ab5599d64ed12f3161813193c63ff724cee9f13';

// Helper — runs one test and returns a structured result with test results
function run_test($label, $url, $method, $options = []) {
    
    $response = HTTP::curl($url, $method, [], [], $options);
    return [
        'label'   => $label,
        'method'  => $method,
        'status'  => $response['status'],
        'message' => $response['json']['message'] ?? ($response['error'] ?? $response['body']),
    ];
}

$tests = [];  // to store the resuts from the various tests below. 

// 1. Is the API endpoint reachable? — sends a HEAD request (like GET but server returns no body)
//    to get a quick answer on whether the URL responds at all, without downloading any content.
// Note: Wanted to package this test like the other ones, but there is a convenience method 
//       HTTP::url_responds() in the HTTP class the could be used for this test.
$tests[] = run_test('API endpoint reachable (HEAD request)', $api_url, 'HEAD');

// Dont get a response message for this one, so adding it. 
$tests[0]['message'] = ($tests[0]['status']==401) ? 'YES' : 'NO';

// 2. No Authorization header at all — expect 401
$tests[] = run_test('No Authorization header', $api_url, 'GET');

// 3. Invalid token (not found in user table) — expect 403
$tests[] = run_test('Invalid Token', $api_url, 'GET', ['bearer' => 'not-a-real-token']);

// 4. Unsupported HTTP method — The code in part 4 instructions I gave you would give 405 error from your API.
//    But the Apache web server software on our server is rejecing anything but GET and POST and returning a generic error HTML page. 
$tests[] = run_test('Unsupported method (DELETE)', $api_url, 'DELETE', ['bearer' => $api_token]);

// 5. Valid token — GET all records — expect 200
$tests[] = run_test('Valid token — GET all records', $api_url, 'GET', ['bearer' => $api_token]);


?>
<!DOCTYPE html>
<html>
<head><title>API Test Client</title></head>
<body>

<h3>API Test Client</h3>
<b>URL:</b> <?= htmlspecialchars($api_url) ?><br><br>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <td><b>#</b></td>
        <td><b>Test</b></td>
        <td><b>Method</b></td>
        <td><b>Response Status</b></td>
        <td><b>Message / Error</b></td>
    </tr>
    <? $num = 1; foreach ($tests as $test) { ?>
    <tr>
        <td><?= $num++ ?></td>
        <td><?= htmlspecialchars($test['label']) ?></td>
        <td><?= htmlspecialchars($test['method']) ?></td>
        <td><?= htmlspecialchars($test['status']) ?></td>
        <td><?= htmlspecialchars($test['message']) ?></td>
    </tr>
    <? } ?>
</table>

</body>
</html>
