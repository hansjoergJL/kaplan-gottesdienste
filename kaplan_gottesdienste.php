<?php
defined('ABSPATH') or die("Please use as described.");

/**
 * Plugin Name:  KaPlan Gottesdienste
 * _Plugin URI: https://www.kaplan-software.de
 * Description: Anzeige aktueller Gottesdienste aus KaPlan
 * Version: 1.6.6
 * Author: Peter Hellerhoff & Hans-Joerg Joedike
 * Author URI: https://www.kaplan-software.de
 * License: GPL2 or newer
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  kaplan-import
 * GitHub Plugin URI: hansjoergJL/kaplan-gottesdienste
 * GitHub Branch: main
 * Requires PHP: 7.4
 * Requires WP: 4.0
 */

// Version 1.6.6  [Jö] 2025-01-08  Enhanced debugging, diagnostic shortcode
// Version 1.6.5  [Jö] 2025-01-08  Debug support, attribute handling fixes
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
        $leitung = $atts['leitung'];  // Ausgabeformat Leitung: K / N / VN / V.N / TVN / TV.N (T=Titel V=Vorname N=Nachname K=Kuerzel)
        $req .= 'get.asp?Arbeitsgruppe=' . $atts['arbeitsgruppe']
            . '&Code=' . $atts['code']
            . '&mode=' . $atts['mode']
            . '&options=L' . $atts['options']
            . '&type=json&req=plugin'
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
    private static function format_leitung($Format, $Kuerzel, $Titel, $Vorname, $Nachname, $Ordensname, $LeitungGast, $Organisation) {
        // Ausgabeformat Leitung: K / N / VN / V.N / TN / TVN / TV.N / O (T=Titel V=Vorname N=Nachname K=Kuerzel, O=Organisation)
        if ('' . $Format == '') {
            return '';
        }
        if ($Format == 'K') {  // Kürzel
            return $Kuerzel;
        }
        if ($Ordensname) {
            return trim($Titel . ' ' . $Ordensname);
        }
        if ($Format == 'N') {  // Nachname
            return $Nachname;
        }
        if ($Format == 'VN') {  // Vorname Nachname
            return trim($Vorname . ' ' . $Nachname);
        }
        if ($Format == 'V.N') {  // V. Nachname
            return trim(substr($Vorname, 0, 1) . '. ' . $Nachname, ' .');
        }
        if ($Format == 'TN') {   // Titel Nachname
            return trim($Titel . ' ' . $Nachname);
        }
        if ($Format == 'TVN') {   // Titel Vorname Nachname
            return trim($Titel . ' ' . $Vorname . ' ' . $Nachname);
        }
        if ($Format == 'TV.N') {  // Titel V. Nachname
            return trim($Titel . ' ' . substr($Vorname . ' ', 0, 1) . '. ' . $Nachname, ' .');
        }
        if ($Format == 'O') {  // Organisation
            if ($Organisation != '') {
                return $Organisation;
            }
            return trim(substr($Vorname . ' ', 0, 1) . '. ' . $Nachname, ' .');
        }
        return $Nachname;
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

    // In dieser Funktion wird die eigentliche Ausgabe in der Variable $html zusammengesetzt.
    public static function get_html($atts) {
        // Debug: Show received attributes (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('KaPlan Plugin - Received attributes: ' . print_r($atts, true));
        }
        
        // Validate required parameters
        if (empty($atts['server'])) {
            $debug_info = '';
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $debug_keys = [];
                foreach ($atts as $key => $value) {
                    $debug_keys[] = $key . '="' . $value . '"';
                }
                $debug_info = ' (Debug: ' . implode(', ', $debug_keys) . ')';
            }
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Server-Parameter ist erforderlich' . $debug_info . '</p></div>';
        }
        if (empty($atts['arbeitsgruppe'])) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Arbeitsgruppe-Parameter ist erforderlich</p></div>';
        }
        if (empty($atts['code'])) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Code-Parameter ist erforderlich</p></div>';
        }
        
        $url = self::get_url($atts);
        $options = $atts['options'] ?? '';

        $html = '';
        
        // Use WordPress HTTP API for better compatibility
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'user-agent' => 'KaPlan WordPress Plugin/1.6.4'
        ]);
        
        if (is_wp_error($response)) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Verbindung zum KaPlan-Server fehlgeschlagen. ' . esc_html($response->get_error_message()) . '</p></div>';
        }
        
        $json = wp_remote_retrieve_body($response);
        if (empty($json)) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Keine Antwort vom KaPlan-Server erhalten.</p></div>';
        }
        
        $data = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '<div class="kaplan-export"><p style="color: red;">Fehler: Ungültige Antwort vom KaPlan-Server.</p></div>';
        }
        // $data enthält nun alle Termine als Array von Objekten
        
        $html .= '<div class="kaplan-export">';
        $html .= '<dl class="kalender">';

        if (is_null($data)) {
            $html .= 'Es liegen derzeit keine Termine vor.';
        } else {
            $last_date = false;
            $Last_ANID = false;
            foreach ($data as $key=>$termin) {
                // $termin ist ein Objekt mit den Datenfeldern aus der JSON Abfrage
                
                $Template = $atts['template'];
                $Leitung = '' . $atts['leitung'];  // Ausgabeformat Leitung: K / N / VN / V.N / TVN / TV.N (T=Titel V=Vorname N=Nachname K=Kuerzel)
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
                    if ($Leitung != '') {
                        $PE_Kuerzel = (isset($termin->PE_Kuerzel) ? $termin->PE_Kuerzel : '');
                        $PE_Titel = (isset($termin->PE_Titel) ? $termin->PE_Titel : '');
                        $PE_Vorname = (isset($termin->PE_Vorname) ? $termin->PE_Vorname : '');
                        $PE_Nachname = (isset($termin->PE_Nachname) ? $termin->PE_Nachname : '');
                        $PE_Ordensname = (isset($termin->PE_Ordensname) ? $termin->PE_Ordensname : '');
                        $PE_LeitungGast = (isset($termin->PE_LeitungGast) ? $termin->PE_LeitungGast : '');
                        $PE_Organisation = '';
                        $Ltg = self::format_leitung($Leitung, $PE_Kuerzel, $PE_Titel, $PE_Vorname, $PE_Nachname, $PE_Ordensname, $PE_LeitungGast, $PE_Organisation);
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
                    if ($Leitung != '') {
                        $PE_Kuerzel = '';
                        $PE_Titel = (isset($termin->Titel) ? $termin->Titel : '');
                        $PE_Vorname = (isset($termin->Vorname) ? $termin->Vorname : '');
                        $PE_Nachname = (isset($termin->Nachname) ? $termin->Nachname : '');
                        $PE_Ordensname = (isset($termin->Ordensname) ? $termin->Ordensname : '');
                        $PE_Organisation = (isset($termin->Organisation) ? $termin->Organisation : '');
                        $PE_LeitungGast = '';
                        $Ltg = self::format_leitung($Leitung, $PE_Kuerzel, $PE_Titel, $PE_Vorname, $PE_Nachname, $PE_Ordensname, $PE_LeitungGast, $PE_Organisation);
                    } else {
                        $Ltg = '';
                    }
                    $RegLink = (isset($termin->RegLink) ? $termin->RegLink : '');
                    
                    // "AN_ID": 3388
                    if ($Last_ANID == $termin->AN_ID) {  // nur 1 Hauptraum anzeigen
                        $Template = "-";
                    } else {
                        $Last_ANID = $termin->AN_ID;
                    }
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
                }
            }
            $html .= '<br>&nbsp;</dd>';
        }
        $html .= '</dl>';
        $html .= '</div>';

        return $html;
    }
}

