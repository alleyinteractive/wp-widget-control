<?php
/**
 * Widget_Instances class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control\Storage;

use ArrayAccess;
use Mantle\Contracts\Support\Arrayable;

use function Mantle\Support\Helpers\collect;

/**
 * Representation of a collection of widget instances.
 *
 * @template TInstance of array
 */
class Widget_Instances implements Arrayable, ArrayAccess {
	/**
	 * Constructor.
	 *
	 * @param array<int, Widget_Instance<TInstance>> $instances Widget instances.
	 */
	public function __construct( protected array $instances = [] ) {
		foreach ( $instances as $instance ) {
			assert( $instance instanceof Widget_Instance, 'All instances must be of type ' . Widget_Instance::class . '.' );
		}
	}

	/**
	 * Whether an offset exists within an instance.
	 *
	 * @param mixed $offset Offset to check.
	 * @return bool
	 */
	public function offsetExists( mixed $offset ): bool {
		return isset( $this->instances[ $offset ] );
	}

	/**
	 * Offset to retrieve from the widget instance.
	 *
	 * @param mixed $offset Offset to retrieve.
	 * @return Widget_Instance<TInstance>|null
	 */
	public function offsetGet( mixed $offset ): ?Widget_Instance {
		return $this->instances[ $offset ] ?? null;
	}

	/**
	 * Assign a value to the specified offset in the widget instance.
	 *
	 * @param mixed $offset Offset to assign.
	 * @param mixed $value Value to assign.
	 *
	 * @phpstan-param Widget_Instance<TInstance> $offset
	 */
	public function offsetSet( mixed $offset, mixed $value ): void {
		assert( $value instanceof Widget_Instance, 'Value must be an instance of Widget_Instance.' );

		$this->instances[ $offset ] = $value; // @phpstan-ignore-line assign.propertyType
	}

	/**
	 * Unset an offset in the widget instance.
	 *
	 * @param mixed $offset Offset to unset.
	 */
	public function offsetUnset( mixed $offset ): void {
		unset( $this->instances[ $offset ] );
	}

	/**
	 * Append a widget instance to the collection.
	 *
	 * @param Widget_Instance $instance Widget instance to append.
	 * @return int The index of the newly added instance.
	 */
	public function append( Widget_Instance $instance ): int {
		$this->instances[] = $instance;

		return array_key_last( $this->instances );
	}

	/**
	 * Clear all widget instances.
	 */
	public function clear(): void {
		$this->instances = [];
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
	 * Get the number of widget instances.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->instances );
	}

	/**
	 * Get the widget instance by index.
	 *
	 * @return array<int, Widget_Instance<TInstance>>
	 */
	public function to_array(): array {
		return $this->instances;
	}

	/**
	 * Get the next available index for a new widget instance.
	 *
	 * @return int
	 */
	public function get_next_index(): int {
		return collect( $this->instances )->keys()->max() + 1; // @phpstan-ignore-line binaryOp.invalid
	}
}
