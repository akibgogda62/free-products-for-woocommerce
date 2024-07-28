<?php
/*
Plugin Name: Free products for woocommerce 
Plugin URI: 
Description: A custom plugin for free products.
Version: 1.0
Author: Aakib Gogda
Author URI: https://github.com/akibgogda62/
*/

require_once plugin_dir_path(__FILE__) . "settings.php";
require_once plugin_dir_path(__FILE__) . "general-functions.php";

add_action("woocommerce_after_cart_table", "display_free_products_after_cart");
add_action("woocommerce_product_options_general_product_data","add_custom_checkbox_field");
add_action("woocommerce_process_product_meta", "save_custom_checkbox_field");
add_action("wp_ajax_add_product_to_cart_ajax", "add_product_to_cart_ajax");
add_action("wp_ajax_nopriv_add_product_to_cart_ajax","add_product_to_cart_ajax");
add_action("woocommerce_before_calculate_totals", "set_price_as_free");
add_filter("woocommerce_cart_item_name","set_product_custom_name_for_free",1,3);
add_filter("woocommerce_cart_item_quantity", "set_quantity", 10, 3);

function display_free_products_after_cart()
{
    // Check if the checkbox is enabled
    if (get_option("free_products_enable_checkbox")) {
        if (WC()->cart->subtotal > get_option("minimum_order_price")) {

            $max_gift = floor(
                WC()->cart->subtotal / get_option("minimum_order_price")
            );
            $count = 0;

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                if (isset($cart_item["free_item_from_cart"])) {
                    $product_id = $cart_item["free_item_from_cart"]["product_id"];
                    $code = $cart_item["free_item_from_cart"]["code"];
                    $quantity = $cart_item["quantity"];
                    // Check if the code matches the specified pattern
                    if ($code == "free-" . $product_id) {
                        $count += $quantity;
                    }
                }
            }
            $display = false;
            if ($count >= $max_gift) {
                $display = true;
            }
            ?>
            <div class="gift-products" style="display:<?php echo $display
                ? "none"
                : "block"; ?>">
                <h1><?php echo "You can also add the below items in cart at no extra cost."; ?></h1>
                <?php
                // Custom query to fetch products marked as "Consider for Gift?"
                $args = [
                    "post_type" => "product",
                    "posts_per_page" => -1,
                    "meta_query" => [
                        [
                            "key" => "_consider_for_gift",
                            "value" => "yes",
                            "compare" => "=",
                        ],
                    ],
                ];

                $gift_products_query = new WP_Query($args);
                $gift_products_posts = $gift_products_query->posts;

                if (!empty($gift_products_posts)):
                    foreach ($gift_products_posts as $post):

                        $product_id = $post->ID;
                        $product_title = get_the_title($product_id);
                        setup_postdata($post);
                        ?>
                        <div class="product-item">
                            <input type="checkbox" id="product-<?php echo $product_id; ?>" name="selected_products[]" value="<?php echo $product_id; ?>" class="select-product-checkbox">
                            <label for="product-<?php echo $product_id; ?>"><?php echo "Free " .
    $product_title; ?></label>
                        </div>
                <?php
                    endforeach;
                    wp_reset_postdata();
                endif;
                ?>

            </div>
<?php
        }
    }
}

function add_custom_checkbox_field()
{
    woocommerce_wp_checkbox([
        "id" => "_consider_for_gift",
        "label" => __("Consider for Gift?", "woocommerce"),
        "description" => __(
            "Check this box to mark the product as a gift item.",
            "woocommerce"
        ),
    ]);
}

function save_custom_checkbox_field($post_id)
{
    $checkbox = isset($_POST["_consider_for_gift"]) ? "yes" : "no";
    update_post_meta($post_id, "_consider_for_gift", $checkbox);
}

function add_product_to_cart_ajax()
{
    if (isset($_POST["product_id"]) && isset($_POST["custom_price"])) {
        $product_id = intval($_POST["product_id"]);
        $custom_price = floatval($_POST["custom_price"]);
        $quantity = 1;

        // Add product to cart with custom price

        WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            0,
            [],
            [
                "free_item_from_cart" => [
                    "code" => "free-" . $product_id,
                    "product_id" => $product_id,
                    "title" => "FREE " . get_the_title($product_id),
                ],
            ]
        );

        wp_send_json_success("Product added to cart.");
    } else {
        wp_send_json_error("Invalid request.");
    }
    wp_die();
}

function set_price_as_free($cart_object)
{
    foreach ($cart_object->cart_contents as $cart_item_key => $value) {
        if (!isset($value["free_item_from_cart"])) {
            continue;
        }
        $product_id = $value["free_item_from_cart"]["product_id"];
        $code = $value["free_item_from_cart"]["code"];
        if ($code == "free-" . $product_id) {
            $value["data"]->set_price(0);
        }
    }
}

function set_quantity($quantity, $cart_item_key, $cart_item)
{
    // Check if the cart item has the 'free_item_from_cart' key
    if (isset($cart_item["free_item_from_cart"])) {
        $product_id = $cart_item["free_item_from_cart"]["product_id"];
        $code = $cart_item["free_item_from_cart"]["code"];

        // Check if the code matches the specified pattern
        if ($code == "free-" . $product_id) {
            // Get the current quantity of the item in the cart
            $current_quantity = isset($cart_item["quantity"])
                ? $cart_item["quantity"]
                : 0;

            // Set quantity as current quantity + 1
            $quantity = $current_quantity;
        }
    }

    return $quantity;
}

function set_product_custom_name_for_free(
    $product_name,
    $values,
    $cart_item_key
) {
    if (!empty($values["free_item_from_cart"])) {
        $product_name =
            $values["free_item_from_cart"]["title"] ?? $product_name;
    }

    return $product_name;
}
?>
