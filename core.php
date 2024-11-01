<?php
/**
 * Plugin Name: WP Iletimerkezi SMS
 * Plugin URI: @todo
 * Description: A simple plugin to add SMS capability to your website using the Iletimerkezi API. Allows developers to easily extend the settings page and built in functionality.
 * Version: 1.0.0
 * Author: iletimerkezi.com
 * Author URI: https://www.iletimerkezi.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'ILT_CORE_VERSION', '1.0.0' );
define( 'ILT_CORE_OPTION', 'ilt_option' );
define( 'ILT_CORE_OPTION_PAGE', 'iletimerkezi-options' );
define( 'ILT_CORE_SETTING', 'iletimerkezi-options' );
define( 'ILT_LOGS_OPTION', 'ilt_logs' );

if( !defined( 'ILT_TD' ) ) {
	define( 'ILT_TD', 'iletimerkezi-php' );
}

if( !defined( 'ILT_PATH' ) ) {
	define( 'ILT_PATH', plugin_dir_path( __FILE__ ) );
}

require_once( ILT_PATH . 'iletimerkezi-php/autoload.php' );
require_once( ILT_PATH . 'helpers.php' );
require_once( ILT_PATH . 'url-shorten.php' );
if ( is_admin() ) {
	require_once( ILT_PATH . 'admin-pages.php' );
}

class WP_Iletimerkezi_Sms {
	private static $instance;
	private $page_url;

	private function __construct() {
		$this->set_page_url();
	}

	public function init() {
		$options = $this->get_options();

		load_plugin_textdomain( ILT_TD, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		if ( is_admin() ) {
			/** Settings Pages **/
			add_action( 'admin_init', array( $this, 'register_settings' ), 1000 );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 1000 );

		}

		/** User Profile Settings **/
		if( isset( $options['mobile_field'] ) && $options['mobile_field'] ) {
			add_filter( 'user_contactmethods', 'ilt_add_contact_item', 10 );
		}
	}

	/**
	 * Add the Iletimerkezi item to the Settings menu
	 * @return void
	 * @access public
	 */
	public function admin_menu() {
		add_options_page( __( 'Iletimerkezi', ILT_TD ), __( 'Iletimerkezi', ILT_TD ), 'administrator', ILT_CORE_OPTION_PAGE, array( $this, 'display_tabs' ) );
	}

	/**
	 * Determines what tab is being displayed, and executes the display of that tab
	 * @return void
	 * @access public
	 */
	public function display_tabs() {
		$options = $this->get_options();
		$tabs = $this->get_tabs();
		$current = ( !isset( $_GET['tab'] ) ) ? current( array_keys( $tabs ) ) : $_GET['tab'];
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div><h2><?php _e( 'Iletimerkezi SMS for WordPress', ILT_TD ); ?></h2>
			<h2 class="nav-tab-wrapper"><?php
			foreach( $tabs as $tab => $name ) {
				$classes = array( 'nav-tab' );
				if( $tab == $current ) {
					$classes[] = 'nav-tab-active';
				}
				$href = esc_url( add_query_arg( 'tab', $tab, $this->page_url ) );
				echo '<a class="' . implode( ' ', $classes ) . '" href="' . $href . '">' . $name . '</a>';
			}
			?>
			</h2>

			<?php do_action( 'ilt_display_tab', $current, $this->page_url ); ?>
		</div>
		<?php
	}

	/**
	 * Saves the URL of the plugin settings page into the class property
	 * @return void
	 * @access public
	 */
	public function set_page_url() {
		$base = admin_url( 'options-general.php' );
		$this->page_url = add_query_arg( 'page',  ILT_CORE_OPTION_PAGE, $base );
	}

	/**
	 * Returns an array of settings tabs, extensible via a filter
	 * @return void
	 * @access public
	 */
	public function get_tabs() {
		$default_tabs = array(
			'general' => __( 'Settings', ILT_TD ),
			'logs' => __( 'Logs', ILT_TD ),
			'test' => __( 'Test', ILT_TD ),
		);
		return apply_filters( 'ilt_settings_tabs', $default_tabs );
	}

	/**
	 * Register/Whitelist our settings on the settings page, allow extensions and other plugins to hook into this
	 * @return void
	 * @access public
	 */
	public function register_settings() {
		register_setting( ILT_CORE_SETTING, ILT_CORE_OPTION, 'ilt_sanitize_option' );
		do_action( 'ilt_register_additional_settings' );
	}

	/**
	 * Original get_options unifier
	 * @return array List of options
	 * @access public
	 */
	public function get_options() {
		return ilt_get_options();
	}

	/**
	 * Get the singleton instance of our plugin
	 * @return class The Instance
	 * @access public
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new WP_Iletimerkezi_Sms();
		}

		return self::$instance;
	}

	/**
	 * Adds the options to the options table
	 * @return void
	 * @access public
	 */
	public static function plugin_activated() {
		add_option( ILT_CORE_OPTION, ilt_get_defaults() );
		add_option( ILT_LOGS_OPTION, '' );
	}

	/**
	 * Deletes the options to the options table
	 * @return void
	 * @access public
	 */
	public static function plugin_uninstalled() {
		delete_option( ILT_CORE_OPTION );
		delete_option( ILT_LOGS_OPTION );
	}

}

$ilt_instance = WP_Iletimerkezi_Sms::get_instance();
add_action( 'plugins_loaded', array( $ilt_instance, 'init' ) );
register_activation_hook( __FILE__, array( 'WP_Iletimerkezi_Sms', 'plugin_activated' ) );
register_uninstall_hook( __FILE__, array( 'WP_Iletimerkezi_Sms', 'plugin_uninstalled' ) );