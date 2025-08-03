=== Menu À la Carte Plugin ===
Contributors: Erik Kokinda
Tags: restaurant, menu, à la carte, custom post type, taxonomy
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 0.9.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin to manage à la carte menus via a custom post type (“menu_alacarte”) and a hierarchical taxonomy (“menu_category”).  
Supports subcategories (child terms) and sub‐subcategories for “Nealkoholické nápoje” and “Vínna karta” with hover or tap‐revealed dropdowns.  
Responsive: images stack under text on mobile.

== Installation ==
1. Upload the `menu-alacarte-plugin` folder to `/wp-content/plugins/`.  
2. Activate the plugin through the “Plugins” menu in WordPress.  
3. Create categories via “Dishes → Menu Categories”:
   - Top‐level: Antipasti, Niečo k vínu, Zuppe, Insalate, Risotto, Pasta, Piatti di carne, Piatti di pesce, Contorno, Dezerty, Pinsa, Detské menu, Nealkoholické nápoje, Miešané nápoje, Vínna karta  
   - Under Nealkoholické nápoje → čaj, kava, fľaškové nealko, domáce Limonády  
   - Under Vínna karta → Rozlievané vína, šumivé vína, biele vína, červené vína, ružové vína  
   - Under Rozlievané vína → Šumivé, Biele, Červené, Ružové  
4. Add “À la Carte” items via “Add New Dish,” assign them to categories, fill in the meta‐fields (names, measurement, price, allergens).  
5. Place the shortcode `[menu_alacarte]` on any page or post to render the menu.

== Changelog ==
= 0.9.9 =
* Added hierarchical submenus for “Nealkoholické nápoje” (4 children) and “Vínna karta” (children + grandchildren).  
* Updated CSS/JS to support hover‐and‐touch dropdowns.  
* Bumped JPEG quality to 100.  
* Minor version bump, responsive fixes.

= 0.9.8 =
* Initial public release.