<?php
/**
 * SidebarTest class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control\Tests\Feature\Storage;

use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Sidebar tests.
 */
class SidebarTest extends TestCase {
	public function test_it_can_retrieve_a_sidebar_by_location(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		$this->assertEquals( 3, $sidebar->count() );

		$this->assertEquals(
			[ 'nav_menu-1', 'block-2', 'example_widget-2' ],
			$sidebar->to_array(),
		);
	}

	public function test_it_can_add_widgets_to_a_sidebar(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		$this->assertEquals( 3, $sidebar->count() );

		$sidebar->append( 'example_widget-4' );
		$sidebar->save();

		$this->assertEquals( 4, $sidebar->count() );
		$this->assertContains( 'example_widget-4', $sidebar->to_array() );

		$sidebar->insert_before( 'block-99', 'block-2' );
		$sidebar->insert_after( 'example_widget-6', 'example_widget-2' );

		$this->assertEquals( 6, $sidebar->count() );
		$this->assertEquals(
			[
				'nav_menu-1',
				'block-99',
				'block-2',
				'example_widget-2',
				'example_widget-6',
				'example_widget-4',
			],
			$sidebar->to_array(),
		);
		$this->assertTrue( $sidebar->contains( 'block' ) );
		$this->assertTrue( $sidebar->contains( 'block-99' ) );
		$this->assertFalse( $sidebar->contains( 'unknown' ) );
	}

	public function test_it_can_remove_widgets_from_a_sidebar(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		$this->assertEquals( 3, $sidebar->count() );

		$sidebar->remove( 'block-2' );

		$this->assertEquals( 2, $sidebar->count() );
		$this->assertNotContains( 'block-2', $sidebar->to_array() );
	}

	public function test_it_can_add_a_new_sidebar_that_does_not_have_data(): void {
		$sidebar = Sidebar::from( 'sidebar-4' );

		$this->assertEquals( 0, $sidebar->count() );

		$sidebar->append( 'example_widget-5' );
		$sidebar->save();

		$this->assertEquals( 1, $sidebar->count() );
		$this->assertCount( 1, Sidebar::from( 'sidebar-4' )->to_array() );
	}

	#[DataProvider( 'invalid_widget_id_data_provider' )]
	public function test_it_throws_an_error_on_invalid_widget_id( mixed $invalid_widget_id ): void {
		$this->expectException( \InvalidArgumentException::class );

		$sidebar = Sidebar::from( 'sidebar-1' );
		$sidebar->append( $invalid_widget_id );
	}

	public static function invalid_widget_id_data_provider(): array {
		return [
			[ 12345 ],
			[ 'Example here' ],
			[ 'Example_here' ],
			[ 'Example_here-2' ],
			[ 'example_here_$' ],
			[ 'example_here_$-8' ],
			[ 'example_here_1' ],
			[ 'example_here_1-' ],
		];
	}

	public function test_sidebar_widgets_must_be_unique(): void {
		$sidebar = Sidebar::from( 'sidebar-1' );

		$this->expectException( \InvalidArgumentException::class );
		$sidebar->append( 'nav_menu-1' );
	}
}
