<?php
/**
 * Event Single Elementor Widget.
 *
 * @since   5.4.0
 *
 * @package Tribe\Events\Pro\Integrations\Elementor\Widgets
 */

namespace Tribe\Events\Pro\Integrations\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Tribe\Events\Views\V2\Assets;
use Tribe\Events\Views\V2\Template_Bootstrap;
use Tribe__Utils__Array as Arr;

class Widget_Event_Single_Legacy extends Widget_Abstract {
	use Traits\Event_Query;

	/**
	 * {@inheritdoc}
	 */
	protected static $widget_slug = 'event_single_legacy';

	/**
	 * {@inheritdoc}
	 */
	protected $widget_icon = 'eicon-single-post';

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		$this->widget_title = __( 'Event', 'tribe-events-calendar-pro' );
	}

	/**
	 * Render widget output.
	 *
	 * @since 5.4.0
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$event_query_settings = $this->get_event_query_settings( $settings );
		$event_query_settings = $this->set_id_from_repository_if_unset( $event_query_settings );

		/** @var Template_Bootstrap $bootstrap */
		$bootstrap = tribe( Template_Bootstrap::class );

		global $post, $wp_query;
		$backup_query = $wp_query;

		$repository = $this->build_event_repository( $event_query_settings );
		$repository->per_page( 1 );
		$posts      = $repository->all();

		if ( ! $posts ) {
			return;
		}

		$wp_query = $repository->get_query_for_posts( $posts );
		$post     = $posts[0];
		setup_postdata( $post );

		$selector = $this->get_unique_selector();

		add_filter( 'tribe_events_virtual_should_show_control_markers', '__return_true' );
		add_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_true' );
		$html = '<div class="single-tribe_events">' . $bootstrap->get_view_html() . '</div>';
		remove_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_true' );
		remove_filter( 'tribe_events_virtual_should_show_control_markers', '__return_true' );

		$wp_query = $backup_query;

		// We need to keep resetting since inside of the single V1 view we call `the_post()`.
		wp_reset_postdata();

		$this->enqueue_render_assets( $settings );

		$styles = $this->get_style_overrides( $settings, $selector );
		$styles = '<style>' . implode( "\n", $styles ) . '</style>';

		echo $styles . $html;
	}

	/**
	 * Get the style overrides for the output.
	 *
	 * @since 5.4.0
	 *
	 * @param array $settings Array of Elementor Widget settings.
	 * @param string $selector CSS Selector for the Elementor Widget.
	 *
	 * @return array
	 */
	protected function get_style_overrides( $settings = [], $selector = '' ) {
		$setting_style_map = [
			'after_html'         => '.tribe-events-after-html',
			'all_events_link'    => '.tribe-events-back',
			'before_html'        => '.tribe-events-before-html',
			'calendar_links'     => '.tribe-events-cal-links',
			'cost'               => '.tribe-events-cost',
			'custom_fields'      => '.tribe-events-meta-group-other',
			'description'        => '.tribe-events-single-event-description',
			'details_categories' => [
				'.tribe-events-event-categories-label',
				'.tribe-events-event-categories-label + dd',
			],
			'details_cost'       => [
				'.tribe-events-event-cost-label',
				'.tribe-events-event-cost-label + dd',
			],
			'details_date'       => [
				'.tribe-events-start-date-label',
				'.tribe-events-start-date-label + dd',
			],
			'details_tags'       => [
				'.tribe-events-event-categories-label + dd + dt',
				'.tribe-event-tags',
			],
			'details_time'       => [
				'.tribe-events-start-time-label',
				'.tribe-events-start-time-label + dd',
			],
			'featured_image'     => '.tribe-events-event-image',
			'footer'             => '#tribe-events-footer',
			'navigation'         => '.tribe-events-nav-pagination',
			'notices'            => '.tribe-events-notices',
			'organizer'          => '.tribe-events-meta-group-organizer',
			'organizer_email'    => [
				'.tribe-organizer-email-label',
				'.tribe-organizer-email',
			],
			'organizer_name'     => '.tribe-organizer',
			'organizer_phone'    => [
				'.tribe-organizer-tel-label',
				'.tribe-organizer-tel',
			],
			'organizer_url'      => [
				'.tribe-organizer-url-label',
				'.tribe-organizer-url',
			],
			'related_events'     => [
				'.tribe-events-related-events-title',
				'.tribe-related-events',
			],
			'tickets'            => [
				'.cart',
				'.event-tickets',
			],
			'title'              => '.tribe-events-single-event-title',
			'venue'              => '.tribe-events-single-section.tribe-events-event-meta.secondary',
			'venue_name'         => '.tribe-venue',
			'venue_location'     => '.tribe-venue-location',
			'venue_map'          => '.tribe-events-venue-map',
			'venue_phone'        => [
				'.tribe-venue-tel-label',
				'.tribe-venue-tel',
			],
			'venue_url'          => [
				'.tribe-venue-url-label',
				'.tribe-venue-url',
			],
			'virtual_video_embed' => '.tribe-events-virtual-single-video-embed',
			'virtual_watch_button' => [
				'.tribe-events-virtual-link-button',
				'.tribe-events-virtual-single-zoom-details__meta-group--link-button',
			],
			'virtual_zoom_link' => '.tribe-events-virtual-single-zoom-details__meta-group--zoom-link',
			'virtual_zoom_phone' => '.tribe-events-virtual-single-zoom-details__meta-group--zoom-phone',
		];

		$styles = [];

		/*---------------------------------------------------
		 * Setup our simple deactivations.
		 *---------------------------------------------------*/
		foreach ( $setting_style_map as $setting => $map ) {
			if ( tribe_is_truthy( Arr::get( $settings, $setting ) ) ) {
				continue;
			}

			if ( ! is_array( $map ) ) {
				$map = [ $map ];
			}

			// Prepend the widget's ID to each selector.
			$selectors = array_map( function( $value ) use ( $selector ) {
				return "{$selector} {$value}";
			}, $map );

			$selectors = implode( ', ', $selectors );

			$styles[] = "{$selectors} { display: none !important; }";
		}

		/*---------------------------------------------------
		 * We have some more complicated deactivations to check.
		 *---------------------------------------------------*/
		if ( ! tribe_is_truthy( Arr::get( $settings, 'date-time' ) ) ) {
			if ( ! tribe_is_truthy( Arr::get( $settings, 'cost' ) ) ) {
				$styles[] = "{$selector} .tribe-events-schedule { display: none !important; }";
			} else {
				$styles[] = "{$selector} .tribe-events-schedule h2 { display: none !important; }";
			}
		}

		if ( ! tribe_is_truthy( Arr::get( $settings, 'details' ) ) ) {
			if ( tribe_is_truthy( Arr::get( $settings, 'organizer' ) ) ) {
				$styles[] = "{$selector} .tribe-events-meta-group-details { display: none !important; }";
			} else {
				$styles[] = "{$selector} .tribe-events-single-section.tribe-events-event-meta.primary { display: none !important; }";
			}
		}

		if (
			tribe_is_truthy( Arr::get( $settings, 'venue_map' ) )
			&& ! tribe_is_truthy( Arr::get( $settings, 'venue_name' ) )
			&& ! tribe_is_truthy( Arr::get( $settings, 'venue_location' ) )
			&& ! tribe_is_truthy( Arr::get( $settings, 'venue_phone' ) )
			&& ! tribe_is_truthy( Arr::get( $settings, 'venue_url' ) )
		) {
			$styles[] = "{$selector} .tribe-events-meta-group-venue { display: none !important; }";
			$styles[] = "{$selector} .tribe-events-venue-map { float: none; margin-left: 20px; }";
		}

		if (
			! tribe_is_truthy( Arr::get( $settings, 'virtual_watch_button' ) )
			&& ! tribe_is_truthy( Arr::get( $settings, 'virtual_zoom_link' ) )
			&& ! tribe_is_truthy( Arr::get( $settings, 'virtual_zoom_phone' ) )
		) {
			$styles[] = "{$selector} .tribe-events-virtual-single-zoom-details { display: none !important; }";
		}

		return $styles;
	}

	/**
	 * Enqueues necessary assets for rendering the widget.
	 *
	 * @since 5.4.0
	 *
	 * @param array $settings Array of Elementor Widget settings.
	 */
	protected function enqueue_render_assets( $settings = [] ) {
		/*---------------------------------------------------
		 * Enqueue some stuff if certain settings are truthy.
		 *---------------------------------------------------*/
		tribe_asset_enqueue_group( 'events-styles' );

		if ( tribe_is_truthy( Arr::get( $settings, 'related-events' ) ) ) {
			tribe_asset_enqueue( 'tribe-events-full-pro-calendar-style' );
			tribe_asset_enqueue( 'tribe-events-calendar-pro-style' );
			tribe_asset_enqueue( 'tribe-events-calendar-pro-override-style' );
		}

		if ( tribe_is_truthy( Arr::get( $settings, 'tickets' ) ) ) {
			tribe_asset_enqueue( 'event-tickets-reset-css' );
			tribe_asset_enqueue( 'event-tickets-tickets-css' );
			tribe_asset_enqueue( 'event-tickets-tickets-rsvp-css' );
			tribe_asset_enqueue( 'event-tickets-tickets-rsvp-js' );
			tribe_asset_enqueue( 'event-tickets-attendees-list-js' );
			tribe_asset_enqueue( 'event-tickets-details-js' );
			tribe_asset_enqueue( 'tribe-tickets-forms-style' );

			if ( class_exists( 'Tribe__Tickets__Main' ) ) {
				if ( tribe_tickets_new_views_is_enabled() || tribe_tickets_rsvp_new_views_is_enabled() ) {
					tribe_asset_enqueue( 'tribe-tickets-loader' );
				}

				if ( tribe_tickets_new_views_is_enabled() ) {
					tribe_asset_enqueue( 'tribe-common-responsive' );
					tribe_asset_enqueue( 'tribe-tickets-utils' );
				}
			}
		}

		tribe_asset_enqueue( 'tribe-events-virtual-single-skeleton' );

		if ( tribe( Assets::class )->should_enqueue_full_styles() ) {
			tribe_asset_enqueue( 'tribe-events-virtual-single-full' );
		}
	}

	/**
	 * Register widget controls.
	 *
	 * @since 5.4.0
	 */
	protected function register_controls() {
		$this->add_event_query_section();

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'        => __( 'Title', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'date-time',
			[
				'label'        => __( 'Date/Time', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'cost',
			[
				'label'        => __( 'Cost', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'description',
			[
				'label'        => __( 'Description', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'featured_image',
			[
				'label'        => __( 'Featured Image', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'notices',
			[
				'label'        => __( 'Notices', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		if ( class_exists( 'Tribe__Tickets__Main' ) ) {
			$this->add_control(
				'tickets',
				[
					'label'        => __( 'RSVP/Tickets', 'tribe-events-calendar-pro' ),
					'description'  => __( 'RSVP/Tickets are not available in preview mode.', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);
		}

		$this->start_controls_tabs(
			'options_tabs'
		);

		$this->start_controls_tab(
			'option_details_tab',
			[
				'label' => __( 'Details', 'tribe-events-calendar-pro' ),
			]
		);

		$this->add_base_and_child_controls(
			'details',
			[
				'label'        => __( 'Event Details', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			],
			[
				'date'          => __( 'Date', 'tribe-events-calendar-pro' ),
				'time'          => __( 'Time', 'tribe-events-calendar-pro' ),
				'cost'          => __( 'Cost', 'tribe-events-calendar-pro' ),
				'categories'    => __( 'Categories', 'tribe-events-calendar-pro' ),
				'tags'          => __( 'Tags', 'tribe-events-calendar-pro' ),
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'option_organizer_tab',
			[
				'label' => __( 'Organizer', 'tribe-events-calendar-pro' ),
			]
		);

		$this->add_base_and_child_controls(
			'organizer',
			[
				'label'        => __( 'Organizer', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			],
			[
				'name'       => __( 'Name', 'tribe-events-calendar-pro' ),
				'phone'      => __( 'Phone', 'tribe-events-calendar-pro' ),
				'email'      => __( 'Email', 'tribe-events-calendar-pro' ),
				'url'        => __( 'Website', 'tribe-events-calendar-pro' ),
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'option_venue_tab',
			[
				'label' => __( 'Venue', 'tribe-events-calendar-pro' ),
			]
		);

		$this->add_base_and_child_controls(
			'venue',
			[
				'label'        => __( 'Venue', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			],
			[
				'name'       => __( 'Name', 'tribe-events-calendar-pro' ),
				'location'   => __( 'Location', 'tribe-events-calendar-pro' ),
				'phone'      => __( 'Phone', 'tribe-events-calendar-pro' ),
				'url'        => __( 'Website', 'tribe-events-calendar-pro' ),
				'map'        => __( 'Map', 'tribe-events-calendar-pro' ),
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'custom_section',
			[
				'label' => __( 'Custom Content', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'custom_fields',
			[
				'label'        => __( 'Custom Fields', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'before_html',
			[
				'label'        => __( 'Before HTML', 'tribe-events-calendar-pro' ),
				'description'  => __( 'Customizable HTML to display before the event.', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'after_html',
			[
				'label'        => __( 'After HTML', 'tribe-events-calendar-pro' ),
				'description'  => __( 'Customizable HTML to display after the event.', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'navigation_section',
			[
				'label' => __( 'Navigation', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'all_events_link',
			[
				'label'        => __( 'All Events Link', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'calendar_links',
			[
				'label'        => __( 'Calendar Links', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'related_events',
			[
				'label'        => __( 'Related Events', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'footer',
			[
				'label'        => __( 'Footer', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'navigation',
			[
				'label'        => __( 'Event Navigation', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'footer' => 'yes',
				],
			]
		);
		$this->end_controls_section();

		// Start style tab

		// Start content section
		$this->start_controls_section(
			'content',
			[
				'label' => esc_html__( 'Content', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_warning',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__(
					'The style of this widget is often affected by your theme and plugins. If you experience an issue, try switching to a basic WordPress theme and deactivate related plugins.',
					'tribe-events-calendar-pro'
				),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		// Start event title
		$this->add_control(
			'heading_event_title_style',
			[
				'label' => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'title' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_title_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-single-event-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'title' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_title_typography',
				'selector' => '{{WRAPPER}} .tribe-events-single-event-title',
				'condition' => [
					'title' => 'yes'
				],
			]
		);
		// End event title

		// Start datetime
		$this->add_control(
			'heading_datetime_style',
			[
				'label' => esc_html__( 'Date/Time', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'date-time' => 'yes'
				],
			]
		);

		$this->add_control(
			'datetime_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-event-date-start, {{WRAPPER}} .tribe-event-date-end' => 'color: {{VALUE}};',
				],
				'condition' => [
					'date-time' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'datetime_typography',
				'selector' => '{{WRAPPER}} .tribe-event-date-start, {{WRAPPER}} .tribe-event-date-end',
				'condition' => [
					'date-time' => 'yes'
				],
			]
		);
		// End datetime

		// Start cost
		$this->add_control(
			'heading_cost_style',
			[
				'label' => esc_html__( 'Cost', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'cost' => 'yes'
				],
			]
		);

		$this->add_control(
			'cost_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-cost' => 'color: {{VALUE}};',
				],
				'condition' => [
					'cost' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'cost_typography',
				'selector' => '{{WRAPPER}} .tribe-events-cost',
				'condition' => [
					'cost' => 'yes'
				],
			]
		);
		// End cost

		// Start description
		$this->add_control(
			'heading_description_style',
			[
				'label' => esc_html__( 'Description', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'description' => 'yes'
				],
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-single-event-description p' => 'color: {{VALUE}};',
				],
				'condition' => [
					'description' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .tribe-events-single-event-description p',
				'condition' => [
					'description' => 'yes'
				],
			]
		);
		// End description

		// End content section
		$this->end_controls_section();

		// Start featured image
		$this->start_controls_section(
			'featured_image_style',
			[
				'label' => esc_html__( 'Featured Image', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'featured_image' => 'yes'
				],
			]
		);

		$this->add_responsive_control(
			'featured_image_width',
			[
				'label' => esc_html__( 'Width', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => [ '%', 'px', 'vw' ],
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-image img' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'featured_image' => 'yes'
				],
			]
		);

		$this->add_responsive_control(
			'featured_image_space',
			[
				'label' => esc_html__( 'Max Width', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => [ '%', 'px', 'vw' ],
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-image img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'featured_image_height',
			[
				'label' => esc_html__( 'Height', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'size_units' => [ 'px', 'vh' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-image img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'featured_image_object_fit',
			[
				'label' => esc_html__( 'Object Fit', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::SELECT,
				'condition' => [
					'height[size]!' => '',
				],
				'options' => [
					'' => esc_html__( 'Default', 'tribe-events-calendar-pro' ),
					'fill' => esc_html__( 'Fill', 'tribe-events-calendar-pro' ),
					'cover' => esc_html__( 'Cover', 'tribe-events-calendar-pro' ),
					'contain' => esc_html__( 'Contain', 'tribe-events-calendar-pro' ),
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-image img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'featured_image_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'featured_image_effects' );

		$this->add_control(
			'featured_image_opacity',
			[
				'label' => esc_html__( 'Opacity', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-image img' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'featured_image' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'featured_image_css_filters',
				'selector' => '{{WRAPPER}} img',
				'condition' => [
					'featured_image' => 'yes'
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'featured_image_image_border',
				'selector' => '{{WRAPPER}} img',
				'separator' => 'before',
				'condition' => [
					'featured_image' => 'yes'
				],
			]
		);

		$this->add_responsive_control(
			'featured_image_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'featured_image' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'featured_image_box_shadow',
				'exclude' => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .tribe-events-event-image img',
				'condition' => [
					'featured_image' => 'yes'
				],
			]
		);

		$this->end_controls_section();

		$this->end_controls_section();
		// End featured image

		// Start event details
		$this->start_controls_section(
			'event_details_style',
			[
				'label' => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'details' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_details_background_color',
			[
				'label' => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .single-tribe_events .tribe-events-event-meta.primary' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'details' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_details_color',
			[
				'label' => esc_html__( 'Title Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-meta-group-details .tribe-events-single-section-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_details_typography',
				'selector' => '{{WRAPPER}} .tribe-events-meta-group-details .tribe-events-single-section-title',
				'condition' => [
					'details' => 'yes'
				],
			]
		);

		// Start event date
		$this->add_control(
			'heading_event_date_style',
			[
				'label' => esc_html__( 'Date', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'details_date' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_date_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-start-datetime-label, {{WRAPPER}} .tribe-events-end-datetime-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_date' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_date_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-start-datetime-label, {{WRAPPER}} .tribe-events-end-datetime-label',
				'condition' => [
					'details_date' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_date_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'details_date' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_date_color',
			[
				'label' => esc_html__( 'Date Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-start-datetime, {{WRAPPER}} .tribe-events-end-datetime' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_date' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_date_typography',
				'label' => esc_html__( 'Date Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-start-datetime, {{WRAPPER}} .tribe-events-end-datetime',
				'condition' => [
					'details_date' => 'yes'
				],
			]
		);
		// End event date

		// Start event cost
		$this->add_control(
			'heading_event_cost_style',
			[
				'label' => esc_html__( 'Cost', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'details_cost' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_cost_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-cost-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_cost' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_cost_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-event-cost-label',
				'condition' => [
					'details_cost' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_cost_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'details_cost' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_cost_color',
			[
				'label' => esc_html__( 'Cost Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-cost' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_cost' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_cost_typography',
				'label' => esc_html__( 'Cost Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-event-cost',
				'condition' => [
					'details_cost' => 'yes'
				],
			]
		);
		// End event cost

		// Start event categories
		$this->add_control(
			'heading_event_categories_style',
			[
				'label' => esc_html__( 'Categories', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'details_categories' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_categories_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-event-categories-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_categories' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_categories_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-event-categories-label',
				'condition' => [
					'details_categories' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_categories_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'details_categories' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_categories_color',
			[
				'label' => esc_html__( 'Categories Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-events-event-categories a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_categories' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_categories_typography',
				'label' => esc_html__( 'Categories Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-event-categories',
				'condition' => [
					'details_categories' => 'yes'
				],
			]
		);
		// End event categories

		// Start event tags
		$this->add_control(
			'heading_event_tags_style',
			[
				'label' => esc_html__( 'Tags', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'details_tags' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_tags_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-event-tags-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_tags' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_tags_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-event-tags-label',
				'condition' => [
					'details_tags' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_tags_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'details_tags' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_tags_color',
			[
				'label' => esc_html__( 'Tags Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-event-tags a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details_tags' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_tags_typography',
				'label' => esc_html__( 'Tags Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-event-tags',
				'condition' => [
					'details_tags' => 'yes'
				],
			]
		);
		// End event tags

		// Start event website
		$this->add_control(
			'heading_event_website_style',
			[
				'label' => esc_html__( 'Website', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'details' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_website_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-events-event-url a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'details' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_website_typography',
				'selector' => '{{WRAPPER}} .tribe-events-event-url',
				'condition' => [
					'details' => 'yes'
				],
			]
		);
		// End event website

		$this->end_controls_section();
		// End event details

		// Start event organizer
		$this->start_controls_section(
			'event_organizer_style',
			[
				'label' => esc_html__( 'Organizer', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'organizer' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_color',
			[
				'label' => esc_html__( 'Heading Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-meta-group-organizer .tribe-events-single-section-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'organizer' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_typography',
				'selector' => '{{WRAPPER}} .tribe-events-meta-group-organizer .tribe-events-single-section-title',
				'condition' => [
					'organizer' => 'yes'
				],
			]
		);

		// Start organizer name
		$this->add_control(
			'heading_event_organizer_name_style',
			[
				'label' => esc_html__( 'Name', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'organizer_name' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_name_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-organizer a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'organizer_name' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_name_typography',
				'selector' => '{{WRAPPER}} .tribe-organizer',
				'condition' => [
					'organizer_name' => 'yes'
				],
			]
		);
		// End organizer name

		// Start organizer phone
		$this->add_control(
			'heading_event_organizer_phone_style',
			[
				'label' => esc_html__( 'Phone', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'organizer_phone' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_phone_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-organizer-tel-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'organizer_phone' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_phone_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-organizer-tel-label',
				'condition' => [
					'organizer_phone' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_phone_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'organizer_phone' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_phone_color',
			[
				'label' => esc_html__( 'Phone Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-organizer-tel' => 'color: {{VALUE}};',
				],
				'condition' => [
					'organizer_phone' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_phone_typography',
				'label' => esc_html__( 'Phone Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-organizer-tel',
				'condition' => [
					'organizer_phone' => 'yes'
				],
			]
		);
		// End organizer phone

		// Start organizer email
		$this->add_control(
			'heading_event_organizer_email_style',
			[
				'label' => esc_html__( 'Email', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'organizer_email' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_email_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-organizer-email-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'organizer_email' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_email_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-organizer-email-label',
				'condition' => [
					'organizer_email' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_email_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'organizer_email' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_email_color',
			[
				'label' => esc_html__( 'Email Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-organizer-email' => 'color: {{VALUE}};',
				],
				'condition' => [
					'organizer_email' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_email_typography',
				'label' => esc_html__( 'Email Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-organizer-email',
				'condition' => [
					'organizer_email' => 'yes'
				],
			]
		);
		// End organizer email

		// Start organizer website
		$this->add_control(
			'heading_event_organizer_website_style',
			[
				'label' => esc_html__( 'Website', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'organizer_url' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_organizer_website_label_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-organizer-url a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'organizer_url' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_website_label_typography',
				'selector' => '{{WRAPPER}} .tribe-organizer-url',
				'condition' => [
					'organizer_url' => 'yes'
				],
			]
		);
		// End organizer website

		$this->end_controls_section();
		// End event organizer

		// Start event venue
		$this->start_controls_section(
			'event_venue_style',
			[
				'label' => esc_html__( 'Venue', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'venue' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_background_color',
			[
				'label' => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .single-tribe_events .tribe-events-event-meta.secondary' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'venue' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_color',
			[
				'label' => esc_html__( 'Heading Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-meta-group-venue .tribe-events-single-section-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'venue' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_typography',
				'selector' => '{{WRAPPER}} .tribe-events-meta-group-venue .tribe-events-single-section-title',
				'condition' => [
					'venue' => 'yes'
				],
			]
		);

		// Start venue name
		$this->add_control(
			'heading_event_venue_name_style',
			[
				'label' => esc_html__( 'Name', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'venue_name' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_name_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-venue a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'venue_name' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_name_typography',
				'selector' => '{{WRAPPER}} .tribe-venue',
				'condition' => [
					'venue_name' => 'yes'
				],
			]
		);
		// End venue name

		// Start venue location
		$this->add_control(
			'heading_event_venue_location_style',
			[
				'label' => esc_html__( 'Location', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'venue_location' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_location_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-address' => 'color: {{VALUE}};',
				],
				'condition' => [
					'venue_location' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_location_typography',
				'selector' => '{{WRAPPER}} .tribe-address, {{WRAPPER}} #tribe-events-content .tribe-events-gmap',
				'condition' => [
					'venue_location' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_location_link_color',
			[
				'label' => esc_html__( 'Link Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-events-gmap' => 'color: {{VALUE}};',
				],
				'condition' => [
					'venue_location' => 'yes'
				],
			]
		);
		// End venue location

		// Start venue phone
		$this->add_control(
			'heading_event_venue_phone_style',
			[
				'label' => esc_html__( 'Phone', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'venue_phone' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_phone_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-venue-tel-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'venue_phone' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_phone_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-venue-tel-label',
				'condition' => [
					'venue_phone' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_phone_separator_panel',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'venue_phone' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_phone_color',
			[
				'label' => esc_html__( 'Phone Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-venue-tel' => 'color: {{VALUE}};',
				],
				'condition' => [
					'venue_phone' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_phone_typography',
				'label' => esc_html__( 'Phone Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-venue-tel',
				'condition' => [
					'venue_phone' => 'yes'
				],
			]
		);
		// End venue phone

		// Start venue website
		$this->add_control(
			'heading_event_venue_website_style',
			[
				'label' => esc_html__( 'Website', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'venue_url' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_venue_website_label_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-venue-url a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'venue_url' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_website_label_typography',
				'selector' => '{{WRAPPER}} .tribe-venue-url',
				'condition' => [
					'venue_url' => 'yes'
				],
			]
		);
		// End venue website

		$this->end_controls_section();
		// End event venue

		// Start custom fields
		$this->start_controls_section(
			'event_custom_fields_style',
			[
				'label' => esc_html__( 'Custom Fields', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_custom_fields_color',
			[
				'label' => esc_html__( 'Heading Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-meta-group-other .tribe-events-single-section-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_custom_fields_typography',
				'selector' => '{{WRAPPER}} .tribe-events-meta-group-other .tribe-events-single-section-title',
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_custom_fields_separator_panel_top',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_custom_fields_label_color',
			[
				'label' => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-meta-group-other dt' => 'color: {{VALUE}};',
				],
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_custom_fields_label_typography',
				'label' => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-meta-group-other dt',
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_custom_fields_separator_panel_bottom',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_custom_fields_single_color',
			[
				'label' => esc_html__( 'Custom Fields Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-meta-group-other .tribe-meta-value' => 'color: {{VALUE}};',
				],
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_custom_fields_single_typography',
				'label' => esc_html__( 'Custom Fields Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-meta-group-other .tribe-meta-value',
				'condition' => [
					'custom_fields' => 'yes'
				],
			]
		);

		$this->end_controls_section();
		// End custom fields

		// Start navigation
		$this->start_controls_section(
			'event_navigation_style',
			[
				'label' => esc_html__( 'Navigation', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		// Start all events link
		$this->add_control(
			'heading_event_all_events_link_style',
			[
				'label' => esc_html__( 'All Events Link', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'all_events_link' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_all_events_link_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-events-back a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'all_events_link' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_all_events_link_typography',
				'selector' => '{{WRAPPER}} .tribe-events-back',
				'condition' => [
					'all_events_link' => 'yes'
				],
			]
		);
		// End all events link

		// Start calendar links
		$this->add_control(
			'heading_event_calendar_links_style',
			[
				'label' => esc_html__( 'Calendar Links', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'calendar_links' => 'yes'
				],
			]
		);

		$this->add_control(
			'event_calendar_links_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-events-cal-links a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'calendar_links' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_calendar_links_typography',
				'selector' => '{{WRAPPER}} .tribe-events-cal-links',
				'condition' => [
					'calendar_links' => 'yes'
				],
			]
		);
		// End calendar links

		// Start event footer navigation
		$this->add_control(
			'heading_footer_navigation_style',
			[
				'label' => esc_html__( 'Footer Navigation', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'navigation' => 'yes'
				],
			]
		);

		$this->add_control(
			'footer_navigation_color',
			[
				'label' => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #tribe-events-content .tribe-events-nav-previous a, {{WRAPPER}} #tribe-events-content .tribe-events-nav-next a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'navigation' => 'yes'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'footer_navigation_typography',
				'selector' => '{{WRAPPER}} .tribe-events-nav-previous, {{WRAPPER}} .tribe-events-nav-next',
				'condition' => [
					'navigation' => 'yes'
				],
			]
		);
		// End footer navigation

		$this->end_controls_section();
		// End navigation

		$this->end_controls_section();
		// End events section

		// End style tab

		if ( class_exists( 'Tribe\\Events\\Virtual\\Plugin' ) ) {
			$this->start_controls_section(
				'virtual_section',
				[
					'label' => __( 'Virtual', 'tribe-events-calendar-pro' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				]
			);

			$this->add_control(
				'virtual_video_embed',
				[
					'label'        => __( 'Video Embed', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);

			$this->add_control(
				'virtual_watch_button',
				[
					'label'        => __( 'Watch Button', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);

			$this->add_control(
				'virtual_zoom_link',
				[
					'label'        => __( 'Zoom Link', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);

			$this->add_control(
				'virtual_zoom_phone',
				[
					'label'        => __( 'Zoom Dial-in Info', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);
			$this->end_controls_section();
		}
	}

	/**
	 * Enqueues assets for this widget.
	 */
	public function enqueue_editor_assets() {
		tribe_asset_enqueue_group( 'events-styles' );
		tribe_asset_enqueue( 'tribe-events-full-pro-calendar-style' );
		tribe_asset_enqueue( 'tribe-events-calendar-pro-style' );
		tribe_asset_enqueue( 'tribe-events-calendar-pro-override-style' );
		tribe_asset_enqueue( 'tribe-events-virtual-single-skeleton' );

		tribe_asset_enqueue( 'event-tickets-reset-css' );
		tribe_asset_enqueue( 'event-tickets-tickets-css' );
		tribe_asset_enqueue( 'event-tickets-tickets-rsvp-css' );
		tribe_asset_enqueue( 'event-tickets-tickets-rsvp-js' );
		tribe_asset_enqueue( 'event-tickets-attendees-list-js' );
		tribe_asset_enqueue( 'event-tickets-details-js' );
		tribe_asset_enqueue( 'tribe-tickets-forms-style' );

		if ( class_exists( 'Tribe__Tickets__Main' ) ) {
			if ( tribe_tickets_new_views_is_enabled() || tribe_tickets_rsvp_new_views_is_enabled() ) {
				tribe_asset_enqueue( 'tribe-tickets-loader' );
			}

			if ( tribe_tickets_new_views_is_enabled() ) {
				tribe_asset_enqueue( 'tribe-common-responsive' );
				tribe_asset_enqueue( 'tribe-tickets-utils' );
			}
		}

		if ( tribe( Assets::class )->should_enqueue_full_styles() ) {
			tribe_asset_enqueue( 'tribe-events-virtual-single-full' );
		}
	}

	/**
	 * Add base CHOOSE control and conditional CHOOSE sub-controls.
	 *
	 * @since 5.4.0
	 *
	 * @param string $parent_id Control parent ID.
	 * @param array $parent_args Control parent Arguments.
	 * @param array $controls Collection of dependent child controls.
	 */
	protected function add_base_and_child_controls( $parent_id, array $parent_args, array $controls ) {
		$this->add_control( $parent_id, $parent_args );

		foreach ( $controls as $id => $label ) {
			$this->add_control(
				"{$parent_id}_{$id}",
				[
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => [
						$parent_id => 'yes',
					],
				]
			);
		}
	}
}
