<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
/*
* Thêm nút mua ngay vào trang chi tiết sản phẩm Woocommerce
*/
add_action('woocommerce_after_add_to_cart_button','vuontainguyen_quickbuy_after_addtocart_button');
function vuontainguyen_quickbuy_after_addtocart_button(){
    global $product;
    ?>
    <style>
        .vuontainguyen-quickbuy button.single_add_to_cart_button.loading:after {
            display: none;
        }
        .vuontainguyen-quickbuy button.single_add_to_cart_button.button.alt.loading {
            color: #fff;
            pointer-events: none !important;
        }
        .vuontainguyen-quickbuy button.buy_now_button {
            position: relative;
            color: rgba(255,255,255,0.05);
        }
        .vuontainguyen-quickbuy button.buy_now_button:after {
            animation: spin 500ms infinite linear;
            border: 2px solid #fff;
            border-radius: 32px;
            border-right-color: transparent !important;
            border-top-color: transparent !important;
            content: "";
            display: block;
            height: 16px;
            top: 50%;
            margin-top: -8px;
            left: 50%;
            margin-left: -8px;
            position: absolute;
            width: 16px;
        }
    </style>
    <button type="button" class="button buy_now_button">
        <?php _e('Mua ngay', 'vuontainguyen'); ?>
    </button>
    <input type="hidden" name="is_buy_now" class="is_buy_now" value="0" autocomplete="off"/>
    <script>
        jQuery(document).ready(function(){
            jQuery('body').on('click', '.buy_now_button', function(e){
                e.preventDefault();
                var thisParent = jQuery(this).parents('form.cart');
                if(jQuery('.single_add_to_cart_button', thisParent).hasClass('disabled')) {
                    jQuery('.single_add_to_cart_button', thisParent).trigger('click');
                    return false;
                }
                thisParent.addClass('vuontainguyen-quickbuy');
                jQuery('.is_buy_now', thisParent).val('1');
                jQuery('.single_add_to_cart_button', thisParent).trigger('click');
            });
        });
    </script>
    <?php
}
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout');
function redirect_to_checkout($redirect_url) {
    if (isset($_REQUEST['is_buy_now']) && $_REQUEST['is_buy_now']) {
        $redirect_url = wc_get_checkout_url(); //đổi thành wc_get_cart_url()
    }
    return $redirect_url;
}

/*Thêm nút hủy đơn hàng*/
add_filter('woocommerce_my_account_my_orders_actions', 'add_cancel_order_button', 10, 2);
function add_cancel_order_button($actions, $order) {
    if ($order->has_status('processing')) {
        $actions['cancel'] = array(
            'url'  => wp_nonce_url( add_query_arg('cancel_order', $order->get_id()), 'woocommerce-cancel_order' ),
            'name' => __('Cancel', 'woocommerce'),
            'action' => 'cancel', // khuyến cáo là không cần thay đổi action này
        );
    }
    return $actions;
}
/*Xử lý nút Hủy đơn hàng*/
add_action('template_redirect', 'handle_cancel_order_action');
function handle_cancel_order_action() {
    if (isset($_GET['cancel_order']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'woocommerce-cancel_order')) {
        $order_id = intval($_GET['cancel_order']);
        $order = wc_get_order($order_id);

        if ($order && $order->get_status() === 'processing') {
            $order->update_status('cancelled', __('Order cancelled by customer', 'woocommerce'));
            wc_add_notice(__('Your order has been cancelled.', 'woocommerce'), 'success');
        } else {
            wc_add_notice(__('Unable to cancel the order. Please contact support.', 'woocommerce'), 'error');
        }
        
        wp_safe_redirect(wc_get_account_endpoint_url('orders'));
        exit;
    }
}
