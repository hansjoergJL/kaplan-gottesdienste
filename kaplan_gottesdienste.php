<?php
defined('ABSPATH') or die("Please use as described.");

// Global version constant
$kaplan_plugin_version = '1.9.3';

/**
 * Plugin Name:  KaPlan Gottesdienste
 * Plugin URI: http://www.jlsoftware.de/software/kaplan-plugin/
 * Description: Anzeige aktueller Gottesdienste aus KaPlan
 * Version: 1.9.1
 * Author: Peter Hellerhoff & Hans-Joerg Joedike
 * Author URI: http://www.jlsoftware.de/
 * License: GPL2 or newer
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  kaplan-import
 * GitHub Plugin URI: hansjoergJL/kaplan-gottesdienste
 * GitHub Branch: main
 * Requires PHP: 7.4
 * Requires WP: 2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KAPLAN_PLUGIN_VERSION', $kaplan_plugin_version);
define('KAPLAN_PLUGIN_FILE', __FILE__);
define('KAPLAN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KAPLAN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KAPLAN_GITHUB_REPO', 'hansjoergJL/kaplan-gottesdienste');

// Load the GitHub updater
require_once KAPLAN_PLUGIN_DIR . 'includes/class-kaplan-updater.php';

// Initialize the updater when plugins are loaded
add_action('plugins_loaded', 'kaplan_init_updater', 11);

/**
 * Initialize the GitHub updater
 */
function kaplan_init_updater() {
    if (class_exists('KaPlan_GitHub_Updater')) {
        new KaPlan_GitHub_Updater(
            KAPLAN_PLUGIN_FILE,
            KAPLAN_PLUGIN_VERSION,
            KAPLAN_GITHUB_REPO
            // Add GitHub token as 4th parameter if you have a private repo:
            // , 'your_github_token_here'
        );
    }
}

// Version 1.9.3  [Jö] 2025-10-04  Dienste, Liturg
// Version 1.9.2  [Jö] 2025-10-04  Fixed guest Leitung display in Template 1,2,3
// Version 1.9.1  [Jö] 2025-09-30  Added comment to preserve deprecation warning in DateTime_german::format method
// Version 1.9.0  [Jö] 2025-09-23  Template "3" Improvements
// Version 1.8.9  [Jö] 2025-09-23  CRITICAL FIX: PHP 8.4 compatibility - fixed syntax error in debug comment and return type
// Version 1.8.5  [Jö] 2025-09-21  CRITICAL FIX: Smart quotes normalization in shortcode attributes
// Version 1.8.4  [Jö] 2025-01-21  Updated version requirements: PHP 5.5, WordPress 2.7
// Version 1.8.3  [Jö] 2025-01-09  Added Template="3" with 3 columns
// Version 1.8.2  [Jö] 2025-01-09  CRITICAL FIX: Template default behavior and VT mode
// Version 1.8.1  [Jö] 2025-01-09  Enhanced documentation for Template="2" and built-in updates
// Version 1.8.0  [Jö] 2025-01-09  Added Template="2" for columnar layout with date headers
// Version 1.7.0  [Jö] 2025-01-08  Stable version, revert complex features causing errors
// Version 1.6.4  [Jö] 2025-01-07  Code formatting, consistent indentation
// Version 1.6.3  [Jö] 2025-01-07  PHP 8+ compatibility, security improvements
// Version 1.6.2  [Jö] 2025-01-07  Parameter validation, error handling
// Version 1.6.1  [Jö] 2024-04-26  Gemeindetermine (mode=GT)
// Version 1.6    [Jö] 2024-04-18  Zelebrantenangabe (Leitung=...)
// Version 1.5.1  [Jö] 2023-04-20  TE_Zusatz2 integriert
// Version 1.5    [Jö] 2023-04-09  Fällt-aus mit Kirchenname
// Version 1.4    [Jö] 2022-03-29  Wordpress 5.9.2 Kompatibilität
// Version 1.3.x  [Jö] 2021-02-05  http-Links im Zusatzfeld werden in kurzen "Internet"-Link umgeschrieben
// Version 1.0    [PH] 2021

class kaplan_kalender {
    
