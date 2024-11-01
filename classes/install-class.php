<?php
/**
 * install-classes
 * The library of all the installation class
 * @author Swashata <swashata4u@gmail.com>
 * @package WP Feedback & Surver Manager
 * @subpackage Installation
 * @version 1.1.4
 */

class wp_feedback_install {

    /**
     * Database prefix
     * Mainly used for MS compatibility
     * @var string
     */
    var $prefix;

    public function __construct() {
        global $wpdb;
        $prefix = '';
        if(is_multisite()) {
            global $blog_id;
            $prefix = $wpdb->base_prefix . $blog_id . '_';
        } else {
            $prefix = $wpdb->prefix;
        }

        $this->prefix = $prefix;
    }

    /**
     * install
     * Do the things
     */
    public function install() {
        $this->checkversions();
        $this->checkdb();
        $this->checkop();
        $this->set_capability();
    }

    /**
     * Restores the WP Options to the defaults
     * Deletes the default options set and calls checkop
     */
    public function restore_op() {
        delete_option('wp_feedback_info');
        delete_option('wp_feedback_global');
        delete_option('wp_feedback_surver');
        delete_option('wp_feedback_feedback');

        $this->checkop();
    }

    /**
     * Restores the database
     * Deletes the current tables and freshly installs the new one
     * @global wpdb $wpdb
     */
    public function restore_db() {
        global $wpdb;

        $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS {$this->prefix}sur_feed"));
        $this->checkdb();
    }

    /**
     * Checks whether PHP version 5 or greater is installed or not
     * Also checks whether WordPress version is greater than or equal to the required
     *
     * If fails then it automatically deactivates the plugin
     * and gives error
     * @return void
     */
    private function checkversions() {
        if (version_compare(PHP_VERSION, '5.0.0', '<')) {
            deactivate_plugins(plugin_basename(wp_feedback_loader::$abs_file));
            wp_die(__('The plugin requires PHP version greater than or equal to 5.x.x', 'wpadmrs'));
            return;
        }

        if(version_compare(get_bloginfo('version'), '3.3.0', '<')) {
            deactivate_plugins(plugin_basename(wp_feedback_loader::$abs_file));
            wp_die(__('The plugin requires WordPress version greater than or equal to 3.3.x', 'wpadmrs'));
            return;
        }
    }

    /**
     * creates the table and options
     * @access public
     * @global wpdb $wpdb
     * @global string $charset_collate
     */
    public function checkdb() {
        /**
         * Include the necessary files
         * Also the global options
         */
        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        } else {
            require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        }
        global $wpdb, $charset_collate;


