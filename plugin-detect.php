<?php
/**
 * Plugin Name: WordPress Plugin Checker
 * Description: Allows you to use a shortcode to display a form where users can enter a WordPress website URL and check the active plugins on that website, including information and a screenshot from the WordPress.org repository.
 * Version: 1.0
 * Author: Your Name
 */

function plugin_checker_shortcode($atts) {
  $output = '<form method="post" action="">';
  $output .= '<label for="url">Enter WordPress website URL:</label>';
  $output .= '<input type="text" name="url" id="url" value="" required>';
  $output .= '<input type="submit" value="Check Plugins">';
  $output .= '</form>';

  if (isset($_POST['url'])) {
    $url = esc_url($_POST['url']);

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
      $output .= '<p>Unable to retrieve plugin information. Please check the URL and try again.</p>';
    } else {
      $html = wp_remote_retrieve_body($response);
      if (stripos($html, 'wp-content/plugins') === false) {
        $output .= '<p>No plugins found on this website.</p>';
      } else {
        $output .= '<ul>';
        preg_match_all('#/wp-content/plugins/(.+?)/#', $html, $matches);
        $plugins = array_unique($matches[1]);
        foreach ($plugins as $plugin_slug) {
          $plugin_info = wp_remote_get('https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json');
          if (!is_wp_error($plugin_info)) {
            $plugin_info = json_decode(wp_remote_retrieve_body($plugin_info));
            if (isset($plugin_info->name)) {
              $output .= '<li>';
              $output .= ' <a href="https://wordpress.org/plugin/' . $plugin_slug . '" target="_blank">' . $plugin_info->name . '</a>';              $output .= '</li>';
            }
          }
        }
        $output .= '</ul>';
      }
    }
  }

  return $output;
}
add_shortcode('plugin-checker', 'plugin_checker_shortcode');

