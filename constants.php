<?php

global $wpdb;

define( 'KMCFMF_URL', plugin_dir_url( __FILE__ ) );
define( 'KMCFMF_DIR', plugin_dir_path( __FILE__ ) );
const KMCFMF_JS_URL  = KMCFMF_URL . 'js';
const KMCFMF_CSS_URL = KMCFMF_URL . 'css';


const KMCFMF_LIB_DIR        = KMCFMF_DIR . 'lib';
const KMCFMF_CORE_DIR       = KMCFMF_DIR . 'core';
const KMCFMF_MODELS_DIR     = KMCFMF_DIR . 'models';
const KMCFMF_MIGRATIONS_DIR = KMCFMF_DIR . 'migrations';
const KMCFMF_MODULE_DIR     = KMCFMF_DIR . 'modules';
const KMCFMF_ASSET_URL      = KMCFMF_URL . 'assets';
const KMCFMF_IMAGES_URL     = KMCFMF_ASSET_URL . '/images';

const KMCFMF_TEXT_DOMAIN = 'cf7-message-filter';
define( 'KMCFMF_TABLE_PREFIX', $wpdb->prefix . 'kmcf7_' );

