<?php
/**
 * IntegrationTest class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control\Tests\Feature;

use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget;
use Alley\WP\Widget_Control\Storage\Widget_Instance;
use Alley\WP\Widget_Control\Tests\ExampleWidget;
use Alley\WP\Widget_Control\Tests\TestCase;
use Alley\WP\Widget_Control\Widget_Control;

use function Alley\WP\Widget_Control\reload_widgets;

/**
 * Integration tests for widget and sidebar storage.
 */
class IntegrationTest extends TestCase {
	/**
	 * Test to validate the default rendering of the sidebar setup by the TestCase.
	 */
	public function test_it_can_render_a_sidebar(): void {
		// Sidebar 1 has nav_menu-2, block-2, example_widget-2.
		$sidebar = $this->render_sidebar( 'sidebar-1' );

		$this->assertStringContainsString( 'Block 2', $sidebar );
		$this->assertStringNotContainsString( 'Block 3', $sidebar );
		$this->assertStringNotContainsString( 'Block 4', $sidebar );

		$this->assertStringContainsString( 'Example Widget 2', $sidebar );
		$this->assertStringNotContainsString( 'Example Widget 3', $sidebar );
		$this->assertStringNotContainsString( 'Example Widget 4', $sidebar );
	}

	public function test_it_can_append_to_a_sidebar(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		// Append a new widget instance to the sidebar.
		$sidebar->append( Widget::from( ExampleWidget::class )->append( [ 'content' => 'Hello, World!' ] ) );

		reload_widgets();

		$sidebar = $this->render_sidebar( 'sidebar-1' );

		// Verify the sidebar still contains the original widget.
		$this->assertStringContainsString( 'Block 2', $sidebar );

		// Verify the sidebar now contains the new widget.
		$this->assertStringContainsString( 'Hello, World!', $sidebar );
	}

	public function test_it_can_fully_curate_a_sidebar(): void {
		Sidebar::from( 'sidebar-1' )->set( [
			Widget::from( ExampleWidget::class )->append( [ 'content' => 'Hello, World! 1' ] ),
			Widget::from( ExampleWidget::class )->append( [ 'content' => 'Hello, World! 2' ] ),
		] );

		reload_widgets();

		$sidebar = $this->render_sidebar( 'sidebar-1' );

		$this->assertStringContainsString( 'Hello, World! 1', $sidebar );
		$this->assertStringContainsString( 'Hello, World! 2', $sidebar );
		$this->assertStringNotContainsString( 'Block 2', $sidebar );
		$this->assertStringNotContainsString( 'Example Widget 2', $sidebar );
	}

	public function test_it_can_clear_a_sidebar(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		// Clear the sidebar.
		$sidebar->clear();

		reload_widgets();

		// Verify the sidebar is empty.
		$this->assertEmpty( $this->render_sidebar( 'sidebar-1' ) );
	}

	public function test_it_can_remove_a_widget_by_id(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		// Remove a specific widget by ID.
		$sidebar->remove( 'example_widget-2' );

		reload_widgets();

		// Verify the widget is removed.
		$this->assertStringNotContainsString( 'Example Widget 2', $this->render_sidebar( 'sidebar-1' ) );
	}

	public function test_it_can_remove_a_widget_by_index(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		// Remove the first widget by index.
		$sidebar->remove_index( 2 ); // Example Widget 2 is at index 2.

		reload_widgets();

		$sidebar = $this->render_sidebar( 'sidebar-1' );

		// block-2 should still be there.
		$this->assertStringContainsString( 'Block 2', $sidebar );
		$this->assertStringNotContainsString( 'Example Widget 2', $sidebar );
	}

	public function test_it_can_filter_a_sidebar_by_widget_id(): void {
		Sidebar::from( 'sidebar-1' )->filter_by_id(
			fn ( string $widget_id ) => ! str_contains( $widget_id, 'example_widget' ),
		);

		reload_widgets();

		$sidebar = $this->render_sidebar( 'sidebar-1' );

		// Verify the sidebar only contains non-example widgets.
		$this->assertStringContainsString( 'Block 2', $sidebar );
		$this->assertStringNotContainsString( 'Example Widget 2', $sidebar );
		$this->assertStringNotContainsString( 'Example Widget 3', $sidebar );
	}

	public function test_it_can_filter_by_widget_instance(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		$_SERVER['__valid'] = false;

		// Filter out all widgets that are not ExampleWidget instances.
		$sidebar->filter( function ( Widget_Instance $widget ): bool {
			$_SERVER['__valid'] = true;

			return $widget->id_base === 'example_widget';
		} );

		reload_widgets();

		$sidebar = $this->render_sidebar( 'sidebar-1' );

		// Verify the sidebar only contains ExampleWidget instances.
		$this->assertStringContainsString( 'Example Widget 2', $sidebar );
		$this->assertStringNotContainsString( 'Block 2', $sidebar );

		// Verify the filter callback was called.
		$this->assertTrue( $_SERVER['__valid'] ?? false, 'Filter callback was not called.' );
	}
}
