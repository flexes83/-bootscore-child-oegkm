<?php
/** Unlisted FRAXplus preview. No patient records or API credentials in the theme. */
if (!defined('ABSPATH')) { exit; }

function oegkm_fraxplus_key(): string {
    return trim((string) (defined('OEGKM_FRAXPLUS_API_KEY') ? OEGKM_FRAXPLUS_API_KEY : getenv('OEGKM_FRAXPLUS_API_KEY')));
}

function oegkm_fraxplus_ready(): bool {
    return defined('OEGKM_FRAXPLUS_ENABLED') && OEGKM_FRAXPLUS_ENABLED === true && oegkm_fraxplus_key() !== '';
}

add_action('template_redirect', function () {
    global $wp, $wp_query;
    if (trim((string) $wp->request, '/') !== 'mitgliederbereich/fraxplus-vorschau') { return; }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    nocache_headers();
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    if (!is_user_logged_in()) {
        wp_safe_redirect(bootscore_child_oegkm_login_page_url(home_url('/mitgliederbereich/fraxplus-vorschau/')));
        exit;
    }
    bootscore_child_oegkm_require_member_access();
    $wp_query->is_404 = false;
    status_header(200);
    add_filter('pre_get_document_title', static function () { return 'FRAXplus Vorschau – ÖGKM'; });
    add_filter('wp_robots', static function ($robots) { $robots['noindex'] = true; $robots['nofollow'] = true; return $robots; });
    wp_enqueue_style('oegkm-fraxplus', get_stylesheet_directory_uri() . '/assets/css/fraxplus.css', ['bootscore-child-oegkm'], bootscore_child_oegkm_asset_version('assets/css/fraxplus.css'));
    wp_enqueue_script('oegkm-fraxplus', get_stylesheet_directory_uri() . '/assets/js/fraxplus.js', [], bootscore_child_oegkm_asset_version('assets/js/fraxplus.js'), true);
    wp_localize_script('oegkm-fraxplus', 'oegkmFraxplus', [
        'endpoint' => rest_url('oegkm/v1/fraxplus/calculate'),
        'nonce' => wp_create_nonce('wp_rest'),
        'ready' => oegkm_fraxplus_ready(),
    ]);
    require get_stylesheet_directory() . '/page-fraxplus-preview.php';
    exit;
}, 0);

function oegkm_fraxplus_permission(WP_REST_Request $request) {
    if (!is_user_logged_in() || !bootscore_child_oegkm_user_is_member()) {
        return new WP_Error('fraxplus_forbidden', 'Bitte als freigeschaltetes ÖGKM-Mitglied anmelden.', ['status' => 403]);
    }
    if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
        return new WP_Error('fraxplus_nonce', 'Die Sitzung bitte durch Neuladen aktualisieren.', ['status' => 403]);
    }
    return true;
}

function oegkm_fraxplus_invalid(string $message): WP_Error {
    return new WP_Error('fraxplus_input', $message, ['status' => 400]);
}

