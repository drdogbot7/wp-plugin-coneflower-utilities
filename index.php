<?php
/**
 * Plugin Name: Coneflower Utilities
 * Description: A WordPress plugin that does a few useful things.
 * Version: 0.1.0
 * Author: Jeremy Mullis, Coneflower Consulting
 * Author URI: https://www.coneflower.org
 * License: GPL2
 */


// Get settings or use defaults
$big_size_threshold = get_option('cfu_big_size_threshold', 1920);
$webp_quality = get_option('cfu_webp_quality', 82);

// Add settings page
add_action('admin_menu', function() {
	add_options_page(
		'Coneflower Utilities Settings',
		'Coneflower Utilities',
		'manage_options',
		'cfu-settings',
		'cfu_render_settings_page'
	);
});

add_action('admin_init', function() {
	// Detect supported formats for default
	$gd_info = function_exists('gd_info') ? gd_info() : [];
	$webp_supported = (isset($gd_info['WebP Support']) && $gd_info['WebP Support']) || (class_exists('Imagick') && in_array('WEBP', array_map('strtoupper', Imagick::queryFormats())));
	$avif_supported = (isset($gd_info['AVIF Support']) && $gd_info['AVIF Support']) || (class_exists('Imagick') && in_array('AVIF', array_map('strtoupper', Imagick::queryFormats())));
	$default_convert = 'none';
	if ($avif_supported) {
		$default_convert = 'avif';
	} elseif ($webp_supported) {
		$default_convert = 'webp';
	}
	register_setting('cfu_settings_group', 'cfu_big_size_threshold', [
		'type' => 'integer',
		'sanitize_callback' => 'absint',
		'default' => 1920
	]);
	register_setting('cfu_settings_group', 'cfu_jpeg_quality', [
		'type' => 'integer',
		'sanitize_callback' => function($v) { return min(100, max(0, intval($v))); },
		'default' => 82
	]);
	register_setting('cfu_settings_group', 'cfu_webp_quality', [
		'type' => 'integer',
		'sanitize_callback' => function($v) { return min(100, max(0, intval($v))); },
		'default' => 82
	]);
	register_setting('cfu_settings_group', 'cfu_avif_quality', [
		'type' => 'integer',
		'sanitize_callback' => function($v) { return min(100, max(0, intval($v))); },
		'default' => 82
	]);
	register_setting('cfu_settings_group', 'cfu_convert_uploads_to', [
		'type' => 'string',
		'sanitize_callback' => function($v) {
			$allowed = ['none','webp','avif'];
			return in_array($v, $allowed) ? $v : 'none';
		},
		'default' => $default_convert
	]);
	register_setting('cfu_settings_group', 'cfu_disable_medium_large_image_size', [
		'type' => 'boolean',
		'sanitize_callback' => function($v) { return (bool)$v; },
		'default' => false
	]);
	register_setting('cfu_settings_group', 'cfu_disable_1536x1536_image_size', [
		'type' => 'boolean',
		'sanitize_callback' => function($v) { return (bool)$v; },
		'default' => false
	]);
	register_setting('cfu_settings_group', 'cfu_disable_2048x2048_image_size', [
		'type' => 'boolean',
		'sanitize_callback' => function($v) { return (bool)$v; },
		'default' => false
	]);
	register_setting('cfu_settings_group', 'cfu_disable_comments', [
		'type' => 'boolean',
		'sanitize_callback' => function($v) { return (bool)$v; },
		'default' => false
	]);
	register_setting('cfu_settings_group', 'cfu_force_strong_passwords', [
		'type' => 'boolean',
		'sanitize_callback' => function($v) { return (bool)$v; },
		'default' => false
	]);
});

