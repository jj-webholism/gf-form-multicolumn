<?php
/**
 * Main Plugin class file.
 *
 * @package WH\GF\Multicolumn
 */

namespace {
	// Fallback stub for unit testing environments where Gravity Forms is not loaded.
	if ( ! class_exists( 'GFAddOn' ) ) {
		/**
		 * Fallback GFAddOn stub.
		 */
		class GFAddOn {


		}
	}
}

namespace WH\GF\Multicolumn {

	use WH\GF\Multicolumn\Admin\Admin;
	use WH\GF\Multicolumn\Frontend\Frontend;

	if ( class_exists( '\GFForms' ) ) {
		\GFForms::include_addon_framework();
	}

	if ( ! class_exists( 'WH\GF\Multicolumn\Plugin' ) ) {

		/**
		 * Main plugin controller class extending Gravity Forms AddOn framework.
		 */
		class Plugin extends \GFAddOn {



			/**
			 * Plugin name identifier.
			 *
			 * @var string
			 */
			protected $plugin_name = 'gf-form-multicolumn';

			/**
			 * Current plugin version.
			 *
			 * @var string
			 */
			protected $_version = '4.0.9';

			/**
			 * Minimum required Gravity Forms version.
			 *
			 * @var string
			 */
			protected $_min_gravityforms_version = '1.9';

			/**
			 * Gravity Forms AddOn slug.
			 *
			 * @var string
			 */
			protected $_slug = 'gfmc';

			/**
			 * Relative path to main plugin file.
			 *
			 * @var string
			 */
			protected $_path = 'gf-form-multicolumn/gf-form-multicolumn.php';

			/**
			 * Full path to main plugin file.
			 *
			 * @var string
			 */
			protected $_full_path = __FILE__;

			/**
			 * Plugin instance container.
			 *
			 * @var Plugin|null
			 */
			private static $_instance = null;

			/**
			 * Retrieves the single instance of the class.
			 *
			 * @return Plugin Singleton instance.
			 */
			public static function get_instance() {
				if ( null === self::$_instance ) {
					self::$_instance = new self();
				}

				return self::$_instance;
			}

			/**
			 * Minimum PHP version requirements.
			 *
			 * @return array Required specifications.
			 */
			public function minimum_requirements() {
				return array(
					'php' => array(
						'version' => '7.0',
					),
				);
			}

			/**
			 * Pre-initialization hook handler.
			 *
			 * @return void
			 */
			public function pre_init() {
				parent::pre_init();

				add_action( 'plugins_loaded', array( $this, 'set_locale' ) );
			}

			/**
			 * Initialization hook handler.
			 *
			 * @return void
			 */
			public function init() {
				parent::init();

				add_filter(
					'gform_preview_styles',
					array( $this, 'enqueue_admin_preview_scripts' ),
					10,
					1
				);

				$public = new Frontend(
					$this->get_plugin_name(),
					$this->get_version()
				);

				add_filter(
					'gform_enable_legacy_markup',
					array( $public, 'filter_legacy_markup' ),
					10,
					2
				);
			}

			/**
			 * Initializes backend-only features.
			 *
			 * @return void
			 */
			public function init_admin() {
				if ( is_admin() ) {
					new Admin(
						$this->get_plugin_name(),
						$this->_version
					);

					parent::init_admin();
				}
			}

			/**
			 * Initializes frontend functionality.
			 *
			 * @return void
			 */
			public function init_frontend() {
				parent::init_frontend();

				$public = new Frontend(
					$this->get_plugin_name(),
					$this->get_version()
				);

				add_action(
					'gform_enqueue_scripts',
					array( $public, 'dequeue_selected_scripts' ),
					11
				);

				$public->display();
			}

			/**
			 * Initializes AJAX handlers.
			 *
			 * @return void
			 */
			public function init_ajax() {
				parent::init_ajax();

				$ajax_form = new Frontend(
					$this->get_plugin_name(),
					$this->get_version()
				);

				$ajax_form->display();
			}

			/**
			 * Registers frontend and backend JavaScript files.
			 *
			 * @return array Array of script specifications.
			 */
			public function scripts() {
				$scripts[] = array(
					'handle'    => 'gf-form-multicolumn-public',
					'src'       => plugins_url( '../assets/js/gf-form-multicolumn.min.js', __FILE__ ),
					'version'   => $this->get_version(),
					'deps'      => array( 'jquery', 'gform_conditional_logic' ),
					'in_footer' => true,
					'callback'  => array( $this, 'dequeue_if_in_admin_area' ),
					'enqueue'   => array(
						array(
							'field_types' => array(
								'column_start',
								'column_break',
								'column_end',
							),
						),
					),
				);

				$scripts[] = array(
					'handle'  => 'gfmc_scripts_admin',
					'src'     => plugins_url( '../assets/js/gf-form-multicolumn-admin.min.js', __FILE__ ),
					'version' => $this->get_version(),
					'strings' => array(
						'tooManyColumnStarts' => __( ' too many Row Starts to Row Ends. Please review and remove the excess Row Starts.', 'gf-form-multicolumn' ),
						'tooManyColumnEnds'   => __( ' too many Row Ends to Row Starts. Please review and remove the excess Row Ends.', 'gf-form-multicolumn' ),
					),
					'enqueue' => array(
						array(
							'admin_page' => array(
								'form_editor',
								'form_settings',
							),
						),
					),
				);

				return array_merge( parent::scripts(), $scripts );
			}

			/**
			 * Prevents public scripts from enqueueing inside WordPress Admin.
			 *
			 * @return bool True after dequeuing.
			 */
			public function dequeue_if_in_admin_area() {
				if ( is_admin() ) {
					wp_dequeue_script( 'gf-form-multicolumn-public' );
					wp_deregister_script( 'gf-form-multicolumn-public' );
				}

				return true;
			}

			/**
			 * Registers frontend and backend CSS stylesheets.
			 *
			 * @return array Array of style specifications.
			 */
			public function styles() {
				$styles = array(
					array(
						'handle'  => 'gf-form-multicolumn',
						'src'     => plugins_url( '../assets/css/gf-form-multicolumn.min.css', __FILE__ ),
						'version' => $this->get_version(),
						'enqueue' => array(
							array(
								'field_types' => array(
									'column_start',
									'column_break',
									'column_end',
								),
							),
						),
					),
				);

				return array_merge( parent::styles(), $styles );
			}

			/**
			 * Enqueue preview styles.
			 *
			 * @param array $styles Existing styles.
			 * @return array Modified styles.
			 */
			public function enqueue_admin_preview_scripts( $styles = array() ): array {
				$styles[] = 'preview_stylesheet';
				return $styles;
			}

			/**
			 * Loads internationalization domain.
			 *
			 * @return void
			 */
			public function set_locale() {
				$plugin_i18n = new I18n();
				$plugin_i18n->load_plugin_textdomain();
			}

			/**
			 * Gets the plugin slug name.
			 *
			 * @return string Plugin name.
			 */
			public function get_plugin_name() {
				return $this->plugin_name;
			}

			/**
			 * Gets current plugin version string.
			 *
			 * @return string Version string.
			 */
			public function get_version() {
				return $this->_version;
			}
		}
	}
}
