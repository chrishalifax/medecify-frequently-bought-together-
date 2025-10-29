<?php
/**
 * Plugin Name: Frequently Bought Together for WooCommerce
 * Plugin URI: https://www.medecify.com
 * Description: Display frequently bought together products on WooCommerce product pages with Amazon-style UI. Desktop: 4 products | Mobile: 3 products | All products clickable.
 * Version: 1.1.1
 * Author: Genesis
 * Author URI: https://www.medecify.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frequently-bought-together
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', 'fbt_enqueue_assets');
function fbt_enqueue_assets() {
    if (is_product()) {
        wp_enqueue_script('jquery');
        
        // Enqueue a dummy style handle first, then add inline styles to it
        wp_register_style('fbt-styles', false);
        wp_enqueue_style('fbt-styles');
        
        wp_add_inline_style('fbt-styles', '
            .fbt-container {
                background: #fff;
                border: 1px solid #e0e0e0;
                padding: 20px;
                margin: 30px 0;
                font-family: Arial, sans-serif;
                clear: both;
                width: 100%;
                box-sizing: border-box;
                opacity: 0;
                animation: slideInFade 0.8s ease forwards;
            }
            
            @keyframes slideInFade {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .fbt-title {
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 20px;
                color: #0f1111;
            }
            
            .fbt-main-section {
                display: flex;
                align-items: flex-start;
                gap: 20px;
                margin-bottom: 15px;
                flex-wrap: wrap;
            }
            
            .fbt-products {
                display: flex;
                align-items: center;
                gap: 0;
                flex: 1;
                flex-wrap: nowrap;
                min-width: 300px;
            }
            
            .fbt-product {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                position: relative;
                width: 160px;
                flex: 0 0 160px;
                opacity: 0;
                animation: slideInUp 0.6s ease forwards;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .fbt-product:hover {
                transform: translateY(-3px);
            }
            
            .fbt-product:nth-child(1) { animation-delay: 0.1s; }
            .fbt-product:nth-child(3) { animation-delay: 0.3s; }
            .fbt-product:nth-child(5) { animation-delay: 0.5s; }
            .fbt-product:nth-child(7) { animation-delay: 0.7s; }
            
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .fbt-product-image-wrapper {
                position: relative;
                width: 150px;
                height: 150px;
                border: 1px solid #e0e0e0;
                background: #f8f8f8;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 8px;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                border-radius: 4px;
            }
            
            .fbt-product-image-wrapper:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0,0,0,0.15);
                border-color: #007185;
            }
            
            .fbt-product-image {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                transition: transform 0.2s ease;
            }
            
            .fbt-product:hover .fbt-product-image {
                transform: scale(1.05);
            }
            
            .fbt-checkbox {
                position: absolute;
                top: 5px;
                right: 5px;
                width: 18px;
                height: 18px;
                accent-color: #007185;
                cursor: pointer;
                transition: transform 0.2s ease;
                z-index: 2;
            }
            
            .fbt-checkbox:hover {
                transform: scale(1.15);
            }
            
            .fbt-product-price {
                font-size: 14px;
                color: #0f1111;
                font-weight: 500;
                text-align: center;
                white-space: nowrap;
            }
            
            .fbt-product-price .currency {
                font-size: 11px;
                vertical-align: top;
            }
            
            .fbt-product-price .whole {
                font-size: 16px;
                font-weight: 500;
            }
            
            .fbt-product-price .fraction {
                font-size: 11px;
                vertical-align: top;
            }
            
            .fbt-plus {
                font-size: 24px;
                color: #565959;
                margin: 0 10px;
                align-self: center;
                flex: 0 0 auto;
                opacity: 0;
                animation: fadeIn 0.8s ease forwards 0.4s;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            .fbt-summary {
                min-width: 280px;
                padding-left: 20px;
                border-left: 1px solid #e0e0e0;
                flex: 0 0 auto;
                opacity: 0;
                animation: slideInRight 0.8s ease forwards 0.6s;
            }
            
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            .fbt-total-price {
                font-size: 16px;
                color: #0f1111;
                margin-bottom: 15px;
                text-align: right;
            }
            
            .fbt-add-to-cart {
                background: #ffd814;
                border: 1px solid #fcd200;
                border-radius: 8px;
                color: #0f1111;
                cursor: pointer;
                font-size: 14px;
                font-weight: 400;
                padding: 8px 15px;
                text-align: center;
                width: 100%;
                transition: all 0.2s ease;
                white-space: nowrap;
                transform: translateY(0);
            }
            
            .fbt-add-to-cart:hover {
                background: #f7ca00;
                border-color: #f2c200;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(255, 200, 0, 0.4);
            }
            
            .fbt-add-to-cart:active {
                transform: translateY(0);
                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            }
            
            .fbt-add-to-cart:disabled {
                background: #e6e6e6;
                border-color: #e6e6e6;
                color: #999;
                cursor: not-allowed;
                transform: none;
            }
            
            .fbt-info {
                background: #e7f3ff;
                border: 1px solid #c4e4ff;
                border-radius: 4px;
                padding: 8px 12px;
                font-size: 12px;
                color: #0f1111;
                margin-top: 15px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .fbt-info-icon {
                color: #007185;
                font-weight: bold;
                font-size: 14px;
                width: 16px;
                height: 16px;
                border: 1px solid #007185;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            
            .fbt-show-details {
                color: #007185;
                text-decoration: none;
                cursor: pointer;
                transition: color 0.2s ease;
            }
            
            .fbt-show-details:hover {
                text-decoration: underline;
                color: #c7511f;
            }
            
            .fbt-modal {
                display: none;
                position: fixed;
                z-index: 10000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                backdrop-filter: blur(2px);
            }
            
            .fbt-modal-content {
                background-color: white;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #e0e0e0;
                width: 90%;
                max-width: 500px;
                border-radius: 8px;
                position: relative;
                animation: modalSlideIn 0.3s ease;
            }
            
            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .fbt-modal-close {
                color: #aaa;
                float: right;
                font-size: 24px;
                font-weight: bold;
                cursor: pointer;
                line-height: 1;
                transition: color 0.2s ease;
            }
            
            .fbt-modal-close:hover {
                color: #000;
            }
            
            .fbt-modal h4 {
                margin: 0 0 15px 0;
                color: #0f1111;
                font-size: 18px;
            }
            
            .fbt-modal p {
                margin: 10px 0;
                color: #565959;
                line-height: 1.4;
            }
            
            .desktop-only {
                display: block;
            }
            
            .mobile-only {
                display: none;
            }
            
            /* Mobile Responsive */
            @media (max-width: 768px) {
                .fbt-container {
                    padding: 15px;
                    margin: 20px 0;
                }
                
                .fbt-title {
                    font-size: 20px;
                    margin-bottom: 15px;
                }
                
                .fbt-main-section {
                    flex-direction: column;
                    gap: 15px;
                }
                
                .fbt-products {
                    display: flex;
                    justify-content: space-around;
                    align-items: center;
                    width: 100%;
                    min-width: auto;
                    gap: 5px;
                    overflow-x: hidden;
                    padding: 0 10px;
                }
                
                .fbt-product {
                    width: calc(33.333% - 10px);
                    flex: 0 0 calc(33.333% - 10px);
                    max-width: 140px;
                }
                
                .fbt-product-image-wrapper {
                    width: 100%;
                    height: 0;
                    padding-bottom: 100%;
                    position: relative;
                }
                
                .fbt-product-image {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                }
                
                .fbt-plus {
                    font-size: 18px;
                    margin: 0 5px;
                    align-self: center;
                    flex: 0 0 auto;
                }
                
                .fbt-summary {
                    border-left: none;
                    border-top: 1px solid #e0e0e0;
                    padding-left: 0;
                    padding-top: 15px;
                    min-width: auto;
                    width: 100%;
                    text-align: center;
                }
                
                .fbt-total-price {
                    text-align: center;
                }
                
                .fbt-add-to-cart {
                    font-size: 16px;
                    padding: 15px 20px;
                    border-radius: 25px;
                    font-weight: 600;
                }
                
                .fbt-checkbox {
                    width: 16px;
                    height: 16px;
                    top: 3px;
                    right: 3px;
                }
                
                .desktop-only {
                    display: none !important;
                }
                
                .mobile-only {
                    display: block !important;
                }
                
                /* Show only first 3 products on mobile (current + 2 related) */
                .fbt-product:nth-child(n+6) {
                    display: none !important;
                }
                
                .fbt-plus:nth-child(n+5) {
                    display: none !important;
                }
            }
            
            /* Mobile Modal Styles */
            .fbt-mobile-modal {
                display: none;
                position: fixed;
                z-index: 10000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.7);
                backdrop-filter: blur(3px);
            }
            
            .fbt-mobile-modal-content {
                background-color: white;
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                max-height: 80vh;
                border-radius: 20px 20px 0 0;
                overflow-y: auto;
                animation: slideUpModal 0.4s ease;
            }
            
            @keyframes slideUpModal {
                from {
                    transform: translateY(100%);
                }
                to {
                    transform: translateY(0);
                }
            }
            
            .fbt-mobile-header {
                position: sticky;
                top: 0;
                background: white;
                padding: 15px 20px;
                border-bottom: 1px solid #e0e0e0;
                border-radius: 20px 20px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .fbt-mobile-title {
                font-size: 18px;
                font-weight: 600;
                color: #0f1111;
            }
            
            .fbt-mobile-close {
                font-size: 24px;
                color: #666;
                cursor: pointer;
                padding: 5px;
                transition: color 0.2s ease;
            }
            
            .fbt-mobile-close:hover {
                color: #000;
            }
            
            .fbt-mobile-products {
                padding: 20px;
            }
            
            .fbt-mobile-product {
                display: flex;
                align-items: flex-start;
                padding: 15px 0;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: background-color 0.2s ease;
                border-radius: 8px;
                margin-bottom: 5px;
            }
            
            .fbt-mobile-product:hover {
                background-color: #f8f9fa;
            }
            
            .fbt-mobile-product:last-child {
                border-bottom: none;
            }
            
            .fbt-mobile-checkbox {
                margin-right: 15px;
                margin-top: 5px;
                width: 20px;
                height: 20px;
                accent-color: #007185;
                cursor: pointer;
            }
            
            .fbt-mobile-product-image {
                width: 120px;
                height: 120px;
                margin-right: 15px;
                border: 1px solid #e0e0e0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f8f8;
                border-radius: 4px;
                transition: transform 0.2s ease;
            }
            
            .fbt-mobile-product:hover .fbt-mobile-product-image {
                transform: scale(1.02);
            }
            
            .fbt-mobile-product-image img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            
            .fbt-mobile-product-info {
                flex: 1;
            }
            
            .fbt-mobile-product-title {
                font-size: 14px;
                color: #0f1111;
                margin-bottom: 5px;
                line-height: 1.3;
                font-weight: 500;
            }
            
            .fbt-mobile-product-size {
                font-size: 12px;
                color: #565959;
                margin-bottom: 8px;
                line-height: 1.2;
            }
            
            .fbt-mobile-product-price {
                font-size: 16px;
                color: #B12704;
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            .fbt-mobile-footer {
                position: sticky;
                bottom: 0;
                background: white;
                padding: 20px;
                border-top: 1px solid #e0e0e0;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            }
            
            .fbt-mobile-total {
                text-align: center;
                font-size: 18px;
                color: #0f1111;
                margin-bottom: 15px;
                font-weight: 600;
            }
            
            .fbt-mobile-add-btn {
                width: 100%;
                background: #ffd814;
                border: 1px solid #fcd200;
                border-radius: 25px;
                color: #0f1111;
                font-size: 16px;
                font-weight: 600;
                padding: 15px;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .fbt-mobile-add-btn:hover {
                background: #f7ca00;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(255, 200, 0, 0.3);
            }
        ');
    }
}

// Get related products function
function get_fbt_products($current_product_id, $limit = 3) {
    $current_product = wc_get_product($current_product_id);
    if (!$current_product) {
        return array();
    }

    $related_ids = array();
    $excluded_ids = array($current_product_id); // Keep track of all excluded IDs
    
    // 1. First priority: Cross-sell products (manually set related products)
    $cross_sells = $current_product->get_cross_sell_ids();
    if (!empty($cross_sells)) {
        foreach ($cross_sells as $cross_sell_id) {
            if (count($related_ids) >= $limit) break;
            
            // Verify product is valid and not already added
            if (!in_array($cross_sell_id, $excluded_ids)) {
                $cs_product = wc_get_product($cross_sell_id);
                if ($cs_product && $cs_product->is_purchasable() && $cs_product->is_in_stock()) {
                    $related_ids[] = $cross_sell_id;
                    $excluded_ids[] = $cross_sell_id;
                }
            }
        }
    }
    
    // 2. Second priority: Up-sell products
    if (count($related_ids) < $limit) {
        $up_sells = $current_product->get_upsell_ids();
        if (!empty($up_sells)) {
            foreach ($up_sells as $up_sell_id) {
                if (count($related_ids) >= $limit) break;
                
                // Verify product is valid and not already added
                if (!in_array($up_sell_id, $excluded_ids)) {
                    $us_product = wc_get_product($up_sell_id);
                    if ($us_product && $us_product->is_purchasable() && $us_product->is_in_stock()) {
                        $related_ids[] = $up_sell_id;
                        $excluded_ids[] = $up_sell_id;
                    }
                }
            }
        }
    }

    // 3. Third priority: Products from same categories (most relevant)
    if (count($related_ids) < $limit) {
        $categories = wp_get_post_terms($current_product_id, 'product_cat', array('fields' => 'ids'));
        
        if (!empty($categories)) {
            $remaining = $limit - count($related_ids);
            
            // Get products from same categories, ordered by date (to avoid always showing same products)
            $category_products = wc_get_products(array(
                'category' => $categories,
                'exclude' => $excluded_ids,
                'limit' => $remaining * 3, // Get more to filter better ones
                'status' => 'publish',
                'orderby' => 'date', // Mix it up with date ordering
                'order' => 'DESC',
                'meta_query' => array(
                    array(
                        'key' => '_stock_status',
                        'value' => 'instock',
                        'compare' => '='
                    )
                )
            ));

            // Filter and prioritize by rating, review count, and sales
            $scored_products = array();
            foreach ($category_products as $product) {
                $product_id = $product->get_id();
                
                // Double-check not already included
                if (in_array($product_id, $excluded_ids)) {
                    continue;
                }
                
                if (!$product->is_purchasable()) {
                    continue;
                }
                
                $rating = floatval($product->get_average_rating());
                $review_count = intval($product->get_review_count());
                $popularity = intval(get_post_meta($product_id, 'total_sales', true) ?: 0);
                
                // Improved scoring: prioritize products with reviews and good ratings
                // Give base score to all products, boost those with ratings
                $score = 10; // Base score
                
                if ($rating > 0) {
                    $score += ($rating * 5); // Rating weight (max 25 points)
                }
                
                if ($review_count > 0) {
                    $score += min($review_count * 2, 30); // Review count weight (max 30 points)
                }
                
                if ($popularity > 0) {
                    $score += min($popularity * 0.5, 20); // Sales weight (max 20 points)
                }
                
                // Add slight randomness to avoid always showing the same products
                $score += rand(0, 5);
                
                $scored_products[] = array(
                    'id' => $product_id,
                    'product' => $product,
                    'score' => $score
                );
            }
            
            // Sort by score (highest first)
            usort($scored_products, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            // Add top scored products
            foreach ($scored_products as $scored_product) {
                if (count($related_ids) >= $limit) break;
                
                if (!in_array($scored_product['id'], $excluded_ids)) {
                    $related_ids[] = $scored_product['id'];
                    $excluded_ids[] = $scored_product['id'];
                }
            }
        }
    }

    // 4. Fourth priority: Products from same tags
    if (count($related_ids) < $limit) {
        $tags = wp_get_post_terms($current_product_id, 'product_tag', array('fields' => 'ids'));
        
        if (!empty($tags)) {
            $remaining = $limit - count($related_ids);
            
            $tag_products = wc_get_products(array(
                'tag' => $tags,
                'exclude' => $excluded_ids,
                'limit' => $remaining * 2,
                'status' => 'publish',
                'orderby' => 'rand', // Randomize to show variety
                'meta_query' => array(
                    array(
                        'key' => '_stock_status',
                        'value' => 'instock',
                        'compare' => '='
                    )
                )
            ));

            foreach ($tag_products as $product) {
                if (count($related_ids) >= $limit) break;
                
                $product_id = $product->get_id();
                if (!in_array($product_id, $excluded_ids) && $product->is_purchasable()) {
                    $related_ids[] = $product_id;
                    $excluded_ids[] = $product_id;
                }
            }
        }
    }

    // 5. Fifth priority: Products in similar price range from any category
    if (count($related_ids) < $limit) {
        $current_price = floatval($current_product->get_price());
        
        if ($current_price > 0) {
            $price_min = $current_price * 0.5; // 50% lower
            $price_max = $current_price * 2.0;   // 100% higher
            
            $remaining = $limit - count($related_ids);
            
            $price_range_products = wc_get_products(array(
                'exclude' => $excluded_ids,
                'limit' => $remaining * 2,
                'status' => 'publish',
                'orderby' => 'rand',
                'meta_query' => array(
                    array(
                        'key' => '_price',
                        'value' => array($price_min, $price_max),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN'
                    ),
                    array(
                        'key' => '_stock_status',
                        'value' => 'instock',
                        'compare' => '='
                    )
                )
            ));

            foreach ($price_range_products as $product) {
                if (count($related_ids) >= $limit) break;
                
                $product_id = $product->get_id();
                if (!in_array($product_id, $excluded_ids) && $product->is_purchasable()) {
                    $related_ids[] = $product_id;
                    $excluded_ids[] = $product_id;
                }
            }
        }
    }

    // 6. Last priority: Most popular products (fallback)
    if (count($related_ids) < $limit) {
        $remaining = $limit - count($related_ids);
        
        $popular_products = wc_get_products(array(
            'exclude' => $excluded_ids,
            'limit' => $remaining * 2,
            'status' => 'publish',
            'orderby' => 'popularity',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '='
                )
            )
        ));

        foreach ($popular_products as $product) {
            if (count($related_ids) >= $limit) break;
            
            $product_id = $product->get_id();
            if (!in_array($product_id, $excluded_ids) && $product->is_purchasable()) {
                $related_ids[] = $product_id;
                $excluded_ids[] = $product_id;
            }
        }
    }

    // Convert IDs to product objects with final validation
    $products = array();
    foreach ($related_ids as $id) {
        $product = wc_get_product($id);
        if ($product && $product->is_purchasable() && $product->is_in_stock()) {
            $products[] = $product;
            if (count($products) >= $limit) break; // Ensure we don't exceed limit
        }
    }

    return $products;
}

// CHANGED: Display right before product description tabs (after availability, SKU, brands)
// Using woocommerce_after_single_product_summary with priority 5 to show before description tabs (priority 10)
add_action('woocommerce_after_single_product_summary', 'display_frequently_bought_together', 5);

function display_frequently_bought_together() {
    global $product;
    
    // Check if we're on a product page and have a product
    if (!is_product() || !$product) {
        return;
    }
    
    // Get related products - 3 for desktop (to show 4 total including current), 2 for mobile (to show 3 total)
    $related_products = get_fbt_products($product->get_id(), 3);
    
    if (empty($related_products)) {
        return;
    }
    
    $current_product_price = $product->get_price();
    $current_product_title = $product->get_name();
    $current_product_image = wp_get_attachment_image_src($product->get_image_id(), 'woocommerce_thumbnail');
    $current_product_image = $current_product_image ? $current_product_image[0] : wc_placeholder_img_src();
    $current_product_url = $product->get_permalink();
    
    // Ensure we have valid data
    if (!$current_product_price || !$current_product_title) {
        return;
    }
    
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
                // Show only first 3 related products on desktop (total 4 products)
                $desktop_count = 0;
                foreach ($related_products as $index => $related_product): 
                    if ($desktop_count >= 3) break; // Only show 3 related products on desktop
                    $desktop_count++;
                    
                    $related_image = wp_get_attachment_image_src($related_product->get_image_id(), 'woocommerce_thumbnail');
                    $related_image = $related_image ? $related_image[0] : wc_placeholder_img_src();
                    $related_url = $related_product->get_permalink();
                ?>
                    <div class="fbt-plus">+</div>
                    <div class="fbt-product related-product" data-product-id="<?php echo $related_product->get_id(); ?>" data-price="<?php echo $related_product->get_price(); ?>" onclick="window.location.href='<?php echo esc_url($related_url); ?>'">
                        <div class="fbt-product-image-wrapper">
                            <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr($related_product->get_name()); ?>" class="fbt-product-image">
                            <input type="checkbox" class="fbt-checkbox" checked data-product-id="<?php echo $related_product->get_id(); ?>" onclick="event.stopPropagation();">
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
                <!-- Desktop Button -->
                <div class="desktop-only">
                    <button class="fbt-add-to-cart" onclick="handleAddToCart()">Buy all <span class="fbt-count">4</span>: <span class="fbt-current-price-btn"></span></button>
                </div>
                <!-- Mobile Button -->
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
    
    <!-- Modal for Show Details -->
    <div id="fbt-modal" class="fbt-modal">
        <div class="fbt-modal-content">
            <span class="fbt-modal-close" onclick="closeFBTModal()">&times;</span>
            <h4>Shipping & Seller Information</h4>
            <p><strong>Seller:</strong> MedeCify</p>
            <p><strong>Shipping:</strong> All items in this bundle are shipped together from MedeCify's warehouse.</p>
            <p><strong>Delivery Time:</strong> Standard delivery within 2-5 business days.</p>
            <p><strong>Returns:</strong> 30-day return policy applies to all items in this bundle.</p>
            <p><strong>Bundle Discount:</strong> Save when you buy these items together instead of purchasing them separately.</p>
        </div>
    </div>
    
    <!-- Mobile Modal for FBT -->
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
                            echo $currency_decoded . number_format($current_product_price, 2); 
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Related Products - Only show first 2 for mobile (total 3) -->
                <?php 
                $mobile_count = 0;
                foreach ($related_products as $related_product): 
                    if ($mobile_count >= 2) break; // Only show 2 related products on mobile
                    $mobile_count++;
                    $related_url = $related_product->get_permalink();
                ?>
                <div class="fbt-mobile-product" onclick="window.location.href='<?php echo esc_url($related_url); ?>'">
                    <input type="checkbox" class="fbt-mobile-checkbox" checked data-product-id="<?php echo $related_product->get_id(); ?>" data-price="<?php echo $related_product->get_price(); ?>" onclick="event.stopPropagation();">
                    <div class="fbt-mobile-product-image">
                        <?php 
                        $related_image = wp_get_attachment_image_src($related_product->get_image_id(), 'woocommerce_thumbnail');
                        $related_image = $related_image ? $related_image[0] : wc_placeholder_img_src();
                        ?>
                        <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr($related_product->get_name()); ?>">
                    </div>
                    <div class="fbt-mobile-product-info">
                        <div class="fbt-mobile-product-title"><?php echo esc_html($related_product->get_name()); ?></div>
                        <div class="fbt-mobile-product-size">Size: (Pack of 1)</div>
                        <div class="fbt-mobile-product-price">
                            <?php echo $currency_decoded . number_format($related_product->get_price(), 2); ?>
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
        // Initialize
        updateFBTSummary();
        updateMobileFBTSummary();
        
        // Handle desktop checkbox changes
        $('.fbt-checkbox:not(:disabled)').change(function() {
            updateFBTSummary();
        });
        
        // Handle mobile checkbox changes  
        $('.fbt-mobile-checkbox:not(:disabled)').change(function() {
            updateMobileFBTSummary();
        });
        
        // Update desktop summary (4 products max)
        function updateFBTSummary() {
            var totalPrice = 0;
            var selectedCount = 0;
            var currency = '<?php echo addslashes(html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, "UTF-8")); ?>';
            
            // Always include current product
            var currentPrice = <?php echo floatval($current_product_price); ?>;
            totalPrice += currentPrice;
            selectedCount++;
            
            // Add selected related products (desktop shows max 3 related)
            $('.fbt-checkbox:not(:disabled):checked').each(function() {
                var productElement = $(this).closest('.fbt-product');
                var price = parseFloat(productElement.data('price'));
                if (!isNaN(price)) {
                    totalPrice += price;
                    selectedCount++;
                }
            });
            
            // Update desktop display only
            $('.fbt-current-price').text(currency + totalPrice.toFixed(2));
            $('.fbt-current-price-btn').text(currency + totalPrice.toFixed(2));
            $('.fbt-count').text(selectedCount);
            
            // Enable/disable button
            $('.desktop-only .fbt-add-to-cart').prop('disabled', selectedCount === 0);
        }
        
        // Update mobile summary (3 products max)  
        function updateMobileFBTSummary() {
            var totalPrice = 0;
            var selectedCount = 0;
            var currency = '<?php echo addslashes(html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, "UTF-8")); ?>';
            
            // Always include current product
            var currentPrice = <?php echo floatval($current_product_price); ?>;
            totalPrice += currentPrice;
            selectedCount++;
            
            // Add selected related products (mobile shows max 2 related)
            $('.fbt-mobile-checkbox:not(:disabled):checked').each(function() {
                var price = parseFloat($(this).data('price'));
                if (!isNaN(price)) {
                    totalPrice += price;
                    selectedCount++;
                }
            });
            
            // Update mobile display elements only
            $('.fbt-mobile-current-price').text(currency + totalPrice.toFixed(2));
            $('.fbt-mobile-count-display').text(selectedCount);
            $('.fbt-mobile-price-display').text(currency + totalPrice.toFixed(2));
            $('.fbt-mobile-count').text(selectedCount);
            
            // Enable/disable mobile buttons
            $('.fbt-mobile-add-btn').prop('disabled', selectedCount === 0);
        }
    });
    
    // Handle Add to Cart (Desktop)
    function handleAddToCart() {
        var selectedProducts = [];
        var currentProductId = <?php echo $product->get_id(); ?>;
        
        // Always include current product
        selectedProducts.push({
            id: currentProductId,
            quantity: 1
        });
        
        // Add selected related products (desktop)
        jQuery('.fbt-checkbox:not(:disabled):checked').each(function() {
            var productId = jQuery(this).data('product-id');
            if (productId) {
                selectedProducts.push({
                    id: productId,
                    quantity: 1
                });
            }
        });
        
        addFBTProductsToCart(selectedProducts, 'desktop');
    }
    
    // Add to cart function for mobile
    function addFBTToCart() {
        var selectedProducts = [];
        var currentProductId = <?php echo $product->get_id(); ?>;
        
        // Always include current product
        selectedProducts.push({
            id: currentProductId,
            quantity: 1
        });
        
        // Add selected related products (mobile)
        jQuery('.fbt-mobile-checkbox:not(:disabled):checked').each(function() {
            var productId = jQuery(this).data('product-id');
            if (productId) {
                selectedProducts.push({
                    id: productId,
                    quantity: 1
                });
            }
        });
        
        addFBTProductsToCart(selectedProducts, 'mobile');
    }
    
    // AJAX function to add products to cart
    function addFBTProductsToCart(products, source) {
        if (products.length === 0) return;
        
        var button = source === 'mobile' ? jQuery('.fbt-mobile-add-btn') : jQuery('.desktop-only .fbt-add-to-cart');
        var originalText = button.text();
        
        button.prop('disabled', true).text('Adding...');
        
        jQuery.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: {
                action: 'add_multiple_to_cart',
                products: products,
                security: '<?php echo wp_create_nonce("add_multiple_to_cart_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    button.text('Added to Cart!').css('background', '#4CAF50');
                    
                    // Update cart fragments if available
                    if (response.data && response.data.fragments) {
                        jQuery.each(response.data.fragments, function(key, value) {
                            jQuery(key).replaceWith(value);
                        });
                    }
                    
                    // Trigger cart updated event
                    jQuery('body').trigger('added_to_cart');
                    
                    // Redirect to checkout after brief delay
                    setTimeout(function() {
                        window.location.href = '<?php echo wc_get_checkout_url(); ?>';
                    }, 500);
                } else {
                    console.log('Error response:', response);
                    button.prop('disabled', false).text('Try Again').css('background', '#f44336');
                    setTimeout(function() {
                        button.text(originalText).css('background', '');
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error, xhr.responseText);
                button.prop('disabled', false).text('Try Again').css('background', '#f44336');
                setTimeout(function() {
                    button.text(originalText).css('background', '');
                }, 3000);
            }
        });
    }
    
    // Show Details Modal
    function showFBTDetails(event) {
        event.preventDefault();
        jQuery('#fbt-modal').show();
    }
    
    // Close Details Modal
    function closeFBTModal() {
        jQuery('#fbt-modal').hide();
    }
    
    // Close modal when clicking outside
    jQuery(window).click(function(event) {
        if (jQuery(event.target).is('#fbt-modal')) {
            closeFBTModal();
        }
        if (jQuery(event.target).is('#fbt-mobile-modal')) {
            closeMobileFBTModal();
        }
    });
    
    // Mobile modal functions
    function showMobileFBTModal() {
        jQuery('#fbt-mobile-modal').show();
    }
    
    function closeMobileFBTModal() {
        jQuery('#fbt-mobile-modal').hide();
    }
    </script>
    <?php
}

