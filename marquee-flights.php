<?php
/*
Plugin Name: Dynamic Marquee Flights
Plugin URI: https://yourwebsite.com/dynamic-marquee-flights
Description: A plugin to dynamically display a marquee with flight links. Manage links via the admin panel and display them using a shortcode.
Version: 1.1
Author: Your Name
Author URI: https://yourwebsite.com
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
function dm_marquee_admin_menu() {
    add_menu_page(
        'Marquee Flights Settings', // Page title
        'Marquee Flights',          // Menu title
        'manage_options',           // Capability
        'marquee-flights',          // Menu slug
        'dm_marquee_settings_page', // Callback function
        'dashicons-admin-links',    // Icon
        90                          // Position
    );
}
add_action('admin_menu', 'dm_marquee_admin_menu');

// Admin settings page
function dm_marquee_settings_page() {
    // Save the data
    if (isset($_POST['dm_save_marquee_links'])) {
        $links = [];
        if (!empty($_POST['dm_marquee_links']['text']) && !empty($_POST['dm_marquee_links']['url'])) {
            foreach ($_POST['dm_marquee_links']['text'] as $index => $text) {
                $url = $_POST['dm_marquee_links']['url'][$index];
                if (!empty($text) && !empty($url)) {
                    $links[] = [
                        'text' => sanitize_text_field($text),
                        'url' => esc_url_raw($url),
                    ];
                }
            }
        }
        update_option('dm_marquee_links', $links);
        echo '<div class="updated"><p>Links saved successfully!</p></div>';
    }

    // Retrieve saved links
    $links = get_option('dm_marquee_links', []);

    ?>
    <div class="wrap">
        <h1>Marquee Flights Settings</h1>
        <form method="post">
            <table class="form-table">
                <thead>
                    <tr>
                        <th>Flight Text</th>
                        <th>URL</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="dm_marquee_links_table">
                    <?php if (!empty($links)) : ?>
                        <?php foreach ($links as $link) : ?>
                            <tr>
                                <td><input type="text" name="dm_marquee_links[text][]" value="<?php echo esc_attr($link['text']); ?>" class="regular-text" /></td>
                                <td><input type="url" name="dm_marquee_links[url][]" value="<?php echo esc_url($link['url']); ?>" class="regular-text" /></td>
                                <td><button type="button" class="button dm-remove-link">Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p><button type="button" id="dm_add_link" class="button">Add Link</button></p>
            <p><input type="submit" name="dm_save_marquee_links" class="button button-primary" value="Save Links"></p>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addLinkButton = document.getElementById('dm_add_link');
            const linksTable = document.getElementById('dm_marquee_links_table');

            addLinkButton.addEventListener('click', function () {
                const row = `
                    <tr>
                        <td><input type="text" name="dm_marquee_links[text][]" class="regular-text" /></td>
                        <td><input type="url" name="dm_marquee_links[url][]" class="regular-text" /></td>
                        <td><button type="button" class="button dm-remove-link">Remove</button></td>
                    </tr>`;
                linksTable.insertAdjacentHTML('beforeend', row);
            });

            linksTable.addEventListener('click', function (e) {
                if (e.target.classList.contains('dm-remove-link')) {
                    e.target.closest('tr').remove();
                }
            });
        });
    </script>
    <?php
}

// Enqueue Font Awesome for the marquee icons
function dm_enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'dm_enqueue_font_awesome');

// Shortcode to display the marquee
function dm_marquee_shortcode() {
    $links = get_option('dm_marquee_links', []);
    if (empty($links)) {
        return '<p>No links available. Add some links in the admin panel.</p>';
    }

    $content = '
    <style>
        marquee:hover {
            animation-play-state: paused;
            -webkit-animation-play-state: paused;
        }
		.elementor-shortcode i.fa.fa-circle {
			color: #fff;
			font-size: 10px;
			padding: 0px 12px
		}
		.flight-color-link{
			font-family: "Plus Jakarta Sans", Sans-serif;
			font-size: 16px;
			font-weight: 400;
			color:#fff;
		}
		.flight-icon-color{
			rotate: -50deg;
			color: #FFD600;
		}
		.marquee {
		  position: relative;
		  width: 100vw;
		  max-width: 100%;
		  height: 200px;
		  overflow-x: hidden;
		}
		@keyframes marquee {
		  from { transform: translateX(0); }
		  to { transform: translateX(-50%); }
		}

    </style>
    <marquee onmouseover="this.stop();" onmouseout="this.start();" direction="left" loop="0">';
    foreach ($links as $link) {
        $text = esc_html($link['text']);
        $url = esc_url($link['url']);
        $content .= "
        <a href=\"{$url}\">
            <i class=\"fa fa-fighter-jet flight-icon-color\" aria-hidden=\"true\"></i>
            <span class=\"flight-color-link\">{$text}</span>
			<i class=\"fa fa-circle\" aria-hidden=\"true\"></i>
        </a>";
    }
    $content .= '</marquee>';

    return $content;
}
add_shortcode('dynamic_marquee', 'dm_marquee_shortcode');
