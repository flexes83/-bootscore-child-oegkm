<?php
/** Standalone request validation/auth/proxy checks; no WordPress or network needed. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
class WP_Error {
    public $code; public $message; public $data;
    function __construct($code, $message, $data = []) { $this->code = $code; $this->message = $message; $this->data = $data; }
}
class WP_REST_Request {
    public $body; public $nonce = 'valid';
    function get_header($key) { return $this->nonce; }
    function get_json_params() { return $this->body; }
    function get_body() { return json_encode($this->body); }
}
class WP_REST_Response { public $data; public $headers = []; function __construct($data) { $this->data = $data; } function header($key, $value) { $this->headers[$key] = $value; } }
function add_action(...$args) {}
function add_filter(...$args) {}
function is_wp_error($v) { return $v instanceof WP_Error; }
function is_user_logged_in() { return $GLOBALS['logged_in'] ?? false; }
function bootscore_child_oegkm_user_is_member() { return $GLOBALS['member'] ?? false; }
function wp_verify_nonce($nonce, $name) { return $nonce === 'valid' && $name === 'wp_rest'; }
function get_current_user_id() { return 123; }
function get_transient($key) { return $GLOBALS['rate'] ?? 0; }
function set_transient($key, $count, $ttl) { $GLOBALS['stored'][] = [$key, $count, $ttl]; }
function wp_json_encode($value) { return json_encode($value); }
function wp_remote_get($url, $args) { $GLOBALS['calls'][] = [$url, $args]; return ['status' => 200, 'body' => '{"sessionToken":"test-token"}', 'cookies' => ['session-cookie']]; }
function wp_remote_post($url, $args) { $GLOBALS['calls'][] = [$url, $args]; return $GLOBALS['upstream']; }
function wp_remote_retrieve_body($r) { return $r['body']; }
function wp_remote_retrieve_response_code($r) { return $r['status']; }
function wp_remote_retrieve_cookies($r) { return $r['cookies']; }
require __DIR__ . '/../inc/fraxplus.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } echo "PASS $message\n"; }
function invalid($input) { return is_wp_error(oegkm_fraxplus_validate($input)); }
$base = ['age' => 50, 'sex' => 'F', 'weight' => 70, 'height' => 160, 'bmd' => '0', 'previousFracture' => true, 'parentFracturedHip' => true, 'smoking' => false, 'glucocorticoids' => false, 'rheumatoidArthritis' => false, 'secondaryOsteoporosis' => false, 'alcohol' => false];
$input = ['base' => $base, 'adjustment' => ['type' => 'NPF', 'numberPrevFract' => '2']];
$v = oegkm_fraxplus_validate($input);
check(!is_wp_error($v) && $v['base']['country'] === 16 && $v['base']['femoralNeckBMD'] === ['scanningEquipment' => '0'] && $v['adjustment']['numberPrevFract'] === '2', 'documented NPF request with Austrian model');
$bad = $input; $bad['base']['age'] = 39; check(invalid($bad), 'reject age below supported range');
$bad = $input; unset($bad['base']['smoking']); check(invalid($bad), 'reject unanswered risk factors');
$bad = $input; $bad['base']['previousFracture'] = false; check(invalid($bad), 'require prior fracture for NPF');
$bad = $input; $bad['base']['bmd'] = '4'; $bad['base']['bmdValue'] = -1.5; check(invalid($bad), 'reject NPF with BMD');
$bad = $input; $bad['adjustment']['type'] = []; check(invalid($bad), 'reject malformed adjustment type without server error');
$bad = $input; $bad['adjustment']['numberPrevFract'] = '5'; check(invalid($bad), 'reject unsupported fracture count');
$bad = $input; $bad['adjustment'] = ['type' => 'HAL', 'hal' => 120]; check(invalid($bad), 'HAL requires BMD');
$tbs = ['base' => array_merge($base, ['bmd' => '1', 'bmdValue' => 0.8]), 'adjustment' => ['type' => 'TBS', 'scanningEquipment' => '2', 'value' => 1.2]];
check(invalid($tbs), 'TBS requires matching DXA equipment');
$tbs['adjustment']['scanningEquipment'] = '1'; check(!invalid($tbs), 'TBS with matching equipment and supported BMI');
$fa = ['base' => $base, 'adjustment' => ['type' => 'FA', 'measurement' => 'UdBmd', 'value' => 0.5, 'zFA' => 2]];
$v = oegkm_fraxplus_validate($fa); check(isset($v['adjustment']['UdBmd']) && !isset($v['adjustment']['zFA']), 'forward exactly one validated forearm measurement');
$request = new WP_REST_Request(); $request->body = $input;
check(is_wp_error(oegkm_fraxplus_permission($request)), 'deny anonymous API access');
$GLOBALS['logged_in'] = true; check(is_wp_error(oegkm_fraxplus_permission($request)), 'deny non-member API access');
$GLOBALS['member'] = true; $request->nonce = 'bad'; check(is_wp_error(oegkm_fraxplus_permission($request)), 'require session nonce');
$request->nonce = 'valid'; check(oegkm_fraxplus_permission($request) === true, 'allow authenticated member with nonce');
check(oegkm_fraxplus_calculate($request)->code === 'fraxplus_unavailable' && empty($GLOBALS['calls']), 'preview never calls supplier without explicit activation');
define('OEGKM_FRAXPLUS_ENABLED', true); define('OEGKM_FRAXPLUS_API_KEY', 'test-server-key');
$GLOBALS['upstream'] = ['status' => 200, 'body' => json_encode(['fraxScore' => ['mof' => ['display' => 16], 'hip' => ['display' => 1.6]], 'fraxplusAdjustments' => [['type' => 'NPF', 'adjustedMof' => ['display' => 18], 'adjustedHip' => ['display' => 1.8]]], 'originalRequest' => ['private' => 'not-forwarded']])];
$result = oegkm_fraxplus_calculate($request);
check($result instanceof WP_REST_Response && $result->data['adjustment']['mof'] === 18.0 && !isset($result->data['originalRequest']), 'forward safe display values only');
$post = $GLOBALS['calls'][1];
check($post[0] === 'https://www.fraxplus.org/api/v1/frax/adjust' && $post[1]['headers']['X-CSRF-Token'] === 'test-token' && $post[1]['headers']['x-api-key'] === 'test-server-key' && $post[1]['cookies'] === ['session-cookie'], 'server-side API key, CSRF token and session cookies');
check(!str_contains(json_encode($result->data), 'test-server-key') && $result->headers['Cache-Control'] === 'private, no-store, max-age=0', 'no key exposure and no cached medical result');
$GLOBALS['upstream'] = ['status' => 202, 'body' => json_encode(['fraxScore' => ['mof' => ['display' => 16], 'hip' => ['display' => 1.6]], 'fraxplusAdjustments' => [['type' => 'NPF', 'error' => 'no result']]])];
$result = oegkm_fraxplus_calculate($request); check($result->data['partial'] === true && $result->data['adjustment'] === null, 'partial adjustment is never presented as success');
$GLOBALS['rate'] = 10; check(oegkm_fraxplus_calculate($request)->data['status'] === 429, 'rate-limit authenticated calculation requests');
