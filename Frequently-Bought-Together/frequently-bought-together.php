<?php
/**
 * Plugin Name: MedeCify Frequently Bought Together
 * Plugin URI: https://www.medecify.com
 * Description: Display frequently bought together products on WooCommerce product pages with Amazon-style UI. Strict RANDOM category-first selection; fallbacks: tags → price range → popular → global random.
 * Version: 1.1.5
 * Author: MedeCify Team
 * Author URI: https://www.medecify.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frequently-bought-together
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Ensure WooCommerce is active
add_action('plugins_loaded', function() {
    if ( ! class_exists('WooCommerce') ) return;
});

/** ---------------------------
 * Assets (CSS only, JS uses inline)
 * --------------------------- */
add_action('wp_enqueue_scripts', 'fbt_enqueue_assets');
function fbt_enqueue_assets() {
    if (is_product()) {
        wp_enqueue_script('jquery');
        wp_register_style('fbt-styles', false);
        wp_enqueue_style('fbt-styles');

        wp_add_inline_style('fbt-styles', '
            .fbt-container{background:#fff;border:1px solid #e0e0e0;padding:20px;margin:30px 0;font-family:Arial,sans-serif;clear:both;width:100%;box-sizing:border-box;opacity:0;animation:slideInFade .8s ease forwards}
            @keyframes slideInFade{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
            .fbt-title{font-size:24px;font-weight:700;margin-bottom:20px;color:#0f1111}
            .fbt-main-section{display:flex;align-items:flex-start;gap:20px;margin-bottom:15px;flex-wrap:wrap}
            .fbt-products{display:flex;align-items:center;gap:0;flex:1;flex-wrap:nowrap;min-width:300px}
            .fbt-product{display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;width:160px;flex:0 0 160px;opacity:0;animation:slideInUp .6s ease forwards;cursor:pointer;transition:all .2s ease}
            .fbt-product:hover{transform:translateY(-3px)}
            .fbt-product:nth-child(1){animation-delay:.1s}.fbt-product:nth-child(3){animation-delay:.3s}.fbt-product:nth-child(5){animation-delay:.5s}.fbt-product:nth-child(7){animation-delay:.7s}
            @keyframes slideInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
            .fbt-product-image-wrapper{position:relative;width:150px;height:150px;border:1px solid #e0e0e0;background:#f8f8f8;display:flex;align-items:center;justify-content:center;margin-bottom:8px;transition:transform .2s ease,box-shadow .2s ease;border-radius:4px}
            .fbt-product-image-wrapper:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.15);border-color:#007185}
            .fbt-product-image{max-width:100%;max-height:100%;object-fit:contain;transition:transform .2s ease}
            .fbt-product:hover .fbt-product-image{transform:scale(1.05)}
            .fbt-checkbox{position:absolute;top:5px;right:5px;width:18px;height:18px;accent-color:#007185;cursor:pointer;transition:transform .2s ease;z-index:2}
            .fbt-checkbox:hover{transform:scale(1.15)}
            .fbt-product-price{font-size:14px;color:#0f1111;font-weight:500;text-align:center;white-space:nowrap}
            .fbt-product-price .currency{font-size:11px;vertical-align:top}
            .fbt-product-price .whole{font-size:16px;font-weight:500}
            .fbt-product-price .fraction{font-size:11px;vertical-align:top}
            .fbt-plus{font-size:24px;color:#565959;margin:0 10px;align-self:center;flex:0 0 auto;opacity:0;animation:fadeIn .8s ease forwards .4s}
            @keyframes fadeIn{from{opacity:0}to{opacity:1}}
            .fbt-summary{min-width:280px;padding-left:20px;border-left:1px solid #e0e0e0;flex:0 0 auto;opacity:0;animation:slideInRight .8s ease forwards .6s}
            @keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
            .fbt-total-price{font-size:16px;color:#0f1111;margin-bottom:15px;text-align:right}
            .fbt-add-to-cart{background:#ffd814;border:1px solid #fcd200;border-radius:8px;color:#0f1111;cursor:pointer;font-size:14px;font-weight:400;padding:8px 15px;text-align:center;width:100%;transition:all .2s ease;white-space:nowrap;transform:translateY(0)}
            .fbt-add-to-cart:hover{background:#f7ca00;border-color:#f2c200;transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,200,0,.4)}
            .fbt-add-to-cart:active{transform:translateY(0);box-shadow:0 2px 6px rgba(0,0,0,.2)}
            .fbt-add-to-cart:disabled{background:#e6e6e6;border-color:#e6e6e6;color:#999;cursor:not-allowed;transform:none}
            .fbt-info{background:#e7f3ff;border:1px solid #c4e4ff;border-radius:4px;padding:8px 12px;font-size:12px;color:#0f1111;margin-top:15px;display:flex;align-items:center;gap:8px}
            .fbt-info-icon{color:#007185;font-weight:bold;font-size:14px;width:16px;height:16px;border:1px solid #007185;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
            .fbt-show-details{color:#007185;text-decoration:none;cursor:pointer;transition:color .2s ease}
            .fbt-show-details:hover{text-decoration:underline;color:#c7511f}
            .fbt-modal{display:none;position:fixed;z-index:10000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,.5);backdrop-filter:blur(2px)}
            .fbt-modal-content{background:#fff;margin:15% auto;padding:20px;border:1px solid #e0e0e0;width:90%;max-width:500px;border-radius:8px;position:relative;animation:modalSlideIn .3s ease}
            @keyframes modalSlideIn{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
            .fbt-modal-close{color:#aaa;float:right;font-size:24px;font-weight:bold;cursor:pointer;line-height:1;transition:color .2s ease}
            .fbt-modal-close:hover{color:#000}
            .desktop-only{display:block}.mobile-only{display:none}
            @media (max-width:768px){
                .fbt-container{padding:15px;margin:20px 0}
                .fbt-title{font-size:20px;margin-bottom:15px}
                .fbt-main-section{flex-direction:column;gap:15px}
                .fbt-products{display:flex;justify-content:space-between;align-items:center;width:100%;min-width:auto;gap:2px;overflow-x:hidden;padding:0 5px}
                .fbt-product{width:calc(33.333% - 4px);flex:0 0 calc(33.333% - 4px);max-width:108px;min-width:90px}
                .fbt-product-image-wrapper{width:100%;height:0;padding-bottom:100%;position:relative}
                .fbt-product-image{position:absolute;top:0;left:0;width:100%;height:100%}
                .fbt-product-price{font-size:11px}
                .fbt-product-price .currency{font-size:9px}
                .fbt-product-price .whole{font-size:13px}
                .fbt-product-price .fraction{font-size:9px}
                .fbt-plus{font-size:16px;margin:0 1px}
                .fbt-summary{border-left:none;border-top:1px solid #e0e0e0;padding-left:0;padding-top:15px;min-width:auto;width:100%;text-align:center}
                .fbt-total-price{text-align:center}
                .fbt-add-to-cart{font-size:16px;padding:15px 20px;border-radius:25px;font-weight:600}
                .fbt-checkbox{width:16px;height:16px;top:3px;right:3px}
                .desktop-only{display:none !important}
                .mobile-only{display:block !important}
                .fbt-product:nth-child(n+6){display:none !important}
                .fbt-plus:nth-child(n+5){display:none !important}
            }
            .fbt-mobile-modal{display:none;position:fixed;z-index:10000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,.7);backdrop-filter:blur(3px)}
            .fbt-mobile-modal-content{background:#fff;position:absolute;bottom:0;left:0;right:0;max-height:80vh;border-radius:20px 20px 0 0;overflow-y:auto;animation:slideUpModal .4s ease}
            @keyframes slideUpModal{from{transform:translateY(100%)}to{transform:translateY(0)}}
            .fbt-mobile-header{position:sticky;top:0;background:#fff;padding:15px 20px;border-bottom:1px solid #e0e0e0;border-radius:20px 20px 0 0;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 4px rgba(0,0,0,.1)}
            .fbt-mobile-title{font-size:18px;font-weight:600;color:#0f1111}
            .fbt-mobile-close{font-size:24px;color:#666;cursor:pointer;padding:5px;transition:color .2s ease}
            .fbt-mobile-close:hover{color:#000}
            .fbt-mobile-products{padding:20px}
            .fbt-mobile-product{display:flex;align-items:flex-start;padding:15px 0;border-bottom:1px solid #f0f0f0;cursor:pointer;transition:background-color .2s ease;border-radius:8px;margin-bottom:5px}
            .fbt-mobile-product:hover{background:#f8f9fa}
            .fbt-mobile-product:last-child{border-bottom:none}
            .fbt-mobile-checkbox{margin-right:15px;margin-top:5px;width:20px;height:20px;accent-color:#007185;cursor:pointer}
            .fbt-mobile-product-image{width:120px;height:120px;margin-right:15px;border:1px solid #e0e0e0;display:flex;align-items:center;justify-content:center;background:#f8f8f8;border-radius:4px;transition:transform .2s ease}
            .fbt-mobile-product:hover .fbt-mobile-product-image{transform:scale(1.02)}
            .fbt-mobile-product-image img{max-width:100%;max-height:100%;object-fit:contain}
            .fbt-mobile-product-info{flex:1}
            .fbt-mobile-product-title{font-size:14px;color:#0f1111;margin-bottom:5px;line-height:1.3;font-weight:500}
            .fbt-mobile-product-size{font-size:12px;color:#565959;margin-bottom:8px;line-height:1.2}
            .fbt-mobile-product-price{font-size:16px;color:#B12704;font-weight:600;margin-bottom:5px}
            .fbt-mobile-footer{position:sticky;bottom:0;background:#fff;padding:20px;border-top:1px solid #e0e0e0;box-shadow:0 -2px 10px rgba(0,0,0,.1)}
            .fbt-mobile-total{text-align:center;font-size:18px;color:#0f1111;margin-bottom:15px;font-weight:600}
            .fbt-mobile-add-btn{width:100%;background:#ffd814;border:1px solid #fcd200;border-radius:25px;color:#0f1111;font-size:16px;font-weight:600;padding:15px;cursor:pointer;transition:all .2s ease}
            .fbt-mobile-add-btn:hover{background:#f7ca00;transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,200,0,.3)}
        ');
    }
}

/** -------------------------------------------------------
 * STRICT product selection (RANDOM category-first)
 * ------------------------------------------------------- */
function medecify_fbt_query_random_by_tax( $exclude_ids, $taxonomy, $term_ids, $limit ) {
    if ( empty( $term_ids ) ) return array();

    $args = array(
        'limit'      => max( $limit * 3, $limit ),
        'status'     => 'publish',
        'orderby'    => 'rand',
        'exclude'    => array_map('absint', $exclude_ids),
        'return'     => 'objects',
        'tax_query'  => array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => array_map('absint', (array) $term_ids),
                'operator' => 'IN',
                'include_children' => true,
            )
        ),
        'meta_query' => array(
            array(
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            )
        )
    );

    $products = wc_get_products( $args );

    // Filter to purchasable & in stock, trim to limit
    $out = array();
    foreach ( $products as $p ) {
        if ( count($out) >= $limit ) break;
        if ( $p && $p->is_type(array('simple','variable','subscription','variable-subscription','bundle','composite','external')) && $p->is_purchasable() && $p->is_in_stock() ) {
            $out[] = $p;
        }
    }
    return $out;
}

function medecify_fbt_query_random_by_price_range( $exclude_ids, $price, $limit ) {
    $price_val = floatval($price);
    if ( $price_val <= 0 ) return array();

    $min = $price_val * 0.5;
    $max = $price_val * 2.0;

    $args = array(
        'limit'      => max($limit * 3, $limit),
        'status'     => 'publish',
        'orderby'    => 'rand',
        'exclude'    => array_map('absint', $exclude_ids),
        'return'     => 'objects',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            ),
            array(
                'key'     => '_price',
                'value'   => array($min, $max),
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN'
            ),
        ),
    );

    $products = wc_get_products($args);
    $out = array();
    foreach ($products as $p) {
        if (count($out) >= $limit) break;
        if ($p && $p->is_purchasable() && $p->is_in_stock()) {
            $out[] = $p;
        }
    }
    return $out;
}

function medecify_fbt_query_popular_fallback( $exclude_ids, $limit ) {
    $args = array(
        'limit'      => max($limit * 2, $limit),
        'status'     => 'publish',
        'orderby'    => 'popularity',
        'order'      => 'DESC',
        'exclude'    => array_map('absint', $exclude_ids),
        'return'     => 'objects',
        'meta_query' => array(
            array(
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            )
        )
    );
    $products = wc_get_products($args);
    $out = array();
    foreach ($products as $p) {
        if (count($out) >= $limit) break;
        if ($p && $p->is_purchasable() && $p->is_in_stock()) $out[] = $p;
    }
    return $out;
}

function medecify_fbt_query_global_random_fallback( $exclude_ids, $limit ) {
    $args = array(
        'limit'      => max($limit * 2, $limit),
        'status'     => 'publish',
        'orderby'    => 'rand',
        'exclude'    => array_map('absint', $exclude_ids),
        'return'     => 'objects',
        'meta_query' => array(
            array(
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            )
        )
    );
    $products = wc_get_products($args);
    $out = array();
    foreach ($products as $p) {
        if (count($out) >= $limit) break;
        if ($p && $p->is_purchasable() && $p->is_in_stock()) $out[] = $p;
    }
    return $out;
}

/**
 * STRICT RANDOM category-first product picker
 * Fallbacks: tags → price range → popular → global random
 */
function get_fbt_products( $current_product_id, $limit = 3 ) {
    $current = wc_get_product( $current_product_id );
    if ( ! $current ) return array();

    $exclude_ids = array( (int) $current_product_id );

    // Collect all category term IDs on current product
    $cat_terms = wp_get_post_terms( $current_product_id, 'product_cat', array('fields' => 'ids') );
    $tag_terms = wp_get_post_terms( $current_product_id, 'product_tag', array('fields' => 'ids') );

    // 1) CATEGORY (RANDOM) — STRICT: if any products found here, return ONLY these (no mixing)
    if ( ! empty( $cat_terms ) ) {
        $cat_products = medecify_fbt_query_random_by_tax( $exclude_ids, 'product_cat', $cat_terms, $limit );
        if ( ! empty( $cat_products ) ) {
            return array_slice( $cat_products, 0, $limit );
        }
    }

    // 2) TAGS (RANDOM) — only if category returned zero
    if ( ! empty( $tag_terms ) ) {
        $tag_products = medecify_fbt_query_random_by_tax( $exclude_ids, 'product_tag', $tag_terms, $limit );
        if ( ! empty( $tag_products ) ) {
            return array_slice( $tag_products, 0, $limit );
        }
    }

    // 3) PRICE RANGE (RANDOM) — only if category & tags returned zero
    $price_products = medecify_fbt_query_random_by_price_range( $exclude_ids, $current->get_price(), $limit );
    if ( ! empty( $price_products ) ) {
        return array_slice( $price_products, 0, $limit );
    }

    // 4) POPULAR (fallback)
    $popular_products = medecify_fbt_query_popular_fallback( $exclude_ids, $limit );
    if ( ! empty( $popular_products ) ) {
        return array_slice( $popular_products, 0, $limit );
    }

    // 5) GLOBAL RANDOM (last fallback)
    $random_products = medecify_fbt_query_global_random_fallback( $exclude_ids, $limit );
    return array_slice( $random_products, 0, $limit );
}

/** ----------------------------------------
 * Render block on single product pages
 * ---------------------------------------- */
add_action('woocommerce_after_single_product_summary', 'display_frequently_bought_together', 5);
function display_frequently_bought_together() {
    if ( ! is_product() ) return;

    global $product;
    if ( ! $product ) return;

    $related_products = get_fbt_products( $product->get_id(), 3 );
    if ( empty( $related_products ) ) return;

    $current_product_price = $product->get_price();
    $current_product_title = $product->get_name();
    $current_product_image = wp_get_attachment_image_src( $product->get_image_id(), 'woocommerce_thumbnail' );
    $current_product_image = $current_product_image ? $current_product_image[0] : wc_placeholder_img_src();
    $current_product_url   = $product->get_permalink();

    if ( ! $current_product_price || ! $current_product_title ) return;

    ?>
    <div class="fbt-container">
        <h3 class="fbt-title">Frequently bought together</h3>

        <div class="fbt-main-section">
            <div class="fbt-products">
                <!-- Current Product -->
                <div class="fbt-product current-product" onclick="window.location.href='<?php echo esc_url($current_product_url); ?>'">
                    <div class="fbt-product-image-wrapper">
                        <img src="<?php echo esc_url($current_product_image); ?>" alt="<?php echo esc_attr($current_product_title); ?>" class="fbt-product-image">
                        <input type="checkbox" class="fbt-checkbox" checked disabled onclick="event.stopPropagation();">
                    </div>
                    <div class="fbt-product-price">
                        <?php 
                        $price_number = floatval($current_product_price);
                        $currency = get_woocommerce_currency_symbol();
                        $currency_decoded = html_entity_decode($currency, ENT_QUOTES, 'UTF-8');

                        if ($price_number == intval($price_number)) {
                            echo '<span class="currency">' . $currency_decoded . '</span><span class="whole">' . intval($price_number) . '</span>';
                        } else {
                            $price_parts = explode('.', number_format($price_number, 2));
                            echo '<span class="currency">' . $currency_decoded . '</span><span class="whole">' . ltrim($price_parts[0], '0') . '</span><span class="fraction">.' . (isset($price_parts[1]) ? $price_parts[1] : '00') . '</span>';
                        }
                        ?>
                    </div>
                </div>

                <?php 
                $desktop_count = 0;
                foreach ($related_products as $index => $related_product): 
                    if ($desktop_count >= 3) break;
                    $desktop_count++;

                    $related_image = wp_get_attachment_image_src($related_product->get_image_id(), 'woocommerce_thumbnail');
                    $related_image = $related_image ? $related_image[0] : wc_placeholder_img_src();
                    $related_url = $related_product->get_permalink();
                ?>
                    <div class="fbt-plus">+</div>
                    <div class="fbt-product related-product" data-product-id="<?php echo esc_attr($related_product->get_id()); ?>" data-price="<?php echo esc_attr($related_product->get_price()); ?>" onclick="window.location.href='<?php echo esc_url($related_url); ?>'">
                        <div class="fbt-product-image-wrapper">
                            <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr($related_product->get_name()); ?>" class="fbt-product-image">
                            <input type="checkbox" class="fbt-checkbox" checked data-product-id="<?php echo esc_attr($related_product->get_id()); ?>" onclick="event.stopPropagation();">
                        </div>
                        <div class="fbt-product-price">
                            <?php 
                            $price_number = floatval($related_product->get_price());
                            $currency_decoded = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');

                            if ($price_number == intval($price_number)) {
                                echo '<span class="currency">' . $currency_decoded . '</span><span class="whole">' . intval($price_number) . '</span>';
                            } else {
                                $price_parts = explode('.', number_format($price_number, 2));
                                echo '<span class="currency">' . $currency_decoded . '</span><span class="whole">' . ltrim($price_parts[0], '0') . '</span><span class="fraction">.' . (isset($price_parts[1]) ? $price_parts[1] : '00') . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="fbt-summary">
                <div class="fbt-total-price">
                    Total price: <span class="fbt-current-price"></span>
                </div>
                <div class="desktop-only">
                    <button class="fbt-add-to-cart" onclick="handleAddToCart()">Buy all <span class="fbt-count">4</span>: <span class="fbt-current-price-btn"></span></button>
                </div>
                <div class="mobile-only">
                    <button class="fbt-add-to-cart" onclick="showMobileFBTModal()">Buy all <span class="fbt-mobile-count-display">3</span>: <span class="fbt-mobile-price-display"></span></button>
                </div>
            </div>
        </div>

        <div class="fbt-info">
            <div class="fbt-info-icon">i</div>
            <div>
                These items are shipped from and sold by MedeCify.
                <a href="#" class="fbt-show-details" onclick="showFBTDetails(event)">Show details</a>
            </div>
        </div>
    </div>

    <!-- Desktop Modal -->
    <div id="fbt-modal" class="fbt-modal">
        <div class="fbt-modal-content">
            <span class="fbt-modal-close" onclick="closeFBTModal()">&times;</span>
            <h4>Shipping & Seller Information</h4>
            <p><strong>Seller:</strong> MedeCify</p>
            <p><strong>Shipping:</strong> All items in this bundle are shipped together from MedeCify&apos;s warehouse.</p>
            <p><strong>Delivery Time:</strong> Standard delivery within 2-5 business days.</p>
            <p><strong>Returns:</strong> 30-day return policy applies to all items in this bundle.</p>
            <p><strong>Bundle Discount:</strong> Save when you buy these items together instead of purchasing them separately.</p>
        </div>
    </div>

    <!-- Mobile Modal -->
    <div id="fbt-mobile-modal" class="fbt-mobile-modal">
        <div class="fbt-mobile-modal-content">
            <div class="fbt-mobile-header">
                <div class="fbt-mobile-title">Frequently bought together</div>
                <div class="fbt-mobile-close" onclick="closeMobileFBTModal()">×</div>
            </div>
            <div class="fbt-mobile-products">
                <!-- Current Product -->
                <div class="fbt-mobile-product" onclick="window.location.href='<?php echo esc_url($current_product_url); ?>'">
                    <input type="checkbox" class="fbt-mobile-checkbox" checked disabled onclick="event.stopPropagation();">
                    <div class="fbt-mobile-product-image">
                        <img src="<?php echo esc_url($current_product_image); ?>" alt="<?php echo esc_attr($current_product_title); ?>">
                    </div>
                    <div class="fbt-mobile-product-info">
                        <div class="fbt-mobile-product-title"><?php echo esc_html($current_product_title); ?></div>
                        <div class="fbt-mobile-product-size">Size: (Pack of 1)</div>
                        <div class="fbt-mobile-product-price">
                            <?php 
                            $currency_decoded = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');
                            echo esc_html($currency_decoded . number_format((float)$current_product_price, 2)); 
                            ?>
                        </div>
                    </div>
                </div>

                <?php 
                $mobile_count = 0;
                foreach ($related_products as $related_product): 
                    if ($mobile_count >= 2) break;
                    $mobile_count++;
                    $related_url = $related_product->get_permalink();
                    $related_image = wp_get_attachment_image_src($related_product->get_image_id(), 'woocommerce_thumbnail');
                    $related_image = $related_image ? $related_image[0] : wc_placeholder_img_src();
                ?>
                <div class="fbt-mobile-product" onclick="window.location.href='<?php echo esc_url($related_url); ?>'">
                    <input type="checkbox" class="fbt-mobile-checkbox" checked data-product-id="<?php echo esc_attr($related_product->get_id()); ?>" data-price="<?php echo esc_attr($related_product->get_price()); ?>" onclick="event.stopPropagation();">
                    <div class="fbt-mobile-product-image">
                        <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr($related_product->get_name()); ?>">
                    </div>
                    <div class="fbt-mobile-product-info">
                        <div class="fbt-mobile-product-title"><?php echo esc_html($related_product->get_name()); ?></div>
                        <div class="fbt-mobile-product-size">Size: (Pack of 1)</div>
                        <div class="fbt-mobile-product-price">
                            <?php echo esc_html($currency_decoded . number_format((float)$related_product->get_price(), 2)); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="fbt-mobile-footer">
                <div class="fbt-mobile-total">
                    Total price: <span class="fbt-mobile-current-price"></span>
                </div>
                <button class="fbt-mobile-add-btn" onclick="addFBTToCart()">Add all <span class="fbt-mobile-count">3</span> to cart</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        updateFBTSummary();
        updateMobileFBTSummary();

        $('.fbt-checkbox:not(:disabled)').on('change', updateFBTSummary);
        $('.fbt-mobile-checkbox:not(:disabled)').on('change', updateMobileFBTSummary);

        function updateFBTSummary() {
            var totalPrice = 0, selectedCount = 0;
            var currency = '<?php echo addslashes(html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, "UTF-8")); ?>';
            var currentPrice = <?php echo (float) $current_product_price; ?>;
            totalPrice += currentPrice; selectedCount++;

            $('.fbt-checkbox:not(:disabled):checked').each(function() {
                var price = parseFloat($(this).closest(".fbt-product").data("price"));
                if (!isNaN(price)) { totalPrice += price; selectedCount++; }
            });

            $('.fbt-current-price').text(currency + totalPrice.toFixed(2));
            $('.fbt-current-price-btn').text(currency + totalPrice.toFixed(2));
            $('.fbt-count').text(selectedCount);
            $('.desktop-only .fbt-add-to-cart').prop('disabled', selectedCount === 0);
        }

        function updateMobileFBTSummary() {
            var totalPrice = 0, selectedCount = 0;
            var currency = '<?php echo addslashes(html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, "UTF-8")); ?>';
            var currentPrice = <?php echo (float) $current_product_price; ?>;
            totalPrice += currentPrice; selectedCount++;

            $('.fbt-mobile-checkbox:not(:disabled):checked').each(function() {
                var price = parseFloat($(this).data('price'));
                if (!isNaN(price)) { totalPrice += price; selectedCount++; }
            });

            $('.fbt-mobile-current-price').text(currency + totalPrice.toFixed(2));
            $('.fbt-mobile-count-display').text(selectedCount);
            $('.fbt-mobile-price-display').text(currency + totalPrice.toFixed(2));
            $('.fbt-mobile-count').text(selectedCount);
            $('.fbt-mobile-add-btn').prop('disabled', selectedCount === 0);
        }
    });

    function handleAddToCart() {
        var selectedProducts = [];
        var currentProductId = <?php echo (int) $product->get_id(); ?>;
        selectedProducts.push({id: currentProductId, quantity: 1});

        jQuery('.fbt-checkbox:not(:disabled):checked').each(function() {
            var productId = jQuery(this).data('product-id');
            if (productId) selectedProducts.push({id: productId, quantity: 1});
        });
        addFBTProductsToCart(selectedProducts, 'desktop');
    }

    function addFBTToCart() {
        var selectedProducts = [];
        var currentProductId = <?php echo (int) $product->get_id(); ?>;
        selectedProducts.push({id: currentProductId, quantity: 1});

        jQuery('.fbt-mobile-checkbox:not(:disabled):checked').each(function() {
            var productId = jQuery(this).data('product-id');
            if (productId) selectedProducts.push({id: productId, quantity: 1});
        });
        addFBTProductsToCart(selectedProducts, 'mobile');
    }

    function addFBTProductsToCart(products, source) {
        if (!products.length) return;

        var button = source === 'mobile' ? jQuery('.fbt-mobile-add-btn') : jQuery('.desktop-only .fbt-add-to-cart');
        var originalText = button.text();
        button.prop('disabled', true).text('Adding...');

        jQuery.ajax({
            url: '<?php echo esc_url( admin_url("admin-ajax.php") ); ?>',
            type: 'POST',
            data: {
                action: 'add_multiple_to_cart',
                products: products,
                security: '<?php echo wp_create_nonce("add_multiple_to_cart_nonce"); ?>'
            },
            success: function(response) {
                if (response && response.success) {
                    button.text('Added to Cart!').css('background', '#4CAF50');
                    if (response.data && response.data.fragments) {
                        jQuery.each(response.data.fragments, function(key, value) {
                            jQuery(key).replaceWith(value);
                        });
                    }
                    jQuery('body').trigger('added_to_cart');
                    setTimeout(function(){ window.location.href = '<?php echo esc_url( wc_get_checkout_url() ); ?>'; }, 500);
                } else {
                    button.prop('disabled', false).text('Try Again').css('background', '#f44336');
                    setTimeout(function(){ button.text(originalText).css('background', ''); }, 3000);
                }
            },
            error: function() {
                button.prop('disabled', false).text('Try Again').css('background', '#f44336');
                setTimeout(function(){ button.text(originalText).css('background', ''); }, 3000);
            }
        });
    }

    function showFBTDetails(e){ e.preventDefault(); jQuery("#fbt-modal").show(); }
    function closeFBTModal(){ jQuery("#fbt-modal").hide(); }
    jQuery(window).on("click", function(e){
        if (jQuery(e.target).is("#fbt-modal")) closeFBTModal();
        if (jQuery(e.target).is("#fbt-mobile-modal")) closeMobileFBTModal();
    });
    function showMobileFBTModal(){ jQuery("#fbt-mobile-modal").show(); }
    function closeMobileFBTModal(){ jQuery("#fbt-mobile-modal").hide(); }
    </script>
    <?php
}

/** ----------------------------------------
 * AJAX: add multiple products to cart
 * ---------------------------------------- */
add_action('wp_ajax_add_multiple_to_cart', 'handle_add_multiple_to_cart');
add_action('wp_ajax_nopriv_add_multiple_to_cart', 'handle_add_multiple_to_cart');

function handle_add_multiple_to_cart() {
    if ( ! function_exists('WC') || ! WC()->cart ) {
        wp_send_json_error('WooCommerce not available');
    }

    if ( empty($_POST['security']) || ! wp_verify_nonce( $_POST['security'], 'add_multiple_to_cart_nonce' ) ) {
        wp_send_json_error('Security check failed');
    }

    $items = isset($_POST['products']) && is_array($_POST['products']) ? $_POST['products'] : array();
    if ( empty($items) ) {
        wp_send_json_error('No products specified');
    }

    $added_products = array();
    $errors = array();

    foreach ( $items as $item ) {
        $pid = isset($item['id']) ? intval($item['id']) : 0;
        $qty = isset($item['quantity']) ? intval($item['quantity']) : 0;
        if ( $pid <= 0 || $qty <= 0 ) continue;

        $p = wc_get_product($pid);
        if ( ! $p || ! $p->is_purchasable() ) {
            $errors[] = sprintf('Product %d is not available', $pid);
            continue;
        }

        try {
            $key = WC()->cart->add_to_cart( $pid, $qty );
            if ( $key ) {
                $added_products[] = array('product_id'=>$pid, 'quantity'=>$qty, 'cart_item_key'=>$key);
            } else {
                $errors[] = sprintf('Failed to add product %d to cart', $pid);
            }
        } catch ( Exception $e ) {
            $errors[] = sprintf('Error adding product %d: %s', $pid, $e->getMessage());
        }
    }

    if ( empty($added_products) ) {
        wp_send_json_error(array('message' => 'No products were added to cart', 'errors' => $errors));
    }

    $fragments = array();
    if ( function_exists('woocommerce_mini_cart') ) {
        ob_start();
        woocommerce_mini_cart();
        $mini_cart = ob_get_clean();

        $fragments = apply_filters('woocommerce_add_to_cart_fragments', array(
            'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
            '.cart-contents' => '<span class="cart-contents">' . WC()->cart->get_cart_contents_count() . '</span>',
            '.cart-total'    => '<span class="cart-total">' . WC()->cart->get_cart_subtotal() . '</span>'
        ));
    }

    wp_send_json_success(array(
        'message'       => 'Products added to cart successfully',
        'added_products'=> $added_products,
        'errors'        => $errors,
        'fragments'     => $fragments,
        'cart_hash'     => WC()->cart->get_cart_hash(),
        'cart_count'    => WC()->cart->get_cart_contents_count()
    ));
}
