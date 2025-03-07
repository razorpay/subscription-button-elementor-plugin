<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

require_once __DIR__.'/../razorpay-subscription-buttons.php';
require_once __DIR__.'/../razorpay-sdk/Razorpay.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors;

if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * @since 1.1.0
 */
class RazorpayElementsSubscriptionButton extends Widget_Base
{
    /**
     * Retrieve the widget name.
     *
     * @since 1.1.0
     *
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'razorpay_subs_button';
    }

    /**
     * Retrieve the widget title.
     *
     * @since 1.1.0
     *
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Razorpay Subscription Button', 'subscription-for-elementor');
    }

    /**
     * Retrieve the widget icon.
     *
     * @since 1.1.0
     *
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'fa fa-credit-card-alt';
    }

    /**
     * Retrieve the list of categories the widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * Note that currently Elementor supports only one category.
     * When multiple categories passed, Elementor uses the first one.
     *
     * @since 1.1.0
     *
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return array('general');
    }

    /**
     * Register the widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.1.0
     *
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'razorpay_subs_button',
            array(
                'label' => __('Razorpay Subscription Button', 'subscription-for-elementor')
            )
        );

        $this->add_control(
            'select_button',
            [
                'label' => __('Select Button', 'plugin-domain'),
                'type' => Controls_Manager::SELECT,
                'default' => 'select',
                'options' => $this->get_buttons(),
            ]
        );

        $this->end_controls_section();
    }

    public function get_buttons()
    {
        $buttons = array();

        $rzp_payment_button_loader = new RZP_Subscription_Button_Elementor_Loader();

        $api = $rzp_payment_button_loader->get_razorpay_api_instance();

        try
        {
            $items = $api->paymentPage->all(['view_type' => 'subscription_button', "status" => 'active', 'count' => 100]);
        }
        catch (\Exception $e)
        {
            $message = $e->getMessage();

            wp_die('<div class="error notice">
                <p>RAZORPAY ERROR: Payment button fetch failed with the following message: '.$message.'</p>
             </div>');
        }

        if ($items)
        {
            $buttons['select'] = 'select';

            foreach ($items['items'] as $item)
            {
                $buttons[$item['id']] = $item['title'];
            }
        }

        return $buttons;
    }

    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.1.0
     *
     * @access protected
     */
    protected function render()
    {
        if (\Elementor\Plugin::instance()->editor->is_edit_mode())
        {
            return;
        }

        $settings = $this->get_settings_for_display();

        if (isset($settings['select_button']) === true)
        {
            if (! function_exists('get_plugin_data'))
            {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            $mod_version = get_plugin_data(plugin_dir_path(__DIR__) . 'razorpay-subscription-buttons.php')['Version'];

            $dataPlugin = "wordpress-subscription-button-elementor-".$mod_version;
            ?>

            <form>
            <?php

            wp_print_script_tag(
                array(
                    "src" => "https://cdn.razorpay.com/static/widget/subscription-button.js",
                    "data-plugin " => $dataPlugin,
                    "data-subscription_button_id" => (! empty($settings["select_button"]) ? $settings["select_button"] : " ")
                )
            );
            ?>
            </form>
            <?php
        }
    }

    /**
     * Render the widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 2.9.0
     *
     * @access protected
     */
    protected function content_template()
    {
        ?>
        <# if (settings.select_button === 'select') { #>
            <div class="elementor-counter-title">Please select subscription button.</div>
        <# } else { #>
            <img src=" <?php echo esc_url(plugin_dir_url(__FILE__).'../public/image/elementorSVG.svg');?>" alt="Razorpay" >
        <# } #>
        <?php
    }
}