function cfu_render_settings_page() {
	// Check for WebP and AVIF support
	$gd_info = function_exists('gd_info') ? gd_info() : [];
	$webp_supported = (isset($gd_info['WebP Support']) && $gd_info['WebP Support']) || (class_exists('Imagick') && in_array('WEBP', array_map('strtoupper', Imagick::queryFormats())));
	$avif_supported = (isset($gd_info['AVIF Support']) && $gd_info['AVIF Support']) || (class_exists('Imagick') && in_array('AVIF', array_map('strtoupper', Imagick::queryFormats())));
	$convert_uploads_to = get_option('cfu_convert_uploads_to', 'none');
	?>
	<div class="wrap">
		<h1>Coneflower Utilities Settings</h1>
		<?php if (!$webp_supported): ?>
		<div class="notice notice-warning"><p><strong>Warning:</strong> WebP image support is not available on this server. WebP conversion and delivery will not work. Please enable WebP support in GD or ImageMagick.</p></div>
		<?php endif; ?>
		<?php if (!$avif_supported): ?>
		<div class="notice notice-warning"><p><strong>Warning:</strong> AVIF image support is not available on this server. AVIF conversion and delivery will not work. Please enable AVIF support in GD or ImageMagick.</p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields('cfu_settings_group'); ?>
			<?php do_settings_sections('cfu_settings_group'); ?>
			<h2>Image Settings</h2>
			<table class="form-table">
				<tr>
					<th scope="row">Max Image Size</th>
					<td>
						<input type="number" name="cfu_big_size_threshold" value="<?php echo esc_attr(get_option('cfu_big_size_threshold', 1920)); ?>" min="0" />
						<span class="description">Wordpress default is 2560. Images larger than this size will be scaled down on upload.</span>
					</td>
				</tr>
				<tr>
					<th scope="row">JPEG Quality (0-100)</th>
					<td><input type="number" name="cfu_jpeg_quality" value="<?php echo esc_attr(get_option('cfu_jpeg_quality', 82)); ?>" min="0" max="100" />
						<span class="description">Wordpress default is 82.</span>
					</td>
				</tr>
				<tr>
					<th scope="row">WebP Quality (0-100)</th>
					<td>
						<input type="number" name="cfu_webp_quality" value="<?php echo esc_attr(get_option('cfu_webp_quality', 82)); ?>" min="0" max="100" />
						<span class="description">Wordpress default is 82.</span>
					</td>
				</tr>
				<tr>
					<th scope="row">AVIF Quality (0-100)</th>
					<td>
						<input type="number" name="cfu_avif_quality" value="<?php echo esc_attr(get_option('cfu_avif_quality', 82)); ?>" min="0" max="100" />
						<span class="description">Wordpress default is 82.</span>
					</td>
				</tr>
				<tr>
					<th scope="row">Convert new JPEG uploads to</th>
					<td>
						<select name="cfu_convert_uploads_to">
							<option value="none" <?php selected($convert_uploads_to, 'none'); ?> >No conversion</option>
							<option value="webp" <?php selected($convert_uploads_to, 'webp'); ?> <?php disabled(!$webp_supported); ?> >WebP</option>
							<option value="avif" <?php selected($convert_uploads_to, 'avif'); ?> <?php disabled(!$avif_supported); ?> >AVIF</option>
						</select>
						<span class="description">Choose the format to convert new JPEG uploads to. Only supported formats are enabled. Selecting AVIF will also convert WebP to AVIF.</span>
					</td>
				</tr>
				<tr>
					<th scope="row">Disable <code>medium_large</code> image size.</th>
					<td><input type="checkbox" name="cfu_disable_medium_large_image_size" value="1" <?php checked(1, get_option('cfu_disable_medium_large_image_size', false), true); ?> /> Disable the 756x0 image size.</td>
				</tr>
				<tr>
					<th scope="row">Disable <code>1536x1536</code> image size.</th>
					<td><input type="checkbox" name="cfu_disable_1536x1536_image_size" value="1" <?php checked(1, get_option('cfu_disable_1536x1536_image_size', false), true); ?> /> Disable the 1536x1536 (2x medium) image size.</td>
				</tr>
				<tr>
					<th scope="row">Disable <code>2048x2048</code> image size.</th>
					<td><input type="checkbox" name="cfu_disable_2048x2048_image_size" value="1" <?php checked(1, get_option('cfu_disable_2048x2048_image_size', false), true); ?> /> Disable the 2048x2048 (2x large) image size.</td>
				</tr>
			</table>
			<h2>Comments</h2>
			<table class="form-table">
				<tr>
					<th scope="row">Disable Comments</th>
					<td><input type="checkbox" name="cfu_disable_comments" value="1" <?php checked(1, get_option('cfu_disable_comments', false), true); ?> /> Disable all comments site-wide.</td>
				</tr>
			</table>
			<h2>Security</h2>
			<table class="form-table">
				<tr>
					<th scope="row">Force Strong Passwords</th>
					<td><input type="checkbox" name="cfu_force_strong_passwords" value="1" <?php checked(1, get_option('cfu_force_strong_passwords', false), true); ?> /> Hide the option to allow weak passwords.</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// set maximum image size
function cfu_set_big_image_size_threshold($threshold) {
	return get_option('cfu_big_size_threshold', 1920);
}
add_filter('big_image_size_threshold', 'cfu_set_big_image_size_threshold');

// disable "medium_large" size
function cfu_disable_medium_large_image_size($sizes) {
	return array_diff($sizes, ['medium_large']);  // Medium Large (768 x 0)
};
if (get_option('cfu_disable_medium_large_image_size', false)) {
	add_filter('intermediate_image_sizes', 'cfu_disable_medium_large_image_size');
}

//disable 1536x1536 image size
function cfu_disable_1536x1536_image_size() {
	remove_image_size( '1536x1536' );
}
if (get_option('cfu_disable_1536x1536_image_size', false)) {
	add_action( 'init', 'cfu_disable_1536x1536_image_size' );
}

// disable 2048x2048 image size
function cfu_disable_2048x2048_image_size() {
	remove_image_size( '2048x2048' );
}
if (get_option('cfu_disable_2048x2048_image_size', false)) {
	add_action( 'init', 'cfu_disable_2048x2048_image_size' );
}

// Set image quality for each format
function cfu_set_image_quality( $quality, $mime_type ) {
	if ( 'image/jpeg' === $mime_type ) {
	 return get_option('cfu_jpeg_quality', 82);
	}
	if ( 'image/webp' === $mime_type ) {
	 return get_option('cfu_webp_quality', 82);
	}
	if ( 'image/avif' === $mime_type ) {
	 return get_option('cfu_avif_quality', 82);
	}
	return $quality;
}
add_filter( 'wp_editor_set_quality', 'cfu_set_image_quality', 10, 2 );

// Set output format for new JPEG and WebP uploads
function cfu_set_image_editor_output_format( $formats ) {
	$convert_to = get_option('cfu_convert_uploads_to', 'none');
	if ($convert_to === 'webp') {
			$formats['image/jpeg'] = 'image/webp';
	} elseif ($convert_to === 'avif') {
			$formats['image/webp'] = 'image/avif';
			$formats['image/jpeg'] = 'image/avif';
	} else {
			unset($formats['image/jpeg']); // Use default
	}
	return $formats;
}
add_filter('image_editor_output_format', 'cfu_set_image_editor_output_format');

// Disable comments
function cfu_disable_comments() {
	// Close comments on the front-end
	add_filter('comments_open', '__return_false', 20, 2);
	add_filter('pings_open', '__return_false', 20, 2);

	// Hide existing comments
	add_filter('comments_array', '__return_empty_array', 10, 2);

	// Remove comments links from admin bar
	if (is_admin_bar_showing()) {
			remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	}
}

function cfu_disable_comments_admin() {
	// Redirect any user trying to access comments page
	global $pagenow;

	if ($pagenow === 'edit-comments.php') {
			wp_redirect(admin_url());
			exit();
	}

	// Remove comments metabox from dashboard
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

	// Disable support for comments and trackbacks in post types
	foreach (get_post_types() as $post_type) {
		if (post_type_supports($post_type, 'comments')) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}

	// Remove comments page in menu
	remove_menu_page('edit-comments.php');
}

if (get_option('cfu_disable_comments', false)) {
	add_action('init', 'cfu_disable_comments');
	add_action('admin_init', 'cfu_disable_comments_admin');
}

// Force strong passwords by hiding the option to allow weak passowrds
function force_strong_passwords_login() {
	wp_add_inline_style( 'login', '.pw-weak{display:none!important}' );
}

function force_strong_passwords_admin() {
	wp_add_inline_style( 'wp-admin', '.pw-weak{display:none!important}' );
}

if (get_option('cfu_force_strong_passwords', false)) {
	add_action( 'login_enqueue_scripts', 'force_strong_passwords_login');
	add_action('admin_enqueue_scripts', 'force_strong_passwords_admin');
}