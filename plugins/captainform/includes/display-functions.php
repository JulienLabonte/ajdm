<?php
defined('ABSPATH') or die('No direct access!');
/*our display functions for outputting information*/
$custom_vars = null;
function captainform_add_content($content)
{
    $content = captainform_text_filter($content);
    return $content;
}

function captainform_filter_summary($content)
{
    $content = captainform_text_filter($content, NULL, NULL, true);
    return $content;
}

add_filter('the_content', 'captainform_add_content');
add_filter('post_content', 'captainform_add_content');
add_filter('the_excerpt', 'captainform_filter_summary');

function captainform_get_custom_vars_from_shortcode($shortcode)
{
    $custom_vars_string = '';

    preg_match_all('/cf_custom_var([\\d-]+)=(\'[^\']+\'|"[^"]+")/', $shortcode, $matches, PREG_SET_ORDER);
    if (count($matches))
        foreach ($matches as $custom_var)
            if (isset($custom_var[1]) && !empty($custom_var[1]) && isset($custom_var[2]) && !empty($custom_var[2]))
                $custom_vars_string .= '&control' . $custom_var[1] . '=' . urlencode(substr($custom_var[2], 1, -1));

    return $custom_vars_string;
}

function captainform_get_custom_vars_from_url()
{
    $custom_vars_string = '';

    foreach ($_GET as $key => $value) {
        if (strpos($key, 'control') !== false) {
            $custom_vars_string .= '&' . $key . '=' . $value;
        }
    }

    return $custom_vars_string;
}

function captainform_get_custom_vars($shortcode, $extra_vars)
{
    $custom_vars_string = '';
    $custom_vars_string .= captainform_get_custom_vars_from_shortcode($shortcode);
    $custom_vars_string .= captainform_get_custom_vars_from_url();
    if (gettype($extra_vars) == "array" && count($extra_vars)) {
        foreach ($extra_vars as $key => $value)
            $custom_vars_string .= '&' . $key . '=' . $value;
    } elseif (strlen($extra_vars))
        $custom_vars_string .= $extra_vars;

    if (strlen($custom_vars_string))
        $custom_vars_string .= '&embeddingCustomVars=true';

    return $custom_vars_string;
}

