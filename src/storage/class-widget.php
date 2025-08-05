<?php
/**
 * Widget_Storage class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control\Storage;

use Mantle\Contracts\Support\Arrayable;
use WP_Widget;

use function Mantle\Support\Helpers\collect;
use function Mantle\Support\Helpers\option;

/**
 * Representation of a widget's storage in options.
 *
 * Widget instances are stored in an array indexed by their numeric index. The
 * instances are stored in the "widget_{id_base}" option. Each instance is
 * represented by a {@see \Alley\WP\Widget_Control\Storage\Widget_Instance},
 *
 * @template TInstance of array
 */
class Widget implements Arrayable {
	/**
	 * Widget instances.
	 *
	 * @var Widget_Instances<TInstance>
	 */
	private Widget_Instances $instances;

	/**
	 * Create a widget storage instance from a widget ID base.
	 *
	 * @param string|class-string<WP_Widget> $id_base The widget ID base or widget class name.
	 * @return self<array<string, mixed>>
	 */
	public static function from( string $id_base ): self {
		if ( class_exists( $id_base ) ) {
			$instance = new $id_base();

			assert( $instance instanceof WP_Widget, 'Widget ID base must be a valid widget class that extends WP_Widget.' );

			$id_base = $instance->id_base;
		}

		return new self( $id_base, option( "widget_{$id_base}", [] )->array() ); // @phpstan-ignore-line argument.type
	}

	/**
	 * Constructor.
	 *
	 * @throws \InvalidArgumentException If an invalid widget instance is provided.
	 *
	 * @param string                                                       $id_base Widget ID base.
	 * @param array<int, Widget_Instance<TInstance>>|array<int, TInstance> $instances Widget instances.
	 */
	public function __construct( public readonly string $id_base, array $instances = [] ) {
		foreach ( $instances as $index => $instance ) {
			if ( is_array( $instance ) ) {
				$instances[ $index ] = new Widget_Instance(
					id_base: $id_base,
					instance: $instance,
					index: $index,
				);
			} elseif ( '_multiwidget' === $index ) { // @phpstan-ignore-line
				unset( $instances['_multiwidget'] ); // @phpstan-ignore-line
			} elseif ( ! $instance instanceof Widget_Instance ) { // @phpstan-ignore-line instanceof.alwaysTrue
				throw new \InvalidArgumentException( esc_html( 'Invalid widget instance provided for ' . $index . ' in widget ' . $this->id_base ) );
			}
		}

		$this->instances = new Widget_Instances( $instances );
	}

	/**
	 * Append a widget instance to the end of the sidebar.
	 *
	 * @param array<TInstance> $instance Widget instance to append.
	 */
	public function append( array $instance ): Widget_Instance {
		$instance = new Widget_Instance(
			id_base: $this->id_base,
			index: $this->instances->get_next_index(),
			instance: $instance,
		);

		$this->set( $instance, $instance->index );

		return $instance;
	}

	/**
	 * Insert a widget instance at a specific index.
	 *
	 * @param array<TInstance>|Widget_Instance<TInstance> $instance Widget instance to insert.
	 * @param int                                         $index    Index to insert at.
	 */
	public function set( array|Widget_Instance $instance, int $index ): static {
		if ( is_array( $instance ) ) {
			$instance = new Widget_Instance(
				id_base: $this->id_base,
				index: $index,
				instance: $instance,
			);
		}

		$this->instances[ $index ] = $instance;

		return $this->save();
	}

	/**
	 * Remove a widget instance by index.
	 *
	 * @param int $index Widget index to remove.
	 */
	public function remove( int $index ): static {
		$this->instances->remove( $index );

		return $this->save();
	}

	/**
	 * Clear all widget instances.
	 */
	public function clear(): static {
		$this->instances->clear();

		return $this->save();
	}

	/**
	 * Get a widget instance by index.
	 *
	 * @param int $index Widget index to retrieve.
	 * @return Widget_Instance<TInstance>|null The widget instance or null if not found.
	 */
	public function get( int $index ): ?Widget_Instance {
		return $this->instances[ $index ] ?? null;
	}

	/**
	 * Save the widget's instances to options.
	 *
	 * @return static
	 */
	protected function save(): static {
		$instances = collect( $this->instances )->to_array();

		// Merge arrays not using array_merge to preserve the numeric indexes.
		update_option( "widget_{$this->id_base}", $instances + [ '_multiwidget' => 1 ] );

		return $this;
	}

	/**
	 * Get the number of instances for the widget.
	 *
	 * @return int Number of instances.
	 */
	public function count(): int {
		return $this->instances->count();
	}

	/**
	 * Retrieve the instances for a widget as an array.
	 *
	 * @return array<int, TInstance>
	 */
	public function to_array(): array {
		return $this->instances->to_array(); // @phpstan-ignore-line return.type
	}

	/**
	 * Dump the widget's instances.
	 */
	public function dump(): void {
		dump( $this->instances->to_array() );
	}

	/**
	 * Dump the widget's instances and exit.
	 *
	 * @return never
	 */
	public function dd(): never {
		dd( $this->instances->to_array() );
	}
}