class DateTime_german extends DateTime {

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
    // Debug: Show raw attributes before processing
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('KaPlan Plugin - Raw shortcode attributes: ' . print_r($atts, true));
    }
    
    // normalize attribute keys, lowercase (WordPress converts them automatically, but let's be explicit)
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    
    // override default attributes with user attributes
    $atts = array_merge(
        [   'mode' => 'B',        // => A=Kirchengruppiert, B=Chronologisch
            'options' => '',      // => keine Optionen
            'secure' => '1',      // => https
            'leitung' => '',      // => ohne Ausgabe Leitung
            'template' => '1',    // => Standard-Ausgabeformat Datum + 1 Zeile Daten
            'server' => '',       // => KaPlan Server
            'arbeitsgruppe' => '', // => Arbeitsgruppe
            'code' => '',         // => Zugriffscode
            'days' => ''          // => Anzahl Tage
        ],  $atts);
    
    // Debug: Show final processed attributes
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('KaPlan Plugin - Final processed attributes: ' . print_r($atts, true));
    }
    
    return kaplan_kalender::get_html($atts);
}

add_shortcode('ausgabe_kaplan', 'kaplan_kalender');

// Debug shortcode for testing (remove in production)
function kaplan_debug_shortcode($atts) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return 'Debug mode disabled';
    }
    
    $output = '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
    $output .= '<h4>KaPlan Debug Output:</h4>';
    $output .= '<strong>Raw attributes:</strong><br>';
    
    if (empty($atts)) {
        $output .= 'No attributes received!';
    } else {
        foreach ($atts as $key => $value) {
            $output .= sprintf('%s = "%s" (length: %d)<br>', 
                esc_html($key), 
                esc_html($value), 
                strlen($value)
            );
        }
    }
    
    $output .= '</div>';
    return $output;
}
add_shortcode('kaplan_debug', 'kaplan_debug_shortcode');
