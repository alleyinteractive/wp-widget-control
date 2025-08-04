<?php
/**
 * Sidebar_Storage class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control;

use Mantle\Contracts\Support\Arrayable;

use function Mantle\Support\Helpers\option;

/**
 * Representation of a single sidebar's widgets.
 */
class Sidebar_Storage implements Arrayable {
	/**
	 * Create a Sidebar instance from a location.
	 *
	 * @param string $location The sidebar location.
	 * @return self
	 */
	public static function from( string $location ): self {
		return new self( $location, option( 'sidebars_widgets', [] )->get( $location, [] )->array() );
	}

	/**
	 * Constructor.
	 *
	 * @param string   $location
	 * @param string[] $widgets
	 */
	public function __construct( public readonly string $location, public array $widgets = [] ) {}

	/**
	 * Append a widget to the end of the sidebar.
	 *
	 * @param string $widget_id Widget ID to append.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function append( string $widget_id ): void {
		$this->validate_widget_id( $widget_id );

		if ( in_array( $widget_id, $this->widgets, true ) ) {
			throw new \InvalidArgumentException( 'Widget ID must be unique within the sidebar.' );
		}

		$this->widgets[] = $widget_id;
	}

	/**
	 * Prepend a widget to the beginning of the sidebar.
	 *
	 * @param string $widget_id Widget ID to prepend.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function prepend( string $widget_id ): void {
		$this->validate_widget_id( $widget_id );

		if ( in_array( $widget_id, $this->widgets, true ) ) {
			throw new \InvalidArgumentException( 'Widget ID must be unique within the sidebar.' );
		}

		array_unshift( $this->widgets, $widget_id );
	}

	/**
	 * Insert a widget before another widget in the sidebar.
	 *
	 * @param string $widget_id         Widget ID to insert.
	 * @param string $before_widget_id  Widget ID to insert before.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function insert_before( string $widget_id, string $before_widget_id ): void {
		$this->validate_widget_id( $widget_id );

		if ( in_array( $widget_id, $this->widgets, true ) ) {
			throw new \InvalidArgumentException( 'Widget ID must be unique within the sidebar.' );
		}

		$index = array_search( $before_widget_id, $this->widgets, true );

		if ( false !== $index ) {
			array_splice( $this->widgets, $index, 0, [ $widget_id ] );
		} else {
			$this->prepend( $widget_id );
		}
	}

	/**
	 * Insert a widget after another widget in the sidebar.
	 *
	 * @param string $widget_id        Widget ID to insert.
	 * @param string $after_widget_id  Widget ID to insert after.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function insert_after( string $widget_id, string $after_widget_id ): void {
		$this->validate_widget_id( $widget_id );

		$index = array_search( $after_widget_id, $this->widgets, true );

		if ( false !== $index ) {
			array_splice( $this->widgets, $index + 1, 0, [ $widget_id ] );
		} else {
			$this->append( $widget_id );
		}
	}

	/**
	 * Remove a widget from the sidebar by widget ID.
	 *
	 * @param string $widget_id Widget ID to remove.
	 */
	public function remove( string $widget_id ): void {
		$this->validate_widget_id( $widget_id );

		$index = array_search( $widget_id, $this->widgets, true );

		if ( false !== $index ) {
			unset( $this->widgets[ $index ] );
			$this->widgets = array_values( $this->widgets );
		}
	}

	/**
	 * Remove a widget from the sidebar by index.
	 *
	 * @param int $index Index of the widget to remove.
	 */
	public function remove_index( int $index ): void {
		if ( isset( $this->widgets[ $index ] ) ) {
			unset( $this->widgets[ $index ] );
			$this->widgets = array_values( $this->widgets );
		}
	}

	/**
	 * Save the sidebar's widgets to the WordPress options table.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save(): bool {
		$sidebars_widgets = option( 'sidebars_widgets', [] )->array();

		if ( ! isset( $sidebars_widgets[ $this->location ] ) ) {
			$sidebars_widgets[ $this->location ] = [];
		}

		$sidebars_widgets[ $this->location ] = $this->widgets;

		return update_option( 'sidebars_widgets', $sidebars_widgets );
	}

	/**
	 * Get the number of widgets in the sidebar.
	 *
	 * @return int Number of widgets.
	 */
	public function count(): int {
		return count( $this->widgets );
	}

	/**
	 * Retrieve the widgets for a sidebar as an array.
	 *
	 * @return string[] Array of widget IDs.
	 */
	public function to_array(): array {
		return $this->widgets;
	}

	/**
	 * Dump the sidebar's widgets and exit.
	 *
	 * @return never
	 */
	public function dd(): never {
		dd( $this->widgets );
	}

	/**
	 * Validate the format of a widget ID.
	 *
	 * Widget IDs are the widget's ID base with the instance number appended.
	 * For example, 'example_widget-2'.
	 *
	 * @param string $widget_id Widget ID to validate.
	 * @throws \InvalidArgumentException If widget ID format is invalid.
	 */
	protected function validate_widget_id( string $widget_id ): void {
		if ( ! preg_match( '/^[a-z0-9_-]+-[0-9]+$/', $widget_id ) ) {
			throw new \InvalidArgumentException( 'Invalid widget ID format.' );
		}
	}
}
