<?php
/**
 * The main Events Virtual plugin service provider: it bootstraps the plugin code.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual
 */

namespace Tribe\Events\Virtual;

use Tribe\Events\Virtual\Export\Export_Provider;
use Tribe__Autoloader;

/**
 * Class Plugin
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual
 */
class Plugin extends \tad_DI52_ServiceProvider {
	/**
	 * Stores the version for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '1.11.0';

	/**
	 * Stores the base slug for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SLUG = 'events-virtual';

	/**
	 * Stores the base slug for the extension.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const FILE = EVENTS_VIRTUAL_FILE;

	/**
	 * The slug that will be used to identify HTTP requests the plugin should handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $request_slug = 'ev_request';

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * Setup the Extension's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 */
	public function register() {
		tribe_events_virtual_load_text_domain();
		// Set up the plugin provider properties.
		$this->plugin_path = trailingslashit( dirname( static::FILE ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		$this->register_autoloader();

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'events-virtual', $this );
		$this->container->singleton( 'events-virtual.plugin', $this );
		$this->container->register( PUE::class );

		$this->load_template_tags();

		if ( ! $this->check_plugin_dependencies() ) {
			// If the plugin dependency manifest is not met, then bail and stop here.
			return;
		}

		// Start binds.

		$this->container->singleton( Template::class, Template::class );
		$this->container->singleton( Event_Meta::class, Event_Meta::class );
		$this->container->singleton( Metabox::class, Metabox::class );
		$this->container->singleton( JSON_LD::class, JSON_LD::class );
		$this->container->singleton( Template_Modifications::class, Template_Modifications::class );

		// End binds.

		$this->container->register( Hooks::class );
		$this->container->register( Assets::class );
		$this->container->register( Compatibility::class );
		$this->container->register( Export_Provider::class );
		$this->container->register( Rewrite\Rewrite_Provider::class );
		$this->container->register( Context\Context_Provider::class );
		$this->container->register( ORM\ORM_Provider::class );
		$this->container->register( Views\V2\Views_Provider::class );
		$this->container->register( Editor\Provider::class );
	}

	/**
	 * Checks whether the plugin dependency manifest is satisfied or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the plugin dependency manifest is satisfied or not.
	 */
	protected function check_plugin_dependencies() {
		$this->register_plugin_dependencies();

		if ( ! tribe_check_plugin( static::class ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Registers the plugin and dependency manifest among those managed by Tribe Common.
	 *
	 * @since 1.0.0
	 */
	protected function register_plugin_dependencies() {
		$plugin_register = new Plugin_Register();
		$plugin_register->register_plugin();

		$this->container->singleton( Plugin_Register::class, $plugin_register );
		$this->container->singleton( 'events-virtual.plugin_register', $plugin_register );
	}

	/**
	 * Register the Tribe Autoloader in Virtual Events.
	 *
	 * @since 1.0.0
	 */
	protected function register_autoloader() {
		$autoloader = Tribe__Autoloader::instance();

		// For namespaced classes.
		$autoloader->register_prefix(
			'\\Tribe\\Events\\Virtual\\',
			$this->plugin_path . '/src/Tribe',
			'events-virtual'
		);
	}

	/**
	 * Returns whether meeting and conference support is enabled in the plugin or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether meeting and conference support is enabled in the plugin or not.
	 */
	public static function meetings_enabled() {
		// Allow meetings to be disabled using a constant.
		$enabled = defined( 'EVENTS_VIRTUAL_MEETINGS_ENABLE' )
			? EVENTS_VIRTUAL_MEETINGS_ENABLE
			: true;

		/**
		 * Filters whether to enable meetings support in the plugin or not.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $enable_meetings Whether meetings and conference support is enabled in the plugin or not.
		 */
		return apply_filters( 'tribe_events_virtual_meetings_enabled', $enabled );
	}

	/**
	 * Adds template tags once the plugin has loaded.
	 *
	 * @since 1.0.0
	 */
	protected function load_template_tags() {
		require_once $this->plugin_path . 'src/functions/template-tags/general.php';
	}
}