/** Strict, documented subset; no unknown keys forwarded to the supplier. */
function oegkm_fraxplus_validate($input) {
    if (!is_array($input) || !isset($input['base']) || !is_array($input['base'])) { return oegkm_fraxplus_invalid('Bitte die Basisdaten vollständig angeben.'); }
    $v = $input['base'];
    $base = ['country' => 16]; // Preview currently supports the Austrian model only.
    foreach (['age' => [40, 90], 'weight' => [25, 125], 'height' => [100, 220]] as $name => $bounds) {
        if (!isset($v[$name]) || !is_numeric($v[$name]) || !is_finite((float) $v[$name]) || (float) $v[$name] < $bounds[0] || (float) $v[$name] > $bounds[1]) { return oegkm_fraxplus_invalid('Bitte Alter, Gewicht und Größe innerhalb der angegebenen Grenzen eingeben.'); }
    }
    if (!isset($v['sex']) || !in_array($v['sex'], ['F', 'M'], true)) { return oegkm_fraxplus_invalid('Bitte das Geschlecht auswählen.'); }
    $base['age'] = (float) $v['age'];
    $base['sex'] = $v['sex'];
    $base['bmi'] = ['weight' => (float) $v['weight'], 'height' => (float) $v['height'], 'units' => 'metric'];
    foreach (['previousFracture', 'parentFracturedHip', 'smoking', 'glucocorticoids', 'rheumatoidArthritis', 'secondaryOsteoporosis', 'alcohol'] as $name) {
        if (!array_key_exists($name, $v) || !is_bool($v[$name])) { return oegkm_fraxplus_invalid('Bitte alle klinischen Risikofaktoren mit Ja oder Nein beantworten.'); }
        $base[$name] = $v[$name];
    }
    $bmd = $v['bmd'] ?? '0';
    if (!in_array($bmd, ['0', '1', '2', '3', '4', '5', '5.1', '6', '7'], true)) { return oegkm_fraxplus_invalid('Bitte die BMD-Messmethode prüfen.'); }
    $base['femoralNeckBMD'] = ['scanningEquipment' => $bmd];
    if ($bmd !== '0') {
        if (!isset($v['bmdValue']) || !is_numeric($v['bmdValue']) || !is_finite((float) $v['bmdValue'])) { return oegkm_fraxplus_invalid('Bitte den BMD- bzw. T-Score-Wert eingeben.'); }
        $value = (float) $v['bmdValue'];
        if (($bmd === '4' && ($value < -6 || $value > 6)) || ($bmd !== '4' && ($value <= 0 || $value > 3))) { return oegkm_fraxplus_invalid('Bitte den Messwert und seine Einheit prüfen.'); }
        $base['femoralNeckBMD']['value'] = $value;
        $base['femoralNeckBMD']['unit'] = $bmd === '4' ? 't' : 'g/cm2';
    }
    if (empty($input['adjustment'])) { return ['base' => $base, 'adjustment' => null]; }
    $a = $input['adjustment'];
    if (!is_array($a) || !isset($a['type']) || !is_string($a['type'])) { return oegkm_fraxplus_invalid('Bitte eine einzelne Anpassung auswählen.'); }
    $type = $a['type'];
    $adjustment = ['type' => $type];
    $options = [
        'PREV' => ['fracture' => ['vert', 'hip', 'hum', 'forearm', 'other'], 'time' => ['0-1M', '1-6M', '6-12M', '12-24M']],
        'COR' => ['dose' => ['LOW', 'MID', 'HIG']],
        'DIA' => ['duration' => ['<5y', '5-10y', '>10y']],
        'FALL' => ['falls' => ['0', '1', '2', '3+']],
        'NPF' => ['numberPrevFract' => ['1', '2', '3', '4+']],
        'TBS' => ['scanningEquipment' => ['1', '2']],
        'FNLS' => [], 'HAL' => [], 'PHPT' => [], 'FA' => [],
    ];
    if (!isset($options[$type])) { return oegkm_fraxplus_invalid('Diese Anpassung wird nicht unterstützt.'); }
    if ((in_array($type, ['PREV', 'NPF'], true) && !$base['previousFracture']) || ($type === 'COR' && !$base['glucocorticoids'])) { return oegkm_fraxplus_invalid('Diese Anpassung benötigt den entsprechenden Risikofaktor in den Basisdaten.'); }
    if (in_array($type, ['NPF', 'PHPT'], true) && $bmd !== '0') { return oegkm_fraxplus_invalid('Diese Anpassung ist nur ohne BMD vorgesehen.'); }
    if (in_array($type, ['TBS', 'FNLS', 'HAL'], true) && $bmd === '0') { return oegkm_fraxplus_invalid('Diese Anpassung benötigt eine BMD- bzw. T-Score-Angabe.'); }
    foreach ($options[$type] as $field => $allowed) {
        if ($type === 'DIA' && $bmd === '0') { continue; }
        if (!isset($a[$field]) || !(is_string($a[$field]) || is_int($a[$field])) || !in_array((string) $a[$field], $allowed, true)) { return oegkm_fraxplus_invalid('Bitte die Zusatzangaben vollständig auswählen.'); }
        $adjustment[$field] = (string) $a[$field];
    }
    $numbers = ['TBS' => ['value', 0.5, 2], 'FNLS' => ['tscore', -6, 6], 'HAL' => ['hal', 1, 200]];
    if (isset($numbers[$type])) {
        [$field, $min, $max] = $numbers[$type];
        if (!isset($a[$field]) || !is_numeric($a[$field]) || !is_finite((float) $a[$field]) || (float) $a[$field] < $min || (float) $a[$field] > $max) { return oegkm_fraxplus_invalid('Bitte den Zusatzmesswert innerhalb der angegebenen Grenzen eingeben.'); }
        $adjustment[$field] = (float) $a[$field];
    }
    if ($type === 'TBS') {
        $bmi = (float) $v['weight'] / pow((float) $v['height'] / 100, 2);
        if ($bmi < 15 || $bmi > 37 || !in_array($bmd, ['1', '2', '4'], true) || ($bmd !== '4' && $adjustment['scanningEquipment'] !== $bmd)) { return oegkm_fraxplus_invalid('TBS benötigt BMI 15–37 und ein passendes GE-Lunar- oder Hologic-Gerät.'); }
    }
    if ($type === 'PHPT') {
        if (($a['phptWithoutPtx'] ?? false) !== true) { return oegkm_fraxplus_invalid('PHPT ist hier nur ohne vorangegangene Parathyreoidektomie vorgesehen.'); }
        $adjustment['phptWithoutPtx'] = true;
    }
    if ($type === 'FA') {
        $field = $a['measurement'] ?? '';
        if (!in_array($field, ['zFA', 'UdBmd', 'TdBmd'], true) || !isset($a['value']) || !is_numeric($a['value']) || !is_finite((float) $a['value'])) { return oegkm_fraxplus_invalid('Bitte einen Unterarm-Messwert auswählen und eingeben.'); }
        $value = (float) $a['value'];
        if (($field === 'zFA' && ($value < -4 || $value > 4)) || ($field !== 'zFA' && ($value < 0.1 || $value > 2))) { return oegkm_fraxplus_invalid('Bitte den Wertebereich der Unterarmmessung beachten.'); }
        $adjustment[$field] = $value;
    }
    return ['base' => $base, 'adjustment' => $adjustment];
}