        $prefix = $this->prefix;
        $sqls = array();
        $sqls[] = "CREATE TABLE `" . $prefix . "sur_feed` (
                        id BIGINT(20) UNSIGNED NOT NULL auto_increment,
                        f_name VARCHAR(255) NOT NULL default '',
                        l_name VARCHAR(255) NOT NULL default '',
                        email VARCHAR(255) NOT NULL default '',
                        phone VARCHAR(20) NOT NULL default '',
                        survey TEXT NOT NULL,
                        feedback TEXT NOT NULL,
                        pinfo TEXT NOT NULL,
                        ip VARCHAR(15) NOT NULL default '0.0.0.0',
                        date DATETIME NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY  (id)
                    ) $charset_collate;";

        foreach($sqls as $sql)
            dbDelta ($sql);
    }

    /**
     * Creates the options
     */
    public function checkop() {
        $prefix = $this->prefix;

        $wp_feedback_info = array(
            'version' => wp_feedback_loader::$version,
            'feedback_table' => $prefix . 'sur_feed',
        );

        $wp_feedback_global = array(
            'enable_survey' => true,
            'enable_feedback' => true,
            'enable_pinfo' => true,
            //'enable_opinion' => true, Because of custom pinfo, we do not need opinion anymore @since 1.1.0
            'terms_page' => '',
            'survey_title' => 'Survey',
            'survey_subtitle' => 'Answer a few questions',
            'feedback_title' => 'Feedback',
            'feedback_subtitle' => 'Write Your suggestions',
            'pinfo_title' => 'Personal',
            'pinfo_subtitle' => 'Something about You',
            'success_message' => 'Thank you for giving your feedback',
            'email' => get_option('admin_email'),
            'tab_order' => array(
                0 => 'survey',
                1 => 'feedback',
                2 => 'pinfo',
            ),
        );

        $wp_feedback_survey = array();
        $wp_feedback_feedback = array();
        $wp_feedback_pinfo = array();

        foreach(array('f_name', 'l_name', 'email', 'phone') as $pkey) {
            $wp_feedback_pinfo[$pkey] = array(
                'enabled' => true,
                'required' => true,
            );
        }

        for($i = 0; $i < 20; $i++) {
            $wp_feedback_survey[$i] = array(
                'enabled' => false,
                'question' => '',
                'options' => '',
                'type' => 'single',
                'required' => true,
            );
            $wp_feedback_feedback[$i] = array(
                'enabled' => false,
                'name' => '',
                'description' => '',
                'email' => '',
                'required' => false,
            );
            $wp_feedback_pinfo[$i] = array(
                'enabled' => false,
                'question' => '',
                'options' => '',
                'type' => 'free-input',
                'required' => false,
            );
        }

        if(!get_option('wp_feedback_info')) {
            //new installation
            add_option('wp_feedback_info', $wp_feedback_info);
            add_option('wp_feedback_global', $wp_feedback_global);
            add_option('wp_feedback_survey', $wp_feedback_survey);
            add_option('wp_feedback_feedback', $wp_feedback_feedback);
            add_option('wp_feedback_pinfo', $wp_feedback_pinfo);
        } else {
            //older version check
            $old_op = get_option('wp_feedback_info');
            switch($old_op['version']) {
                default :
                case '1.0.0' :
                case '1.0.1' :
                case '1.0.2' :
                case '1.1.0' :
                case '1.1.1' :
                case '1.1.2' :
                    //although the new datatype were introduced on version 1.1.0, but due to lack of my knowledge on register_activation_hook
                    //it did not pass through well
                    //so have to go through the mess over again.
                    //upgrade the survey
                    $old_survey = get_option('wp_feedback_survey');
                    foreach($wp_feedback_survey as $key => $val) {
                        $wp_feedback_survey[$key] = wp_parse_args($old_survey[$key], $val);
                    }
                    update_option('wp_feedback_survey', $wp_feedback_survey);
                    //upgrade the feedback
                    $old_feedback = get_option('wp_feedback_feedback');
                    foreach($wp_feedback_feedback as $key => $val) {
                        $wp_feedback_feedback[$key] = wp_parse_args($old_feedback[$key], $val);
                    }
                    update_option('wp_feedback_feedback', $wp_feedback_feedback);
                    //add the pinfo
                    add_option('wp_feedback_pinfo', $wp_feedback_pinfo);
                    //update the global options
                    $old_global = get_option('wp_feedback_global');
                    $wp_feedback_global = wp_parse_args($old_global, $wp_feedback_global);
                    update_option('wp_feedback_global', $wp_feedback_global);
                case '1.1.3' :
                    //no updates necessary

            }
            //finally update the info option with the newer version
            update_option('wp_feedback_info', $wp_feedback_info);
        }
    }

    /**
     * Create and set custom capabilities
     * @global WP_Roles $wp_roles
     */
    private function set_capability() {
        global $wp_roles;

        //add capability to admin
        $wp_roles->add_cap('administrator', 'manage_feedback');
        $wp_roles->add_cap('administrator', 'view_feedback');

        //add capability to editor
        $wp_roles->add_cap('editor', 'manage_feedback');
        $wp_roles->add_cap('editor', 'view_feedback');

        //add capability to author
        $wp_roles->add_cap('author', 'view_feedback');
    }
}
