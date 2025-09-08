<?php
defined('ABSPATH') or die('Access denied.');

/**
 * KaPlan Plugin GitHub Updater
 * 
 * Provides automatic plugin updates from GitHub releases without requiring
 * the GitHub Updater plugin. Users get updates directly in their WordPress admin.
 * 
 * @package KaPlan_Gottesdienste
 * @version 1.0.0
 * @author Hans-Joerg Joedike
 */
class KaPlan_GitHub_Updater
{
    /**
     * Plugin file path
     * @var string
     */
    private $plugin_file;
    
    /**
     * Plugin slug (folder-name/file-name.php)
     * @var string
     */
    private $plugin_slug;
    
    /**
     * Plugin basename (folder-name/file-name.php)
     * @var string
     */
    private $plugin_basename;

    /**
     * Plugin directory name (folder-name)
     * @var string
     */
    private $plugin_dirname;
    
    /**
     * Current plugin version
     * @var string
     */
    private $version;
    
    /**
     * GitHub repository (username/repo-name)
     * @var string
     */
    private $github_repo;
    
    /**
     * GitHub API token (optional, for private repos)
     * @var string
     */
    private $github_token;
    
    /**
     * Transient key for caching update data
     * @var string
     */
    private $transient_key;
    
    /**
     * Initialize the updater
     *
     * @param string $plugin_file Full path to main plugin file
     * @param string $version Current plugin version
     * @param string $github_repo GitHub repository (username/repo-name)
     * @param string $github_token Optional GitHub API token
     */
    public function __construct($plugin_file, $version, $github_repo, $github_token = '')
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_basename = plugin_basename($plugin_file);
        $this->plugin_dirname = dirname($this->plugin_basename);
        // Use file name without extension as slug (more stable for plugin_information)
        $this->plugin_slug = basename($this->plugin_basename, '.php');
        $this->version = $version;
        $this->github_repo = $github_repo;
        $this->github_token = $github_token;
        $this->transient_key = 'kaplan_updater_' . md5($this->plugin_basename);
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        // Hook into WordPress update checks
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
        
        // Provide plugin information for the update process
        add_filter('plugins_api', array($this, 'plugin_api_call'), 10, 3);
        
        // Ensure extracted directory matches installed directory name
        add_filter('upgrader_source_selection', array($this, 'ensure_correct_directory'), 10, 4);
        
        // Clean up after plugin update
        add_action('upgrader_process_complete', array($this, 'after_update'), 10, 2);
        