add_action('rest_api_init', function () {
    register_rest_route('oegkm/v1', '/fraxplus/calculate', [
        'methods' => 'POST', 'permission_callback' => 'oegkm_fraxplus_permission',
        'callback' => 'oegkm_fraxplus_calculate',
    ]);
});

add_filter('rest_post_dispatch', function ($response, $server, $request) {
    if (strpos($request->get_route(), '/oegkm/v1/fraxplus/') === 0 && is_object($response) && method_exists($response, 'header')) {
        $response->header('Cache-Control', 'private, no-store, max-age=0');
        $response->header('X-Robots-Tag', 'noindex, nofollow');
    }
    return $response;
}, 10, 3);

function oegkm_fraxplus_calculate(WP_REST_Request $request) {
    $validated = oegkm_fraxplus_validate($request->get_json_params());
    if (is_wp_error($validated)) { return $validated; }
    $key = oegkm_fraxplus_key();
    if (!oegkm_fraxplus_ready()) { return new WP_Error('fraxplus_unavailable', 'Die Live-Berechnung wird nach der API-Freischaltung aktiviert. Bitte vorerst die Beispielvorschau verwenden.', ['status' => 503]); }
    if (strlen($request->get_body()) > 8192) { return oegkm_fraxplus_invalid('Die Anfrage ist zu groß.'); }
    // Per-member request budget. Contains no patient data or credentials.
    $rate_key = 'oegkm_frax_' . get_current_user_id();
    $count = (int) get_transient($rate_key);
    if ($count >= 10) { return new WP_Error('fraxplus_rate', 'Bitte eine Minute warten, bevor Sie erneut berechnen.', ['status' => 429]); }
    set_transient($rate_key, $count + 1, MINUTE_IN_SECONDS);
    $session = wp_remote_get('https://www.fraxplus.org/api/v1/current-user', ['timeout' => 15, 'redirection' => 0, 'headers' => ['x-api-key' => $key, 'Accept' => 'application/json']]);
    if (is_wp_error($session)) { return new WP_Error('fraxplus_connection', 'FRAXplus ist momentan nicht erreichbar. Bitte später erneut versuchen.', ['status' => 502]); }
    $auth = json_decode(wp_remote_retrieve_body($session), true);
    if (wp_remote_retrieve_response_code($session) !== 200 || empty($auth['sessionToken']) || !is_string($auth['sessionToken'])) { return new WP_Error('fraxplus_access', 'Der FRAXplus-API-Zugang ist noch nicht freigeschaltet oder muss geprüft werden.', ['status' => 503]); }
    $adjust = $validated['adjustment'] !== null;
    $body = $adjust ? ['fraxScoreQuery' => $validated['base'], 'fraxplusScoreQuery' => [$validated['adjustment']]] : $validated['base'];
    $response = wp_remote_post('https://www.fraxplus.org/api/v1/frax' . ($adjust ? '/adjust' : ''), [
        'timeout' => 25, 'redirection' => 0,
        'headers' => ['x-api-key' => $key, 'X-CSRF-Token' => $auth['sessionToken'], 'Accept' => 'application/json', 'Content-Type' => 'application/json'],
        'cookies' => wp_remote_retrieve_cookies($session), 'body' => wp_json_encode($body),
    ]);
    if (is_wp_error($response)) { return new WP_Error('fraxplus_connection', 'Die Berechnung wurde nicht bestätigt. Bitte später erneut versuchen.', ['status' => 502]); }
    $status = wp_remote_retrieve_response_code($response);
    if (!in_array($status, [200, 202], true)) {
        return new WP_Error('fraxplus_upstream', in_array($status, [400, 406], true) ? 'FRAXplus kann diese Eingabekombination nicht berechnen. Bitte Messmethode, Werte und Voraussetzungen der Anpassung prüfen.' : 'Die FRAXplus-Berechnung ist momentan nicht verfügbar. Bitte später erneut versuchen.', ['status' => 502]);
    }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data) || !isset($data['fraxScore']['mof'], $data['fraxScore']['hip'])) { return new WP_Error('fraxplus_response', 'FRAXplus hat kein vollständiges Ergebnis geliefert.', ['status' => 502]); }
    // Do not forward echoed requests, server internals or arbitrary supplier text.
    $result = ['base' => [], 'adjustment' => null, 'partial' => $status === 202];
    foreach (['mof', 'hip'] as $name) {
        $value = $data['fraxScore'][$name];
        if (!isset($value['display']) || !is_numeric($value['display']) || $value['display'] < 0 || $value['display'] > 100) { return new WP_Error('fraxplus_response', 'Ungültige Ergebnisdaten. Bitte erneut versuchen.', ['status' => 502]); }
        $result['base'][$name] = (float) $value['display'];
    }
    if ($adjust) {
        $row = $data['fraxplusAdjustments'][0] ?? [];
        if (isset($row['error']) || ($row['type'] ?? '') !== $validated['adjustment']['type']) { $result['partial'] = true; }
        else {
            $result['adjustment'] = ['type' => $row['type'], 'mof' => null, 'hip' => null];
            foreach (['mof' => 'adjustedMof', 'hip' => 'adjustedHip'] as $name => $field) {
                $number = $row[$field]['display'] ?? null;
                if (is_numeric($number) && $number >= 0 && $number <= 100) { $result['adjustment'][$name] = (float) $number; }
            }
            if (!empty($row['warnings'])) { $result['partial'] = true; }
        }
    }
    $rest = new WP_REST_Response($result, 200);
    $rest->header('Cache-Control', 'private, no-store, max-age=0');
    return $rest;
}