function captainform_replace_pattern($matches)
{
    global $custom_vars;
    $shortcode = $matches[1];

    $captainform_theme_style = '';
    if (isset($_GET['captainform_theme_style'])) {
        wp_register_script('captainform_iframe_resizer_win', plugins_url('/includes/js/iframeResizer.contentWindow.min.js', __DIR__), array(), '3.5', false);
        wp_enqueue_script('captainform_iframe_resizer_win');
        wp_register_script('nolink', plugins_url('/includes/js/nolink.js', __DIR__), array(), uniqid(), false);
        wp_enqueue_script('nolink');
        //wp_enqueue_style('hide_adminbar', plugins_url('/includes/css/hide_adminbar.css', __DIR__), false, uniqid());
        $captainform_theme_style = '&style=' . $_GET['captainform_theme_style'];
    }

    $type_regex = '/\[[^\[]*captain-?form*.*type=[\',"]{1}([a-zA-Z0-9\/\-_\.\s]+)[\',"]{1}[^\]]*\]/';
    $lightbox_regex = '/\[[^\[]*captain-?form.*lightbox=[\',"]{1}([a-zA-Z0-9\/\-_\.\s]+)[\',"]{1}[^\]]*\]/';

    $content_regex = "/\\[[^\\[]*captain-?form.*content=[\\',\"]{1}([^'\"]+)[\\'\"]{1}[^\\]]*\\]/i";
    $text_content_regex = '/\[[^\[]*captain-?form.*text_content=[\',"]{1}([^\'"]+)[\'"]{1}[^\]]*\]/';

    $url_regex = '/\[[^\[]*captain-?form.*url=[\',"]{1}([a-zA-Z0-9\/\-_\.\s\:\?\=]+)[\',"]{1}[^\]]*\]/';
    $miliseconds_regex = '/\[[^\[]*captain-?form.*miliseconds=[\',"]{1}([0-9]+)[\',"]{1}[^\]]*\]/';
    $text_color_regex = '/\[[^\[]*captain-?form.*text_color=[\',"]{1}([a-zA-Z0-9]+)[\',"]{1}[^\]]*\]/';
    $bg_color_regex = '/\[[^\[]*captain-?form.*bg_color=[\',"]{1}([a-zA-Z0-9]+)[\',"]{1}[^\]]*\]/';
    $position_regex = '/\[[^\[]*captain-?form.*position=[\',"]{1}([a-zA-Z]+)[\',"]{1}[^\]]*\]/';

    preg_match($type_regex, $shortcode, $matches_type);
    preg_match($lightbox_regex, $shortcode, $matches_lightbox);
    preg_match($content_regex, $shortcode, $matches_content);
    preg_match($text_content_regex, $shortcode, $matches_text_content);
    preg_match($url_regex, $shortcode, $matches_url);
    preg_match($miliseconds_regex, $shortcode, $matches_miliseconds);
    preg_match($text_color_regex, $shortcode, $matches_text_color);
    preg_match($bg_color_regex, $shortcode, $matches_bg_color);
    preg_match($position_regex, $shortcode, $matches_position);

    $shortcode_option_type = isset($matches_type[1]) ? $matches_type[1] : null;
    $shortcode_option_lightbox = isset($matches_lightbox[1]) ? $matches_lightbox[1] : null;
    $shortcode_option_content = isset($matches_content[1]) ? urldecode($matches_content[1]) : null;
    $shortcode_option_text_content = isset($matches_text_content[1]) ? urldecode($matches_text_content[1]) : null;
    $shortcode_option_url = isset($matches_url[1]) ? $matches_url[1] : null;
    $shortcode_option_miliseconds = isset($matches_miliseconds[1]) ? $matches_miliseconds[1] : null;
    $shortcode_option_text_color = isset($matches_text_color[1]) ? $matches_text_color[1] : '';
    $shortcode_option_bg_color = isset($matches_bg_color[1]) ? $matches_bg_color[1] : '';
    $shortcode_option_position = isset($matches_position[1]) ? $matches_position[1] : '';

    if (isset($_GET['cf_form_id']) && isset($_GET['captainform_theme_style'])) {
        if (intval($_GET['cf_form_id']) && is_numeric($matches[4]) && $_GET['cf_form_id'] !== intval($matches[4])) {
            echo 'Form hidden in preview mode.';
            return '';
        }
    }

    $custom_vars_string = captainform_get_custom_vars($shortcode, $custom_vars);

    $form_id = 0;
    if (is_numeric($matches[4]))
        $form_id = intval($matches[4]);
    elseif ($matches[4] == '{cf_form_id}') {
        if (isset($_GET['cf_form_id']) && intval($_GET['cf_form_id'])) {
            $form_id = intval($_GET['cf_form_id']);
        }
    }

    if (!$form_id)
        return '';

    $button_style = '';
    $content = 'Contact Us';

    $options = array(
        'id' => $form_id,
        'style' => $captainform_theme_style,
        'customVars' => isset($custom_vars_string) ? $custom_vars_string : "",
    );

    $pattern_type = $GLOBALS['captainform_formcode_pattern'];

    if ($shortcode_option_lightbox == 1 || isset($_GET['captainform_preview_as_lightbox'])) {
        $miliseconds = 3000;

        if (isset($_GET['captainform_preview_as_lightbox'])) {
            $shortcode_option_type = 'auto-popup';
            $miliseconds = 1000;
        }

        if (isset($GLOBALS['captainform_formcode_pattern_lightbox']))
            $pattern_type = $GLOBALS['captainform_formcode_pattern_lightbox'];

        if (strlen(trim($shortcode_option_text_content))) {
            $content = $shortcode_option_text_content;
        } else if (strlen(trim($shortcode_option_content))) {
            $content = $shortcode_option_content;
        }

        switch ($shortcode_option_type) {
            case 'text':
                break;
            case 'image':
                if (strlen(trim($shortcode_option_url))) {
                    $content = '<img border="0" src="' . $shortcode_option_url . '" />';
                }
                break;
            case 'floating-button':
                if ($shortcode_option_position == 'right') {
                    $pos = 2;
                    $extra_style = 'right: 0; top: 40%;';
                } elseif ($shortcode_option_position == 'left') {
                    $pos = 1;
                    $extra_style = 'left: 0; top: 40%;';
                } elseif ($shortcode_option_position == 'bottom') {
                    $pos = 3;
                    $extra_style = 'right: 20%; bottom: 0;';
                } else {
                    $pos = 1;
                    $extra_style = 'left: 0; top: 0; bottom: 0;';
                }

                $button_style = 'style="outline:0; text-decoration:none; color:transparent; display:scroll ;z-index:10; border: none; position:fixed; ' . $extra_style . '"';
                $content = '<img style="box-shadow: none;" border="0" src="https://' . $GLOBALS['captainform_servicedomain'] . '/verticalbutton2.php?bg=' . $shortcode_option_bg_color . '&fnt=' . $shortcode_option_text_color . '&pos=' . $pos . '&text=' . $content . '&font=arialblk" />';
                break;
            case 'auto-popup':
                if (strlen(trim($shortcode_option_miliseconds))) {
                    $miliseconds = intval($shortcode_option_miliseconds);
                }
                if (isset($GLOBALS['captainform_formcode_pattern_lightbox_auto']))
                    $pattern_type = $GLOBALS['captainform_formcode_pattern_lightbox_auto'];
                break;
            default:
                break;
        }

        $options = array(
            'id' => $form_id,
            'button_style' => $button_style,
            'content' => $content,
            'miliseconds' => $miliseconds,
            'customVars' => isset($custom_vars_string) ? $custom_vars_string : "",
            'style' => $captainform_theme_style
        );
    }

    $formcode = captainform_replace_patterns($pattern_type, $options);
    return $formcode;
}

