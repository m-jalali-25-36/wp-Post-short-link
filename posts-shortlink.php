<?php

/**
 * Plugin Name: Posts short link
 * Description: Posts short link
 * Version: 1.2.0
 * Author: jalali2536
 * Author URI: https://profiles.wordpress.org/jalali2536/
 */

/**
 * For plugin 'page short link'.
 * const query vars name.
 */
const psl_query_vars_name = 'shortlink';
/**
 * For plugin 'page short link'.
 * const options name.
 */
const psl_option_name = 'psl_option';

/**
 * For plugin 'page short link'.
 * @return array Restores the plugin default option.
 */
function psl_get_default_option()
{
    return array(
        'slug' => 'q',
        'indicator_mode' => 'decimal',
        'character' => 0,
    );
}

/**
 * For plugin 'page short link'.
 * @return array Restores the plugin option.
 */
function psl_get_option()
{
    $geted  = get_option(psl_option_name, array());
    $def = psl_get_default_option();
    foreach ($def as $key => $value) {
        if (isset($geted[$key]))
            $def[$key] = $geted[$key];
    }
    return $def;
}

/**
 * For plugin 'page short link'.
 * Convert a number from base arbitrary to ten base.
 * @param string $key A base number of $arr.
 * @param array $arr An array for the base.
 * @return int The base number is ten
 */
function psl_convert_to_decimal($key, $arr)
{
    $res = 0;
    $i = strlen((string)$key);
    $i--;
    $size = count($arr);
    $pow = 1;
    while ($i >= 0) {
        $res += array_search((string)$key[$i], $arr) * $pow;
        $pow *= $size;
        $i--;
    }
    return $res;
}

/**
 * For plugin 'page short link'.
 * Convert a number from base ten to arbitrary base.
 * @param int $n A base ten number to convert.
 * @param array $arr An array for the base.
 * @param int $c Optional. Number of output characters.
 * @return string New base number.
 */
function psl_convert_decimal_to($n, $arr, $c = 0)
{
    $res = '';
    $n = (int)$n;
    $size = count($arr);
    while ($n > 0) {
        $b = $n % $size;
        $res = $arr[$b] . $res;
        $n = ($n - $b) / $size;
    }
    $c -= strlen($res);
    while ($c > 0) {
        $res = $arr[0] . $res;
        $c--;
    }
    return $res;
}
/**
 * For plugin 'page short link'.
 * (A-Z) + (a-z) => 52
 * @return array An array of fifty-two distinct characters.
 */
function psl_get_fiftytwo()
{
    return  array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
}

/**
 * For plugin 'page short link'.
 * (0-9) + (a-z) => 36
 * @return array An array of thirty-six distinct characters.
 */
function psl_get_thirtysix()
{
    return array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
}

/**
 * For plugin 'page short link'.
 * (a-z) => 26
 * @return array An array of twenty-six distinct characters.
 */
function psl_get_twentysix()
{
    return  array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
}

/**
 * For plugin 'page short link'.
 * (0-9) + (a-f) => 16
 * @return array An array of sixteen distinct characters
 */
function psl_get_hexadecimal()
{
    return  array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
}

/**
 * For plugin 'page short link'.
 * Get indicator by id.
 * @param int $id A number as id.
 * @return string A string as an indicator.
 */
function psl_get_indicator($id)
{
    $option = psl_get_option();
    if (isset($option['indicator_mode'])) {
        if (!isset($option['character']))
            $option['character'] = 0;
        switch ($option['indicator_mode']) {
            case 'hexadecimal':
                return psl_convert_decimal_to($id, psl_get_hexadecimal(), $option['character']);

            case 'twentysix':
                return psl_convert_decimal_to($id, psl_get_twentysix(), $option['character']);

            case 'thirtysix':
                return psl_convert_decimal_to($id, psl_get_thirtysix(), $option['character']);

            case 'fiftytwo':
                return psl_convert_decimal_to($id, psl_get_fiftytwo(), $option['character']);

            case 'decimal':
            default:
                return $id;
        }
    }
    return $id;
}

