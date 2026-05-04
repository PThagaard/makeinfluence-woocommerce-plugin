<?php

namespace Thagaard\MakeInfluence;

use DateTimeZone;
use WC_DateTime;

class Plugin
{
    private $pluginName;

    public function __construct()
    {
        $this->pluginName = __('Make Influence', 'makeinfluence');

        add_action('admin_menu', [
            $this,
            'AdminMenu',
        ]);

        add_action('admin_init', [
            $this,
            'RegisterSettings',
        ]);

        add_action('woocommerce_thankyou', [
            $this,
            'SendTracking',
        ]);

        add_action('woocommerce_thankyou', [
            $this,
            'AddTracking',
        ]);
		
        add_action('woocommerce_thankyou', [
            $this,
            'AddConversionTracking',
        ]);
		
		add_action('wp_footer', [
            $this,
            'AddPageTracking',
        ]);

        add_action('woocommerce_thankyou_makeinfluence', [
            $this,
            'SendTracking',
        ]);

        add_action('woocommerce_thankyou_makeinfluence', [
            $this,
            'AddTracking',
        ]);
		
		add_action('woocommerce_thankyou_makeinfluence', [
            $this,
            'AddConversionTracking',
        ]);
		¨
		add_action('admin_notices', [
            $this,
            'AdminNotices',
        ]);
		
		add_action('admin_head', [
            $this,
            'AdminCSS',
        ]);
		
		add_action( 'init', [
            $this,
            'makeinfluence_load_textdomain',
        ]);
		
    }

