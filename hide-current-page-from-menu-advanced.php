<?php
/**
 * Plugin Name: Hide Current Page From Menu – Advanced
 * Plugin URI:  https://wordpress.org/plugins/hide-current-page-from-menu-advanced/
 * Description: Hides the current page’s menu item, with exclusions by user role, menu location, and specific menu item IDs. Secure settings and lightweight performance.
 * Version:     2.0.0
 * Author:      cradlesn
 * Author URI:  https://profiles.wordpress.org/cradlean/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hide-current-page-from-menu-advanced
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8.3
 * Requires PHP: 7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes the current page’s menu item, respecting exclusions.
 *
 * @param array  $items Menu items.
 * @param object $args  Menu arguments.
 * @return array Filtered menu items.
 */
add_filter( 'wp_nav_menu_objects', 'hcpm_remove_current_menu_item', 10, 2 );
function hcpm_remove_current_menu_item( $items, $args ) {
	$current_page_id = (int) get_queried_object_id();
	if ( ! $current_page_id ) {
		return $items;
	}

	$current_user = wp_get_current_user();

	$exclude_roles = (array) get_option( 'hcpm_exclude_roles', array() );
	$exclude_menus = (array) get_option( 'hcpm_exclude_menus', array() );

	$exclude_items_opt = get_option( 'hcpm_exclude_items', array() );
	if ( is_string( $exclude_items_opt ) ) {
		$exclude_items = array_filter( array_map( 'intval', explode( ',', $exclude_items_opt ) ) );
	} else {
		$exclude_items = array_map( 'intval', (array) $exclude_items_opt );
	}

	foreach ( $items as $key => $item ) {
		if ( (int) $item->object_id !== $current_page_id || $item->object !== 'page' ) {
			continue;
		}

		if ( in_array( (int) $item->ID, $exclude_items, true ) ) {
			continue;
		}

		if ( ! empty( array_intersect( $exclude_roles, (array) $current_user->roles ) ) ) {
			continue;
		}

		if ( isset( $args->theme_location ) && in_array( $args->theme_location, $exclude_menus, true ) ) {
			continue;
		}

		unset( $items[ $key ] );
	}

	return $items;
}

/**
 * Adds the settings page under Settings → Hide Menu Item.
 */
add_action( 'admin_menu', 'hcpm_add_admin_menu' );
function hcpm_add_admin_menu() {
	add_options_page(
		__( 'Hide Current Page Settings', 'hide-current-page-from-menu-advanced' ),
		__( 'Hide Menu Item', 'hide-current-page-from-menu-advanced' ),
		'manage_options',
		'hcpm-menu-settings',
		'hcpm_settings_page'
	);
}

/**
 * Registers plugin options with sanitization callbacks.
 */
add_action( 'admin_init', 'hcpm_register_settings' );
function hcpm_register_settings() {

	register_setting(
		'hcpm_settings_group',
		'hcpm_exclude_roles',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'hcpm_sanitize_text_array',
			'default'           => array(),
		)
	);

	register_setting(
		'hcpm_settings_group',
		'hcpm_exclude_menus',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'hcpm_sanitize_text_array',
			'default'           => array(),
		)
	);

	register_setting(
		'hcpm_settings_group',
		'hcpm_exclude_items',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'hcpm_sanitize_item_ids',
			'default'           => array(),
		)
	);
}

/**
 * Sanitizes an array of text inputs.
 */
function hcpm_sanitize_text_array( $input ) {
	if ( is_string( $input ) ) {
		$input = array( $input );
	}
	$input = is_array( $input ) ? $input : array();
	$out   = array();

	foreach ( $input as $v ) {
		$out[] = sanitize_text_field( (string) $v );
	}

	return array_values( array_unique( $out ) );
}

/**
 * Sanitizes comma-separated or array-based item IDs.
 */
function hcpm_sanitize_item_ids( $input ) {
	if ( is_string( $input ) ) {
		$parts = array_map( 'trim', explode( ',', $input ) );
	} elseif ( is_array( $input ) ) {
		$parts = $input;
	} else {
		$parts = array();
	}

	$ints = array();
	foreach ( $parts as $p ) {
		$p = (int) $p;
		if ( $p > 0 ) {
			$ints[] = $p;
		}
	}

	return array_values( array_unique( $ints ) );
}

/**
 * Renders the admin settings page.
 */
function hcpm_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$exclude_roles      = get_option( 'hcpm_exclude_roles', array() );
	$exclude_menus      = get_option( 'hcpm_exclude_menus', array() );
	$exclude_items      = get_option( 'hcpm_exclude_items', array() );
	$exclude_items_csv  = is_array( $exclude_items ) ? implode( ',', array_map( 'intval', $exclude_items ) ) : '';
	$roles              = hcpm_get_all_roles();
	$menu_locations     = get_registered_nav_menus();
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Hide Current Page From Menu – Settings', 'hide-current-page-from-menu-advanced' ); ?></h1>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'hcpm_settings_group' );
			do_settings_sections( 'hcpm_settings_group' );
			?>

			<h2><?php esc_html_e( 'Exclude Specific Menu Items', 'hide-current-page-from-menu-advanced' ); ?></h2>
			<p><?php esc_html_e( 'Enter menu item IDs (comma-separated) that should never be hidden.', 'hide-current-page-from-menu-advanced' ); ?></p>
			<input type="text" name="hcpm_exclude_items" value="<?php echo esc_attr( $exclude_items_csv ); ?>" class="regular-text" />

			<hr>

			<h2><?php esc_html_e( 'Exclude by User Role', 'hide-current-page-from-menu-advanced' ); ?></h2>
			<p><?php esc_html_e( 'Users with these roles will not have the current page hidden.', 'hide-current-page-from-menu-advanced' ); ?></p>
			<?php foreach ( $roles as $role_key => $label ) : ?>
				<label style="display:block;margin:2px 0;">
					<input type="checkbox" name="hcpm_exclude_roles[]" value="<?php echo esc_attr( $role_key ); ?>"
						<?php checked( in_array( $role_key, (array) $exclude_roles, true ) ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>

			<hr>

			<h2><?php esc_html_e( 'Exclude by Menu Location', 'hide-current-page-from-menu-advanced' ); ?></h2>
			<p><?php esc_html_e( 'Selected menu locations will not hide the current page.', 'hide-current-page-from-menu-advanced' ); ?></p>
			<?php foreach ( $menu_locations as $location => $description ) : ?>
				<label style="display:block;margin:2px 0;">
					<input type="checkbox" name="hcpm_exclude_menus[]" value="<?php echo esc_attr( $location ); ?>"
						<?php checked( in_array( $location, (array) $exclude_menus, true ) ); ?> />
					<?php echo esc_html( $description ? $description : $location ); ?>
				</label>
			<?php endforeach; ?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Returns all WordPress roles as key => label.
 *
 * @return array
 */
function hcpm_get_all_roles() {
	if ( ! function_exists( 'wp_roles' ) ) {
		return array();
	}
	$wp_roles = wp_roles();
	$out      = array();

	foreach ( $wp_roles->roles as $key => $role ) {
		$out[ $key ] = isset( $role['name'] ) ? $role['name'] : ucfirst( $key );
	}

	return $out;
}
