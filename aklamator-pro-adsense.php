<?php
/*
Plugin Name: Aklamator Pro Adsense
Plugin URI: http://www.aklamator.com/wordpress
Description: Aklamator Pro AdSense digital PR plugin enables you to easily place AdSense or other custom Ad code on your wordpress site. It also enables you to sell PR announcements, cross promote web sites using RSS feed and provide new services to your clients in digital advertising.
Version: 1.3.2
Author: Aklamator
Author URI: http://www.aklamator.com/
License: GPL2

Copyright 2015 Aklamator.com (email : info@aklamator.com)

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/


/*
 * Add setting link on plugin page
 */

if( !function_exists("aklamatorPro_plugin_settings_link")){

    function aklamatorPro_plugin_settings_link($links) {
        $settings_link = '<a href="admin.php?page=aklamator-pro-adsense">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'aklamatorPro_plugin_settings_link',10 ,2);


/*
 * Activation Hook
 */

register_activation_hook( __FILE__, 'set_up_optionsPro' );

function set_up_optionsPro(){
    add_option('aklamatorProApplicationID', '');
    add_option('aklamatorProPoweredBy', '');
    add_option('aklamatorProSingleWidgetID', '');
    add_option('aklamatorProPageWidgetID', '');
    add_option('aklamatorProSingleWidgetTitle', '');

    // Ads codes
    add_option('aklamatorProAds', '');
    add_option('aklamatorProAds2', '');
    add_option('aklamatorProAds3', '');

    // Custom Ads names
    add_option('aklamatorProAds1Name', '');
    add_option('aklamatorProAds2Name', '');
    add_option('aklamatorProAds3Name', '');
}

/*
 * Uninstall Hook
 */

register_uninstall_hook(__FILE__, 'aklamatorPro_uninstall');

function aklamatorPro_uninstall()
{

    if (get_option('aklamatorProApplicationID')) {
        delete_option('aklamatorProApplicationID');
    }

    if (get_option('aklamatorProPoweredBy')) {
        delete_option('aklamatorProPoweredBy');
    }

    if(get_options('aklamatorProSingleWidgetID')){
        delete_options('aklamatorProSingleWidgetID');
    }

    if(get_options('aklamatorProPageWidgetID')){
        delete_options('aklamatorProPageWidgetID');
    }

    if(get_options('aklamatorProSingleWidgetTitle')){
        delete_options('aklamatorProSingleWidgetTitle');
    }

    // Ads codes
    if(get_options('aklamatorProAds')){
        delete_options('aklamatorProAds');
    }
    if(get_options('aklamatorProAds2')){
        delete_options('aklamatorProAds2');
    }
    if(get_options('aklamatorProAds3')){
        delete_options('aklamatorProAds3');
    }

    // Custom Ad names
    if(get_options('aklamatorProAds1Name')){
        delete_options('aklamatorProAds1Name');
    }
    if(get_options('aklamatorProAds2Name')){
        delete_options('aklamatorProAds2Name');
    }
    if(get_options('aklamatorProAds3Name')){
        delete_options('aklamatorProAds3Name');
    }


}


if (!function_exists("bottom_of_every_postPro")) {
    function bottom_of_every_postPro($content)
    {

        /*  we want to change `the_content` of posts, not pages
            and the text file must exist for this to work */

        if (is_single()) {
            $widget_id = get_option('aklamatorProSingleWidgetID');
        } elseif (is_page()) {
            $widget_id = get_option('aklamatorProPageWidgetID');
        } else {

            /*  if `the_content` belongs to a page or our file is missing
                the result of this filter is no change to `the_content` */

            return $content;
        }

        $title = "";
        if (get_option('aklamatorProSingleWidgetTitle') !== '') {

            $title .= "<h2>" . get_option('aklamatorProSingleWidgetTitle') . "</h2>";
        }
        if (strlen($widget_id) > 7) {
            return $content . $title . stripslashes(htmlspecialchars_decode($widget_id)) . "</br>";

        } else {
            /*  append the text file contents to the end of `the_content` */
            return $content . $title . "<!-- created 2014-11-25 16:22:10 -->
            <div id=\"akla$widget_id\"></div>
            <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = \"http://aklamator.com/widget/$widget_id\";
            fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'aklamator-$widget_id'));</script>
            <!-- end -->" . "<br>";
        }
    }

}

