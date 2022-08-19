<?php

global $wpdb;

define( 'KMCF7MS_URL', plugin_dir_url( __FILE__ ) );
define( 'KMCF7MS_DIR', plugin_dir_path( __FILE__ ) );
const KMCF7MS_JS_URL  = KMCF7MS_URL . 'js';
const KMCF7MS_CSS_URL = KMCF7MS_URL . 'css';


const KMCF7MS_LIB_DIR        = KMCF7MS_DIR . 'lib';
const KMCF7MS_CORE_DIR       = KMCF7MS_DIR . 'core';
const KMCF7MS_MODELS_DIR     = KMCF7MS_DIR . 'models';
const KMCF7MS_MIGRATIONS_DIR = KMCF7MS_DIR . 'migrations';
const KMCF7MS_MODULE_DIR     = KMCF7MS_DIR . 'modules';
const KMCF7MS_AJAX_DIR       = KMCF7MS_DIR . 'ajax';

const KMCF7MS_TEXT_DOMAIN = 'cf7-message-filter';
define( 'KMCF7MS_TABLE_PREFIX', $wpdb->prefix . 'kmcf7_' );

