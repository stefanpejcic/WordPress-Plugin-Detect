<?php
/**
 * Plugin Name: WordPress Plugin Checker
 * Description: Allows you to use a shortcode to display a form where users can enter a WordPress website URL and check the active plugins on that website by searching the source code of the website.
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
        foreach ($plugins as $plugin) {
          $output .= '<li>' . $plugin . '</li>';
        }
        $output .= '</ul>';
      }
    }
  }

  return $output;
}
add_shortcode('plugin-checker', 'plugin_checker_shortcode');