    // Hier wird die URL für die API-Abfrage als JSON zusammengesetzt
    // Die Attribute aus dem Tag im WP-Beitrag kommen hier im Array $atts an.
    // nur Kleinbuchstaben! 
    private static function get_url($atts) {
        $req = ($atts['secure'] == '0' ? 'http' : 'https') . '://' . $atts['server'];
        if (substr($req, -1, 1) != '/') {
            $req .= '/';
        }
        $FormatLeitung = $atts['leitung'];  // Ausgabeformat Leitung: K / N / VN / V.N / TVN / TV.N (T=Titel V=Vorname N=Nachname K=Kuerzel)
        $req .= 'get.asp?Arbeitsgruppe=' . $atts['arbeitsgruppe']
            . '&Code=' . $atts['code']
            . '&mode=' . $atts['mode']
            . '&options=' . ($atts['options'] ? 'L' . $atts['options'] : 'L')
            . '&type=json&req=plugin'
            . ($atts['dienste'] ? ('&dienste=' . $atts['dienste']) : '')
            . ($atts['days'] ? ('&days=' . $atts['days']) : '');
        return $req;
    }

    // Formatierung der Datumswerte mit führender Null
    private static function add_zero($int) {
        return $int < 10 ? ('0' . $int) : (string)$int;
    }
            
    // Formatierung für die Uhrzeit bei abgsagten Terminen (rote Schrift)
    private static function red($str, $red) {
        if ($red) {
            return '<span style="color:red;">' . $str . '</span>';
        } else {
            return $str;
        }
    }

