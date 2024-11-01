<?php
/**
 * The main form class
 * This prints the form and its HTML given the options
 * Also handles the post request to save the user input
 * @version 1.1.4
 */

class wp_feedback_form {
    var $global;
    var $survey;
    var $feedback;
    var $pinfo;
    var $post;

    public function __construct() {
        $this->global = get_option('wp_feedback_global');
        $this->survey = get_option('wp_feedback_survey');
        $this->feedback = get_option('wp_feedback_feedback');
        $this->pinfo = get_option('wp_feedback_pinfo');

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->post = $_POST;

            if(get_magic_quotes_gpc())
                array_walk_recursive ($this->post, array($this, 'stripslashes_gpc'));

            array_walk_recursive ($this->post, array($this, 'htmlspecialchar_ify'));
        }
    }

    /**
     *
     * @global wpdb $wpdb
     */
    public function save_post() {
        global $wpdb, $wp_feedback_info;
        //first validate
        $errors = array();
        $return = array();
        $data = $this->post['wp_feedback'];

        //validate pinfo
        if($this->global['enable_pinfo'] == true) :
            if('' == $data['pinfo']['f_name'] && $this->pinfo['f_name']['enabled'] == true && $this->pinfo['f_name']['required'] == true)
                $errors[] = __('First name is empty', 'fbsr');
            if('' == $data['pinfo']['l_name'] && $this->pinfo['l_name']['enabled'] == true && $this->pinfo['l_name']['required'] == true)
                $errors[] = __('Last name is empty', 'fbsr');
            if('' == $data['pinfo']['email'] && $this->pinfo['email']['enabled'] == true && $this->pinfo['email']['required'] == true)
                $errors[] = __('Email is empty', 'fbsr');
            if(false === is_email($data['pinfo']['email']) && $this->pinfo['email']['enabled'] == true && $this->pinfo['email']['required'] == true)
                $errors[] = __('Invalid email address', 'fbsr');
            if('' == $data['pinfo']['phone'] && $this->pinfo['phone']['enabled'] == true && $this->pinfo['phone']['required'] == true)
                $errors[] = __('Phone number is empty', 'fbsr');
            if($data['pinfo']['human'] != $this->decrypt($data['pinfo']['h_h']))
                $errors[] = __('Please correctly answer the security questions', 'fbsr');

            foreach($this->pinfo as $key => $pinfo) {
                if(in_array($key, array('f_name', 'l_name', 'email', 'phone'), TRUE)) continue;

                if(true == $pinfo['enabled'] && true == $pinfo['required'] && !isset($data['pinfo'][$key])) {
                    $errors[] = __('This question is compulsory: ', 'fbsr') . $pinfo['question'];
                }
            }
        endif;

        //validate survey
        if($this->global['enable_survey'] == true) :
            $survey_validation = true;
            foreach($this->survey as $key => $s) {
                if($s['enabled'] == true && $s['required'] == true && !isset($data['survey'][$key])) {
                    $errors[] = __('Please answer MCQ/Survey Question: ', 'fbsr') . ($key + 1);
                    $survey_validation = false;
                }
            }
            if(false == $survey_validation) {
                $errors[] = __('Please answer the starred surveys/mcq questions', 'fbsr');
            }
        endif;

        //validate feedback
        if($this->global['enable_feedback'] == true) :
            $feedback_validation = true;
            foreach($this->feedback as $key => $f) {
                if($f['enabled'] == true && $f['required'] == true && '' == $data['feedback'][$key]) {
                    $errors[] = __('Please answer Freetype/Feedback Question: ', 'fbsr') . ($key + 1);
                    $feedback_validation = false;
                }
            }

            if(false == $feedback_validation) {
                $errors[] = __('Please answer all the starred feedbacks/freetype questions ', 'fbsr') . count($data['feedback']) . ' ' . $feed_count;
            }
        endif;

        if(empty($errors)) {
            //collect the personal data
            $pinfo = array();
            $dpinfo = array();
            if($this->global['enable_pinfo'] == true) :
                $pinfo['f_name'] = $data['pinfo']['f_name'];
                $pinfo['l_name'] = $data['pinfo']['l_name'];
                $pinfo['email'] = $data['pinfo']['email'];
                $pinfo['phone'] = $data['pinfo']['phone'];

                foreach($this->pinfo as $key => $spinfo) {
                    if(in_array($key, array('f_name', 'l_name', 'email', 'phone'), TRUE)) continue;
                    if(true == $spinfo['enabled'])
                        $dpinfo[$key] = $data['pinfo'][$key];
                }
            endif;
            $pinfo['ip'] = $_SERVER['REMOTE_ADDR'];
            $pinfo['date'] = current_time('mysql');


            //collect the surveys
            $survey = array();
            if($this->global['enable_survey'] == true) :
                foreach($this->survey as $k => $s) {
                    if(true == $s['enabled']) {
                        $survey[$k] = $data['survey'][$k];
                    }
                }
            endif;

            //collect the feedbacks
            $feedback = array();
            if($this->global['enable_feedback'] == true) :
                foreach($this->feedback as $k => $f) {
                    if(true == $f['enabled'] && '' != $data['feedback'][$k]) {
                        $feedback[$k] = $data['feedback'][$k];
                        $this->send_feedback_email($f['email'], $data['feedback'][$k], $f['name'], $pinfo, $data['opinion']);
                    }
                }
            endif;

            //insert
            if($wpdb->insert($wp_feedback_info['feedback_table'], array(
                'f_name' => $pinfo['f_name'],
                'l_name' => $pinfo['l_name'],
                'email' => $pinfo['email'],
                'phone' => $pinfo['phone'],
                'survey' => maybe_serialize($survey),
                'feedback' => maybe_serialize($feedback),
                'pinfo' => maybe_serialize($dpinfo),
                'ip' => $pinfo['ip'],
                'date' => $pinfo['date'],
            ), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'))) {
                $this->send_notification_email($this->global['email'], $pinfo, $wpdb->insert_id);
                $return['msg'] = '<div class="wp_feedback_msg" id="wp_feedback_success"><h4>' . __('Your feedback was successfully submitted', 'fbsr') . '</h4><p>' . htmlspecialchars_decode($this->global['success_message']) . '</p></div>';
                $return['type'] = 'success';
            } else {
                $return['msg'] = '<div class="wp_feedback_msg" id="wp_feedback_error"><h4>' . __('Could not save to the database', 'fbsr') . '</h4><p>' . __('Something terrible has occured. Please contact the administrator.', 'fbsr') . '<ul>';
                $return['type'] = 'fail';
            }

        } else {
            $return['msg'] = '<div class="wp_feedback_msg" id="wp_feedback_error"><h4>' . __('Form Validation Error', 'fbsr') . '</h4><p>' . __('Following errors has occured. Please correct them and resubmit the form.', 'fbsr') . '</p><ul>';
                foreach($errors as $e) {
                    $return['msg'] .= '<li>' . $e . '</li>';
                }
                $return['msg'] .= '</ul></div>';
                $return['type'] = 'fail';
        }
        return $return;
    }

    private function send_notification_email($email, $pinfo, $id) {
        if(trim($email) == '')
            return;

        $content = sprintf(__('
<html><body>
<p>A new feedback has been submitted. You can visit it at</p>
<p><strong>%sadmin.php?page=wp_feedback_view&id=%s</strong>

<h4>User Details</h4>
<ul>
<li><strong>First Name</strong>: %s</li>
<li><strong>Last Name</strong>: %s</li>
<li><strong>Email</strong>: %s</li>
<li><strong>Phone</strong>: %s</li>
</ul>

<p><em>
This is an autogenerated email. Please do not respond to this.<br />
You are receiving this notification because you are one of the email subscribers for the mentioned Feedback.<br />
If you wish to stop receiving emails, then please go to %1$sadmin.php?page=wp_feedback_settings and remove your email from there.<br />
If you can not access the link, then please contact your administrator.
</em></p>

<p>WP Feedback and Survey Manger Plugin <br />
- By Swashata<br />
http://www.intechgrity.com/</p>
</body></html>
', 'fbsr'), get_admin_url(), $id, $pinfo['f_name'], $pinfo['l_name'], $pinfo['email'], $pinfo['phone']);
        $sub = sprintf(__('[%s]New Feedback Notification', 'fbsr'), get_bloginfo('name'));
        $header = 'Content-Type: text/html' . "\r\n";
        wp_mail($email, $sub, $content, $header);
    }

    private function send_feedback_email($email, $content, $sub, $pinfo, $op) {
        if(trim($email) == '')
            return;
        $sub = sprintf(__('[%s] New feedback on the topic: %s', 'fbsr'), get_bloginfo('name'), $sub);

        $content = sprintf(__('
<html><body>

<h2>User Details</h2>
<ul>
<li><strong>First Name</strong>: %s</li>
<li><strong>Last Name</strong>: %s</li>
<li><strong>Email</strong>: %s</li>
<li><strong>Phone</strong>: %s</li>
</ul>

<h2>Feedback Details</h2>
<p><strong>Feedback Topic:</strong> %s</p>
------------------------------------------------------------------------------
%s
------------------------------------------------------------------------------
<p><strong>General Opinion:</strong></p>
------------------------------------------------------------------------------
%s
------------------------------------------------------------------------------

<br /><br />

------------------------------------------------------------------------------
<p><em>
This is an autogenerated email. Please do not respond to this.<br />
You are receiving this notification because you are one of the email subscribers for the mentioned Feedback.<br />
If you wish to stop receiving emails, then please go to %sadmin.php?page=wp_feedback_settings and remove your email from there.<br />
If you can not access the link, then please contact your administrator.
</em></p>

<p>WP Feedback and Survey Manger Plugin <br />
- By Swashata<br />
http://www.intechgrity.com/</p>
</body></html>
', 'fbsr'), $pinfo['f_name'], $pinfo['l_name'], $pinfo['email'], $pinfo['phone'], $sub, wpautop($content), wpautop($op), get_admin_url());

        $header = 'Content-Type: text/html' . "\r\n";
        wp_mail($email, $sub, $content, $header);
    }

    public function print_form() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $tab_count = 0;
        ob_start();
        ?>
<style type="text/css">
    @import url('<?php echo plugins_url('/static/front/css/smoothness/jquery-ui-1.8.22.custom.css', wp_feedback_loader::$abs_file); ?>');
    @import url('<?php echo plugins_url('/static/front/css/validationEngine.jquery.css', wp_feedback_loader::$abs_file); ?>');
    @import url('<?php echo plugins_url('/static/front/css/form.css', wp_feedback_loader::$abs_file); ?>');
</style>
<div id="wp_feedback">
    <form action="" method="post" id="feedback_form">
        <input type="hidden" name="action" value="wp_feedback_submit" />
        <input type="hidden" name="wp_feedback[pinfo][h_h]" value="<?php echo $this->encrypt($num1 + $num2); ?>" />
        <div id="wp_feedback_tabs_wrap">
            <ul id="wp_feedback_tabs">
                <?php foreach($this->global['tab_order'] as $tab) : ?>
                <?php if(true == $this->global['enable_' . $tab]) : $tab_count++; ?>
                <li class="wp_feedback_<?php echo $tab; ?>"><a href="#wp_feedback_tab_<?php echo $tab; ?>"><?php echo $this->global[$tab . '_title']; ?><span><?php echo $this->global[ $tab . '_subtitle']; ?></span></a></li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <?php if(true == $this->global['enable_survey']) : ?>
            <div id="wp_feedback_tab_survey">
                <?php $e = 0; ?>
                <?php foreach($this->survey as $c => $survey) : ?>
                <?php if(true == $survey['enabled']) : $e++; ?>
                <div class="wp_feedback_survey_wrap <?php echo ($e % 2 == 0 ? 'even' : 'odd'); ?> wp_feedback_theme_bg">
                    <div class="wp_feedback_heading">
                        <h4><?php if(true == $survey['required']) echo '<span class="wp_feedback_required">*</span>'; ?><?php echo $survey['question']; ?></h4>
                    </div>
                    <div class="wp_feedback_content">
                        <?php if($survey['type'] == 'single') : ?>
                        <?php $this->print_radioboxes('wp_feedback[survey][' . $c . ']', $survey['options'], $survey['required']); ?>
                        <?php else : ?>
                        <?php $this->print_checkboxes('wp_feedback[survey][' . $c . '][]', $survey['options'], $survey['required']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
                <div class="clear"></div>
            </div>
            <?php endif; ?>

            <?php if(true == $this->global['enable_feedback']) : ?>
            <div id="wp_feedback_tab_feedback">
                <?php $e = 0; ?>
                <?php foreach($this->feedback as $c => $feedback) : ?>
                <?php if(true == $feedback['enabled']) : $e++; ?>
                <div class="wp_feedback_feedback_wrap_o <?php echo ($e % 2 == 0 ? 'even' : 'odd'); ?>">
                    <div class="wp_feedback_feedback_wrap wp_feedback_theme_bg">
                        <?php $this->print_feedback('wp_feedback[feedback][' . $c . ']', $feedback['description'], $feedback['name'], $feedback['required']); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
                <div class="clear"></div>
            </div>
            <?php endif; ?>

            <?php if(true == $this->global['enable_pinfo']) : ?>
            <?php
            $predefined = array(
                'f_name' => __('First Name:', 'fbsr'),
                'l_name' => __('Last Name:', 'fbsr'),
                'email' => __('Email:', 'fbsr'),
                'phone' => __('Phone:', 'fbsr'),
            );
            $e = 0;
            ?>
            <div id="wp_feedback_tab_pinfo">
                <?php foreach($predefined as $ppinfo => $label) : ?>
                <?php if(true == $this->pinfo[$ppinfo]['enabled']) : $e++; ?>
                <div class="wp_feedback_pinfo_input wp_feedback_float wp_feedback_pinfo_predefined <?php echo $ppinfo; ?> <?php echo ($e % 2 == 0 ? 'even' : 'odd'); ?>">
                    <div class="wp_feedback_pinfo_wrap wp_feedback_theme_bg">
                        <div class="wp_feedback_heading">
                            <h4>
                                <?php if(true == $this->pinfo[$ppinfo]['required']) echo '<span class="wp_feedback_required">*</span>'; ?>
                                <label for="wp_feedback_pinfo_<?php echo $ppinfo; ?>"><?php echo $label; ?></label>
                            </h4>
                        </div>
                        <div class="wp_feedback_content">
                            <?php $this->print_textbox('wp_feedback[pinfo][' . $ppinfo . ']', $this->pinfo[$ppinfo]['required']); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
                <div class="clear"></div>
                <?php $e = 0; foreach($this->pinfo as $key => $pinfo) : ?>
                <?php if(in_array($key, array('f_name', 'l_name', 'email', 'phone'), TRUE) || true != $pinfo['enabled']) continue; ?>
                <?php if('required-checkbox' == $pinfo['type']) : ?>
                <div class="wp_feedback_pinfo_rcheckbox wp_feedback_theme_bg">
                    <div class="wp_feedback_content">
                        <label for="wp_feedback_pinfo_<?php echo $key; ?>">
                            <input type="checkbox" class="validate[required]" id="wp_feedback_pinfo_<?php echo $key; ?>" value="true" name="wp_feedback[pinfo][<?php echo $key; ?>]" />
                            <?php echo htmlspecialchars_decode($pinfo['question']); ?>
                        </label>
                    </div>
                </div>
                <?php elseif('free-input' == $pinfo['type']) : $e++; ?>
                <div class="wp_feedback_pinfo_input wp_feedback_float wp_feedback_pinfo_userdefined wp_feedback_pinfo_<?php echo $key; ?> <?php echo ($e % 2 == 0 ? 'even' : 'odd'); ?>">
                    <div class="wp_feedback_pinfo_wrap wp_feedback_theme_bg">
                        <div class="wp_feedback_heading">
                            <h4>
                                <?php if(true == $pinfo['required']) echo '<span class="wp_feedback_required">*</span>'; ?>
                                <label for="wp_feedback_pinfo_<?php echo $key; ?>"><?php echo $pinfo['question']; ?></label>
                            </h4>
                        </div>
                        <div class="wp_feedback_content">
                            <?php $this->print_textbox('wp_feedback[pinfo][' . $key . ']', $pinfo['required']); ?>
                        </div>
                    </div>
                </div>
                <?php else : $e = 0; ?>
                <div class="clear"></div>
                <div class="wp_feedback_pinfo wp_feedback_pinfo_userdefined wp_feedback_pinfo_<?php echo $key; ?>">
                    <div class="wp_feedback_pinfo_wrap wp_feedback_theme_bg">
                        <div class="wp_feedback_heading">
                            <h4>
                                <?php if(true == $pinfo['required']) echo '<span class="wp_feedback_required">*</span>'; ?>
                                <label for="wp_feedback_pinfo_<?php echo $key; ?>"><?php echo $pinfo['question']; ?></label>
                            </h4>
                        </div>
                        <div class="wp_feedback_content">
                        <?php switch($pinfo['type']) :
                            case 'single' :
                                $this->print_radioboxes('wp_feedback[pinfo][' . $key . ']', $pinfo['options'], $pinfo['required']);
                                break;
                            case 'multiple' :
                                $this->print_checkboxes('wp_feedback[pinfo][' . $key . '][]', $pinfo['options'], $pinfo['required']);
                                break;
                            case 'free-text' :
                                $this->print_textarea('wp_feedback[pinfo][' . $key . ']', $pinfo['required']);
                                break;
                        endswitch; ?>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <?php endif; ?>
                <?php endforeach; ?>
                <div class="wp_feedback_pinfo_input wp_feedback_float wp_feedback_pinfo_security wp_feedback_pinfo_human odd">
                    <div class="wp_feedback_pinfo_wrap wp_feedback_theme_bg">
                        <div class="wp_feedback_heading">
                            <h4>
                                <?php echo '<span class="wp_feedback_required">*</span>'; ?>
                                <label for="wp_feedback_pinfo_human"><?php echo __('Security Question: ') . $num1 . ' + ' . $num2 . ' = ?'; ?></label>
                            </h4>
                        </div>
                        <div class="wp_feedback_content">
                            <input type="text" class="validate[required,funcCall[wp_feedback_security]] text" name="wp_feedback[pinfo][human]" id="wp_feedback_pinfo_human" value="" />
                        </div>
                    </div>
                </div>
                <div class="wp_feedback_pinfo_input wp_feedback_float wp_feedback_pinfo_submission wp_feedback_pinfo_date even">
                    <div class="wp_feedback_pinfo_wrap wp_feedback_theme_bg">
                        <div class="wp_feedback_heading">
                            <h4>
                                <label for="wp_feedback_pinfo_human"><?php _e('Submission Date:', 'fbsr'); ?></label>
                            </h4>
                        </div>
                        <div class="wp_feedback_content">
                            <input type="text" class="text" value="<?php echo date('F jS, Y', current_time('timestamp')); ?>" readonly="readonly" />
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <?php if(!empty($this->global['terms_page'])) : $link = get_permalink($this->global['terms_page']); ?>
                <div class="wp_feedback_pinfo_rcheckbox wp_feedback_theme_bg">
                    <div class="wp_feedback_content">
                        <label for="wp_feedback_pinfo_terms">
                            <input type="checkbox" id="wp_feedback_pinfo_terms" name="wp_feedback[pinfo][terms]" class="validate[required]" value="1" />
                            <?php printf(__('By submitting this form, you hereby accept our <a href="%s" target="_blank">Terms & Conditions</a>. Your IP address <strong>%s</strong> will be stored in our database.', 'fbsr'), $link, $_SERVER['REMOTE_ADDR']); ?>
                        </label>
                    </div>
                </div>
                <?php endif; ?>
                <div class="clear"></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="wp_feedback_navigation">
            <?php if($tab_count > 1) : ?>
            <button class="prev_button"><?php _e('&laquo; Previous', 'fbsr'); ?></button>
            <?php endif; ?>
            <button type="submit" class="sub_button"><?php _e('Submit', 'fbsr'); ?></button>
            <?php if($tab_count > 1) : ?>
            <button class="next_button"><?php _e('Next &raquo;', 'fbsr'); ?></button>
            <?php endif; ?>
        </div>
    </form>
    <div id="wp_feedback_ajax" style="display: none">
        <h4><?php _e('Please wait while we are submitting your form', 'fbsr'); ?></h4>
        <p><?php _e('Submitting', 'fbsr'); ?></p>
    </div>
    <!--
    <div id="wp_feedback_success">
        <h4><?php _e('Your feedback was successfully submitted', 'fbsr'); ?></h4>
        <p><?php echo $this->global['success_message']; ?></p>
    </div>
    <div id="wp_feedback_error">
        <h4><?php _e('Some error has occured', 'fbsr'); ?></h4>
        <p><?php _e('Please correct the data and submit again', 'fbsr'); ?></p>
    </div>
    -->
</div>
<script type="text/javascript">
function wp_feedback_security(field, rules, i, options) {
    if(field.val() != <?php echo ($num1 + $num2); ?>) {
        return '<?php _e('* The answer is incorrect. It should be ', 'fbsr'); ?>' + '<?php echo ($num1 + $num2); ?>';
    }
}
</script>
        <?php
        return ob_get_clean();
    }

    private function print_textbox($name, $required = true) {
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        ?>
<input type="text" class="<?php echo ($required ? 'validate[required]' : ''); ?> text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="" />
        <?php
    }

    private function print_textarea($name, $required = false) {
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        ?>
<textarea id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="textarea <?php echo ($required ? 'validate[required]' : ''); ?>"></textarea>
        <?php
    }

    private function print_feedback($name, $description, $f_name, $required = false) {
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        ?>
<div class="<?php if($required == false) echo 'toggle_f'; ?> wp_feedback_freetype">
    <div class="wp_feedback_heading">
        <h4><label for="<?php echo $id; ?>"><?php if($required == false) : ?><input type="checkbox" class="checkbox" id="<?php echo $id; ?>" /><?php else : ?><span class="wp_feedback_required">*</span><?php endif; echo $f_name; ?></label></h4>
    </div>
    <div class="<?php if($required == false) echo 'toggle_d'; ?> inner wp_feedback_content">
        <?php echo wpautop(wptexturize($description)); ?>
        <textarea<?php if($required == true) echo ' id="' . $id . '"'; ?> class="textarea <?php if($required == true) echo 'validate[required]' ?>" name="<?php echo $name; ?>"></textarea>
    </div>
</div>
        <?php
    }

    private function print_radioboxes($name, $options, $required = true) {
        $option = $this->split_options($options);
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        $i = 0;
        ?>
<?php foreach($option as $k => $v) : $i = $k + 1; ?>
<label class="float <?php echo ($k%2 == 0 ? 'even' : 'odd'); ?>" for="<?php echo $id . '_' . $k; ?>">
    <input type="radio" class="<?php echo ($required ? 'validate[required]' : ''); ?> radio" name="<?php echo $name; ?>" id="<?php echo $id . '_' . $k; ?>" value="<?php echo $k; ?>" />
    <?php echo $v; ?>
</label>
<?php endforeach; ?>
<div class="clear"></div>
        <?php
    }

    private function print_checkboxes($name, $options, $required = true) {
        $option = $this->split_options($options);
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        $i = 0;
        ?>
<?php foreach($option as $k => $v) : $i = $k + 1; ?>
<label class="float <?php echo ($k%2 == 0 ? 'even' : 'odd'); ?>" for="<?php echo $id . '_' . $k; ?>">
    <input type="checkbox" class="<?php echo ($required ? 'validate[minCheckbox[1]]' : ''); ?> checkbox" name="<?php echo $name; ?>" id="<?php echo $id . '_' . $k; ?>" value="<?php echo $k; ?>" />
    <?php echo $v; ?>
</label>
<?php endforeach; ?>
<div class="clear"></div>
        <?php
    }

    private function split_options($option) {
        $option = explode("\n", str_replace("\r", '', $option));
        $clean = array();
        array_walk($option, 'trim');
        foreach($option as $v) {
            if('' != $v)
                $clean[] = $v;
        }
        return $clean;
    }

    protected function clean_options(&$value) {
        $value = htmlspecialchars(trim(strip_tags(htmlspecialchars_decode($value))));
    }

    /**
     * stripslashes gpc
     * Strips Slashes added by magic quotes gpc thingy
     * @access protected
     * @param string $value
     */
    protected function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }

    protected function htmlspecialchar_ify(&$value) {
        $value = htmlspecialchars(trim(strip_tags($value)));
    }

    protected function encrypt($input_string){
        $key = NONCE_SALT;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $h_key = hash('sha256', $key, TRUE);
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $h_key, $input_string, MCRYPT_MODE_ECB, $iv));
    }

     protected function decrypt($encrypted_input_string){
        $key = NONCE_SALT;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $h_key = hash('sha256', $key, TRUE);
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $h_key, base64_decode($encrypted_input_string), MCRYPT_MODE_ECB, $iv));
    }
}

