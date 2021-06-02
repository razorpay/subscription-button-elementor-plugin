<?php

/**
 * Plugin Name: Razorpay Subscription Button Elementor
 * Plugin URI:  https://github.com/razorpay/subscription-button-elementor-plugin
 * Description: Razorpay Subscription Button Elementor
 * Version:     1.0
 * Author:      Razorpay
 * Author URI:  https://razorpay.com
 */

require_once __DIR__.'/razorpay-sdk/Razorpay.php';
require_once __DIR__.'/includes/rzp-btn-view.php';
require_once __DIR__.'/includes/rzp-btn-action.php';
require_once __DIR__.'/includes/rzp-btn-settings.php';
require_once __DIR__.'/includes/rzp-subscription-buttons.php';
require_once __DIR__.'/widget/Widget.php';


use Razorpay\Api\Api;
use Razorpay\Api\Errors;

add_action('admin_enqueue_scripts', 'bootstrap_scripts_enqueue_sub_elementor', 0);
add_action('admin_post_rzp_subs_btn_elementor_action', 'razorpay_subscription_button_elementor_action', 0);

function bootstrap_scripts_enqueue_sub_elementor() 
{
    wp_register_style('bootstrap-css-sub-elementor', plugin_dir_url(__FILE__)  . 'public/css/bootstrap.min.css',
                null, null);
    wp_register_style('button-css-sub-elementor', plugin_dir_url(__FILE__)  . 'public/css/button.css',
                null, null);
    wp_enqueue_style('bootstrap-css-sub-elementor');
    wp_enqueue_style('button-css-sub-elementor');

    wp_enqueue_script('jquery');
}

/**
 * This is the RZP Subscription button loader class.
 *
 * @package RZP WP List Table
 */
if (!class_exists('RZP_Subscription_Button_Elementor_Loader')) 
{

	// Adding constants
    if (!defined('RZP_SUBSCRIPTION_ELEMENTOR_BASE_NAME'))
    {
        define('RZP_SUBSCRIPTION_ELEMENTOR_BASE_NAME', plugin_basename(__FILE__));
        
    }

    if (!defined('RZP_REDIRECT_URL'))
    {
        // admin-post.php is a file that contains methods for us to process HTTP requests
        define('RZP_REDIRECT_URL', esc_url( admin_url('admin-post.php')));
    }

	class RZP_Subscription_Button_Elementor_Loader {
		/**
		 * Start up
		 */
		public function __construct()
		{
			add_action('admin_menu', array( $this, 'rzp_add_sub_plugin_page'));

			add_filter('plugin_action_links_' . RZP_SUBSCRIPTION_ELEMENTOR_BASE_NAME, array($this, 'razorpay_sub_plugin_links'));

            $this->settings = new RZP_Subscription_Button_Elementor_Setting();
           
		}

        /**
         * Creating the menu for plugin after load
        **/

        public function rzp_add_sub_plugin_page()
        {
            /* add pages & menu items */
            add_menu_page( esc_attr__( 'Razorpay Subscription Button', 'textdomain' ), esc_html__( 'Razorpay Subscription Buttons Elementor', 'textdomain' ),
            'administrator','razorpay_subs_button_elementor',array( $this, 'rzp_view_sub_buttons_page' ), '', 10);

            add_submenu_page( esc_attr__( 'razorpay_subs_button_elementor', 'textdomain' ), esc_html__( 'Razorpay Settings', 'textdomain' ),
            'Settings', 'administrator','razorpay_sub_elementor_settings', array( $this, 'razorpay_sub_elementor_settings' ));  

            add_submenu_page( esc_attr__( '', 'textdomain' ), esc_html__( 'Razorpay Subscription Buttons Elementor', 'textdomain' ),
            'Razorpay Subscription Buttons Elementor', 'administrator','rzp_button_view_sub_elementor', array( $this, 'rzp_button_view_sub_elementor' ));
        }


        /**
         * Initialize razorpay api instance
        **/
        public function get_razorpay_api_instance()
        {
            $key = get_option('key_id_field');

            $secret = get_option('key_secret_field');

            if(empty($key) === false && empty($secret) === false)
            {
                return new Api($key, $secret);
            }

            wp_die('<div class="error notice">
                        <p>RAZORPAY ERROR: Subscription button fetch failed.</p>
                     </div>'); 
        } 

       
		/**
         * Creating the settings link from the plug ins page
        **/
        function razorpay_sub_plugin_links($links)
        {
            $pluginLinks = array(
                            'settings' => '<a href="'. esc_url(admin_url('admin.php?page=razorpay_sub_elementor_settings')) .'">Settings</a>',
                            'docs'     => '<a href="#">Docs</a>',
                            'support'  => '<a href="https://razorpay.com/contact/">Support</a>'
                        );

            $links = array_merge($links, $pluginLinks);

            return $links;
        }
	
		/**
		 * Razorpay Subscription Button Page
		 */
		public function rzp_view_sub_buttons_page()
		{
			$rzp_subscription_buttons = new RZP_Subscription_Buttons_Elementor();

			$rzp_subscription_buttons->rzp_buttons(); 
		}	

        /**
         * Razorpay Setting Page
         */
        public function razorpay_sub_elementor_settings()
        {
            $this->settings->razorpaySettings();
        }  

        /**
         * Razorpay Setting Page
         */
        public function rzp_button_view_sub_elementor()
        {
            $new_button = new RZP_View_Subs_Button_Elementor();

            $new_button->razorpay_view_button();
        }  
		
	}

}

		
/**
* Instantiate the loader class.
*
* @since     2.0
*/
$RZP_Subscription_Button_Elementor_Loader = new RZP_Subscription_Button_Elementor_Loader();

function razorpay_subscription_button_elementor_action()
{
    $btn_action = new RZP_Subs_Button_Action_Elementor();
    
    $btn_action->process();
}
		