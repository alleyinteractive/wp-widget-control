<?php
/**
 * Widget_Storage class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control\Storage;

use Mantle\Contracts\Support\Arrayable;

use function Mantle\Support\Helpers\collect;
use function Mantle\Support\Helpers\option;

/**
 * Representation of a widget's storage in options.
 *
 * @template TInstance of array
 */
class Widget implements Arrayable {
	/**
	 * Widget instances.
	 *
	 * @var array<int, Widget_Instance<TInstance>>
	 */
	public array $instances;

	/**
	 * Create a widget storage instance from a widget ID base.
	 *
	 * @param string $id_base The widget ID base.
	 * @return self<array<string, mixed>>
	 */
	public static function from( string $id_base ): self {
		return new self( $id_base, option( "widget_{$id_base}", [] )->array() ); // @phpstan-ignore-line argument.type
	}

	/**
	 * Constructor.
	 *
	 * @throws \InvalidArgumentException If an invalid widget instance is provided.
	 *
	 * @param string                                             $id_base Widget ID base.
	 * @param array<int, Widget_Instance<TInstance>>|array<int, TInstance> $instances Widget instances.
	 */
	public function __construct( public readonly string $id_base, array $instances = [] ) {
		foreach ( $instances as $index => $instance ) {
			if ( is_array( $instance ) ) {
				$instances[ $index ] = new Widget_Instance( $instance );
			} elseif ( '_multiwidget' === $index ) { // @phpstan-ignore-line
				unset( $instances['_multiwidget'] ); // @phpstan-ignore-line
			} elseif ( ! $instance instanceof Widget_Instance ) { // @phpstan-ignore-line instanceof.alwaysTrue
				throw new \InvalidArgumentException( esc_html( 'Invalid widget instance provided for ' . $index . ' in widget ' . $this->id_base ) );
			}
		}

		$this->instances = $instances;
	}

	/**
	 * Append a widget instance to the end of the sidebar.
	 *
	 * @param array<TInstance>|Widget_Instance<TInstance> $instance Widget instance to append.
	 * @return int The index of the appended instance.
	 */
	public function append( array|Widget_Instance $instance ): int {
		if ( is_array( $instance ) ) {
			$instance = new Widget_Instance( $instance );
		}

		$index = $this->get_next_index();

		$this->set( $instance, $index );

		return $index;
	}

	/**
	 * Insert a widget instance at a specific index.
	 *
	 * @param array<TInstance>|Widget_Instance<TInstance> $instance Widget instance to insert.
	 * @param int                                         $index    Index to insert at.
	 */
	public function set( array|Widget_Instance $instance, int $index ): void {
		$this->instances[ $index ] = is_array( $instance ) ? new Widget_Instance( $instance ) : $instance;

		ksort( $this->instances );
	}

	/**
	 * Remove a widget instance by index.
	 *
	 * @param int $index Widget index to remove.
	 */
	public function remove( int $index ): void {
		if ( isset( $this->instances[ $index ] ) ) {
			unset( $this->instances[ $index ] );
		}
	}

	/**
	 * Clear all widget instances.
	 */
	public function clear(): void {
		$this->instances = [];
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
	 * @return bool True on success, false on failure.
	 */
	public function save(): bool {
		$instances = collect( $this->instances )->to_array();

		// Merge arrays not using array_merge to preserve the numeric indexes.
		return update_option( "widget_{$this->id_base}", $instances + [ '_multiwidget' => 1 ] );
	}

	/**
	 * Get the number of instances for the widget.
	 *
	 * @return int Number of instances.
	 */
	public function count(): int {
		return count( $this->instances );
	}

	/**
	 * Retrieve the instances for a widget as an array.
	 *
	 * @return array<int, TInstance>
	 */
	public function to_array(): array {
		return $this->instances;
	}

	/**
	 * Dump the widget's instances.
	 */
	public function dump(): void {
		dump( $this->instances );
	}

	/**
	 * Dump the widget's instances and exit.
	 *
	 * @return never
	 */
	public function dd(): never {
		dd( $this->instances );
	}

	/**
	 * Get the next available index for a new widget instance.
	 *
	 * @return int Next index.
	 */
	private function get_next_index(): int {
		return collect( $this->instances )->keys()->max() + 1; // @phpstan-ignore-line binaryOp.invalid
	}
}
