<?php

class AklamatorWidgetPro
{

    public $aklamator_url;
    public $api_data;
    public $api_data_table;

    public function __construct()
    {

        $this->aklamator_url = "http://aklamator.com/";

        if (is_admin()) {
            add_action("admin_menu", array(
                &$this,
                "adminMenu"
            ));

            add_action('admin_init', array(
                &$this,
                "setOptions"
            ));

           // if (get_option('aklamatorProApplicationID') !== '') {

                $this->api_data_table = $this->addNewWebsiteApi(); // Fetch data via aklamator API

                if($this->addNewWebsiteApi() == NULL) { // Fetch data via aklamator API
                    $this->api_data = new stdClass();
                    $this->api_data->data = array();

                }else{
                    $this->api_data = $this->addNewWebsiteApi();
                }
                /* Add new items to the end of array data*/
                $item_add = new stdClass();

                if(get_option('aklamatorProAds') !== ''){
                    $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('aklamatorProAds')));
                    if(get_option('aklamatorProAds1Name') != ""){
                        $item_add->title = get_option('aklamatorProAds1Name');
                    }else{
                        $item_add->title = 'Ad 1 code';
                    }

                    array_push($this->api_data->data, unserialize(serialize($item_add)));
                }
                if(get_option('aklamatorProAds2') !== ''){
                    $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('aklamatorProAds2')));
                    if(get_option('aklamatorProAds2Name') != ""){
                        $item_add->title = get_option('aklamatorProAds2Name');
                    }else{
                        $item_add->title = 'Ad 2 code';
                    }
                    array_push($this->api_data->data, unserialize(serialize($item_add)));
                }
                if(get_option('aklamatorProAds3') !== ''){
                    $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('aklamatorProAds3')));
                    if(get_option('aklamatorProAds3Name') != ""){
                        $item_add->title = get_option('aklamatorProAds3Name');
                    }else{
                        $item_add->title = 'Ad 3 code';
                    }
                    array_push($this->api_data->data, unserialize(serialize($item_add)));
                }

                $item_add->uniq_name = 'none';
                $item_add->title = 'Do not show';
                array_push($this->api_data->data, unserialize(serialize($item_add)));
            //}
        }

        if (get_option('aklamatorProSingleWidgetID') !== 'none') {

            if (get_option('aklamatorProSingleWidgetID') == '') {
                if ($this->api_data->data[0] && $this->api_data->data[0]->uniq_name != 'none') {
                    update_option('aklamatorProSingleWidgetID', $this->api_data->data[0]->uniq_name);
                }

                add_filter('the_content', 'bottom_of_every_postPro');
            }
        }

        if (get_option('aklamatorProPageWidgetID') !== 'none') {

            if (get_option('aklamatorProPageWidgetID') == '') {
                if ($this->api_data->data[0] && $this->api_data->data[0]->uniq_name != 'none') {
                    update_option('aklamatorProPageWidgetID', $this->api_data->data[0]->uniq_name);
                }

            }
            add_filter('the_content', 'bottom_of_every_postPro');
        }



    }

    function setOptions()
    {
        register_setting('aklamatorPro-options', 'aklamatorProApplicationID');
        register_setting('aklamatorPro-options', 'aklamatorProPoweredBy');
        register_setting('aklamatorPro-options', 'aklamatorProSingleWidgetID');
        register_setting('aklamatorPro-options', 'aklamatorProPageWidgetID');
        register_setting('aklamatorPro-options', 'aklamatorProSingleWidgetTitle');
        // Ads codes
        register_setting('aklamatorPro-options', 'aklamatorProAds');
        register_setting('aklamatorPro-options', 'aklamatorProAds2');
        register_setting('aklamatorPro-options', 'aklamatorProAds3');
        // Custom ads name
        register_setting('aklamatorPro-options', 'aklamatorProAds1Name');
        register_setting('aklamatorPro-options', 'aklamatorProAds2Name');
        register_setting('aklamatorPro-options', 'aklamatorProAds3Name');

    }

    public function adminMenu()
    {
        add_menu_page('Aklamator Digital PR', 'Aklamator PR Pro', 'manage_options', 'aklamator-pro-adsense', array(
            $this,
            'createAdminPage'
        ), content_url() . '/plugins/aklamator-pro-adsense/images/aklamator-icon.png');

    }

    public function getSignupUrl()
    {

        return $this->aklamator_url . 'registration/publisher?utm_source=wordpress_pro&utm_medium=admin&e=' . urlencode(get_option('admin_email')) . '&pub=' .  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']).
        '&un=' . urlencode(wp_get_current_user()->display_name);

    }

    private function addNewWebsiteApi()
    {

        if (!is_callable('curl_init')) {
            return;
        }


        $service     = $this->aklamator_url . "wp-authenticate/user";
        $p['ip']     = $_SERVER['REMOTE_ADDR'];
        $p['url']    = site_url();
        $p['source'] = "wordpress";
        $p['AklamatorApplicationID'] = get_option('aklamatorProApplicationID');


        $client = curl_init();

        curl_setopt($client, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($client, CURLOPT_HEADER, 0);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_URL, $service);

        if (!empty($p)) {
            curl_setopt($client, CURLOPT_POST, count($p));
            curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($p));
        }

        $data = curl_exec($client);
        curl_close($client);

        $data = json_decode($data);

        return $data;

    }

    public function createAdminPage()
    {
        $code = get_option('aklamatorProApplicationID');
        $ak_home_url = 'http://aklamator.com';
        $ak_dashboard_url = 'http://aklamator.com/dashboard';

        ?>
        <style>
            #adminmenuback{ z-index: 0}
            #aklamator-options ul { margin-left: 10px; }
            #aklamator-options ul li { margin-left: 15px; list-style-type: disc;}
            #aklamator-options h1 {margin-top: 5px; margin-bottom:10px; color: #00557f}
            .fz-span { margin-left: 23px;}


            .aklamator-signup-button {
                float: left;
                vertical-align: top;
                width: auto;
                height: 30px;
                line-height: 30px;
                padding: 10px;
                font-size: 22px;
                color: white;
                text-align: center;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
                background: #c0392b;
                border-radius: 5px;
                border-bottom: 2px solid #b53224;
                cursor: pointer;
                -webkit-box-shadow: inset 0 -2px #b53224;
                box-shadow: inset 0 -2px #b53224;
                text-decoration: none;
                margin-top: 10px;
                margin-bottom: 10px;
                clear: both;
            }

            a.aklamator-signup-button:hover {
                cursor: pointer;
                color: #f8f8f8;
            }
            textarea {
                overflow: auto;
                padding: 4px 6px;
                line-height: 1.4;
            }

            .btn { border: 1px solid #fff; font-size: 13px; border-radius: 3px; background: transparent; text-transform: uppercase; font-weight: 700; padding: 4px 10px; min-width: 162px; max-width: 100%; text-decoration: none;}
            .btn:Hover, .btn.hovered { border: 1px solid #fff; }
            .btn:Active, .btn.pressed { opacity: 1; border: 1px solid #fff; border-top: 3px solid #17ade0; -webkit-box-shadow: 0 0 0 transparent; box-shadow: 0 0 0 transparent; }

            .btn-primary { background: #1ac6ff; border:1px solid #1ac6ff; color: #fff; text-decoration: none;}
            .btn-primary:hover, .btn-primary.hovered { background: #1ac6ff;  border:1px solid #1ac6ff; opacity:0.9; }
            .btn-primary:Active, .btn-primary.pressed { background: #1ac6ff; border:1px solid #1ac6ff; }

            .box{float: left; margin-left: 10px; width: 600px; background-color:#f8f8f8; padding: 10px; border-radius: 5px;}

        </style>
        <!-- Load css libraries -->

        <link href="//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

        <div id="aklamatorPro-options" style="width:980px;margin-top:10px;">

            <div style="float: left; width: 300px;">

                <a target="_blank" href="<?php echo $ak_home_url; ?>?utm_source=wordpress_pro">
                    <img style="border-radius:5px;border:0px;" src=" <?php echo plugins_url('images/logo.jpg', __FILE__);?>" /></a>
                <?php
                if ($code != '') : ?>
                    <a target="_blank" href="<?php echo $ak_dashboard_url; ?>?utm_source=wordpress_pro">
                        <img style="border:0px;margin-top:5px;border-radius:5px;" src="<?php echo plugins_url('images/dashboard.jpg', __FILE__); ?>" /></a>

                <?php endif; ?>

                <a target="_blank" href="<?php echo $ak_home_url;?>/contact?utm_source=wp-plugin-contact-pro">
                    <img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="<?php echo plugins_url('images/support.jpg', __FILE__); ?>" /></a>



            </div>
            <div class="box">

                <h1>Aklamator Digital PR Rro version</h1>

                <?php

                if ($code == '') : ?>
                    <h3 style="float: left">Step 1:</h3>
                    <a class='aklamator-signup-button' target='_blank' href="<?php echo $this->getSignupUrl(); ?>">Click here to create your FREE account!</a>

                <?php endif; ?>

                <div style="clear: both"></div>
                <?php if ($code == '') { ?>
                    <h3>Step 2: &nbsp;&nbsp;&nbsp;&nbsp; Paste your Aklamator Application ID</h3>
                <?php }else{ ?>
                    <h3>Your Aklamator Application ID</h3>
                <?php } ?>

                <form method="post" action="options.php">
                    <?php
                    settings_fields('aklamatorPro-options');
                    ?>

                    <p >
                        <input type="text" style="width: 400px" name="aklamatorProApplicationID" id="aklamatorProApplicationID" value="<?php
                        echo (get_option("aklamatorProApplicationID"));
                        ?>" maxlength="999" />

                    </p>
                    <p>
                        <input type="checkbox" id="aklamatorProPoweredBy" name="aklamatorProPoweredBy" <?php echo (get_option("aklamatorProPoweredBy") == true ? 'checked="checked"' : ''); ?> Required="Required">
                        <strong>Required</strong> I acknowledge there is a 'powered by aklamator' link on the widget. <br />
                    </p>

                    <p>
                    <h1>Options</h1>

                    <h3 style="font-size:120%;margin-bottom:5px"><?php _e('Add your Adsense Code or any other script codes'); ?></h3>
                    <p style="margin-top:0px"><span class="description"><?php _e('Paste your <strong>Ad</strong> code and you will be able to assign that <strong>Ad</strong> to single post or static page as shown below, and in Widget section you can drag and drop Aklamator widget and chose from dropdown what you want to show in your sidebar.') ?></span></p>

                    <h4><?php _e('Paste your Ad codes :'); ?></h4>
                    <table border="0" cellspacing="0" cellpadding="0">

                        <tr valign="top">
                            <td align="left" style="width:140px; padding-right: 5px"><strong>Ad1:</strong> <br/>Custom Ad name
                                <input id="aklamatorProAds1Name" name="aklamatorProAds1Name" value="<?php echo stripslashes(htmlspecialchars(get_option('aklamatorProAds1Name'))); ?>" placeholder="Optional Ad1 name"/>
                            </td>
                            <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left" id="aklamatorProAds" name="aklamatorProAds" rows="3" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('aklamatorProAds'))); ?></textarea></td>

                        </tr>
                        <tr valign="top">
                            <td align="left" style="width:140px; padding-right: 5px"><strong>Ad2:</strong> <br/>Custom Ad name
                                <input id="aklamatorProAds2Name" name="aklamatorProAds2Name" value="<?php echo stripslashes(htmlspecialchars(get_option('aklamatorProAds2Name'))); ?>" placeholder="Optional Ad2 name"/>
                            </td>
                            <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left" id="aklamatorProAds2" name="aklamatorProAds2" rows="3" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('aklamatorProAds2'))); ?></textarea></td>

                        </tr>
                        <tr valign="top">
                            <td align="left" style="width:140px; padding-right: 5px"><strong>Ad3:</strong> <br/>Custom Ad name
                                <input id="aklamatorProAds3Name" name="aklamatorProAds3Name" value="<?php echo stripslashes(htmlspecialchars(get_option('aklamatorProAds3Name'))); ?>" placeholder="Optional Ad3 name"/>
                            </td>
                            <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left" id="aklamatorProAds3" name="aklamatorProAds3" rows="3" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('aklamatorProAds3'))); ?></textarea></td>

                        </tr>

                    </table>

                    <?php if ($this->api_data->data[0]->uniq_name != 'none') : ?>

                    <label for="aklamatorProSingleWidgetTitle">Title Above widget (Optional): </label>
                    <input type="text" style="width: 300px; margin-top:10px" name="aklamatorProSingleWidgetTitle" id="aklamatorProSingleWidgetTitle" value="<?php echo (get_option("aklamatorProSingleWidgetTitle")); ?>" maxlength="999" />


                        <h4>Select widget to be shown on bottom of the each:</h4>

                        <label for="aklamatorProSingleWidgetID">Single post: </label>
                        <select id="aklamatorProSingleWidgetID" name="aklamatorProSingleWidgetID">
                            <?php
                            foreach ( $this->api_data->data as $item ): ?>
                                <option <?php echo (stripslashes(htmlspecialchars_decode(get_option('aklamatorProSingleWidgetID'))) == $item->uniq_name)? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->uniq_name)); ?>"><?php echo $item->title; ?></option>
                            <?php endforeach; ?>

                        </select>
                        </p>

                        <p>
                            <label for="aklamatorProPageWidgetID">Single page: </label>
                            <select id="aklamatorProPageWidgetID" name="aklamatorProPageWidgetID">
                                <?php
                            foreach ( $this->api_data->data as $item ): ?>
                                <option <?php echo (stripslashes(htmlspecialchars_decode(get_option('aklamatorProPageWidgetID'))) == $item->uniq_name)? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->uniq_name)); ?>"><?php echo $item->title; ?></option>
                            <?php endforeach; ?>

                            </select>
                        </p>


                    <?php endif; ?>
                    <input style ="margin: 15px 0px;" type="submit" value="<?php echo (_e("Save Changes")); ?>" />


                </form>
            </div>

        </div>




        <div style="clear:both"></div>
        <div style="margin-top: 20px; margin-left: 0px; width: 810px;" class="box">
            <!-- Start of dataTables -->
            <div id="aklamatorPro-options">
                <h1>Your Widgets</h1>
            </div>
            <br>
        <?php if(get_option("aklamatorProApplicationID") == "") : ?>
            <a href="<?php echo $this->getSignupUrl(); ?>" target="_blank"><img style="border-radius:5px;border:0px;" src=" <?php echo plugins_url('images/teaser-810x262.png', __FILE__);?>" /></a>
        <?php else : ?>
        <?php if($this->api_data_table->flag ): ?>


            <table cellpadding="0" cellspacing="0" border="0"
                   class="responsive dynamicTable display table table-bordered" width="100%">
                <thead>
                <tr>

                    <th>Name</th>
                    <th>Domain</th>
                    <th>Image size</th>
                    <th>Column/row</th>
                    <th>Created At</th>

                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->api_data_table->data as $item): ?>

                    <tr class="odd">
                        <td style="vertical-align: middle;" ><?php echo $item->title; ?></td>
                        <td style="vertical-align: middle;" >
                            <?php foreach($item->domain_ids as $domain): ?>
                                    <a href="<?php echo $domain->url; ?>" target="_blank"><?php echo $domain->title; ?></a><br/>
                            <?php endforeach; ?>
                        </td>
                        <td style="vertical-align: middle;" ><?php echo $item->img_size; ?>px</td>
                        <td style="vertical-align: middle;" ><?php echo $item->column_number; ?> x <?php echo $item->row_number; ?></td>
                        <td style="vertical-align: middle;" ><?php echo $item->date_created; ?></td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
                <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Domain</th>
                    <th>Immg size</th>
                    <th>Column/row</th>
                    <th>Created At</th>
                </tr>
                </tfoot>
            </table>
            </div>

        <?php else : ?>
            <span style="color:red"><?php echo $this->api_data_table->error; ?></span>
        <?php endif;
    endif;
        ?>

        <!-- load js scripts -->

        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo content_url(); ?>/plugins/aklamator-pro-adsense/assets/dataTables/jquery.dataTables.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function(){

                if ($('table').hasClass('dynamicTable')) {
                    $('.dynamicTable').dataTable({
                        "iDisplayLength": 10,
                        "sPaginationType": "full_numbers",
                        "bJQueryUI": false,
                        "bAutoWidth": false

                    });
                }
            });

        </script>

    <?php
    }


}


new AklamatorWidgetPro();