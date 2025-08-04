<?php
namespace Alley\WP\Widget_Control\Tests\Feature\Storage;

use Alley\WP\Widget_Control\Tests\TestCase;
use Alley\WP\Widget_Control\Storage\Widget;

class WidgetTest extends TestCase {
	public function test_it_can_retrieve_a_widget_instance(): void {
		/**
		 * @var Widget<array{content: string}> $widget
		 */
		$widget = Widget::from( 'block' );

		$this->assertCount( 3, $widget->to_array() );
		$this->assertStringContainsString( "<!-- wp:paragraph -->", $widget->get( 2 )['content'] );
		$this->assertStringContainsString( "<!-- wp:paragraph -->", $widget->get( 3 )['content'] );
		$this->assertStringContainsString( "<!-- wp:heading ", $widget->get( 4 )['content'] );
		$this->assertNull( $widget->get( 5 ) );
	}

	public function test_it_can_append_a_widget_instance(): void {
		/**
		 * @var Widget<array{content: string}> $widget
		 */
		$widget = Widget::from( 'block' );

		$index = $widget->append( [ 'content' => '<!-- wp:paragraph -->New Paragraph<!-- /wp:paragraph -->' ] );
		$this->assertCount( 4, $widget->to_array() );

		$this->assertEquals(
			[ 'content' => '<!-- wp:paragraph -->New Paragraph<!-- /wp:paragraph -->' ],
			$widget->get( $index )->to_array(),
		);
		$this->assertTrue( $widget->save() );

		// Re-fetch the widget to ensure it was saved correctly.
		$widget = Widget::from( 'block' );

		$this->assertCount( 4, $widget->to_array() );
		$this->assertEquals(
			[ 'content' => '<!-- wp:paragraph -->New Paragraph<!-- /wp:paragraph -->' ],
			$widget->get( $index )->to_array(),
		);
	}

	public function test_it_can_ovewrite_a_widget_instance(): void {
		/**
		 * @var Widget<array{content: string}> $widget
		 */
		$widget = Widget::from( 'block' );

		$widget->set(
			[ 'content' => '<!-- wp:paragraph -->Updated Paragraph<!-- /wp:paragraph -->' ],
			index: 2,
		);

		$this->assertEquals(
			[ 'content' => '<!-- wp:paragraph -->Updated Paragraph<!-- /wp:paragraph -->' ],
			$widget->get( 2 )->to_array(),
		);
	}

	public function test_it_can_delete_a_widget_instance(): void {
		/**
		 * @var Widget<array{content: string}> $widget
		 */
		$widget = Widget::from( 'block' );

		$widget->remove( 2 );

		$this->assertCount( 2, $widget->to_array() );
		$this->assertNull( $widget->get( 2 ) );
	}

	public function test_it_can_clear_all_widget_instances(): void {
		/**
		 * @var Widget<array{content: string}> $widget
		 */
		$widget = Widget::from( 'block' );

		$widget->clear();

		$this->assertCount( 0, $widget->to_array() );
	}
}
