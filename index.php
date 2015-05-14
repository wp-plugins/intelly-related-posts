<?php
/*
Plugin Name: Inline Related Posts
Plugin URI: http://intellywp.com/intelly-related-posts/
Description: Finally the plugin to insert INLINE related posts :)
Author: IntellyWP
Author URI: http://intellywp.com/
Email: aleste@intellywp.com
Version: 1.3.2
*/
define('IRP_PLUGIN_PREFIX', 'IRP_');
define('IRP_PLUGIN_FILE',__FILE__);
define('IRP_PLUGIN_NAME', 'intelly-related-posts');
define('IRP_PLUGIN_VERSION', '1.3.2');
define('IRP_PLUGIN_AUTHOR', 'IntellyWP');
define('IRP_PLUGIN_ROOT', dirname(__FILE__).'/');
define('IRP_PLUGIN_IMAGES', plugins_url( 'assets/images/', __FILE__ ));
define('IRP_PLUGIN_ASSETS', plugins_url( 'assets/', __FILE__ ));

define('IRP_LOGGER', FALSE);
define('IRP_DISABLE_RELATED', FALSE);
define('IRP_DEBUG_BLOCK', FALSE);
define('IRP_QUERY_POSTS_OF_TYPE', 1);
define('IRP_QUERY_POST_TYPES', 2);
define('IRP_QUERY_CATEGORIES', 3);
define('IRP_QUERY_TAGS', 4);

define('IRP_ENGINE_SEARCH_CATEGORIES_TAGS', 0);
define('IRP_ENGINE_SEARCH_CATEGORIES', 1);
define('IRP_ENGINE_SEARCH_TAGS', 2);

define('IRP_INTELLYWP_SITE', 'http://www.intellywp.com/');
define('IRP_INTELLYWP_RECEIVER', IRP_INTELLYWP_SITE.'wp-content/plugins/intellywp-manager/data.php');
define('IRP_PAGE_FAQ', IRP_INTELLYWP_SITE.IRP_PLUGIN_NAME);
define('IRP_PAGE_WORDPRESS', 'https://wordpress.org/plugins/'.IRP_PLUGIN_NAME.'/');
define('IRP_PAGE_PREMIUM', IRP_INTELLYWP_SITE.IRP_PLUGIN_NAME);
define('IRP_PAGE_SETTINGS', admin_url().'options-general.php?page='.IRP_PLUGIN_NAME);

define('IRP_TAB_SETTINGS', 'settings');
define('IRP_TAB_SETTINGS_URI', IRP_PAGE_SETTINGS.'&tab='.IRP_TAB_SETTINGS);
define('IRP_TAB_ABOUT', 'about');
define('IRP_TAB_ABOUT_URI', IRP_PAGE_SETTINGS.'&tab='.IRP_TAB_ABOUT);
define('IRP_TAB_FAQ', 'faq');
define('IRP_TAB_FAQ_URI', IRP_PAGE_SETTINGS.'&tab='.IRP_TAB_FAQ);

include_once(dirname(__FILE__).'/autoload.php');
irp_include_php(dirname(__FILE__).'/includes/');

global $irp;
$irp=new IRP_Singleton();

class IRP_Singleton {
    var $Lang;
    var $Utils;
    var $Form;
    var $Check;
    var $Options;
    var $Manager;
    var $Logger;
    var $Cron;
    var $Tracking;
    var $Tabs;

    function __construct() {
        $this->Lang=new IRP_Language();
        $this->Lang->load('irp', IRP_PLUGIN_ROOT.'languages/Lang.txt');

        $this->Utils=new IRP_Utils();
        $this->Form=new IRP_Form();
        $this->Check=new IRP_Check();
        $this->Options=new IRP_Options();
        $this->Manager=new IRP_Manager();
        $this->Logger=new IRP_Logger();
        $this->Cron=new IRP_Cron();
        $this->Tracking=new IRP_Tracking();
        $this->Tabs=new IRP_Tabs();
    }
}
//from Settings_API_Tabs_Demo_Plugin
class IRP_Tabs {
    private $tabs = array();

    function __construct() {
        add_action('admin_menu', array(&$this, 'attachMenu'));
        add_filter('plugin_action_links', array(&$this, 'pluginActions'), 10, 2);
        add_action('admin_enqueue_scripts',  array(&$this, 'enqueueScripts'));
    }

