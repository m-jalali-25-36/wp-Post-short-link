<?php

/**
 * Plugin Name: Post short link
 * Description: Post short link
 * Version: 1.1.0
 * Author: m-jalali
 * Author URI: http://www.m-jalali.ir
 */

const psl_query_vars_name = 'shortlink';
const psl_option_name = 'psl_option';

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
function psl_convert_decimal_to($n, $arr)
{
    $res = '';
    $n = (int)$n;
    $size = count($arr);
    while ($n > 0) {
        $b = $n % $size;
        $res = $arr[$b] . $res;
        $n = ($n - $b) / $size;
    }
    return $res;
}
function psl_get_fiftytwo()
{
    return  array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
}
function psl_get_thirtysix()
{
    return array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
}
function psl_get_twentysix()
{
    return  array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
}
function psl_get_hexadecimal()
{
    return  array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
}

function psl_get_indicator($id)
{
    $option = get_option(psl_option_name, array());
    if (isset($option['indicator_mode'])) {
        switch ($option['indicator_mode']) {
            case 'hexadecimal':
                return psl_convert_decimal_to($id, psl_get_hexadecimal());

            case 'twentysix':
                return psl_convert_decimal_to($id, psl_get_twentysix());

            case 'thirtysix':
                return psl_convert_decimal_to($id, psl_get_thirtysix());

            case 'fiftytwo':
                return psl_convert_decimal_to($id, psl_get_fiftytwo());

            case 'decimal':
            default:
                return $id;
        }
    }
    return $id;
}

function get_shortlink($id)
{
    return get_home_url() . '/' . psl_get_slug()  . '/' . psl_get_indicator($id);
}

function psl_add_column_get_shortlink_title($columns)
{
    return array_merge($columns, array('shortlink' => __('Short Link')));
}
function psl_add_column_get_shortlink($column, $id)
{
    if ($column == 'shortlink') {
        $indicator = psl_get_indicator((int)$id);
        $slug = psl_get_slug();
        $url_shortlink = get_home_url() . '/' . $slug . '/' . $indicator;
        echo '<p onclick="navigator.clipboard.writeText(\'' . $url_shortlink . '\');alert(\'Copied the link\')">' . $slug . '/' . $indicator . '</p>';
    }
}
add_filter('manage_posts_columns', 'psl_add_column_get_shortlink_title', 1);
add_action('manage_posts_custom_column', 'psl_add_column_get_shortlink', 2, 2);
// manage_book_posts_columns manage_posts_custom_column
function psl_get_slug()
{
    $option = get_option(psl_option_name, array());
    if (isset($option['slug']))
        return $option['slug'];
    else
        return 'q';
}

/**
 * Flushes rewrites if our project rule isn't yet added.
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

// Adding a new rule
/**
 * Adds a new rewrite rule.
 *
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

// Adding the id var so that WP recognizes it
function psl_insert_query_vars($vars)
{
    array_push($vars, psl_query_vars_name);
    return $vars;
}
add_filter('query_vars', 'psl_insert_query_vars');


function psl_redirect($vars)
{
    if (key_exists(psl_query_vars_name, $vars)) {
        $id = -1;
        $option = get_option(psl_option_name, array());
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
 * 
 * Panel Admin
 * 
 */

function psl_add_menu()
{
    $tt_page = add_submenu_page("options-general.php", "Post short link", "Post short link", "manage_options", "psl-panel", "psl_admin_panel_display", null, 99);
}
add_action("admin_menu", "psl_add_menu");

function psl_admin_panel_display()
{
    if (isset($_POST['submit'])) {
        $def = array(
            'slug' => 'q',
            'indicator_mode' => 'decimal',
        );
        foreach ($def as $key => $value) {
            if (isset($_POST[$key]))
                $def[$key] = $_POST[$key];
        }
        update_option(psl_option_name, $def);
    }

    $option = get_option(psl_option_name, array(
        'slug' => 'q',
        'indicator_mode' => 'decimal',
    ));
    $indicator_mode = array('decimal', 'hexadecimal', 'twentysix', 'thirtysix', 'fiftytwo');
?>
    <div class="wrap">
        <h1>Post short link</h1>
        <form action="" method="post">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="slug">slug</label></th>
                    <td><input name="slug" type="text" id="slug" value="<?php echo $option['slug']; ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="indicator_mode">indicator</label></th>
                    <td>
                        <select name="indicator_mode" id="indicator_mode">
                            <?php
                            foreach ($indicator_mode as $key) {
                                $selected = $option['indicator_mode'] == $key ? 'selected="selected"' : '';
                                echo "<option value='" . esc_attr($key) . "' $selected>" . $key . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