	public function makeinfluence_load_textdomain() {
		load_plugin_textdomain( 'makeinfluence', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}

    public function AdminMenu()
    {
        add_menu_page(
			'Make Influence',
			'Make Influence',
			'administrator',
			'make-influence', [
				$this,
				'adminPage',
			],
			get_home_url().'/wp-content/plugins/makeinfluence/icon.png',
			58
		);
    }


    public function AdminPage()
    {
        require('partials/admin-page.php');
    }

    public function RegisterSettings()
    {
        register_setting(
            'makeinfluence-settings-group',
            'makeinfluence_business_id', [
            $this,
            'SanitizeText',
        ]);
		
        register_setting(
            'makeinfluence-settings-group',
            'makeinfluence_ios_tracking', [
            $this,
		]);
		
        register_setting(
            'makeinfluence-settings-group',
            'makeinfluence_debug_log', [
            $this,
		]);
    }
	
	public function AdminNotices()
	{
		if ( !get_option('makeinfluence_business_id') ) {
			echo '<div class="notice notice-error is-dismissible"><p>Make Influence: '.__("Business ID mangler.","makeinfluence").' <a href="'.esc_url(admin_url()).'admin.php?page=make-influence">'.__("Gå til indstillinger","makeinfluence").'</a></p></div>';
		}
		if ( get_option('makeinfluence_debug_log') ) {
			echo '<div class="notice notice-error is-dismissible"><p>Make Influence: '.__("Debug log er aktiv - Husk at slå dette fra efter test","makeinfluence").' <a href="'.esc_url(admin_url()).'admin.php?page=make-influence">'.__("Gå til indstillinger","makeinfluence").'</a></p></div>';
		}
		if ( !class_exists( 'WooCommerce' ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>Make Influence: '.__("WooCommerce er ikke aktiveret","makeinfluence").'</p></div>';
		}
	}	
	
	public function AdminCSS()
	{
		echo '<style>
			#adminmenu li#toplevel_page_make-influence img {
				width: 26px;
				height: 26px;
				padding-top: 4px!important;
			}
		</style>';
	}

    public function AddPageTracking()
    {
		
        $disablePagetracking = sanitize_option('makeinfluence_ios_tracking',get_option('makeinfluence_ios_tracking'));
        $businessId = sanitize_option('makeinfluence_business_id',get_option('makeinfluence_business_id'));
		
        if ( $disablePagetracking || empty($businessId) ) {
			return;
        }
		
		echo "<script>
		(function(m,a,k,e,i,n,f) {
			m['MakeInfluenceObject']=i;m[i]=m[i]||{q:[]},
			m[i]=new Proxy(m[i],{get:function(t,p,r){return t.hasOwnProperty(p)
			?t[p]:function(){m[i].q.push({n:p,a:arguments})}}}),n=a.createElement(k),
			f=a.getElementsByTagName(k)[0],n.async=1,n.src=e+'?'+Math.floor(new Date()
			/864e5);f.parentNode.insertBefore(n,f);
		})(window,document,'script','//scripts.makeinfluence.com/a.js', 'MI');
			
			MI.set('business_id', '".esc_attr($businessId)."');
			MI.send('pageview');
		</script>";
    }
	
	public function AddConversionTracking($order_id)
    {
		$order 					= wc_get_order($order_id);
        $disablePagetracking 	= sanitize_option('makeinfluence_ios_tracking',get_option('makeinfluence_ios_tracking'));
        $businessId 			= sanitize_option('makeinfluence_business_id',get_option('makeinfluence_business_id'));
		
		$skip_tracking = apply_filters('mi_modify_skip_tracking', false, $order);
		if ($skip_tracking) return;
		
		if ( $disablePagetracking || empty($businessId) ) {
			return;
        }
		
		if (! $this->shouldSendTracking($order, $businessId)) {
			if ( get_option('makeinfluence_debug_log') ) {
				$this->MIlog("[AddConversionTracking] shouldSendTracking for order #".esc_attr($order_id).": FALSE");
			}
            return;
        }
		
		if ( get_option('makeinfluence_debug_log') ) {
			$this->MIlog("[AddConversionTracking] shouldSendTracking for order #".esc_attr($order_id).": TRUE");
		} 

		$js_promotion_codes = NULL;
		if ( !empty( $order->get_coupon_codes() ) ) {
			$js_promotion_codes = wp_kses_post( json_encode($order->get_coupon_codes()) );
		} else {
			$js_promotion_codes = "''";
		}
			
		echo "<script>
				jQuery(document).ready(function() {
					MI.send('conversion', {
					  unique_id: 		'".esc_attr( $order_id )."',
					  value: 			'".esc_attr( (float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() )."',
					  currency: 		'".esc_attr( $order->get_currency() )."',
					  promotion_codes: 	".$js_promotion_codes."
					});
				});
			</script>";
    }
	
    public function AddTracking($order_id)
    {
        $order 			= wc_get_order($order_id);
        $businessId 	= sanitize_option('makeinfluence_business_id',get_option('makeinfluence_business_id'));
		
		$skip_tracking = apply_filters('mi_modify_skip_tracking', false, $order);
		if ($skip_tracking) return;
		
        if (! $this->shouldSendTracking($order, $businessId)) {
			if ( get_option('makeinfluence_debug_log') ) {
				$this->MIlog("[AddTracking] shouldSendTracking for order #".esc_attr($order_id).": FALSE");
			}
            return;
        }
		if ( get_option('makeinfluence_debug_log') ) {
			$this->MIlog("[AddTracking] shouldSendTracking for order #".esc_attr($order_id).": TRUE");
		}
		
		$promotion_code = NULL;
		if ( !empty( $order->get_coupon_codes() ) ) {
			$promotion_code = implode(',', $order->get_coupon_codes());
		}
		
        echo '<img src="https://system.makeinfluence.com/p?' . esc_html(http_build_query([
                'bid' 				=> esc_attr( $businessId ),
                'uid' 				=> esc_attr( $order->get_id() ),
                'value' 			=> esc_attr( (float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() ),
                'promotion_code' 	=> esc_attr( sanitize_text_field($promotion_code) ),
                'currency' 			=> esc_attr( $order->get_currency() ),
            ]) ) . '">';
    }

    public function SendTracking($order_id)
    {
        $order 			= wc_get_order($order_id);
        $businessId 	= sanitize_option('makeinfluence_business_id',get_option('makeinfluence_business_id'));
		
		$skip_tracking = apply_filters('mi_modify_skip_tracking', false, $order);
		if ($skip_tracking) return;
		
        if (! $this->shouldSendTracking($order, $businessId)) {
			if ( get_option('makeinfluence_debug_log') ) {
				$this->MIlog("[SendTracking] shouldSendTracking for order #".esc_attr($order_id).": FALSE");
			}
            return;
        }
		if ( get_option('makeinfluence_debug_log') ) {
			$this->MIlog("[SendTracking] shouldSendTracking for order #".esc_attr($order_id).": TRUE");
		}
		
		$promotion_code = NULL;
		if ( !empty( $order->get_coupon_codes() ) ) {
			$promotion_code = implode(',', $order->get_coupon_codes());
		}

        $response = wp_remote_post('https://system.makeinfluence.com/track-conversion', [
            'body' => [
                'business_id' 		=> $businessId,
                'cookie_id' 		=> isset($_COOKIE["_miid"]) ? sanitize_text_field($_COOKIE["_miid"]) : null,
                'unique_id' 		=> $order->get_id(),
                'value' 			=> (float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping(),
                'promotion_code' 	=> sanitize_text_field($promotion_code),
                'created_at' 		=> $order->get_date_created()->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
                'currency' 			=> $order->get_currency(),
                'ip' 				=> isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : null,
                'user_agent' 		=> isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : null,
                'http_referer' 		=> isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field($_SERVER['HTTP_REFERER']) : null,
            ],
        ]);
		
		if ( get_option('makeinfluence_debug_log') && !is_wp_error( $response ) ) {
			$this->MIlog("POST-Tracking sent and recieved (200 OK) for order #".esc_attr($order->get_id()));
		}
		
    }

    public function SanitizeText($input)
    {
        return sanitize_text_field($input);
    }
	
	private function MIlog($message)
	{
		$logger = wc_get_logger();
		$context = array( 'source' => 'make-influence-for-woocommerce' );
		$logger->debug( esc_attr(sanitize_textarea_field($message)), $context );
	}

    private function shouldSendTracking($order, $businessId)
    {
        if (! $order or ! $businessId) {
            return false;
        }
		
		if ( class_exists( 'WC_Subscriptions' ) ) {
			if ( wcs_order_contains_subscription($order) && !wcs_order_contains_subscription( $order, array("parent") ) ) {
				return false;
			}
		}

        $createdAtTimestamp = $order->get_date_created()->setTimezone(new DateTimeZone('UTC'))->getTimestamp();
        $nowTimestamp = new WC_DateTime();
        $nowTimestamp = $nowTimestamp->setTimezone(new DateTimeZone('UTC'))->getTimestamp();
        $difference = $nowTimestamp - $createdAtTimestamp;

        if ($difference > 60 * 15) {
            return false;
        }
		
        return true;
    }
}
