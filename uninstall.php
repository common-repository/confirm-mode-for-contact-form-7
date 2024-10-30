<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) die;

require_once __DIR__ . '/inc/ConfirmModeForCF7.php';

ConfirmModeForCF7::uninstall();
