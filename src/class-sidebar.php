<?php
/**
 * Sidebar class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control;

use Mantle\Contracts\Support\Arrayable;

use function Mantle\Support\Helpers\collect;
use function Mantle\Support\Helpers\option;
use function Mantle\Support\Helpers\stringable;

/**
 * Representation of a single sidebar's widgets.
 *
 * Widget IDs are in the format of "widget_id_base-instance_number" and stored
 * in a single option using the "widget_{id_base}" format. For management of
 * the widget instances inside the sidebar, use {@see \Alley\WP\Widget_Control\Widget}.
 */
class Sidebar implements Arrayable {
	/**
	 * Create a Sidebar instance from an existing sidebar location.
	 *
	 * This will retrieve the sidebar's widgets from the WordPress options for
	 * the given location.
	 *
	 * @param string $location The sidebar location.
	 * @return self
	 */
	public static function from( string $location ): self {
		return new self( $location, option( 'sidebars_widgets', [] )->get( $location, [] )->array() ); // @phpstan-ignore-line argument.type
	}

	/**
	 * Constructor.
	 *
	 * @param string   $location Sidebar location.
	 * @param string[] $widgets  Widget IDs in the sidebar.
	 */
	public function __construct( public readonly string $location, public array $widgets = [] ) {}

	/**
	 * Append a widget to the end of the sidebar.
	 *
	 * @param string|Widget_Instance $widget Widget ID or instance to append.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function append( string|Widget_Instance $widget ): static {
		$widget = $this->resolve_widget( $widget );

		$this->validate_widget_id( $widget );

		if ( in_array( $widget, $this->widgets, true ) ) {
			throw new \InvalidArgumentException( 'Widget ID must be unique within the sidebar.' );
		}

		$this->widgets[] = $widget;

		return $this;
	}

	/**
	 * Prepend a widget to the beginning of the sidebar.
	 *
	 * @param string|Widget_Instance $widget Widget ID/instance to prepend.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function prepend( string|Widget_Instance $widget ): static {
		$widget = $this->resolve_widget( $widget );

		$this->validate_widget_id( $widget );

		if ( in_array( $widget, $this->widgets, true ) ) {
			throw new \InvalidArgumentException( 'Widget ID must be unique within the sidebar.' );
		}

		array_unshift( $this->widgets, $widget );

		return $this;
	}

	/**
	 * Insert a widget before another widget in the sidebar.
	 *
	 * @param string|Widget_Instance $widget Widget ID/instance to insert.
	 * @param string                 $before_widget_id Widget ID to insert before.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function insert_before( string|Widget_Instance $widget, string $before_widget_id ): static {
		$widget = $this->resolve_widget( $widget );

		$this->validate_widget_id( $widget );

		if ( in_array( $widget, $this->widgets, true ) ) {
			throw new \InvalidArgumentException( 'Widget ID must be unique within the sidebar.' );
		}

		$index = array_search( $before_widget_id, $this->widgets, true );

		if ( false !== $index && is_int( $index ) ) {
			array_splice( $this->widgets, $index, 0, [ $widget ] );
			return $this;
		}

		return $this->prepend( $widget );
	}

	/**
	 * Insert a widget after another widget in the sidebar.
	 *
	 * @param string|Widget_Instance $widget Widget ID/instance to insert.
	 * @param string                 $after_widget_id Widget ID to insert after.
	 * @throws \InvalidArgumentException If widget ID is invalid or not unique.
	 */
	public function insert_after( string|Widget_Instance $widget, string $after_widget_id ): static {
		$widget = $this->resolve_widget( $widget );

		$this->validate_widget_id( $widget );

		if ( in_array( $widget, $this->widgets, true ) ) {
			throw new \InvalidArgumentException( 'Widget ID must be unique within the sidebar.' );
		}

		$index = array_search( $after_widget_id, $this->widgets, true );

		if ( false !== $index && is_int( $index ) ) {
			array_splice( $this->widgets, $index + 1, 0, [ $widget ] );
			return $this;
		}

		return $this->append( $widget );
	}