class wp_feedback_form_shortcode {
    public function feedback_cb() {
        $form = new wp_feedback_form();
        $show_form = true;
        $this->feedback_enqueue();

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $get_save_post = $form->save_post ();
            if($get_save_post['type'] == 'fail') {
                $show_form = true;
            } else {
                $show_form = false;
            }
        }

        if($show_form)
            return $get_save_post['msg'] . $form->print_form ();
        else
            return $get_save_post['msg'];

    }

    public function feedback_enqueue() {
        //wp_enqueue_script('wp_feedback_shortcode', plugins_url('/static/front/js/form.js', wp_feedback_loader::$abs_file), array('jquery'), wp_feedback_loader::$version, true);
        wp_enqueue_script('wp_feedback_ve', plugins_url('/static/front/js/jquery.validationEngine-en.js', wp_feedback_loader::$abs_file), array('jquery'), wp_feedback_loader::$version, true);
        wp_enqueue_script('wp_feedback_v', plugins_url('/static/front/js/jquery.validationEngine.js', wp_feedback_loader::$abs_file), array('jquery'), wp_feedback_loader::$version, true);
        //wp_enqueue_script('wp_feedback_jquis', plugins_url('/static/front/js/jquery-ui-1.8.22.custom.min.js', wp_feedback_loader::$abs_file), array('jquery'), wp_feedback_loader::$version, true);
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-button');
        wp_enqueue_script('wp_feedback_form', plugins_url('/static/front/js/form.js', wp_feedback_loader::$abs_file), array('jquery'), wp_feedback_loader::$version, true);
        wp_localize_script('wp_feedback_form', 'wpFBObj', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));
    }
}