if (!function_exists('get_shortlink')) {
    /**
     * For plugin 'page short link'.
     * Get short link by id.
     * @param int $id A number as id.
     * @return string short link.
     */
    function get_shortlink($id)
    {
        return get_home_url() . '/' . psl_get_slug()  . '/' . psl_get_indicator($id);
    }
}

/**
 * For plugin 'page short link'.
 * Add a column title to the posts table.
 * Filter 'manage_posts_columns'
 */
function psl_add_column_get_shortlink_title($columns)
{
    return array_merge($columns, array('shortlink' => __('Short Link')));
}
add_filter('manage_posts_columns', 'psl_add_column_get_shortlink_title', 1);

/**
 * For plugin 'page short link'.
 * Add short link column content to the posts table
 * Action 'manage_posts_custom_column'
 */
function psl_add_column_get_shortlink($column, $id)
{
    if ($column == 'shortlink') {
        $indicator = psl_get_indicator((int)$id);
        $slug = psl_get_slug();
        $url_shortlink = get_home_url() . '/' . $slug . '/' . $indicator;
        echo '<p onclick="navigator.clipboard.writeText(\'' . $url_shortlink . '\');alert(\'Copied the link\')" title="Click to copy the address. ' . $url_shortlink . '">../' . $slug . '/' . $indicator . '</p>';
    }
}
add_action('manage_posts_custom_column', 'psl_add_column_get_shortlink', 2, 2);

/**
 * For plugin 'page short link'.
 * Returns the slug.
 * @return string slug.
 */
function psl_get_slug()
{
    $option = psl_get_option();
    if (isset($option['slug']))
        return $option['slug'];
    else
        return 'q';
}

/**
 * For plugin 'page short link'.
 * Flushes rewrites if our short link rule isn't yet added.
 * Action 'wp_loaded'
 */
