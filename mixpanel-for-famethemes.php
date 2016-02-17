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

        $session = edd_get_purchase_session();

        $is_send = true;

        if ( ! $edd_receipt_args['id']  ) {
            $is_send = false;
        }
        $sent =  EDD()->session->get( 'ft_mp_sent_'.$edd_receipt_args['id'] );
        if ( $sent ) {
            $is_send = false;
        }

        $price = $session['price'];
        if ( floatval( $price ) <= 0 ) {
            $is_send = false;
        }


        ?>
        <!-- start Mixpanel -->
        <script type="text/javascript">
            var utm_source, utm_medium, utm_campaign, utm_content;
            var current_user_id = <?php echo intval( $session['user_info']['id'] ) ?>;
            var is_send = <?php echo $is_send ? 'true': 'false'; ?>;

            (function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(".");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;"undefined"!==typeof d?c=b[d]=[]:d="mixpanel";c.people=c.people||[];c.toString=function(b){var a="mixpanel";"mixpanel"!==d&&(a+="."+d);b||(a+=" (stub)");return a};c.people.toString=function(){return c.toString(1)+".people (stub)"};i="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
                for(g=0;g<i.length;g++)f(c,i[g]);b._i.push([a,e,d])};b.__SV=1.2;a=e.createElement("script");a.type="text/javascript";a.async=!0;a.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";f=e.getElementsByTagName("script")[0];f.parentNode.insertBefore(a,f)}})(document,window.mixpanel||[]);

            mixpanel.init("<?php echo FT_MP_API_KEY; ?>", {
                loaded: function() {

                    if ( current_user_id > 0 && is_send ) {
                        utm_source   = mixpanel.get_property("utm_source")  ;
                        utm_medium   = mixpanel.get_property("utm_medium")  ;
                        utm_campaign = mixpanel.get_property("utm_campaign");
                        utm_content  = mixpanel.get_property("utm_content") ;
                        mixpanel.identify( current_user_id );

                        if ( typeof utm_source !== "undefined" ) {
                            mixpanel.people.set( 'utm_source', utm_source );
                            mixpanel.people.append( 'utm_source', utm_source );
                        }

                        if ( typeof utm_medium !== "undefined" ) {
                            mixpanel.people.set( 'utm_medium', utm_medium );
                            mixpanel.people.append( 'utm_medium', utm_medium );
                        }

                        if ( typeof utm_campaign !== "undefined" ) {
                            mixpanel.people.set( 'utm_campaign', utm_campaign );
                            mixpanel.people.append( 'utm_campaign', utm_campaign );
                        }

                        if ( typeof utm_content !== "undefined" ) {
                            mixpanel.people.set( 'utm_content', utm_content );
                            mixpanel.people.append( 'utm_content', utm_content );
                        }

                    }

                }
                }
            );



        </script>
        <!-- end Mixpanel -->
        <?php
        if ( ! $is_send  ) {
            return ;
        }
       // global $edd_receipt_args;
        $success_page = edd_get_option( 'success_page' ) ? is_page( edd_get_option( 'success_page' ) ) : false;
        if ( ! $success_page || ! edd_is_success_page() ){
            return;
        }

        $cart_items = $session['cart_details'];

        if ( isset( $edd_receipt_args['id'] ) ) {
            EDD()->session->set( 'ft_mp_sent_'.$edd_receipt_args['id'], true );
        }
        //  $session['user_info']['id']
        ?>
        <script type="text/javascript">
            mixpanel.identify( <?php echo intval( $session['user_info']['id'] ) ?> );
            mixpanel.people.set( {
                '$first_name' : '<?php echo esc_attr( $session['user_info']['first_name'] ); ?>',
                '$last_name'  : '<?php echo esc_attr( $session['user_info']['last_name'] ); ?>',
                '$email'      : "<?php echo esc_attr( $session['user_info']['email'] ) ?>",
            });

            <?php
            foreach ( $cart_items as $product ){
            ?>
            mixpanel.people.track_charge( <?php echo floatval( $product["price"] ); ?>, { "Product Name": '<?php echo esc_attr( $product["name"] ); ?>' } );
            mixpanel.track( 'Purchased', {"Amount": <?php echo floatval( $product["price"] ) ?>, "Product Name":<?php echo json_encode( $product["name"] ); ?> } );
            //people.append and people.union both add strings to an array property that is not displayed correctly
            mixpanel.people.set( 'Product Name', '<?php echo esc_attr( $product["name"] ); ?>' );
            mixpanel.people.append( 'Product Name', '<?php echo esc_attr( $product["name"] ); ?>' );
            //instead, you can use people.set, and add to the profile the latest product name purchased
            //mixpanel.people.set({ 'Latest Product Purchased': '<?php echo esc_attr( $product["name"] ); ?>'});
            <?php
             }
            ?>
        </script>
        <?php
    }


}

new FT_MP();