	/**
	 * Remove a widget from the sidebar by widget ID.
	 *
	 * @param string|Widget_Instance $widget Widget ID/instance to remove.
	 */
	public function remove( string|Widget_Instance $widget ): static {
		$widget = $this->resolve_widget( $widget );

		$this->validate_widget_id( $widget );

		$index = array_search( $widget, $this->widgets, true );

		if ( false !== $index ) {
			unset( $this->widgets[ $index ] );
			$this->widgets = array_values( $this->widgets );
		}

		return $this;
	}

	/**
	 * Remove a widget from the sidebar by index.
	 *
	 * @param int $index Index of the widget to remove.
	 */
	public function remove_index( int $index ): static {
		if ( isset( $this->widgets[ $index ] ) ) {
			unset( $this->widgets[ $index ] );

			$this->widgets = array_values( $this->widgets );
		}

		return $this;
	}

	/**
	 * Filter the widgets in a sidebar by widget instance.
	 *
	 * @see Sidebar::filter_by_id()
	 *
	 * @param callable $callback Callback function to filter widgets.
	 * @phpstan-param (callable(Widget_Instance $widget): bool) $callback
	 * @return static
	 */
	public function filter( callable $callback ): static {
		$this->widgets = array_filter( $this->widgets, function ( string $widget_id ) use ( $callback ): bool {
			$this->validate_widget_id( $widget_id );

			$widget_id = stringable( $widget_id );

			// Explode after the last dash to get the ID base and instance.
			$id_base  = $widget_id->before_last( '-' )->value();
			$instance = (int) $widget_id->after_last( '-' )->value();

			$instance = Widget::from( $id_base )->get( $instance );

			// Widget instance does not exist.
			if ( ! $instance ) {
				return false;
			}

			return $callback( $instance );
		} );

		return $this;
	}

	/**
	 * Filter the sidebar by widget ID.
	 *
	 * @param callable $callback Callback function to filter widgets.
	 * @return static
	 */
	public function filter_by_id( callable $callback ): static {
		$this->widgets = array_filter( $this->widgets, $callback );

		return $this;
	}

	/**
	 * Clear all widgets from the sidebar.
	 *
	 * This will remove all widgets and reset the sidebar to an empty state.
	 */
	public function clear(): static {
		$this->widgets = [];

		return $this;
	}

	/**
	 * Check if the sidebar contains a widget by ID.
	 *
	 * @param string $widget_id Widget ID to check.
	 * @return bool True if the sidebar contains the widget, false otherwise.
	 */
	public function contains( string $widget_id ): bool {
		return collect( $this->widgets )->some(
			fn ( string $id ) => $id === $widget_id || str_contains( $id, "{$widget_id}-" )
		);
	}

	/**
	 * Set the entire list of widgets in the sidebar.
	 *
	 * @param string[] $widgets Array of widget IDs to set.
	 * @return static
	 */
	public function set( array $widgets ): static {
		$this->widgets = array_map( $this->resolve_widget( ... ), $widgets );

		return $this;
	}

	/**
	 * Save the sidebar's widgets to the WordPress options table.
	 */
	public function save(): static {
		$sidebars_widgets = option( 'sidebars_widgets', [] )->array();

		if ( ! isset( $sidebars_widgets[ $this->location ] ) ) {
			$sidebars_widgets[ $this->location ] = [];
		}

		$sidebars_widgets[ $this->location ] = array_values( $this->widgets );

		wp_set_sidebars_widgets( $sidebars_widgets );

		return $this;
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
	 * Dump the sidebar's widgets.
	 *
	 * @return static
	 */
	public function dump(): static {
		dump( $this->widgets );

		return $this;
	}

	/**
	 * Resolve a widget ID or instance to its string representation.
	 *
	 * @param string|Widget_Instance $widget Widget ID or instance.
	 * @return string Resolved widget ID.
	 */
	protected function resolve_widget( string|Widget_Instance $widget ): string {
		$widget_id = $widget instanceof Widget_Instance ? $widget->get_widget_id() : $widget;

		$this->validate_widget_id( $widget_id );

		return $widget_id;
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
