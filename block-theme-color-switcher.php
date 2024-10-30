<?php
/**
 * Plugin Name: Block Theme Color Switcher
 * Plugin URI:  https://github.com/Arkenon/block-theme-color-switcher
 * Description: This plugin allows users to choose a color palette for the Block Theme from the frontend.
 * Version:     1.0.1
 * Author:      Kadim Gültekin
 * Author URI:  https://kadimgultekin.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: block-theme-color-switcher
 */

// Prevent direct access to the file.
defined( 'ABSPATH' ) || exit();

// Define constants
$plugin_data = get_file_data( __FILE__, array( 'version' => 'Version' ) );
define( 'BLOCK_THEME_COLOR_SWITCHER_VERSION', $plugin_data['version'] );
define( 'BLOCK_THEME_COLOR_SWITCHER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BLOCK_THEME_COLOR_SWITCHER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Enqueue JavaScript for color switcher.
 */
if ( ! function_exists( 'block_theme_color_switcher_enqueue_script' ) ) {

	function block_theme_color_switcher_enqueue_script() {

		wp_enqueue_script(
			'block-theme-color-switcher',
			BLOCK_THEME_COLOR_SWITCHER_PLUGIN_URL . 'js/block-theme-color-switcher.js',
			'jquery',
			BLOCK_THEME_COLOR_SWITCHER_VERSION,
			true // Load in footer.
		);

		$palettes      = block_theme_color_switcher_merge_color_palettes();
		$palettes_json = wp_json_encode( $palettes );
		$inline_script_for_palette = "const palettes = " . $palettes_json . ";";
		wp_add_inline_script( 'block-theme-color-switcher', $inline_script_for_palette, 'before' );

		$default_colors  = block_theme_color_switcher_get_theme_color_palette();
		$default_colors_json = wp_json_encode( $default_colors );
		$inline_script_for_default_colors = "const defaultColors = " . $default_colors_json . ";";
		wp_add_inline_script( 'block-theme-color-switcher', $inline_script_for_default_colors, 'before' );

		wp_enqueue_style(
			'block-theme-color-switcher',
			BLOCK_THEME_COLOR_SWITCHER_PLUGIN_URL . 'css/block-theme-color-switcher.css',
			array(),
			BLOCK_THEME_COLOR_SWITCHER_VERSION
		);

	}

	add_action( 'wp_enqueue_scripts', 'block_theme_color_switcher_enqueue_script' );
}

if ( ! function_exists( 'block_theme_color_switcher_off_canvass_menu' ) ) {

	function block_theme_color_switcher_off_canvass_menu() {
		?>

        <!-- Off-Canvas Menu -->
        <div id="colorSwitcherMenu" class="off-canvas-menu">
            <div style="margin-bottom: 75px;margin-top: 75px;">
                <h6 style="color:black;padding-left: 15px;">
                    <?php echo esc_html_x( 'Color Palettes', 'color_palette_text', 'block-theme-color-switcher' ) ?>
                    <a style="font-size: 10px; cursor: pointer;color:darkgray;"
                       onclick="removeSelectedPaletteData()">
                        (<?php echo esc_html_x( 'Reset', 'reset_text', 'block-theme-color-switcher' ) ?>)
                    </a>
                </h6>
                <div class="palette-container">
                </div>
            </div>
        </div>

        <!-- Off-Canvas Menu Button -->
        <div id="colorPaletteSelector">
            <div class="wp-block-button">
                <a class="wp-block-button__link wp-element-button off-canvas-button" onclick="toggleColorSwitcherMenu()">
                    ⛶ <span style="display: none;" id="switcher-button-text"><?php echo esc_html_x( 'Palette', 'palette_button_text', 'block-theme-color-switcher' ) ?></span>
                </a>
            </div>
        </div>

		<?php
	}

	add_action( 'wp_footer', 'block_theme_color_switcher_off_canvass_menu' );
}


if ( ! function_exists( 'block_theme_color_switcher_get_theme_color_palette' ) ) {

	function block_theme_color_switcher_get_theme_color_palette(): array {

		$theme_json_path = get_template_directory() . '/theme.json';

		if ( file_exists( $theme_json_path ) ) {

			global $wp_filesystem;
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();

			$theme_json = $wp_filesystem->get_contents($theme_json_path);
			$theme_data = json_decode( $theme_json, true );

			$palette = $theme_data['settings']['color']['palette'] ?? [];

			$css_variables = [];
			foreach ( $palette as $color ) {
				$css_var_name                   = '--wp--preset--color--' . $color['slug'];
				$css_variables[ $css_var_name ] = $color['color'];
			}

			return $css_variables;
		}

		return [];
	}

}

if ( ! function_exists( 'block_theme_color_switcher_get_additional_color_palettes' ) ) {

	function block_theme_color_switcher_get_additional_color_palettes(): array {

		$styles_dir = get_template_directory() . '/styles';
		$palettes   = [];

		global $wp_filesystem;
		require_once (ABSPATH . '/wp-admin/includes/file.php');
		WP_Filesystem();

		if ( is_dir( $styles_dir ) ) {
			$json_files = glob( $styles_dir . '/*.json' );

			foreach ( $json_files as $file ) {

				$content       = $wp_filesystem->get_contents($file);
				$data          = json_decode( $content, true );
				$palette_title = $data['title'];

				if ( isset( $data['settings']['color']['palette'] ) ) {

					foreach ( $data['settings']['color']['palette'] as $color ) {
						$css_var_name                                = '--wp--preset--color--' . $color['slug'];
						$palettes[ $palette_title ][ $css_var_name ] = $color['color'];
					}

				}
			}
		}

		return $palettes;
	}

}

if ( ! function_exists( 'block_theme_color_switcher_merge_color_palettes' ) ) {

	function block_theme_color_switcher_merge_color_palettes(): array {

		$theme_palette       = block_theme_color_switcher_get_theme_color_palette();
		$additional_palettes = block_theme_color_switcher_get_additional_color_palettes();

		return array_merge( [ "Default" => $theme_palette, ], $additional_palettes );

	}

}