    function attachMenu() {
        global $irp;

        /*
        $name='IntellyWP';
        add_menu_page($name, $name, 'delete_users', 'iwp-menu', '', '', '81.123');
        $name='UTM Auto Tagger';
        add_submenu_page('iwp-menu', $name, $name
            , 'edit_posts', IRP_PLUGIN_NAME, array(&$this, 'showTabPage'));
        */

        $name='Inline Related Posts';
        add_submenu_page('options-general.php'
            , $name, $name
            , 'edit_posts', IRP_PLUGIN_NAME, array(&$this, 'showTabPage'));
    }
    function pluginActions($links, $file) {
        global $irp;
        if($file==IRP_PLUGIN_NAME.'/index.php'){
            $settings = "<a href='".IRP_PAGE_SETTINGS."'>" . $irp->Lang->L('Settings') . '</a> ';
            $premium = "<a href='".IRP_PAGE_PREMIUM."'>" . $irp->Lang->L('PREMIUM') . '</a> ';
            $links = array_merge(array($settings), $links);
        }
        return $links;
    }
    function enqueueScripts() {
        global $irp;
        wp_enqueue_script('jquery');
        wp_enqueue_script('suggest');
        http://localhost/test/wp-admin/load-styles.php?c=1&dir=ltr&load=dashicons,admin-bar,buttons,media-views,wp-admin,wp-auth-check,wp-color-picker&ver=4.2.2
        wp_enqueue_script('jquery-ui-autocomplete');

        wp_enqueue_style('irp-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.min.css');
        wp_enqueue_style('irp-css', plugins_url('assets/css/style.css', __FILE__ ));
        wp_enqueue_style('irp-select2-css', plugins_url('assets/deps/select2-3.5.2/select2.css', __FILE__ ));
        wp_enqueue_script('irp-select2-js', plugins_url('assets/deps/select2-3.5.2/select2.min.js', __FILE__ ));
        wp_enqueue_script('irp-starrr-js', plugins_url('assets/deps/starrr/starrr.js', __FILE__ ));

        wp_enqueue_script('jquery-qtip', plugins_url('assets/deps/qtip/jquery.qtip.min.js', __FILE__ ), array( 'jquery' ), '1.0.0-RC3', true );

        wp_register_script('irp-common', plugins_url('assets/js/common.js', __FILE__ ), array('jquery', 'jquery-ui-autocomplete'), '1.0', FALSE);
        wp_enqueue_script('irp-common');
    }

    function showTabPage() {
        global $irp;

        $id=intval($irp->Utils->qs('id', 0));
        $tab=$irp->Utils->qs('tab', IRP_TAB_SETTINGS);

        $this->tabs[IRP_TAB_SETTINGS]=$irp->Lang->L('Settings');
        $this->tabs[IRP_TAB_FAQ]=$irp->Lang->L('FAQ');
        $this->tabs[IRP_TAB_ABOUT]=$irp->Lang->L('About');
        ?>
        <div class="wrap" style="margin:5px;">
            <?php
            $this->showTabs();
            $header='';
            switch ($tab) {
                case IRP_TAB_SETTINGS:
                    $header='Settings';
                    break;
                case IRP_TAB_FAQ:
                    $header='Faq';
                    break;
                case IRP_TAB_ABOUT:
                    $header='About';
                    break;
            }

            if($irp->Lang->H($header.'Title')) { ?>
                <h2><?php $irp->Lang->P($header . 'Title') ?></h2>
                <?php if ($irp->Lang->H($header . 'Subtitle')) { ?>
                    <div><?php $irp->Lang->P($header . 'Subtitle') ?></div>
                <?php } ?>
                <div style="clear:both;"></div>
            <?php }

            switch ($tab) {
                case IRP_TAB_SETTINGS:
                    irp_ui_settings();
                    break;
                case IRP_TAB_FAQ:
                    irp_ui_faq();
                    break;
                case IRP_TAB_ABOUT:
                    irp_ui_about();
                    irp_ui_feedback();
                    break;
            } ?>
        </div>
    <?php }

    function showTabs() {
        global $irp;
        $tab=$irp->Check->of('tab', IRP_TAB_SETTINGS);

        ?>
        <h2 class="nav-tab-wrapper" style="float:left; width:97%;">
            <?php
            foreach ($this->tabs as $k=>$v) {
                $active = ($tab==$k ? 'nav-tab-active' : '');
                ?>
                <a style="float:left" class="nav-tab <?php echo $active?>" href="?page=<?php echo IRP_PLUGIN_NAME?>&tab=<?php echo $k?>"><?php echo $v?></a>
                <?php
            }
            ?>
            <style>
                .starrr {display:inline-block}
                .starrr i{font-size:16px;padding:0 1px;cursor:pointer;color:#2ea2cc;}
            </style>
            <div style="float:right; display:none;" id="rate-box">
                <span style="font-weight:700; font-size:13px; color:#555;"><?php $irp->Lang->P('Rate us')?></span>
                <div id="irp-rate" class="starrr" data-connected-input="irp-rate-rank"></div>
                <input type="hidden" id="irp-rate-rank" name="irp-rate-rank" value="5" />

            </div>
            <script>
                jQuery(function() {
                    jQuery(".starrr").starrr();
                    jQuery('#irp-rate').on('starrr:change', function(e, value){
                        var url='https://wordpress.org/support/view/plugin-reviews/<?php echo IRP_PLUGIN_NAME?>?rate=5#postform';
                        window.open(url);
                    });
                    jQuery('#rate-box').show();
                });
            </script>
        </h2>
        <div style="clear:both;"></div>
    <?php }
}