function captainform_remove_pattern()
{
    return '';
}

/**
 * parse content and replace plugin short tag with correct code
 * @param string
 * @return string
 **/
function captainform_text_filter($content, $custom_vars = "", $custom_options = array(), $summary = false)
{
    $GLOBALS['custom_vars'] = $custom_vars;

    $pattern = '/(\[[^\[]*(captain-?form).*(id?|i)=?"?([0-9]+|{cf_form_id})"?[^\]]*\])/';
    if ($summary === true)
        return preg_replace_callback($pattern, 'captainform_remove_pattern', $content);
    else
        return preg_replace_callback($pattern, 'captainform_replace_pattern', $content);
}

/**
 * add editor button for select forms
 * */
// Makes sure the plugin is defined before trying to use it
if (!function_exists('is_plugin_active_for_network')) {
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

//show editor toolbar icon only if plugin is active
if (in_array($captainform_plugin_name . '/' . $captainform_plugin_name . '.php', get_option('active_plugins')) || is_plugin_active_for_network($captainform_plugin_name . '/' . $captainform_plugin_name . '.php'))
    add_filter('mce_buttons', 'captainform_add_button', 0);

function captainform_add_button($buttons)
{
    array_push($buttons, 'separator', 'captainform');
    return $buttons;
}


if (!function_exists('captain_form')) {


    function captain_form($id, $custom_options = array())
    {
        $custom_vars = isset($custom_options['custom_vars']) ? $custom_options['custom_vars'] : '';

        $shortcode_final = '[captainform id="' . $id . '" ';
        $shortcode_final .= (isset($custom_options['lightbox'])) ? "lightbox='{$custom_options['lightbox']}'" : '';
        $shortcode_final .= (isset($custom_options['type'])) ? "type='{$custom_options['type']}'" : '';
        $shortcode_final .= (isset($custom_options['url'])) ? "url='{$custom_options['url']}'" : '';
        if (isset($custom_options['text_content']))
            $shortcode_final .= "text_content='" . $custom_options['text_content'] . "'";
        else
            $shortcode_final .= (isset($custom_options['content'])) ? "content='{$custom_options['content']}'" : '';
        $shortcode_final .= (isset($custom_options['miliseconds'])) ? "miliseconds='{$custom_options['miliseconds']}'" : '';
        $shortcode_final .= (isset($custom_options['text_color'])) ? "text_color='{$custom_options['text_color']}'" : '';
        $shortcode_final .= (isset($custom_options['bg_color'])) ? "bg_color='{$custom_options['bg_color']}'" : '';
        $shortcode_final .= (isset($custom_options['position'])) ? "position='{$custom_options['position']}'" : '';

        $shortcode_final .= ']';

        if ($custom_vars != "") {
            if (gettype($custom_vars) == "array") {
                $custom_vars = http_build_query($custom_vars);
            }
        }
        return captainform_widget_text_filter($shortcode_final, $custom_vars);
    }

    //Allow captain_form() function to be called from any file
    register_activation_hook(__FILE__, 'captain_form');
}