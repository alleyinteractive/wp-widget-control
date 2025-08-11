# WP Widget Control

[![Testing Suite](https://github.com/alleyinteractive/wp-widget-control/actions/workflows/all-pr-tests.yml/badge.svg)](https://github.com/alleyinteractive/wp-widget-control/actions/workflows/all-pr-tests.yml)

Setup and curate WordPress widgets with code.

## Installation

You can install the package via composer:

```bash
composer require alleyinteractive/wp-widget-control
```

## Usage

WP Widget Control lets you programmatically manage WordPress sidebars and
widgets. This can be useful for setting up default widget configurations,
managing widget curation with code for end to end testing, or simply
maintaining widget state in a more structured way.

When you have to manage widgets with code, WordPress doesn't provide a great way
to do that. WP Widget Control fills this gap by providing a simple API for
managing widgets programmatically.

Here are some common usage patterns:

### Retrieve a Sidebar

```php
use Alley\WP\Widget_Control\Storage\Sidebar;

$sidebar = Sidebar::from( 'sidebar-1' );
```

### Append a Widget to a Sidebar

```php
use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget;

$sidebar = Sidebar::from( 'sidebar-1' );

// Append a widget by its ID:
$sidebar->append( 'example_widget-4' );

// You can create a new widget instance from the base ID and append it to the sidebar:
$sidebar->append(
  Widget::from( 'example_widget' )->append( [ 'content' => 'Hello, World!' ] ),
);

// Or by referencing the widget's class:
$sidebar->append(
  Widget::from( \My\Custom\ExampleWidget::class )->append( [ 'content' => 'Hello, World!' ] ),
);
```

### Insert Widgets Before or After Another Widget

```php
use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget;

$sidebar = Sidebar::from( 'sidebar-1' );

// Insert a widget "block-99" before "block-2":
$sidebar->insert_before( widget: 'block-99', before_widget_id: 'block-2' );

// Insert a widget "example_widget-6" after "example_widget-2":
$sidebar->insert_after( widget: 'example_widget-6', after_widget_id: 'example_widget-2' );

// Also supports inserting a widget instance directly.
// Inside a new widget instance before "example_widget-2":
$sidebar->insert_before(
	widget: Widget::from( 'example_widget' )->append( [ 'content' => 'Hello, World!' ] ),
	before_widget_id: 'example_widget-2',
);
```

### Remove a Widget by ID or Index

```php
use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget_Instance;

$sidebar = Sidebar::from( 'sidebar-1' );

// Remove a widget by its ID:
$sidebar->remove( 'block-2' );

// Remove a widget by its index:
$sidebar->remove_index( 2 );
```

### Set All Widgets in a Sidebar

```php
use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget;

$sidebar = Sidebar::from( 'sidebar-1' );

$sidebar->set( [
	// Use existing widget instances (they follow a widget_base-ID pattern).
	'nav_menu-1',
	'block-2',
	'example_widget-2',

	// You can also create a new widget instance and append it to the sidebar.
	Widget::from( 'example_widget' )->append( [ 'content' => 'Hello, World!' ] ),
	Widget::from( \My\Custom\ExampleWidget::class )->append( [ 'content' => 'Hello, World!' ] ),
] );
```

### Filter Widgets in a Sidebar

Remove a specific widget from a sidebar while keeping others:

```php
use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget;
use Alley\WP\Widget_Control\Storage\Widget_Instance;

$sidebar = Sidebar::from( 'sidebar-1' );

// Remove all widgets whose ID contains 'example_widget'.
$sidebar->filter_by_id( function( string $widget_id ) {
	return ! str_contains( $widget_id, 'example_widget' );
} );

// Keep only widgets of a certain type (using Widget_Instance).
$sidebar->filter( function( Widget_Instance $widget ) {
	return $widget->id_base === 'example_widget';
} );
```

### Clear All Widgets from a Sidebar

```php
use Alley\WP\Widget_Control\Storage\Sidebar;

$sidebar = Sidebar::from( 'sidebar-1' );

$sidebar->clear();
```

### Full Sidebar Curation Example

In this example, we will set the sidebar to contain an instance of
`ExampleWidget` and another instance of the `block` widget:

```php
use Alley\WP\Widget_Control\Storage\Sidebar;
use Alley\WP\Widget_Control\Storage\Widget;
use Alley\WP\Widget_Control\Tests\ExampleWidget;

$sidebar->set( [
	Widget::from( ExampleWidget::class )->append( [ 'content' => 'Hello, World! 1' ] ),
	Widget::from( 'block' )->append( [ 'content' => 'Hello, World! 2' ] ),
] );
```

These will be the only two widgets in the sidebar.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.co/careers/).

- [Sean Fisher](https://github.com/srtfisher)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