// AJAX handler for adding multiple products to cart
add_action('wp_ajax_add_multiple_to_cart', 'handle_add_multiple_to_cart');
add_action('wp_ajax_nopriv_add_multiple_to_cart', 'handle_add_multiple_to_cart');

function handle_add_multiple_to_cart() {
    // Check if WooCommerce is active
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error('WooCommerce not available');
        return;
    }
    
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['security'], 'add_multiple_to_cart_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    if (!isset($_POST['products']) || !is_array($_POST['products'])) {
        wp_send_json_error('No products specified');
        return;
    }
    
    $added_products = array();
    $errors = array();
    
    foreach ($_POST['products'] as $item) {
        if (!isset($item['id']) || !isset($item['quantity'])) {
            continue;
        }
        
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        
        if ($product_id <= 0 || $quantity <= 0) {
            continue;
        }
        
        // Check if product exists and is purchasable
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_purchasable()) {
            $errors[] = sprintf('Product %d is not available', $product_id);
            continue;
        }
        
        // Add to cart
        try {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
            
            if ($cart_item_key) {
                $added_products[] = array(
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'cart_item_key' => $cart_item_key
                );
            } else {
                $errors[] = sprintf('Failed to add product %d to cart', $product_id);
            }
        } catch (Exception $e) {
            $errors[] = sprintf('Error adding product %d: %s', $product_id, $e->getMessage());
        }
    }
    
    if (empty($added_products)) {
        wp_send_json_error(array(
            'message' => 'No products were added to cart',
            'errors' => $errors
        ));
        return;
    }
    
    // Get updated cart fragments
    $fragments = array();
    if (function_exists('woocommerce_mini_cart')) {
        ob_start();
        woocommerce_mini_cart();
        $mini_cart = ob_get_clean();
        
        $fragments = apply_filters('woocommerce_add_to_cart_fragments', array(
            'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
            '.cart-contents' => '<span class="cart-contents">' . WC()->cart->get_cart_contents_count() . '</span>',
            '.cart-total' => '<span class="cart-total">' . WC()->cart->get_cart_subtotal() . '</span>'
        ));
    }
    
    wp_send_json_success(array(
        'message' => 'Products added to cart successfully',
        'added_products' => $added_products,
        'errors' => $errors,
        'fragments' => $fragments,
        'cart_hash' => WC()->cart->get_cart_hash(),
        'cart_count' => WC()->cart->get_cart_contents_count()
    ));
}