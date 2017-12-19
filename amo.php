<?php
/*
Plugin Name: Sau Amo
Plugin URI: http://a-sau.ru
Description: AmoCRM for sau_sender.
Version: 1.0
Author: AkinaySau
Author URI: http://a-sau.ru
*/

use Sau\WP\Plugin\Sender\Amo\Amo;

include_once 'vendor/autoload.php';

Amo::init();