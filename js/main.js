jQuery(document).ready(function($) {
    $(document).on('change', '.product-item input[type="checkbox"]', function() {
        var productId = $(this).val();
        if ($(this).is(':checked')) {
            addToCart(productId);
        }
    });

    var ajax_url = my_ajax_object.ajax_url;

    function addToCart(productId) {
        var customPrice = 0.00; // Custom price for free product
            // Send AJAX request
            $.ajax({
                url: ajax_url, // AJAX URL defined in WordPress script
                type: 'POST',
                data: {
                    action: 'add_product_to_cart_ajax', // AJAX action name
                    product_id: productId, // Product ID
                    custom_price: customPrice // Custom price
                },
                success: function(response) {
                    // Handle success response
                    jQuery(".button[name='update_cart']").prop('disabled', false);
                    jQuery(".button[name='update_cart']").trigger("click");
                    $(document.body).trigger('wc_fragment_refresh');
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    console.error('Error adding product to cart:', error);
                }
            });
    }
});
