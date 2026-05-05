<?php
/**
 * HTTP — General-purpose HTTP utility class.
 *
 * This class is a wrapper for PHP's cURL URL-fetching functionality. 
 *
 * All methods are static — no need to instantiate: 
 *  HTTP::curl(), 
 *  HTTP::url_responds()
 *  HTTP::download_file()
 * 
 * HTTP::curl() 
 *   The comprehensive, general-purpose method covering all HTTP methods and options.
 *  
 * The other methods are "convenience methods" — simplified special-case wrappers that use HTTP::curl() 
 * 
 */
class HTTP {
    
    
    /**
     * All-purpose cURL function for HTTP(S) requests.
     *
     * @param string $url         The URL to request
     *
     * The URL is required. All the other input parameters below are optional.
     *
     * @param string $method      HTTP method: GET, POST, PUT, PATCH, DELETE, HEAD
     * @param array  $data        Data to send — appended as query string for GET, sent as body for POST, etc.
     * @param array  $headers     Additional HTTP request headers ['Header-Name: value', ...]
     * @param array  $options     Extra options (default values shown):
     *                              'auth'       => ['user', 'pass']   basic auth (ignored if bearer is set)
     *                              'bearer'     => 'token_string'     adds Authorization: Bearer header
     *                              'timeout'    => 30                 total request timeout in seconds
     *                              'verify_ssl' => true               verify SSL certificate
     *                              'follow'     => true               follow redirects automatically
     *                              'json'       => false              encode data as JSON (default: form-encoded)
     *                              'multipart'  => false              send data as multipart/form-data
     *                              'cookies'    => ['name' => 'value']
     *                              'proxy'      => 'http://proxy:port'
     *                              'user_agent' => 'PHP-cURL/1.0'
     * @return array {
     *   'status'   => int,           // HTTP response code (200, 201, 401, 404, 500, etc.)
     *   'headers'  => array,         // associative array of HTTP response headers, keys lowercased
     *   'body'     => string,        // raw response string exactly as transmitted — always present
     *   'json'     => array|null,    // associative array decoded from body if response Content-Type was JSON, otherwise null
     *   'error'    => string|null,   // cURL transport error message (DNS failure, timeout, SSL error, etc.) — null on success
     *                                //   note: a non-200 HTTP status is NOT a cURL error — check 'status' for that
     *   'info'     => array          // full curl_getinfo() result — total time, bytes transferred, redirect count, etc.
     * }
     */
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    static function curl(
        string $url,
        string $method = 'GET',
        array  $data    = [],
        array  $headers = [],
        array  $options = []
    ): array {

        $method = strtoupper($method);

        // --- Defaults ---
        $timeout    = $options['timeout']    ?? 30;
        $verify_ssl = $options['verify_ssl'] ?? true;
        $follow     = $options['follow']     ?? true;
        $as_json    = $options['json']       ?? false;
        $multipart  = $options['multipart']  ?? false;

        // --- Init ---
        $ch = curl_init();

        // --- Method & Body ---
        switch ($method) {
            case 'GET':
                if (!empty($data)) {
                    $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
                }
                break;

            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($as_json) {
                    $body = json_encode($data);
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Content-Length: ' . strlen($body);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                } elseif ($multipart) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // array = multipart
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
                break;

            case 'PUT':
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                if ($as_json) {
                    $body = json_encode($data);
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Content-Length: ' . strlen($body);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
                break;

            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($data)) {
                    $body = json_encode($data);
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }
                break;

            case 'HEAD':
                curl_setopt($ch, CURLOPT_NOBODY, true);
                break;

            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        // --- Auth ---
        if (!empty($options['bearer'])) {
            $headers[] = 'Authorization: Bearer ' . $options['bearer'];
        } elseif (!empty($options['auth'])) {
            curl_setopt($ch, CURLOPT_USERPWD,
                $options['auth'][0] . ':' . ($options['auth'][1] ?? ''));
        }

        // --- Cookies ---
        if (!empty($options['cookies'])) {
            $cookie_str = http_build_query($options['cookies'], '', '; ');
            curl_setopt($ch, CURLOPT_COOKIE, $cookie_str);
        }

        // --- Proxy ---
        if (!empty($options['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $options['proxy']);
        }

        // --- Capture response headers ---
        $response_headers = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$response_headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) return $len;
                $response_headers[strtolower(trim($header[0]))] = trim($header[1]);
                return $len;
            }
        );

        // --- Core options ---
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
            CURLOPT_SSL_VERIFYPEER => $verify_ssl,
            CURLOPT_SSL_VERIFYHOST => $verify_ssl ? 2 : 0,
            CURLOPT_FOLLOWLOCATION => $follow,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_ENCODING       => '',           // accept all encodings
            CURLOPT_USERAGENT      => $options['user_agent'] ?? 'PHP-cURL/1.0',
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        // --- Execute ---
        $body  = curl_exec($ch);
        $error = curl_error($ch);
        $info  = curl_getinfo($ch);
        curl_close($ch);

        // --- Attempt JSON decode ---
        $json = null;
        if ($body && strpos($response_headers['content-type'] ?? '', 'json') !== false) {
            $json = json_decode($body, true);
        }

        return [
            'status'  => (int) $info['http_code'],
            'headers' => $response_headers,
            'body'    => $body ?: '',
            'json'    => $json,
            'error'   => $error ?: null,
            'info'    => $info,
        ];
    }


    /**
     * Check whether a URL sends back a response by sending a HEAD request.
     * A HEAD request asks the server to respond as it would to a GET but without the response body —
     * useful for checking availability without downloading anything.
     *
     * @param  string $url      The URL to check
     * @param  int    $timeout  Seconds to wait before giving up (default 10)
     * @return bool             true if server responded, false if unreachable
     */
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    static function url_responds(string $url, int $timeout = 10): bool {
        $response = self::curl($url, 'HEAD', [], [], ['timeout' => $timeout]);
        return $response['error'] === null && $response['status'] > 0;
    }


   
    /**
     * Download a URL and save the response body to a local file.
     * Useful for fetching remote files (CSV exports, PDFs, images, etc.) and storing them locally.
     *
     * @param  string $url       The URL to download
     * @param  string $filepath  Path to save the file: relative ('dir/data.csv') or absolute (/full/path/to/dir/data.csv).
     *                           The directory must already exist and be writable by the calling script.
     * @param  array  $options   Same options as HTTP::curl() — bearer token, timeout, etc.
     * @return bool              true on success, false if download or save failed
     */
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    static function download_file(string $url, string $filepath, array $options = []): bool {
        $response = self::curl($url, 'GET', [], [], $options);
        if ($response['error'] || $response['status'] !== 200 || !$response['body']) {
            return false;
        }
        return file_put_contents($filepath, $response['body']) !== false;
    }

}