    // Formatierung für die korrekte Darstellung von Sonderzeichen als HTML-Codes
    // zB   "<" => "&lt;"
    private static function html($str, $nl2br=true) {
        if ($nl2br) {
            return nl2br(htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }
        return htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    
    // Formatierung des Leitungsnamens
    // 
    private static function format_leitung($Format, $Kuerzel, $Titel, $Vorname, $Nachname, $Ordensname, $LeitungGast, $Organisation, $Liturg) {
        // Ausgabeformat Leitung: K / N / VN / V.N / TN / TVN / TV.N / O (T=Titel V=Vorname N=Nachname K=Kuerzel, O=Organisation)
        if ('' . $Format == '') {
            return '';
        }
        $Ret = $Nachname;  // Default
        if ($Format == 'K') {  // Kürzel
            $Ret = $Kuerzel;
        }
        else if ($Ordensname) {
            $Ret =  trim($Titel . ' ' . $Ordensname);
        }
        else if ($Format == 'N') {  // Nachname
            $Ret = $Nachname;
        }
        else if ($Format == 'VN') {  // Vorname Nachname
            $Ret = trim($Vorname . ' ' . $Nachname);
        }
        else if ($Format == 'V.N') {  // V. Nachname
            $Ret = trim(substr($Vorname, 0, 1) . '. ' . $Nachname, ' .');
        }
        else if ($Format == 'TN') {   // Titel Nachname
            $Ret = trim($Titel . ' ' . $Nachname);
        }
        else if ($Format == 'TVN') {   // Titel Vorname Nachname
            $Ret = trim($Titel . ' ' . $Vorname . ' ' . $Nachname);
        }
        else if ($Format == 'TV.N') {  // Titel V. Nachname
            $Ret = trim($Titel . ' ' . substr($Vorname . ' ', 0, 1) . '. ' . $Nachname, ' .');
        }
        else if ($Format == 'O') {  // Organisation
            if ($Organisation != '') {
                $Ret = $Organisation;
            }
            else
                $Ret = trim(substr($Vorname . ' ', 0, 1) . '. ' . $Nachname, ' .');
        }
        if ($LeitungGast != '') {
            if ($Ret != '')
                $Ret .= ', ';
            $Ret .= $LeitungGast;
        }
        if ($Liturg != '') {
            if ($Ret != '')
                $Ret .= ', ';
            $Ret .= $Liturg;
        }
        return $Ret;
    }
    
    // Polyfill für str_ends_with (PHP 8.0+)
    private static function str_ends_with($haystack, $needle) {
        if (function_exists('str_ends_with')) {
            return str_ends_with($haystack, $needle);
        }
        return substr($haystack, -strlen($needle)) === $needle;
    }
    
    // KaPlan spezifische Ersetzungen
    private static function spezial($str) {
        $str = str_replace('+', '&dagger;', $str);  // römisches Kreuz
        return $str;
    }
    
    // Links http..... umranden mit <a href=".....">...</a>
    private static function handle_link($str) {
        $pos = stripos($str, 'http', 0);
        if (is_numeric($pos)) {
            $first = '';
            if ($pos > 0) {
                $first = substr($str, 0, $pos);
            }
            $rest = '';
            $pos2 = strpos($str, ' ', $pos);
            if (is_numeric($pos2)) {
                $rest = substr($str, $pos2);
                $link = substr($str, $pos, $pos2 - $pos);
            } else {
                $link = substr($str, $pos);
            }
            $str = self::spezial(self::html($first)) . '<a href="' . $link . '" target="_blank" rel="noopener noreferrer">Internet</a>' . self::spezial(self::html($rest));
        }
        return $str;
    }

    // CSS für Template 2 Tabellenlayout
    private static function get_template2_css() {
        return '<style>
        .kaplan-table-layout {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .kaplan-date-header td {
            background-color: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        .kaplan-time-column {
            width: 80px;
            padding: 4px 8px;
            vertical-align: top;
            white-space: nowrap;
            padding-left: 20px;
        }
        .kaplan-content-column {
            padding: 4px 8px;
            vertical-align: top;
        }
        .kaplan-event-row td {
            border-bottom: 1px solid #eee;
        }
        .kaplan-additional-info {
            font-size: 0.9em;
            color: #666;
        }
        .kaplan-leitung {
            display: block;
            margin-top: 2px;
            font-size: 0.9em;
            color: #666;
        }
        </style>';
    }

    // CSS für Template 3 Tabellenlayout (3 columns)
    private static function get_template3_css() {
        return '<style>
        .kaplan-table-layout {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .kaplan-date-header td {
            background-color: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        .kaplan-time-column {
            width: 80px;
            padding: 4px 8px;
            vertical-align: top;
            white-space: nowrap;
            padding-left: 20px;
        }
        .kaplan-service-info-column {
            padding: 4px 8px;
            vertical-align: top;
            width: 60%;
        }
        .kaplan-raum-column {
            padding: 4px 8px;
            vertical-align: top;
            width: 25%;
        }
        .kaplan-event-row td {
            border-bottom: 1px solid #eee;
        }
        .kaplan-additional-info {
            font-size: 0.9em;
            color: #666;
        }
        .kaplan-service-line {
            margin: 2px 0;
        }
        .kaplan-leitung-info {
            font-size: 0.9em;
            color: #666;
        }
        .kaplan-leitung {
            display: block;
            margin-top: 2px;
            font-size: 0.9em;
            color: #666;
        }
        </style>';
    }

    // In dieser Funktion wird die eigentliche Ausgabe in der Variable $html zusammengesetzt.
    public static function get_html($atts) {
        // Debug mode - check if debug parameter is set
        $debug = isset($atts['debug']) && $atts['debug'] == '1';
        $debug_info = '';
        
        if ($debug) {
            global $kaplan_plugin_version;
            $debug_info .= '-- KaPlan Debug Start --' . "<br>";
            $debug_info .= '-- Plugin Version: ' . $kaplan_plugin_version . ' --' . "<br>";
            $debug_info .= '-- Parameters: ' . json_encode($atts) . ' --' . "<br>";
        }
        
        // Validate required parameters
        if (empty($atts['server'])) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Server-Parameter ist erforderlich</p></div>';
        }
        if (empty($atts['arbeitsgruppe'])) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Arbeitsgruppe-Parameter ist erforderlich</p></div>';
        }
        if (empty($atts['code'])) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Code-Parameter ist erforderlich</p></div>';
        }
        
        $url = self::get_url($atts);
        $options = $atts['options'] ?? '';
        
        if ($debug) {
            $debug_info .= '-- URL: ' . esc_html($url) . ' --' . "<br>";
        }
        
        // Use WordPress HTTP API for better compatibility
        global $kaplan_plugin_version;
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'user-agent' => 'KaPlan WordPress Plugin/' . $kaplan_plugin_version
        ]);
        
        if (is_wp_error($response)) {
            $error_msg = '<div class="kaplan-export"><p style="color: red;">Fehler: Verbindung zum KaPlan-Server fehlgeschlagen. ' . esc_html($response->get_error_message()) . '</p></div>';
            if ($debug) {
                $error_msg .= '-- Debug Error: ' . esc_html($response->get_error_message()) . ' --' . "<br>";
            }
            return $debug_info . $error_msg;
        }
        
        $json = wp_remote_retrieve_body($response);
        if ($debug) {
            file_put_contents(plugin_dir_path(__FILE__) . 'debug.log', $json);
        }
        if (empty($json)) {
            $error_msg = '<div class="kaplan-export"><p style="color: red;">Fehler: Keine Antwort vom KaPlan-Server erhalten.</p></div>';
            if ($debug) {
                $error_msg .= '-- Debug: Empty response body --' . "<br>";
            }
            return $debug_info . $error_msg;
        }
        
        if ($debug) {
            $debug_info .= '-- Response length: ' . strlen($json) . ' --' . "<br>";
        }
        
        $data = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_msg = '<div class="kaplan-export"><p style="color: red;">Fehler: Ungültige Antwort vom KaPlan-Server.</p></div>';
            if ($debug) {
                $error_msg .= '-- Debug JSON Error: ' . json_last_error_msg() . ' --' . "<br>";
            }
            return $debug_info . $error_msg;
        }
        // $data enthält nun alle Termine als Array von Objekten
        
        if ($debug) {
            $debug_info .= '-- Records found: ' . (is_array($data) ? count($data) : 'Not an array') . ' --' . "<br>";
        }
        
        $Template = $atts['template'] ?? '1';  // Ensure default Template=1
        $html = $debug_info;
        
        // Include CSS for Template 2 and 3
        if ($Template == '2') {
            $html .= self::get_template2_css();
        } elseif ($Template == '3') {
            $html .= self::get_template3_css();
        }
        
        $html .= '<div class="kaplan-export">';
        if ($Template == '2' || $Template == '3') {
            $html .= '<table class="kaplan-table-layout">';
        } else {
            $html .= '<dl class="kalender">';
        }

        if (is_null($data)) {
            $html .= 'Es liegen derzeit keine Termine vor.';
        } else {
            $last_date = false;
            $Last_ANID = false;
            foreach ($data as $key=>$termin) {
                // $termin ist ein Objekt mit den Datenfeldern aus der JSON Abfrage
                
                $SkipThisEntry = false;  // Flag for VT mode duplicates
                $FormatLeitung = '' . $atts['leitung'];  // Ausgabeformat Leitung: K / N / VN / V.N / TVN / TV.N (T=Titel V=Vorname N=Nachname K=Kuerzel)
                if ($atts['mode'] == 'A' || $atts['mode'] == 'B') {
                    $Datum = $termin->TE_Datum;
                    $Tagesbez = (isset($termin->Tagesbez) ? $termin->Tagesbez : '');
                    $Uhrzeit = self::add_zero($termin->TE_von_hh) . '.' . self::add_zero($termin->TE_von_mm);
                    $UhrzeitBis = '';
                    $Anlass = $termin->TE_Bez;
                    $FaelltAus = $termin->TE_FaelltAus;
                    $Zusatz = (isset($termin->Zusatz) ? $termin->Zusatz : '');
                    if ($Zusatz != '' && isset($termin->TE_ZusatzNichtOeffentlich) && $termin->TE_ZusatzNichtOeffentlich) {
                        $Zusatz = '';
                    }
                    $Zusatz2 = (isset($termin->TE_Zusatz2) ? $termin->TE_Zusatz2 : '');
                    $Raum = $termin->RA_Bez;
                    if ($FormatLeitung != '') {
                        $PE_Kuerzel = (isset($termin->PE_Kuerzel) ? $termin->PE_Kuerzel : '');
                        $PE_Titel = (isset($termin->PE_Titel) ? $termin->PE_Titel : '');
                        $PE_Vorname = (isset($termin->PE_Vorname) ? $termin->PE_Vorname : '');
                        $PE_Nachname = (isset($termin->PE_Nachname) ? $termin->PE_Nachname : '');
                        $PE_Ordensname = (isset($termin->PE_Ordensname) ? $termin->PE_Ordensname : '');
                        $TE_LeitungGast = (isset($termin->TE_LeitungGast) ? $termin->TE_LeitungGast : '');
                        $PE_Organisation = '';
                        $Liturg = (isset($termin->Liturg) ? $termin->Liturg : '');
                        $Ltg = self::format_leitung($FormatLeitung, $PE_Kuerzel, $PE_Titel, $PE_Vorname, $PE_Nachname, $PE_Ordensname, $TE_LeitungGast, $PE_Organisation, $Liturg);
                    } else {
                        $Ltg = '';
                    }
                    $RegLink = (isset($termin->RegLink) ? $termin->RegLink : '');
                } else if ($atts['mode'] == "VT") {
                    // "Wochentag": "Freitag", 
                    $Datum = $termin->Datum;  // "Datum": "04/26/2024", 
                    $Uhrzeit = $termin->ZeitVon;  // "ZeitVon": "18:00", 
                    $UhrzeitBis = $termin->ZeitBis;  // "ZeitBis": "21:00", 
                    $Anlass = $termin->Anlass;  // "Anlass": "Seniorentreff", 
                    $FaelltAus = false;
                    $Zusatz = (isset($termin->Zusatz) ? $termin->Zusatz : '');    // "Zusatz": "mit Diavortrag von Hr. X", 
                    // "Gebaeude": "Jugendheim Nordstadt", 
                    $Zusatz2 = (isset($termin->Langtext) ? $termin->Langtext : '');  // "Langtext": "Diesmal etwas ganz Besonderes: Kaffee & Kuchen!", 
                    $Raum = $termin->Raum;  // "Raum": "Jugendheim -  Gruppenraum EG 1", 
                    if ($FormatLeitung != '') {
                        $PE_Kuerzel = '';
                        $PE_Titel = (isset($termin->Titel) ? $termin->Titel : '');
                        $PE_Vorname = (isset($termin->PE_Vorname) ? $termin->PE_Vorname : '');
                        $PE_Nachname = (isset($termin->Nachname) ? $termin->Nachname : '');
                        $PE_Ordensname = (isset($termin->Ordensname) ? $termin->Ordensname : '');
                        $PE_Organisation = (isset($termin->Organisation) ? $termin->Organisation : '');
                        $TE_LeitungGast = '';
                        $Liturg = '';
                        $Ltg = self::format_leitung($FormatLeitung, $PE_Kuerzel, $PE_Titel, $PE_Vorname, $PE_Nachname, $PE_Ordensname, $TE_LeitungGast, $PE_Organisation, $Liturg);
                    } else {
                        $Ltg = '';
                    }
                    $RegLink = (isset($termin->RegLink) ? $termin->RegLink : '');
                    
                    // "AN_ID": 3388
                    if ($Last_ANID == $termin->AN_ID) {  // nur 1 Hauptraum anzeigen
                        $SkipThisEntry = true;  // Skip duplicate entries in VT mode
                    } else {
                        $Last_ANID = $termin->AN_ID;
                    }
                }
                
                // Skip this entry if it's a duplicate in VT mode
                if ($SkipThisEntry) {
                    continue;
                }
                
                if ($Template == '1') {   // Standard-Template
                    if ($Datum != $last_date) {
                        // Neues Datum als Überschrift anzeigen
                        $Date_components = explode('/', $Datum);
                        $Date = new DateTime_german();
                        $Date->setDate($Date_components[2], $Date_components[0], $Date_components[1]);
                        if ($key) {
                            $html .= '</dd>';
                        }
                        $html .= '<dt>' . $Date->format('l, d. F Y');
                        if (strpos($options, 'E') !== false) {
                            if ($Tagesbez != '') {
                                $html .= '&nbsp;-&nbsp;' . $Tagesbez;
                            }
                        }
                        $html .= '</dt>';
                        $html .= '<dd>';
                        $last_date = $Datum;
                    }

                    // Uhrzeit
                    $s = $Uhrzeit;
                    if (strpos($options, 'U') !== false) {
                        $s .= '&nbsp;Uhr';
                    }
                    $html .= self::red($s, $FaelltAus);
                    $html .= '&nbsp;&nbsp;';

                    // Gottesdienst / Veranstaltung und Zusatz
                    $html .= '<b>' . self::html($Anlass) . '</b>';
                    if ($Zusatz != '') {
                        $s = self::handle_link($Zusatz);
                        if ($s != '') {
                            $html .= ' ' . $s;
                        }
                    }

                    // Kirche / Raum / Leitung
                    $s = '';
                    if (strpos($options, 'V-') == false) {
                        $s = $Raum;
                    }
                    if ($Ltg != '') {
                        $s .= ', ' . $Ltg;
                    }
                    if ($s != '') {
                        $html .= ' (<i>' . self::html(trim($s, ', ')) . '</i>)';
                    }

                    if (!$FaelltAus) {
                        if ($Zusatz2 != '') {
                            $html .= '<p style="margin: 0">' . self::handle_link($Zusatz2) . '</p>';
                        }
                        // Anmelde-Link
                        if ($RegLink != '') {
                            $html .= '  <a href="' . esc_url($RegLink) . '" target="_blank" rel="noopener noreferrer">Anmeldung</a>';
                        }
                    } else {  // Fällt aus!!
                        $html .= self::red(' f&auml;llt aus!!', true); 
                    }

                    if (!self::str_ends_with($html, '</p>')) {
                        $html .= '<br />';
                    }
                } elseif ($Template == '2') {   // Two-column table template
                    if ($Datum != $last_date) {
                        // Neues Datum als merged header row
                        $Date_components = explode('/', $Datum);
                        $Date = new DateTime_german();
                        $Date->setDate($Date_components[2], $Date_components[0], $Date_components[1]);
                        $html .= '<tr class="kaplan-date-header"><td colspan="2"><strong>' . esc_html($Date->format('l, d. F Y'));
                        if (strpos($options, 'E') !== false) {
                            if ($Tagesbez != '') {
                                $html .= '&nbsp;-&nbsp;' . esc_html($Tagesbez);
                            }
                        }
                        $html .= '</strong></td></tr>';
                        $last_date = $Datum;
                    }

                    // Event row with time column and content column
                    $html .= '<tr class="kaplan-event-row">';
                    
                    // Time column
                    $s = $Uhrzeit;
                    if (strpos($options, 'U') !== false) {
                        $s .= '&nbsp;Uhr';
                    }
                    $html .= '<td class="kaplan-time-column">' . self::red($s, $FaelltAus) . '</td>';
                    
                    // Content column
                    $html .= '<td class="kaplan-content-column">';
                    
                    // Gottesdienst / Veranstaltung und Zusatz
                    $html .= '<strong>' . self::html($Anlass) . '</strong>';
                    if ($Zusatz != '') {
                        $s = self::handle_link($Zusatz);
                        if ($s != '') {
                            $html .= ' ' . $s;
                        }
                    }

                    // Kirche / Raum (without Leitung)
                    $s = '';
                    if (strpos($options, 'V-') == false) {
                        $s = $Raum;
                    }
                    if ($s != '') {
                        $html .= ' (<em>' . self::html(trim($s, ', ')) . '</em>)';
                    }

                    if (!$FaelltAus) {
                        if ($Zusatz2 != '') {
                            $html .= '<br><span class="kaplan-additional-info">' . self::handle_link($Zusatz2) . '</span>';
                        }
                        // Anmelde-Link
                        if ($RegLink != '') {
                            $html .= ' <a href="' . esc_url($RegLink) . '" target="_blank" rel="noopener noreferrer">Anmeldung</a>';
                        }
                    } else {  // Fällt aus!!
                        $html .= self::red(' f&auml;llt aus!!', true); 
                    }
                    
                    // Leitung as last element on its own line
                    if ($Ltg != '') {
                        $html .= '<br><div class="kaplan-leitung">(' . self::html($Ltg) . ')</div>';
                    }
                    
                    $html .= '</td></tr>';
                } elseif ($Template == '3') {   // Three-column table template
                    if ($Datum != $last_date) {
                        // Neues Datum als merged header row spanning 3 columns
                        $Date_components = explode('/', $Datum);
                        $Date = new DateTime_german();
                        $Date->setDate($Date_components[2], $Date_components[0], $Date_components[1]);
                        $html .= '<tr class="kaplan-date-header"><td colspan="3"><strong>' . esc_html($Date->format('l, d. F Y'));
                        if (strpos($options, 'E') !== false) {
                            if ($Tagesbez != '') {
                                $html .= '&nbsp;-&nbsp;' . esc_html($Tagesbez);
                            }
                        }
                        $html .= '</strong></td></tr>';
                        $last_date = $Datum;
                    }

                    // Event row with three columns: time, service info, raum
                    $html .= '<tr class="kaplan-event-row">';

                    // Time column
                    $s = $Uhrzeit;
                    if (strpos($options, 'U') !== false) {
                        $s .= '&nbsp;Uhr';
                    }
                    $html .= '<td class="kaplan-time-column">' . self::red($s, $FaelltAus) . '</td>';

                    // Service info column (Anlass, Zusatz, Leitung)
                    $html .= '<td class="kaplan-service-info-column">';

                    // Anlass (first line)
                    $html .= '<div class="kaplan-service-line"><strong>' . self::html($Anlass) . '</strong></div>';

                    // Zusatz (second line)
                    if ($Zusatz != '') {
                        $s = self::handle_link($Zusatz);
                        if ($s != '') {
                            $html .= '<div class="kaplan-service-line">' . $s . '</div>';
                        }
                    }

                    // Leitung info removed from here - will be added at the end

                    if (!$FaelltAus) {
                        if ($Zusatz2 != '') {
                            $html .= '<div class="kaplan-service-line"><span class="kaplan-additional-info">' . self::handle_link($Zusatz2) . '</span></div>';
                        }
                        // Anmelde-Link
                        if ($RegLink != '') {
                            $html .= '<div class="kaplan-service-line"> <a href="' . esc_url($RegLink) . '" target="_blank" rel="noopener noreferrer">Anmeldung</a></div>';
                        }
                    } else {  // Fällt aus!!
                        $html .= '<div class="kaplan-service-line">' . self::red(' f&auml;llt aus!!', true) . '</div>';
                    }
                    
                    // Leitung as last element on its own line
                    if ($Ltg != '') {
                        $html .= '<div class="kaplan-service-line kaplan-leitung">(' . self::html($Ltg) . ')</div>';
                    }

                    $html .= '</td>';

                    // Raum column
                    $html .= '<td class="kaplan-raum-column">';
                    if (strpos($options, 'V-') == false) {
                        $html .= self::html($Raum);
                    }
                    $html .= '</td></tr>';
                }
            }
            if ($Template == '2' || $Template == '3') {
                // No additional closing needed for table rows
            } else {
                $html .= '<br>&nbsp;</dd>';
            }
        }
        if ($Template == '2' || $Template == '3') {
            $html .= '</table>';
        } else {
            $html .= '</dl>';
        }
        $html .= '</div>';
        
        if ($debug) {
            $html .= '<br>-- KaPlan Debug End --' . "<br>";
        }

        return $html;
    }
}

