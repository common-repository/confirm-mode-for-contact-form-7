<?php
/**
 * Plugin Name:       Confirm Mode for Contact Form 7
 * Text Domain:       confirm-mode-for-contact-form-7
 * Plugin URI:        https://cmiz.github.io/confirm-mode-for-contact-form-7/
 * Description:       This is an add-on to introduce confirmation mode into Contact Form 7.
 * Version:           1.0.2
 * Requires at least: 5.9
 * Author:            cmiz
 * Author URI:        https://profiles.wordpress.org/cmiz/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) die;

require_once __DIR__ . '/inc/ConfirmModeForCF7.php';

ConfirmModeForCF7::get_instance();
