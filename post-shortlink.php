<?php

/**
 * Plugin Name: Post short link
 * Description: Post short link
 * Version: 1.0.0
 * Author: m-jalali
 * Author URI: http://www.m-jalali.ir
 */

const psl_query_vars_name = 'shortlink';

function psl_get_slug()
{
    $slug = get_option('psl_slug', false);
    if ($slug !== false)
        return $slug;
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
        $url = get_permalink($vars[psl_query_vars_name]);
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
