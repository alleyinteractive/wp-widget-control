<?php
/**
 * Helper functions for WP Widget Control.
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control;

/**
 * Reload the widgets available to WordPress.
 *
 * This is a workaround to be able to set the widgets and then use them right
 * away in tests. This should not be used outside of tests.
 */
function reload_widgets(): void {
	if ( ! defined( 'MANTLE_IS_TESTING' ) || ! MANTLE_IS_TESTING ) {
		_doing_it_wrong(
			__FUNCTION__,
			'This function should only be used in tests.',
			'1.0.0',
		);
	}

	$GLOBALS['wp_widget_factory'] = new \WP_Widget_Factory(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

	$GLOBALS['wp_registered_widgets'] = []; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

	wp_widgets_init();
}
