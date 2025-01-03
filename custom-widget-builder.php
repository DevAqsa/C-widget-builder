<?php
/**
 * Plugin Name: Custom Widget Builder
 * Plugin URI: https://example.com/custom-widget-builder
 * Description: A plugin to build and manage custom widgets.
 * Version: 1.2.0
 * Author: Aqsa Mumtaz
 * Author URI: https://example.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'CWB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CWB_WIDGETS_DIR', CWB_PLUGIN_DIR . 'dynamic-widgets/' );

// Ensure the dynamic widgets directory exists
if ( ! file_exists( CWB_WIDGETS_DIR ) ) {
    mkdir( CWB_WIDGETS_DIR, 0755, true );
}

// Register widgets dynamically
function cwb_register_widgets() {
    foreach ( glob( CWB_WIDGETS_DIR . '*.php' ) as $widget_file ) {
        include_once $widget_file;
        $class_name = basename( $widget_file, '.php' );
        if ( class_exists( $class_name ) ) {
            register_widget( $class_name );
        }
    }
}
add_action( 'widgets_init', 'cwb_register_widgets' );

// Add the admin page under Appearance
function cwb_add_admin_page() {
    add_theme_page(
        __( 'Custom Widgets', 'cwb' ),
        __( 'Custom Widgets', 'cwb' ),
        'manage_options',
        'custom-widget-builder',
        'cwb_render_admin_page'
    );
}
add_action( 'admin_menu', 'cwb_add_admin_page' );

// Render the admin page
function cwb_render_admin_page() {
    if ( isset( $_POST['cwb_create_widget'] ) && check_admin_referer( 'cwb_create_widget_nonce' ) ) {
        $widget_name = sanitize_text_field( $_POST['widget_name'] );
        $widget_content = sanitize_textarea_field( $_POST['widget_content'] );
        $widget_type = sanitize_text_field( $_POST['widget_type'] );

        $class_name = 'CWB_Widget_' . preg_replace( '/[^A-Za-z0-9_]/', '', ucfirst( $widget_name ) );
        $widget_file = CWB_WIDGETS_DIR . $class_name . '.php';

        $widget_code = "<?php
class $class_name extends WP_Widget {
    public function __construct() {
        parent::__construct(
            '" . strtolower( $class_name ) . "',
            __( '$widget_name', 'cwb' ),
            array( 'description' => __( 'A custom $widget_type widget created dynamically', 'cwb' ) )
        );
    }

    public function widget( \$args, \$instance ) {
        echo \$args['before_widget'];
        
        // Display the widget title if set
        if ( ! empty( \$instance['title'] ) ) {
            echo \$args['before_title'] . apply_filters( 'widget_title', \$instance['title'] ) . \$args['after_title'];
        }
";

        if ( $widget_type === 'Text' ) {
            $widget_code .= "        echo wp_kses_post( \$instance['content'] ?? '$widget_content' );";
        } elseif ( $widget_type === 'HTML' ) {
            $widget_code .= "        echo do_shortcode( \$instance['content'] ?? '$widget_content' );";
        } elseif ( $widget_type === 'Image' ) {
            $widget_code .= "        \$image_url = \$instance['image_url'] ?? '$widget_content';
        echo '<img src=\"' . esc_url( \$image_url ) . '\" alt=\"' . esc_attr( \$instance['image_alt'] ?? '' ) . '\" />';";
        }

        $widget_code .= "
        echo \$args['after_widget'];
    }

    public function form( \$instance ) {
        \$title = ! empty( \$instance['title'] ) ? \$instance['title'] : '';
";

        if ( $widget_type === 'Text' || $widget_type === 'HTML' ) {
            $widget_code .= "        \$content = ! empty( \$instance['content'] ) ? \$instance['content'] : '$widget_content';
        ?>
        <p>
            <label for=\"<?php echo esc_attr( \$this->get_field_id( 'title' ) ); ?>\"><?php _e( 'Title:', 'cwb' ); ?></label>
            <input class=\"widefat\" id=\"<?php echo esc_attr( \$this->get_field_id( 'title' ) ); ?>\" 
                   name=\"<?php echo esc_attr( \$this->get_field_name( 'title' ) ); ?>\" type=\"text\" 
                   value=\"<?php echo esc_attr( \$title ); ?>\">
        </p>
        <p>
            <label for=\"<?php echo esc_attr( \$this->get_field_id( 'content' ) ); ?>\"><?php _e( 'Content:', 'cwb' ); ?></label>
            <textarea class=\"widefat\" id=\"<?php echo esc_attr( \$this->get_field_id( 'content' ) ); ?>\" 
                      name=\"<?php echo esc_attr( \$this->get_field_name( 'content' ) ); ?>\" rows=\"5\"><?php echo esc_textarea( \$content ); ?></textarea>
        </p>
        <?php";
        } elseif ( $widget_type === 'Image' ) {
            $widget_code .= "        \$image_url = ! empty( \$instance['image_url'] ) ? \$instance['image_url'] : '$widget_content';
        \$image_alt = ! empty( \$instance['image_alt'] ) ? \$instance['image_alt'] : '';
        ?>
        <p>
            <label for=\"<?php echo esc_attr( \$this->get_field_id( 'title' ) ); ?>\"><?php _e( 'Title:', 'cwb' ); ?></label>
            <input class=\"widefat\" id=\"<?php echo esc_attr( \$this->get_field_id( 'title' ) ); ?>\" 
                   name=\"<?php echo esc_attr( \$this->get_field_name( 'title' ) ); ?>\" type=\"text\" 
                   value=\"<?php echo esc_attr( \$title ); ?>\">
        </p>
        <p>
            <label for=\"<?php echo esc_attr( \$this->get_field_id( 'image_url' ) ); ?>\"><?php _e( 'Image URL:', 'cwb' ); ?></label>
            <input class=\"widefat\" id=\"<?php echo esc_attr( \$this->get_field_id( 'image_url' ) ); ?>\" 
                   name=\"<?php echo esc_attr( \$this->get_field_name( 'image_url' ) ); ?>\" type=\"text\" 
                   value=\"<?php echo esc_url( \$image_url ); ?>\">
        </p>
        <p>
            <label for=\"<?php echo esc_attr( \$this->get_field_id( 'image_alt' ) ); ?>\"><?php _e( 'Image Alt Text:', 'cwb' ); ?></label>
            <input class=\"widefat\" id=\"<?php echo esc_attr( \$this->get_field_id( 'image_alt' ) ); ?>\" 
                   name=\"<?php echo esc_attr( \$this->get_field_name( 'image_alt' ) ); ?>\" type=\"text\" 
                   value=\"<?php echo esc_attr( \$image_alt ); ?>\">
        </p>
        <?php";
        }

        $widget_code .= "
    }

    public function update( \$new_instance, \$old_instance ) {
        \$instance = array();
        \$instance['title'] = ( ! empty( \$new_instance['title'] ) ) ? sanitize_text_field( \$new_instance['title'] ) : '';
";

        if ( $widget_type === 'Text' ) {
            $widget_code .= "        \$instance['content'] = ( ! empty( \$new_instance['content'] ) ) ? sanitize_textarea_field( \$new_instance['content'] ) : '';";
        } elseif ( $widget_type === 'HTML' ) {
            $widget_code .= "        \$instance['content'] = ( ! empty( \$new_instance['content'] ) ) ? wp_kses_post( \$new_instance['content'] ) : '';";
        } elseif ( $widget_type === 'Image' ) {
            $widget_code .= "        \$instance['image_url'] = ( ! empty( \$new_instance['image_url'] ) ) ? esc_url_raw( \$new_instance['image_url'] ) : '';
        \$instance['image_alt'] = ( ! empty( \$new_instance['image_alt'] ) ) ? sanitize_text_field( \$new_instance['image_alt'] ) : '';";
        }

        $widget_code .= "
        return \$instance;
    }
}
";
        file_put_contents( $widget_file, $widget_code );
        echo '<div class="updated"><p>' . __( 'Widget created successfully!', 'cwb' ) . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1><?php _e( 'Custom Widget Builder', 'cwb' ); ?></h1>
        <form method="POST">
            <?php wp_nonce_field( 'cwb_create_widget_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="widget_name"><?php _e( 'Widget Name', 'cwb' ); ?></label></th>
                    <td>
                        <input type="text" id="widget_name" name="widget_name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="widget_type"><?php _e( 'Widget Type', 'cwb' ); ?></label></th>
                    <td>
                        <select id="widget_type" name="widget_type" required>
                            <option value="Text"><?php _e( 'Text', 'cwb' ); ?></option>
                            <option value="HTML"><?php _e( 'HTML', 'cwb' ); ?></option>
                            <option value="Image"><?php _e( 'Image', 'cwb' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="widget_content"><?php _e( 'Widget Content', 'cwb' ); ?></label></th>
                    <td>
                        <textarea id="widget_content" name="widget_content" class="large-text" rows="5" required></textarea>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="cwb_create_widget" class="button-primary"><?php _e( 'Create Widget', 'cwb' ); ?></button>
            </p>
        </form>
    </div>
    <?php
}

// Display all widgets at the bottom of the page
function cwb_display_widgets() {
    if ( ! is_admin() ) {
        echo '<div class="cwb-bottom-widgets">';
        dynamic_sidebar( 'cwb_custom_widgets' );
        echo '</div>';
    }
}
add_action( 'wp_footer', 'cwb_display_widgets' );

// Register a custom sidebar for displaying widgets
function cwb_register_sidebar() {
    register_sidebar( array(
        'name'          => __( 'Custom Widgets', 'cwb' ),
        'id'            => 'cwb_custom_widgets',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'cwb_register_sidebar' );