<?php
/*
Plugin Name: WP Netscope
Plugin URI: http://www.widgilabs.com/
Description: Netscope is a web-based analytics suite. This plugin installs the necessary tags on your WordPress site.
Version: 1.0
Author: nunomorgadinho
Author URI: http://www.widgilabs.com/
License: GPL2
*/

/**
 * WP_Netscope class
 *
 */
if ( !class_exists( 'wp_netscope' ) ) {

	class wp_netscope {

		// Holds the api key value
		var $api_key;

		// instance
		static $instance;

		/**
		 * Add init hooks on class construction
		 */
		function wp_netscope() {

			// allow this instance to be called from outside the class
			self::$instance = $this;

			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		}

		/**
		 * Init callback 
		 * 
		 * Load translations and add iframe code, if present
		 *
		 */
		function init() {

			load_plugin_textdomain( 'wp-netscope', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			$wp_netscope_vars = get_option( 'wp_netscope_vars' );

			if ( !empty( $wp_netscope_vars['apikey'] ) ) {
				$this->api_key = $wp_netscope_vars['apikey'];
				add_action( 'wp_footer', array( $this, 'add_code' ), 999 );
			}
		}


		/**
		 * Admin init callback
		 * 
		 * Register options, add settings page
		 *
		 */
		function admin_init() {

			register_setting(
				'wp_netscope_vars_group',
				'wp_netscope_vars',
				array( $this, 'validate_form' ) );

			add_settings_section(
				'wp_netscope_vars_id',
				__( 'Instructions', 'wp-netscope' ),
				array( $this, 'overview' ),
				'WP Netscope Settings' );

			add_settings_field(
				'wpcp-apikey',
				__( 'Unique web identifier:', 'wp-netscope' ),
				array( $this, 'render_field' ),
				'WP Netscope Settings',
				'wp_netscope_vars_id' );
		}

		/**
		 * Build the menu and settings page callback
		 * 
		 */
		function admin_menu() {

			if ( !function_exists( 'current_user_can' ) || !current_user_can( 'manage_options' ) )
				return;

			if ( function_exists( 'add_options_page' ) )
				add_options_page( __( 'WP Netscope Settings', 'wp-netscope' ), __( 'WP Netscope', 'wp-crpwdprocess' ), 'manage_options', 'wp_netscope', array( $this, 'show_form' ) );
		}

		/**
		 * Show instructions
		 * 
		 */
		function overview() {

			printf( __( '<p>In order for this plugin to function, you need to have a valid netscope identifier. Vist %s for more info.</p>', 'wp-netscope' ), 'http://netscope.marktest.pt/' );

		}

		/**
		 * Render options field
		 * 
		 */ 
		function render_field() {
			$wp_netscope_vars = get_option( 'wp_netscope_vars' );

			?>
        	<input id="wpcp-apikey" name="wp_netscope_vars[apikey]" class="regular-text" value="<?php echo $wp_netscope_vars['apikey']; ?>" />
            <?php
		}

		/**
		 * Validate user options
		 * 
		 */ 
		function validate_form( $input ) {

			$wp_netscope_vars = get_option( 'wp_netscope_vars' );

			if ( isset( $input['apikey'] ) ) {
				// Strip all HTML and PHP tags and properly handle quoted strings
				$wp_netscope_vars['apikey'] = strip_tags( stripslashes( $input['apikey'] ) );

			}
			return $wp_netscope_vars;
		}

		/**
		 * Render options page
		 * 
		 */ 
		function show_form() {
			$wp_netscope_vars = get_option( 'wp_netscope_vars' );

?>
                                <div class="wrap">
                                        <?php screen_icon( "options-general" ); ?>
                                        <h2><?php _e( 'WP Netscope Settings', 'wp-netscope' ); ?></h2>
                                        <form action="options.php" method="post">
                                                <?php settings_fields( 'wp_netscope_vars_group' ); ?>
                                                <?php do_settings_sections( 'WP Netscope Settings' ); ?>
                                                <p class="submit">
                                                        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-netscope' ); ?>" />
                                                </p>
                                        </form>
                                </div>
                        <?php
		}

		/**
		 * Add iframe code to the site's footer
		 * 
		 */ 
		function add_code() {
			$path = basename(get_permalink());
			
			echo "<!— netScope v3 – Begin of gPrism tag -->\n";
			echo "<script type=\"text/javascript\">\n";
			echo "<!--//--><![CDATA[//><!—\n";
			echo "var pp_gemius_identifier = '" . sanitize_text_field( $this->api_key ) . "';\n";

			if (is_front_page())
				echo "var pp_gemius_extraparameters = new Array('gA=Homepage_do_site');\n";
			else 
				echo "var pp_gemius_extraparameters = new Array('gA=" . $path  . "');\n";
			
			echo "var pp_gemius_event = pp_gemius_event || function() {var x = window.gemius_sevents = window.gemius_sevents || []; x[x.length]=arguments;};\n";
			echo "( function(d,t) { var ex; try { var gt=d.createElement(t),s=d.getElementsByTagName(t)[0],l='http'+((location.protocol=='https:')?'s://secure':'://data'); gt.async='true'; gt.src=l+'.netscope.marktest.pt/netscope-gemius.js'; s.parentNode.appendChild(gt);} catch (ex){}}(document,'script'));\n";
			echo "//--><!]]>\n";
			echo "</script>\n";
			echo "<!—End netScope v3 / www.net.marktest.pt / (C) Gemius/Marktest 2013 -->\n";
		}


	}

	new wp_netscope();
}