// Include Admin section
require_once('aklamator-pro-adsense-admin.php');

// Widget section

add_action( 'after_setup_theme', 'vw_setup_vw_widgets_init_aklamatorPro' );

function vw_setup_vw_widgets_init_aklamatorPro() {
    add_action( 'widgets_init', 'vw_widgets_init_aklamatorPro' );
}

function vw_widgets_init_aklamatorPro() {
    register_widget( 'Wp_widget_aklamatorPro' );
}

class Wp_widget_aklamatorPro extends WP_Widget {

    private $default = array(
        'supertitle' => '',
        'title' => '',
        'content' => '',
    );

    public function __construct() {
        // widget actual processes
        parent::__construct(
            'wp_widget_aklamatorPro', // Base ID
            'Aklamator Digital PR Pro', // Name
            array( 'description' => __( 'Display Aklamator Widgets in Sidebar')) // Args
        );

    }

    function widget( $args, $instance ) {
        extract($args);
        //var_dump($instance); die();

        $supertitle_html = '';
        if ( ! empty( $instance['supertitle'] ) ) {
            $supertitle_html = sprintf( __( '<span class="super-title">%s</span>', 'envirra' ), $instance['supertitle'] );
        }

        $title_html = '';
        if ( ! empty( $instance['title'] ) ) {
            $title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base);
            $title_html = $supertitle_html.$title;
        }

        echo $before_widget;
        if ( $instance['title'] ) echo $before_title . $title_html . $after_title;
        ?>
        <?php echo $this->show_widget(do_shortcode( $instance['widget_id'] )); ?>
        <?php

        echo $after_widget;
    }

    private function show_widget($widget_id)
    {
        $code = "";

        if (strlen($widget_id) > 7)
            echo $widget_id;
        else { ?>
            <!-- created 2014-11-25 16:22:10 -->
            <div id="akla<?php echo $widget_id; ?>"></div>
            <script>(function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = "http://aklamator.com/widget/<?php echo $widget_id; ?>";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'aklamator-<?php echo $widget_id; ?>'));</script>
            <!-- end -->
        <?php }
    }

    function form( $instance ) {

        $widget_data = new AklamatorWidgetPro();

        $instance = wp_parse_args( (array) $instance, $this->default );

        $supertitle = strip_tags( $instance['supertitle'] );
        $title = strip_tags( $instance['title'] );
        $content = $instance['content'];
        $widget_id = $instance['widget_id'];


        if($widget_data->api_data->data[0]->uniq_name != 'none'): ?>

            <!-- title -->
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (text shown above widget):','envirra-backend'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </p>

            <!-- Select - dropdown -->
            <label for="<?php echo $this->get_field_id('widget_id'); ?>"><?php _e('Widget:','envirra-backend'); ?></label>
            <select id="<?php echo $this->get_field_id('widget_id'); ?>" name="<?php echo $this->get_field_name('widget_id'); ?>">
                <?php foreach ( $widget_data->api_data->data as $item ): ?>
                    <option <?php echo ($widget_id == stripslashes(htmlspecialchars_decode($item->uniq_name)))? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->uniq_name)); ?>"><?php echo $item->title; ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <br>
            <br>
        <?php else :?>
            <br>
            <span style="color:red">Please make sure that you configured Aklamator plugin correctly</span>
            <a href="<?php echo admin_url(); ?>admin.php?page=aklamator-pro-adsense">Click here to configure Aklamator plugin</a>
            <br>
            <br>
        <?php endif;

    }
}