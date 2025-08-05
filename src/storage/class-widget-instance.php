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
 * Widget instances are stored in a single "widget_{id_base}" option (see
 * {@see \Alley\WP\Widget_Control\Storage\Widget}).
 *
 * @template TInstance of array = array<string, mixed>
 * @implements ArrayAccess<key-of<TInstance>, value-of<TInstance>>
 */
class Widget_Instance implements Arrayable, ArrayAccess {
	/**
	 * Constructor.
	 *
	 * @param string   $id_base Widget ID base.
	 * @param array    $instance Widget instance.
	 * @phpstan-param TInstance $instance
	 * @param int|null $index Optional index of the widget instance.
	 */
	public function __construct( public readonly string $id_base, public array $instance, public ?int $index = null ) {}

	/**
	 * Whether an offset exists within an instance.
	 *
	 * @param mixed $offset Offset to check.
	 * @return bool
	 */
	public function offsetExists( mixed $offset ): bool {
		return isset( $this->instance[ $offset ] );
	}

	/**
	 * Offset to retrieve from the widget instance.
	 *
	 * @param mixed $offset Offset to retrieve.
	 * @return mixed
	 */
	public function offsetGet( mixed $offset ): mixed {
		return $this->instance[ $offset ] ?? null;
	}

	/**
	 * Assign a value to the specified offset in the widget instance.
	 *
	 * @param mixed $offset Offset to assign.
	 * @param mixed $value Value to assign.
	 */
	public function offsetSet( mixed $offset, mixed $value ): void {
		$this->instance[ $offset ] = $value; // @phpstan-ignore-line assign.propertyType
	}

	/**
	 * Unset an offset in the widget instance.
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

	/**
	 * Retrieve the widget ID that is stored inside a sidebar in the 'sidebars_widgets' option.
	 *
	 * @return string
	 */
	public function get_widget_id(): string {
		return $this->id_base . '-' . ( $this->index ?? '' );
	}
}
