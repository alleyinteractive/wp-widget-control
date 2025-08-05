<?php
namespace Alley\WP\Widget_Control\Tests\Feature;

use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget;
use Alley\WP\Widget_Control\Tests\ExampleWidget;
use Alley\WP\Widget_Control\Tests\TestCase;
use Alley\WP\Widget_Control\Widget_Control;

use function Mantle\Support\Helpers\capture;

/**
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
class WidgetControlTest extends TestCase {
	public function test_it_can_curate_a_widget_by_class(): void {
		$this->assertStringNotContainsString( 'Hello, World!', $this->render_sidebar( 'sidebar-1' ) );

		$widget = Widget::from( ExampleWidget::class );
		// dump($widget->append( [ 'content' => 'Hello, World!' ] ), $widget->to_array());
		Sidebar::from( 'sidebar-1' )->clear()->append(
			Widget::from( ExampleWidget::class )->append( [ 'content' => 'Hello, World!' ] )
		);

		Widget::from( ExampleWidget::class )->dump();

		Sidebar::from( 'sidebar-1' )->dump();

		dump(get_option('widget_example_widget'));

		dd($this->render_sidebar( 'sidebar-1' ));
		$this->assertStringContainsString( 'Hello, World!', $this->render_sidebar( 'sidebar-1' ) );
	}

	protected function render_sidebar( string $sidebar_id ): string {
		return capture( fn () => dynamic_sidebar( $sidebar_id ) );
	}
}
