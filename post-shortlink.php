<?php

/**
 * Plugin Name: Post short link
 * Description: Post short link
 * Version: 1.0.0
 * Author: m-jalali
 * Author URI: http://www.m-jalali.ir
 */



// Adding a new rule
/**
 * Adds a new rewrite rule.
 *
 * @param array $rules Existing rewrite rules.
 * @return array (Maybe) modified list of rewrites.
 */
function wpdocs_insert_rewrite_rules($rules)
{
    $newrules = array();
    $newrules['q/([^/]+)/?$'] = 'index.php?shortlink=$matches[1]';
    return $newrules + $rules;
}
add_filter('rewrite_rules_array', 'wpdocs_insert_rewrite_rules');

// Adding the id var so that WP recognizes it
function wpdocs_insert_query_vars($vars)
{
    array_push($vars, 'shortlink');
    return $vars;
}
function wpdocs_re_query_vars($vars)
{
    if (key_exists('shortlink', $vars)) {
        $url = get_permalink($vars['shortlink']);
        if (wp_redirect($url)) {
            exit;
        }
    }
    return $vars;
}
add_filter('query_vars', 'wpdocs_insert_query_vars');
add_filter('request', 'wpdocs_re_query_vars');
