<?php
/**
 * Widget_Instance class file
 *
 * @package wp-widget-control
 */

namespace Alley\WP\Widget_Control\Storage;

use ArrayAccess;
use Mantle\Contracts\Support\Arrayable;

/**
 * Representation of a single widget instance.
 *
 * @template TInstance of array
 * @implements ArrayAccess<key-of<TInstance>, value-of<TInstance>>
 */
class Widget_Instance implements Arrayable, ArrayAccess {
	/**
	 * Constructor.
	 *
	 * @param array $instance Widget instance.
	 * @phpstan-param TInstance $instance
	 */
	public function __construct( public array $instance ) {}

	/**
	 * Whether an offset exists.
	 *
	 * @param mixed $offset Offset to check.
	 * @return bool
	 */
	public function offsetExists( mixed $offset ): bool {
		return isset( $this->instance[ $offset ] );
	}

	/**
	 * Offset to retrieve.
	 *
	 * @param mixed $offset Offset to retrieve.
	 * @return mixed
	 */
	public function offsetGet( mixed $offset ): mixed {
		return $this->instance[ $offset ] ?? null;
	}

	/**
	 * Assign a value to the specified offset.
	 *
	 * @param mixed $offset Offset to assign.
	 * @param mixed $value Value to assign.
	 */
	public function offsetSet( mixed $offset, mixed $value ): void {
		$this->instance[ $offset ] = $value; // @phpstan-ignore-line assign.propertyType
	}

	/**
	 * Unset an offset.
	 *
	 * @param mixed $offset Offset to unset.
	 */
	public function offsetUnset( mixed $offset ): void {
		unset( $this->instance[ $offset ] );
	}

	/**
	 * Convert the widget instance to an array.
	 *
	 * @return TInstance The widget instance as an array.
	 */
	public function to_array(): array {
		return $this->instance;
	}
}
