<?php
/**
 * Widget_Control class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control;

use WP_Widget;

/**
 * Example Package
 */
class Widget_Control {
	/**
	 * The widget ID base.
	 *
	 * @var string
	 */
	public readonly string $widget_id_base;

	/**
	 * Constructor.
	 *
	 * @param string $widget The widget class name or ID base.
	 */
	public function __construct( string|WP_Widget $widget ) {
		if ( $widget instanceof WP_Widget ) {
			$this->widget_id_base = $widget->id_base;
			return;
		}

		if ( class_exists( $widget ) ) {
			$instance = new $widget();

			assert( $instance instanceof WP_Widget, 'Passed widget class must extend WP_Widget.' );

			$this->widget_id_base = $instance->id_base;
			return;
		}

		$this->widget_id_base = $widget;
	}
}