function psl_flush_rules()
{
    $rules = get_option('rewrite_rules');
    $slug = psl_get_slug();
    if (!isset($rules["$slug/([^/]+)/?$"])) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
add_action('wp_loaded', 'psl_flush_rules');

/**
 * For plugin 'page short link'.
 * Adds a short link rewrite rule.
 * Filter 'rewrite_rules_array'
 * @param array $rules Existing rewrite rules.
 * @return array (Maybe) modified list of rewrites.
 */
function psl_insert_rewrite_rules($rules)
{
    $newrules = array();
    $slug = psl_get_slug();
    $newrules["$slug/([^/]+)/?$"] = 'index.php?' . psl_query_vars_name . '=$matches[1]';
    return $newrules + $rules;
}
add_filter('rewrite_rules_array', 'psl_insert_rewrite_rules');

/**
 * For plugin 'page short link'.
 * Adding the shortlink var so that WP recognizes it.
 * Filter 'query_vars'
 * @param array $vars
 * @return array
 */
function psl_insert_query_vars($vars)
{
    array_push($vars, psl_query_vars_name);
    return $vars;
}
add_filter('query_vars', 'psl_insert_query_vars');

/**
 * For plugin 'page short link'.
 * Checks if there is a short link redirects to the main address otherwise continues.
 * Filter 'request'
 * @param array $vars
 * @return array
 */
function psl_redirect($vars)
{
    if (key_exists(psl_query_vars_name, $vars)) {
        $id = -1;
        $option = psl_get_option();
        if (isset($option['indicator_mode'])) {
            switch ($option['indicator_mode']) {
                case 'hexadecimal':
                    $id = (int) psl_convert_to_decimal(strtolower((string)$vars[psl_query_vars_name]), psl_get_hexadecimal());
                    break;

                case 'twentysix':
                    $id = (int) psl_convert_to_decimal(strtolower((string)$vars[psl_query_vars_name]), psl_get_twentysix());
                    break;

                case 'thirtysix':
                    $id = (int) psl_convert_to_decimal(strtolower((string)$vars[psl_query_vars_name]), psl_get_thirtysix());
                    break;

                case 'fiftytwo':
                    $id = (int) psl_convert_to_decimal($vars[psl_query_vars_name], psl_get_fiftytwo());
                    break;

                case 'decimal':
                default:
                    $id = (int)$vars[psl_query_vars_name];
                    break;
            }
        } else {
            $id = (int)$vars[psl_query_vars_name];
        }
        $url = get_permalink($id);
        if ($url === false) {
            if (wp_redirect(get_home_url())) {
                exit;
            }
        } else {
            if (wp_redirect($url)) {
                exit;
            }
        }
    }
    return $vars;
}
add_filter('request', 'psl_redirect');


/**
 * For plugin 'page short link'.
 * Add Panel options for Admin.
 * Filter 'admin_menu'
 */
function psl_add_menu()
{
    $tt_page = add_submenu_page("options-general.php", "Posts short link", "Posts short link", "manage_options", "psl-panel", "psl_admin_panel_display", null, 99);
}
add_action("admin_menu", "psl_add_menu");
/**
 * 
 * @param mixed $value
 * @param mixed $sample
 * @param array $arr
 * 
 * @return bool if data is valide then return true
 */
function psl_validate($value, $sample, $arr = array())
{
    if (isset($arr['name'])) {
        if (isset($arr[$arr['name']]) && is_array($arr[$arr['name']]))
            $arr = $arr[$arr['name']];
        else
            $arr = array();
    }

    $arr = array_merge(array(
        'empty_allowed' => false,
        'max' => false,
        'min' => false,
        'length' => false,
        'max_length' => false,
        'min_length' => false,
    ), $arr);

    if (empty($value) && !$arr['empty_allowed'])
        return false;

    if (gettype($value) != gettype($sample))
        return false;

    if (is_numeric($value) && $arr['max'] !== false)
        if ($value > $arr['max'])
            return false;

    if (is_numeric($value) && $arr['min'] !== false)
        if ($value < $arr['min'])
            return false;

    if (is_string($value) && $arr['length'] !== false)
        if (strlen($value) !== $arr['length'])
            return false;

    if (is_string($value) && $arr['max_length'] !== false)
        if (strlen($value) > $arr['max_length'])
            return false;

    if (is_string($value) && $arr['min_length'] !== false)
        if (strlen($value) < $arr['min_length'])
            return false;


    return true;
}

/**
 * For plugin 'page short link'.
 * Admin panel viewer.
 */
function psl_admin_panel_display()
{
    if (isset($_POST['submit'])) {
        $def = psl_get_default_option();
        foreach ($def as $key => $value) {
            if (isset($_POST[$key]) && psl_validate($_POST[$key], $value, array('name' => $key, 'character' => array('min' => 0,'max'=>40))))
                $def[$key] = sanitize_key($_POST[$key]);
        }
        update_option(psl_option_name, $def);
    }

    $option = psl_get_option();
    $indicator_mode = array('decimal', 'hexadecimal', 'twentysix', 'thirtysix', 'fiftytwo');
?>
    <div class="wrap">
        <h1>Posts short link</h1>
        <form action="" method="post">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="slug">slug</label></th>
                    <td><input name="slug" type="text" id="slug" value="<?php echo wp_kses($option['slug'], ''); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="indicator_mode">indicator</label></th>
                    <td>
                        <select name="indicator_mode" id="indicator_mode">
                            <?php
                            foreach ($indicator_mode as $key) {
                                $selected = $option['indicator_mode'] == $key ? 'selected="selected"' : '';
                                echo "<option value='" . esc_attr($key) . "' $selected>" . wp_kses($key, '') . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="character">Character</label></th>
                    <td><input name="character" type="number" id="character" min="0" max="40" value="<?php echo wp_kses($option['character'], ''); ?>" class="regular-number" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
