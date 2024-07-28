<?php
add_action( 'wp_enqueue_scripts', 'free_products_enqueue_scripts' );

function free_products_enqueue_scripts() {
    
    wp_enqueue_script( 'free_products-script', plugins_url( '/js/main.js', __FILE__ ), array('jquery'), '1.0', true );

    wp_localize_script( 'free_products-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}
?>