class wp_feedback_trend {
    public $survey;
    public $global;

    public function __construct() {
        $this->global = get_option('wp_feedback_global');
        $this->survey = get_option('wp_feedback_survey');
    }

    public function print_trend() {
        $data = $this->get_data();

        if($data['type'] == 'Empty') {
            return __('Not enough data to populate the trends. Please be patient!', 'fbsr');
        }

        $info = array();
        ob_start();
        ?>
<style type="text/css">
@import url('<?php echo plugins_url('/static/front/css/form.css', wp_feedback_loader::$abs_file); ?>');
</style>
<noscript><?php _e('You need to enabled JavaScript to view this page', 'fbsr'); ?></noscript>
<div id="wp_feedback">
<?php $i = 0; foreach($this->survey as $sk => $survey) : ?>
<?php if($survey['enabled'] == true) : $i++; ?>
<?php $info[$sk] = explode("\r\n", $survey['options']); ?>
    <div class="wp_feedback_float <?php echo ($i % 2 == 0 ? 'even' : 'odd'); ?>">
        <div id="wp_feedback_trend_<?php echo $sk; ?>" class="wp_feedback_trend wp_feedback_theme_bg ">
            <div class="wp_feedback_heading">
                <h4><?php echo $survey['question']; ?></h4>
            </div>
            <div class="wp_feedback_content">
                <div id="wp_feedback_trend_<?php echo $sk; ?>_pie"><img src="<?php echo plugins_url('/static/admin/images/ajax.gif', wp_feedback_loader::$abs_file); ?>" /></div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php endforeach; ?>
</div>
<?php if($i == 0) : //No survey acticvated ?>
<div class="p-message red">
    <p>
        <?php _e('No survey has been activated yet! Please go to the <strong>Settings</strong> page and enable and setup some survey question to see data here.', 'fbsr'); ?>
    </p>
</div>
<?php endif; ?>
<div class="clear" style="clear: both"></div>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load('visualization', '1.0', {'packages':['corechart']});
</script>
<script type="text/javascript">
    var data = <?php echo json_encode((object) $data); ?>;
    var info = <?php echo json_encode((object) $info); ?>;

    function onLoadPie() {
        for(var question in info) {
            var gdata = new Array();
            gdata[0] = new Array('<?php _e('Options', 'fbsr'); ?>', '<?php _e('Count', 'fbsr'); ?>');
            for(var option in info[question]) {
                gdata[gdata.length] = new Array(info[question][option], data[question][option]);
            }

            document.getElementById('wp_feedback_trend_' + question + '_pie').innerHTML = '';

            new google.visualization.PieChart(document.getElementById('wp_feedback_trend_' + question + '_pie')).draw(google.visualization.arrayToDataTable(gdata), {
                title : '<?php _e('Answers', 'fbsr'); ?>',
                is3D : true
            });
        }
    }
    google.setOnLoadCallback(onLoadPie);
</script>
<p style="text-align: right">
    <small><em><?php printf(__('Powered by: %s', 'fbsr'), '<a href="http://www.intechgrity.com/wp-plugins/wp-feedback-survey-manager/">WP Feedback & Survey Manager Plugin - WordPress - InTechgrity</a>') ?></em></small>
</p>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the data from the database
     * It does not check whether the survey question is enabled. It simply returns all the values from the latest 100 db rows
     * @access Private
     * @global wpdb $wpdb
     * @global array $wp_feedback_info
     * @return array Associative array of enabled survey questions' results where the key corresponds to the question number
     */
    private function get_data() {
        $data = get_transient('wp_feedback_data_t');

        if(false !== $data) {
            return $data;
        }

        $data = array();

        global $wpdb, $wp_feedback_info;
        $results = $wpdb->get_col("SELECT survey FROM {$wp_feedback_info['feedback_table']} ORDER BY `date` DESC LIMIT 0,100");
        if(null == $results) {
            $data['type'] = 'Empty';
        } else {
            $data['type'] = 'NonEmpty';
            foreach($results as $result) {
                $result = maybe_unserialize($result);
                foreach($result as $k => $r) {
                    if(is_array($r)) {
                        foreach($r as $l) {
                            $data[$k][$l]++;
                        }
                    } else {
                        $data[$k][$r]++;
                    }
                }
            }
        }

        set_transient('wp_feedback_data_t', $data, 1*60*60);
        return $data;
    }
}
