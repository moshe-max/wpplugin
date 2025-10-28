=== Hide Current Page From Menu – Advanced ===
Contributors: cradlean
Tags: menu, navigation, hide current page, hide menu item, user roles, custom menus, page visibility
Requires at least: 5.0
Tested up to: 6.8.3
Stable tag: 1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Hide the current page’s menu item dynamically with advanced options for user roles, menu locations, and post types. Clean, fast, and fully theme-safe.

== Description ==

**Hide Current Page From Menu – Advanced** is a lightweight WordPress plugin that automatically hides the menu item corresponding to the current page, post, or custom post type.  
It works dynamically — no need to edit your menus manually — and supports nested menu structures, specific menu locations, and role-based visibility.

**Perfect for:**  
- Hiding the “current” page to simplify navigation  
- Restricting certain menus for logged-in or logged-out users  
- Keeping dynamic menus clean without coding

### Features
* Automatically hides the current page’s menu item.
* Supports nested (hierarchical) menus and ancestor cleanup.
* Works with pages, posts, and custom post types.
* Role-based visibility controls.
* Menu location targeting.
* Lightweight and optimized for performance — no front-end JavaScript required.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin from the **Plugins** menu in WordPress.
3. Go to **Settings → Menu Visibility** to configure exclusions by role, menu, or post type.

== Screenshots ==

1. The settings page showing role and menu options.

== Changelog ==

= 1.0 =
* Initial release.
* Hides current page menu items with role, menu, and post type exclusions.
* Supports nested menus with automatic ancestor cleanup.
* Fully compatible with pages, posts, and custom post types.

== Upgrade Notice ==

= 1.0 =
First stable release with full support for roles, menus, post types, and nested menu structures.

== Frequently Asked Questions ==

= How do I configure the plugin? =
Go to **Settings → Menu Visibility** and choose which menu items, user roles, or locations to exclude.

= Does this plugin support custom post types? =
Yes, it supports pages, posts, and any registered custom post type that appears in menus.

= Will it affect my theme or menu design? =
No. The plugin works only on the PHP menu output layer using standard WordPress filters — it’s fully theme-safe and doesn’t modify your actual menu data.