class DateTime_german extends DateTime {

    /**
     * Format the date/time value as a string
     * @param string $format The format string
     * @return string The formatted date string
     */
    // DO NOT FIX: Keep this method signature as-is to maintain backwards compatibility.
    // The deprecation warning about return type compatibility is intentionally ignored.
    public function format($format) {
        return 
            str_replace(
                array('January','February','March','May','June','July','October','December'),
                array('Januar','Februar','März','Mai','Juni','Juli','Oktober','Dezember'),
            str_replace(
                array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',),
                array('Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag',),
                parent::format($format)));
    }
}

function kaplan_kalender($atts = [], $content = null, $tag = '') {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // override default attributes with user attributes
    $atts = array_merge(
        [
            'mode' => 'B',        // => A=Kirchengruppiert, B=Chronologisch
            'options' => '',      // => keine Optionen
            'secure' => '1',      // => https
            'leitung' => '',      // => ohne Ausgabe Leitung
            'template' => '1',    // => Standard-Ausgabeformat Datum + 1 Zeile Daten
            'server' => '',       // => KaPlan Server
            'arbeitsgruppe' => '', // => Arbeitsgruppe
            'code' => '',         // => Zugriffscode
            'days' => ''          // => Anzahl Tage
        ],  $atts);

    // Normalize smart quotes and sanitize numeric attributes to avoid malformed URLs
    foreach ($atts as $k => $v) {
        if (is_string($v)) {
            $v = str_replace(
                ['"', '"', '„', "'", "'", '‹', '›', '″', '‴', '‚', '‛', '〝', '〞', '〟', '﹁', '﹂', '﹃', '﹄', '＂', '＇'],
                ['"', '"', '"', '\'', '\'', '\'', '\'', '"', '"', '\'', '\'', '"', '"', '"', '\'', '\'', '"', '"', '"', '\''],
                $v
            );
            // Trim surrounding quotes/spaces
            $v = trim($v, " \t\n\r\0\x0B\"'" );
            $atts[$k] = $v;
        }
    }
    // Ensure numeric-only fields are numbers
    foreach (['days','template'] as $nk) {
        if (isset($atts[$nk]) && is_string($atts[$nk])) {
            $atts[$nk] = preg_replace('/\D+/', '', $atts[$nk]);
        }
    }

    return kaplan_kalender::get_html($atts);
}

add_shortcode('ausgabe_kaplan', 'kaplan_kalender');
