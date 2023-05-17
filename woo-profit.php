<?php
/**
 * Plugin Name: Woo Profit
 * Description: Учет чистой прибыли с продажи товара, вы будете знать чистую прибыль с продажи товара. Сформируйте отчет за период (день, неделя, месяц, год).
 * Version: 1.0.0
 * Author: Upwoo - plugins for Woocommerce
 * Author URI: store.upwoo.ru/woocommerce/woo-profit
 */

if (!defined('ABSPATH')) {
    die;
}

require_once(plugin_dir_path(__FILE__) . 'inc/class.woo_profit.php');

function sebestoimost_woocommerce_init() {
    $sebestoimost_plugin = new Woo_Profit();
    $sebestoimost_plugin->init();
}
add_action('plugins_loaded', 'sebestoimost_woocommerce_init');
