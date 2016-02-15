<?php
/**
 * Plugin Name:     MixPanel for FameThemes
 * Plugin URI:      #
 * Description:
 * Version:         1.0.0
 * Author:          FameThemes
 * Author URI:      #
 * Text Domain:
 */

// import Mixpanel
require dirname( __FILE__ ). '/mixpanel/lib/Mixpanel.php';

// get the Mixpanel class instance, replace with your project token
// a513e89907b67abc5b3aea400e4e9707


class FT_MP_Track_Payment {
    public $user_id;
    public $payment;
    public $user = false;

    function __construct( $payment_id , $status = false ) {


        $total =  get_post_meta( $payment_id, '_edd_payment_total', true );
        $customer_id =  get_post_meta( $payment_id, '_edd_payment_customer_id', true );
        $meta =  get_post_meta( $payment_id, '_edd_payment_meta', true );
        if ( ! is_array( $meta ) ) {
            $meta = array();
        }

        if ( ! isset ( $meta['cart_details'] ) || ! is_array( $meta['cart_details'] ) || empty( $meta['cart_details']  )  ) {
            return;
        }

       // var_dump(  $meta['cart_details'] );

        //

        //return ;


        // $trans_id = edd_get_payment_transaction_id( $payment_id );
        $mp = Mixpanel::getInstance("a513e89907b67abc5b3aea400e4e9707");
        $total = floatval( $total );
        if ( $status == 'publish' ){
            foreach ( $meta['cart_details'] as $item ) {
                $mp->track(
                    "Purchase",
                    array(
                        "Amount" => floatval( $item['price'] ),
                        'Product Name'=>  $item['name']
                    )
                );
            }
            // mixpanel.track("Purchase", {"Amount":100, "Product Name":"X", ... }};
            // $mp->people->trackCharge( $customer_id, $total );
            // $mp->people->append( $customer_id, "favorites", array("Baseball", "Reading"));
        }

        switch( $status ) {
            case 'refunded':
               // $mp->people->trackCharge( $customer_id, - $total );
                foreach ( $meta['cart_details'] as $item ) {
                    $mp->track( "Purchase", array( "Amount" => - floatval( $item['price'] ), 'Product Name'=>  $item['name'] ) );
                }
                break;
            case 'failed':
                break;
        }


        // track a custom "button click" event
        ///$mp->track("button click", array("label" => "Login"));


        // track an event
        //$mp->track("button clicked", array("label" => "sign-up"));

        //$author_obj = get_user_by('id', $this->user_id );
        /*
        switch( $status ) {
            case 'refunded':
                $this->process_refund();
                break;
            case 'failed':
                $this->process_failure();
                break;
        }
        */

    }


}

class FT_MP {

    function __construct(){
        add_action( 'edd_before_payment_status_change', array( __CLASS__, 'tracking_order' ), 35, 3 );
    }

    public static function tracking_order( $payment_id,  $status = '', $old_status = '' ){
        new FT_MP_Track_Payment( $payment_id , $status, $old_status );
    }

}

new FT_MP();

/*
$mp = Mixpanel::getInstance("a513e89907b67abc5b3aea400e4e9707");

// track an event
$mp->track("button clicked", array("label" => "sign-up"));

// create/update a profile for user id 12345
$mp->people->set(12345, array(
    '$first_name'       => "John",
    '$last_name'        => "Doe",
    '$email'            => "john.doe@example.com",
    '$phone'            => "5555555555",
    "Favorite Color"    => "red"
));

*/