        // Add settings link for manual update check (admin only)
        if (is_admin()) {
            add_filter('plugin_action_links_' . $this->plugin_basename, array($this, 'add_action_links'));
            add_action('admin_notices', array($this, 'show_update_notices'));
        }
    }
    
    /**
     * Check for updates by comparing with GitHub releases
     *
     * @param object $transient WordPress update transient
     * @return object Modified transient
     */
    public function check_for_updates($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Check if our plugin is in the checked list
        if (!isset($transient->checked[$this->plugin_basename])) {
            return $transient;
        }
        
        // Get remote version info
        $remote_version_data = $this->get_remote_version_info();
        
        if ($remote_version_data && version_compare($this->version, $remote_version_data['new_version'], '<')) {
            // Update is available
            $transient->response[$this->plugin_basename] = (object) array(
                'slug' => $this->plugin_slug,
                'new_version' => $remote_version_data['new_version'],
                'package' => $remote_version_data['package'],
                'url' => $remote_version_data['url'],
                'tested' => $remote_version_data['tested'],
                'requires' => $remote_version_data['requires'],
                'requires_php' => $remote_version_data['requires_php']
            );
        }
        
        return $transient;
    }
    
    /**
     * Handle plugin API calls for update information
     *
     * @param false|object|array $result The result object or array
     * @param string $action The type of information being requested
     * @param object $args Plugin API arguments
     * @return false|object|array Modified result
     */
    public function plugin_api_call($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }
        
        if (!isset($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }
        
        $remote_version_data = $this->get_remote_version_info();
        
        if (!$remote_version_data) {
            return $result;
        }
        
        return (object) array(
            'name' => $remote_version_data['name'],
            'slug' => $this->plugin_slug,
            'version' => $remote_version_data['new_version'],
            'author' => $remote_version_data['author'],
            'author_profile' => $remote_version_data['author_profile'],
            'homepage' => $remote_version_data['url'],
            'requires' => $remote_version_data['requires'],
            'requires_php' => $remote_version_data['requires_php'],
            'tested' => $remote_version_data['tested'],
            'downloaded' => $remote_version_data['downloaded'],
            'last_updated' => $remote_version_data['last_updated'],
            'sections' => array(
                'description' => $remote_version_data['description'],
                'changelog' => $remote_version_data['changelog']
            ),
            'download_link' => $remote_version_data['package']
        );
    }
    
    /**
     * Get version information from GitHub releases
     *
     * @return array|false Version data or false on failure
     */
    private function get_remote_version_info()
    {
        // Check cache first
        $cached_data = get_transient($this->transient_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $api_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        
        $request_args = array(
            'timeout' => 15,
            'user-agent' => 'KaPlan WordPress Plugin Updater/1.0.0'
        );
        
        // Add authorization header if token is provided
        if (!empty($this->github_token)) {
            $request_args['headers'] = array(
                'Authorization' => 'token ' . $this->github_token
            );
        }
        
        $response = wp_remote_get($api_url, $request_args);
        
        if (is_wp_error($response)) {
            error_log('KaPlan Updater: Failed to fetch release info - ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $release_data = json_decode($body, true);
        
        if (!$release_data || !isset($release_data['tag_name'])) {
            error_log('KaPlan Updater: Invalid release data received');
            return false;
        }
        
        // Find the .zip asset
        $package_url = '';
        if (isset($release_data['assets']) && is_array($release_data['assets'])) {
            foreach ($release_data['assets'] as $asset) {
                if (isset($asset['name']) && substr($asset['name'], -4) === '.zip') {
                    $package_url = $asset['browser_download_url'];
                    break;
                }
            }
        }
        
        // Fallback to zipball_url if no .zip asset found
        if (empty($package_url) && isset($release_data['zipball_url'])) {
            $package_url = $release_data['zipball_url'];
        }
        
        if (empty($package_url)) {
            error_log('KaPlan Updater: No download package found in release');
            return false;
        }
        
        // Parse version from tag (remove 'v' prefix if present)
        $new_version = ltrim($release_data['tag_name'], 'v');
        
        $version_data = array(
            'new_version' => $new_version,
            'package' => $package_url,
            'url' => isset($release_data['html_url']) ? $release_data['html_url'] : "https://github.com/{$this->github_repo}",
            'name' => 'KaPlan Gottesdienste',
            'author' => 'Peter Hellerhoff & Hans-Joerg Joedike',
            'author_profile' => 'https://www.kaplan-software.de',
            'requires' => '4.0',
            'requires_php' => '7.4',
            'tested' => '6.4',
            'downloaded' => 0,
            'last_updated' => isset($release_data['published_at']) ? $release_data['published_at'] : date('Y-m-d H:i:s'),
            'description' => 'Anzeige aktueller Gottesdienste aus KaPlan',
            'changelog' => $this->format_changelog($release_data)
        );
        
        // Cache the result for 12 hours
        set_transient($this->transient_key, $version_data, 12 * HOUR_IN_SECONDS);
        
        return $version_data;
    }
    
    /**
     * Format changelog from GitHub release data
     *
     * @param array $release_data GitHub release data
     * @return string Formatted changelog
     */
    private function format_changelog($release_data)
    {
        $changelog = '<h4>Version ' . ltrim($release_data['tag_name'], 'v') . '</h4>';
        
        if (!empty($release_data['body'])) {
            // Convert markdown to basic HTML
            $body = $release_data['body'];
            $body = preg_replace('/^## (.+)$/m', '<h4>$1</h4>', $body);
            $body = preg_replace('/^### (.+)$/m', '<h5>$1</h5>', $body);
            $body = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $body);
            $body = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $body);
            $body = nl2br($body);
            $changelog .= $body;
        } else {
            $changelog .= '<p>Siehe <a href="' . $release_data['html_url'] . '" target="_blank">GitHub Release</a> für Details.</p>';
        }
        
        return $changelog;
    }
    
    /**
     * Ensure the extracted source directory is renamed to match the installed plugin directory.
     * This avoids ending up with duplicate plugin folders when the ZIP name differs.
     *
     * @param string $source Source path of the update being installed
     * @param string $remote_source Remote source path
     * @param WP_Upgrader $upgrader Upgrader instance
     * @param array $hook_extra Extra information about the upgrade
     * @return string Renamed source path
     */
    public function ensure_correct_directory($source, $remote_source, $upgrader, $hook_extra)
    {
        if (
            empty($hook_extra['type']) || $hook_extra['type'] !== 'plugin' ||
            (isset($hook_extra['plugin']) && $hook_extra['plugin'] !== $this->plugin_basename)
        ) {
            return $source;
        }

        global $wp_filesystem;
        if (!$wp_filesystem) {
            return $source;
        }

        $desired_path = trailingslashit(dirname($source)) . $this->plugin_dirname;
        $current_path = untrailingslashit($source);

        if ($current_path === $desired_path) {
            return $source;
        }

        // If desired path already exists, remove it to allow rename
        if ($wp_filesystem->exists($desired_path)) {
            $wp_filesystem->delete($desired_path, true);
        }

        // Rename extracted directory
        if ($wp_filesystem->move($current_path, $desired_path, true)) {
            return trailingslashit($desired_path);
        }

        return $source; // Fallback if move failed
    }

    /**
     * Clean up after plugin update
     *
     * @param WP_Upgrader $upgrader_object
     * @param array $hook_extra
     */
    public function after_update($upgrader_object, $hook_extra)
    {
        if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === $this->plugin_basename) {
            // Clear our cache after successful update
            delete_transient($this->transient_key);
        }
    }
    
    /**
     * Show admin notices for update status
     */
    public function show_update_notices()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'plugins') {
            return;
        }
        
        // Show notice if update check was manually triggered
        if (isset($_GET['kaplan_update_checked'])) {
            $version_data = $this->get_remote_version_info();
            if ($version_data && version_compare($this->version, $version_data['new_version'], '<')) {
                echo '<div class="notice notice-warning"><p>';
                echo sprintf(
                    'KaPlan Plugin: Update verfügbar! Version %s ist verfügbar (aktuell: %s).',
                    esc_html($version_data['new_version']),
                    esc_html($this->version)
                );
                echo '</p></div>';
            } elseif ($version_data) {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo 'KaPlan Plugin: Sie haben die neueste Version (' . esc_html($this->version) . ').';
                echo '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>';
                echo 'KaPlan Plugin: Konnte nicht nach Updates suchen. Bitte versuchen Sie es später erneut.';
                echo '</p></div>';
            }
        }
    }

    /**
     * Add action links to plugin list
     *
     * @param array $links Existing action links
     * @return array Modified action links
     */
    public function add_action_links($links)
    {
        $check_update_link = sprintf(
            '<a href="%s" title="Manuell nach neuen Versionen suchen">%s</a>',
            add_query_arg(array(
                'kaplan_check_update' => '1',
                '_wpnonce' => wp_create_nonce('kaplan_check_update')
            ), admin_url('plugins.php')),
            'Nach Updates suchen'
        );
        
        // Handle manual update check
        if (isset($_GET['kaplan_check_update']) && wp_verify_nonce($_GET['_wpnonce'], 'kaplan_check_update')) {
            $this->force_check_for_updates();
            wp_safe_redirect(add_query_arg('kaplan_update_checked', '1', admin_url('plugins.php')));
            exit;
        }
        
        array_unshift($links, $check_update_link);
        
        return $links;
    }
    
    /**
     * Force check for updates (clears cache)
     */
    public function force_check_for_updates()
    {
        delete_transient($this->transient_key);
        delete_site_transient('update_plugins');
        
        // Trigger update check
        wp_update_plugins();
        
        return $this->get_remote_version_info();
    }
    
    /**
     * Get current plugin info
     *
     * @return array Plugin information
     */
    public function get_plugin_info()
    {
        return array(
            'file' => $this->plugin_file,
            'basename' => $this->plugin_basename,
            'slug' => $this->plugin_slug,
            'version' => $this->version,
            'github_repo' => $this->github_repo
        );
    }
}
