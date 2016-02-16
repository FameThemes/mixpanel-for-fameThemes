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

define( 'FT_MP_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'FT_MP_API_KEY', 'baffcc730f0885fb6e682a0d629b2fa0' );


class FT_MP {

    function __construct(){
       add_action( 'wp_footer',array( __CLASS__ , 'footer' ) );
    }

    public static function footer() {
        global $edd_receipt_args;
        ?>
        <!-- start Mixpanel -->
        <script type="text/javascript">(function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(".");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;"undefined"!==typeof d?c=b[d]=[]:d="mixpanel";c.people=c.people||[];c.toString=function(b){var a="mixpanel";"mixpanel"!==d&&(a+="."+d);b||(a+=" (stub)");return a};c.people.toString=function(){return c.toString(1)+".people (stub)"};i="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
                for(g=0;g<i.length;g++)f(c,i[g]);b._i.push([a,e,d])};b.__SV=1.2;a=e.createElement("script");a.type="text/javascript";a.async=!0;a.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";f=e.getElementsByTagName("script")[0];f.parentNode.insertBefore(a,f)}})(document,window.mixpanel||[]);
            mixpanel.init("<?php echo FT_MP_API_KEY; ?>");</script>
        <!-- end Mixpanel -->
        <?php
        if ( ! $edd_receipt_args['id']  ) {
            return ;
        }
        $sent =  EDD()->session->get( 'ft_mp_sent_'.$edd_receipt_args['id'] );
        if ( $sent ) {
           return ;
        }
       // global $edd_receipt_args;
        $success_page = edd_get_option( 'success_page' ) ? is_page( edd_get_option( 'success_page' ) ) : false;
        if ( ! $success_page || ! edd_is_success_page() ){
            return;
        }
        $session = edd_get_purchase_session();
        $price = $session['price'];
        $cart_items = $session['cart_details'];

        if ( isset( $edd_receipt_args['id'] ) ) {
            EDD()->session->set( 'ft_mp_sent_'.$edd_receipt_args['id'], true );
        }
        //  $session['user_info']['id']
        ?>
        <script type="text/javascript">

            mixpanel.identify( <?php echo $session['user_info']['id'] ?> );
            mixpanel.people.track_charge( <?php echo floatval( $price ); ?> );
            mixpanel.people.set( {
                '$first_name' : '<?php echo esc_attr( $session['user_info']['first_name'] ); ?>',
                '$last_name'  : '<?php echo esc_attr( $session['user_info']['last_name'] ); ?>',
                '$email'      : "<?php echo esc_attr( $session['user_info']['email'] ) ?>",
            });
            <?php
            foreach ( $cart_items as $product ){
            ?>
            mixpanel.track( 'Purchase', {"Amount": <?php echo floatval( $product["item_price"] ) ?>, "Product":<?php echo json_encode( $product["name"] ); ?> });
            mixpanel.people.union( {
                Products: '<?php echo esc_attr( $product["name"] ); ?>'
            } );
            <?php
             }
            ?>
        </script>
        <?php
    }


}

new FT_MP();
