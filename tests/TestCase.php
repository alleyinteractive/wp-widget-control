<?php
namespace Alley\WP\Widget_Control\Tests;

use Mantle\Testkit\Test_Case as TestkitTest_Case;

use function Mantle\Testing\block_factory;

/**
 * WP Widget Control Base Test Case
 */
abstract class TestCase extends TestkitTest_Case {
	protected function setUp(): void {
		parent::setUp();

		register_widget( ExampleWidget::class );

		for ( $i = 1; $i <= 3; $i++ ) {
			register_sidebar( [
				'id'            => "sidebar-{$i}",
				'name'          => "Sidebar {$i}",
				'before_widget' => '<div class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			] );
		}

		update_option(
			'sidebars_widgets',
			[
				'sidebar-1' => [ 'nav_menu-1', 'block-2', 'example_widget-2' ],
				'sidebar-2' => [ 'block-3', 'example_widget-3' ],
				'sidebar-3' => [ 'example_widget-4', 'block-4' ],
			]
		);

		update_option( 'widget_nav_menu', [
			1 => [],
			'_multiwidget' => 1,
		] );

		update_option( 'widget_block', [
			2 => [ 'content' => block_factory()->paragraph() ],
			3 => [ 'content' => block_factory()->paragraphs() ],
			4 => [ 'content' => block_factory()->heading() ],
			'_multiwidget' => 1,
		] );

		update_option( 'widget_example_widget', [
			2 => [ 'title' => 'Example Widget 2' ],
			3 => [ 'title' => 'Example Widget 3' ],
			4 => [ 'title' => 'Example Widget 4' ],
			'_multiwidget' => 1,
		] );
	}
}

class ExampleWidget extends \WP_Widget {
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
