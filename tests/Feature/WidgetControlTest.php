<?php
namespace Alley\WP\Widget_Control\Tests\Feature;

use Alley\WP\Widget_Control\Tests\TestCase;
use Alley\WP\Widget_Control\Widget_Control;

/**
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
class WidgetControlTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		register_widget( Example_Widget::class );
	}

	public function test_it_can_curate_a_widget_by_class(): void {
		$control = new Widget_Control( Example_Widget::class );

		dd($control);
	}
}

class Example_Widget extends \WP_Widget {
	public function __construct() {
		parent::__construct(
			'example_widget',
			__( 'Example Widget', 'text_domain' ),
			array( 'description' => __( 'An example widget for demonstration purposes.', 'text_domain' ) )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo '<p>' . __( 'Hello, World!', 'text_domain' ) . '</p>';
		echo $args['after_widget'];
	}
}
