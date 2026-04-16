<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Y4YM_Loader    $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * Container for core service objects.
	 *
	 * @since    5.1.0
	 * @access   protected
	 * @var      array    $services    Holds instances of core functionality objects.
	 */
	protected $services = [];

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {

		if ( defined( 'Y4YM_PLUGIN_VERSION' ) ) {
			$this->version = Y4YM_PLUGIN_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'yml-for-yandex-market';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		// ! $this->define_public_hooks(); - отключил
		$this->define_core_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Y4YM_Data. Defines all the plugin data in database.
	 * - Y4YM_Loader. Orchestrates the hooks of the plugin.
	 * - Y4YM_i18n. Defines internationalization functionality.
	 * - Y4YM_Admin. Defines all hooks for the admin area.
	 * - Y4YM_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 * 
	 * @return   void
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-i18n.php';

		/** ----------------------------------- */

		/**
		 * The class responsible for unified options management for the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-options.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'assets/data/y4ym-constants.php';

		/**
		 * These classes are responsible for generating the feed.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/traits/global/traits-y4ym-global-variables.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-y4ym-generation-xml.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-y4ym-write-file.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-yfym-feed-file-meta.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-yfym-feed-updater.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-y4ym-rules-list.php';

		/**
		 * Adding third-party libraries.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/functions-icpd-woocommerce-1-1-1.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/class-icpd-set-admin-notices.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/class-icpd-promo.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/backward-compatibility.php';

		/**
		 * These classes are responsible for updating the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/updates/class-y4ym-plugin-form-activate.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/updates/class-y4ym-plugin-upd.php';

		/**
		 * The class responsible for the feedback form inside the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feedback/class-y4ym-feedback.php';

		/**
		 * The classes are responsible for core the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-y4ym-error-log.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-y4ym-get-closed-tag.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-y4ym-get-open-tag.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-y4ym-get-paired-tag.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-y4ym-registry.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-y4ym-data.php';

		/**
		 * This class manages the CRON tasks of generating the YML feed.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cron/class-yfym-cron-manager.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/woocommerce/class-y4ym-taxonomy.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wordpress/class-y4ym-mime-types.php';

		// Подключение CLI команды
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wp-cli/class-y4ym-wp-cli-command.php';
		}

		/** ----------------------------------- */

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-y4ym-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-y4ym-public.php';

		$this->loader = new Y4YM_Loader();

		$this->services['cron_manager'] = new Y4YM_Cron_Manager();
		$this->services['feed_updater'] = new Y4YM_Feed_Updater();
		$this->services['taxonomy'] = new Y4YM_Taxonomy();
		$this->services['mime_types'] = new Y4YM_Mime_Types();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Y4YM_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 * 
	 * @return   void
	 */
	private function set_locale() {

		$plugin_i18n = new Y4YM_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * 
	 * @return   void
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Y4YM_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_admin->init_hooks( $this->loader );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * 
	 * @return   void
	 */
	private function define_public_hooks() {

		$plugin_public = new Y4YM_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public->init_hooks( $this->loader );

	}

	/**
	 * Register hooks that are related to core functionality, but not tied 
	 * to admin or public-facing logic.
	 * 
	 * @since    0.1.0
	 * @access   private
	 * 
	 * @return   void
	 */
	private function define_core_hooks() {

		$cron_manager = $this->services['cron_manager'];
		$cron_manager->init_hooks( $this->loader );

		$feed_updater = $this->services['feed_updater'];
		$taxonomy = $this->services['taxonomy'];
		$mime_types = $this->services['mime_types'];

		// слушаем изменение количества товаров в заказе
		$this->loader->add_action( 'woocommerce_reduce_order_item_stock', $feed_updater, 'check_update_feed_stock_change', 50, 3 );

		// добавляем новую таксономию для коллекций
		$this->loader->add_action( 'init', $taxonomy, 'add_new_taxonomies' );
		$this->loader->add_action( 'yfym_collection_add_form_fields', $taxonomy, 'add_meta_product_cat' );
		$this->loader->add_action( 'yfym_collection_edit_form_fields', $taxonomy, 'edit_meta_product_cat' );
		$this->loader->add_action( 'edited_yfym_collection', $taxonomy, 'save_meta_product_cat' );
		$this->loader->add_action( 'create_yfym_collection', $taxonomy, 'save_meta_product_cat' );

		// Разрешим загрузку xml и csv файлов
		$this->loader->add_action( 'upload_mimes', $mime_types, 'add_mime_types' );

	}

	/**
	 * Registers all plugin widgets with WordPress.
	 *
	 * This method is called on the 'widgets_init' action hook and registers
	 * each widget class so it can be used in theme sidebars.
	 *
	 * If additional widgets are created in the future, add them here using:
	 *     register_widget( 'Your_Widget_Class_Name' );
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function register_widgets() {

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 * 
	 * @return   void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since    0.1.0
	 * 
	 * @return   string     The name of the plugin. For example: `yml-for-yandex-market`.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since    0.1.0
	 * 
	 * @return   Y4YM_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since    0.1.0
	 * 
	 * @return  string    The version number of the plugin. For example: `0.1.0`.
	 */
	public function get_version() {
		return $this->version;
	}

}
