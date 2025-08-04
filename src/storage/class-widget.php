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
	 * Create a Widget instance from a location.
	 *
	 * @param string $location The sidebar location.
	 * @return self
	 */
	public static function from( string $id_base ): self {
		return new self( $id_base, option( "widget_{$id_base}", [] )->array() );
	}

	/**
	 * Constructor.
	 *
	 * @param string           $id_base Widget ID base.
	 * @param array<TInstance> $instances Widget instances.
	 */
	public function __construct( public readonly string $id_base, public array $instances = [] ) {
		unset( $this->instances['_multiwidget'] );
	}

	/**
	 * Append a widget instance to the end of the sidebar.
	 *
	 * @param TInstance $instance Widget instance to append.
	 * @param int|null  $index    Optional index to insert at, defaults to the next available index.
	 * @return int The index of the appended instance.
	 */
	public function append( array $instance ): int {
		$index = $this->get_next_index();

		$this->set( $instance, $index );

		return $index;
	}

	/**
	 * Insert a widget instance at a specific index.
	 *
	 * @param TInstance $instance Widget instance to insert.
	 * @param int       $index    Index to insert at.
	 */
	public function set( array $instance, int $index ): void {
		$this->instances[ $index ] = $instance;

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
	 * Get a widget instance by index.
	 *
	 * @param int $index Widget index to retrieve.
	 * @return TInstance|null The widget instance or null if not found.
	 */
	public function get( int $index ): ?array {
		return $this->instances[ $index ] ?? null;
	}

	/**
	 * Save the widget's instances to options.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save(): bool {
		// Merge arrays not using array_merge to preserve the numeric indexes.
		return update_option( "widget_{$this->id_base}", $this->instances + [ '_multiwidget' => 1 ] );
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
	 * @return array<TInstance>
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
	protected function get_next_index(): int {
		return collect( $this->instances )->keys()->max() + 1;
	}
}
