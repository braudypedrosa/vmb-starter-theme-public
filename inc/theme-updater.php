<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_stylesheet_directory() . '/vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Boot the VMB GitHub updater once per request.
 *
 * @return \YahnisElsts\PluginUpdateChecker\v5\Vcs\ThemeUpdateChecker
 */
function vmb_get_theme_update_checker() {
	static $update_checker = null;

	if ( null !== $update_checker ) {
		return $update_checker;
	}

	$update_checker = PucFactory::buildUpdateChecker(
		'https://github.com/braudypedrosa/vmb-starter-theme-public/',
		get_stylesheet_directory() . '/style.css',
		'vmb-starter-theme'
	);

	$update_checker->setBranch( 'main' );
	$update_checker->getVcsApi()->enableReleaseAssets( '/(^|\/)vmb-starter-theme\.zip($|[?&#])/i' );

	return $update_checker;
}

add_action( 'after_setup_theme', 'vmb_get_theme_update_checker', 5 );
