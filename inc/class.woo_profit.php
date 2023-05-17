<?php
if (!defined('ABSPATH')) {
    exit;
}

class Woo_Profit {
    public function __construct() {
        add_action('woocommerce_product_options_pricing', array($this, 'add_cost_price_field'));
        add_action('woocommerce_process_product_meta', array($this, 'save_cost_price_field'));
        add_filter('manage_edit-product_columns', array($this, 'add_custom_columns'));
        add_action('manage_product_posts_custom_column', array($this, 'display_custom_columns'), 10, 2);
        add_filter('manage_edit-product_sortable_columns', array($this, 'make_columns_sortable'));
        add_action('add_meta_boxes', array($this, 'add_cost_price_field_to_order_meta_box'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_cost_price_field_in_order'));
        add_filter('manage_edit-shop_order_columns', array($this, 'add_custom_columns_to_orders'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_custom_columns_in_orders'), 10, 2);
        add_filter('manage_edit-shop_order_sortable_columns', array($this, 'make_columns_sortable_in_orders'));
    }

    public function init() {
        // Дополнительные действия и фильтры, связанные с плагином
    }

    public function add_cost_price_field() {
        woocommerce_wp_text_input(
            array(
                'id' => '_cost_price',
                'label' => 'Себестоимость',
                'data_type' => 'price',
            )
        );
    }

    public function save_cost_price_field($post_id) {
        $cost_price = isset($_POST['_cost_price']) ? sanitize_text_field($_POST['_cost_price']) : '';
        update_post_meta($post_id, '_cost_price', $cost_price);
    }

    public function add_custom_columns($columns) {
        $columns['cost_price'] = 'Себестоимость';
        $columns['profit'] = 'Прибыль';
        return $columns;
    }

    public function display_custom_columns($column, $post_id) {
        if ($column === 'cost_price') {
            $cost_price = get_post_meta($post_id, '_cost_price', true);
            echo wc_price($cost_price);
        } elseif ($column === 'profit') {
            $cost_price = get_post_meta($post_id, '_cost_price', true);
            $regular_price = get_post_meta($post_id, '_regular_price', true);
            $sale_price = get_post_meta($post_id, '_sale_price', true);

            if ($sale_price && $sale_price < $regular_price) {
                $profit = $sale_price - $cost_price;
            } else {
                $profit = $regular_price - $cost_price;
            }

            echo wc_price($profit);
        }
    }

    public function make_columns_sortable($columns) {
        $columns['cost_price'] = 'cost_price';
        $columns['profit'] = 'profit';
        return $columns;
    }

    public function add_cost_price_field_to_order_meta_box() {
        add_meta_box(
            'order_cost_price',
            'Себестоимость',
            array($this, 'display_cost_price_field_in_order_meta_box'),
            'shop_order',
            'normal',
            'high'
        );
    }

    public function display_cost_price_field_in_order_meta_box($post) {
        $order = wc_get_order($post->ID);
        $cost_price = $order->get_meta('_cost_price');
        $profit = $order->get_meta('_profit');

        echo '<p><strong>Себестоимость:</strong> ' . wc_price($cost_price) . '</p>';
        echo '<p><strong>Прибыль:</strong> ' . wc_price($profit) . '</p>';
    }

    public function save_cost_price_field_in_order($order_id) {
        $order = wc_get_order($order_id);
        $items = $order->get_items();

        $cost_price = 0;
        $profit = 0;

        foreach ($items as $item) {
            $product = $item->get_product();

            if ($product) {
                $quantity = $item->get_quantity();
                $item_cost_price = get_post_meta($product->get_id(), '_cost_price', true);

                if ($item_cost_price) {
                    $cost_price += $item_cost_price * $quantity;
                    $profit += ($product->get_price() - $item_cost_price) * $quantity;
                }
            }
        }

        $order->update_meta_data('_cost_price', $cost_price);
        $order->update_meta_data('_profit', $profit);
        $order->save();
    }

    public function add_custom_columns_to_orders($columns) {
        $columns['cost_price'] = 'Себестоимость';
        $columns['profit'] = 'Прибыль';
        return $columns;
    }

    public function display_custom_columns_in_orders($column, $post_id) {
        if ($column === 'cost_price') {
            $order = wc_get_order($post_id);
            $cost_price = $order->get_meta('_cost_price');
            echo wc_price($cost_price);
        } elseif ($column === 'profit') {
            $order = wc_get_order($post_id);
            $profit = $order->get_meta('_profit');
            echo wc_price($profit);
        }
    }

    public function make_columns_sortable_in_orders($columns) {
        $columns['cost_price'] = 'cost_price';
        $columns['profit'] = 'profit';
        return $columns;
    }
}
