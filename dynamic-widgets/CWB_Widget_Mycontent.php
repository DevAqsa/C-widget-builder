<?php
class CWB_Widget_Mycontent extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'cwb_widget_mycontent',
            __( 'my content', 'cwb' ),
            array( 'description' => __( 'A custom HTML widget created dynamically', 'cwb' ) )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        
        // Display the widget title if set
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        echo do_shortcode( $instance['content'] ?? '&lt;?php
/**
 * Plugin Name: Custom Widget Builder
 * Plugin URI: https://example.com/custom-widget-builder
 * Description: A plugin to build and manage custom widgets.
 * Version: 1.2.0
 * Author: Aqsa Mumtaz
 * Author URI: https://example.com
 * License: GPL2
 */

if ( ! defined( \&#039;ABSPATH\&#039; ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( \&#039;CWB_PLUGIN_DIR\&#039;, plugin_dir_path( __FILE__ ) );
define( \&#039;CWB_WIDGETS_DIR\&#039;, CWB_PLUGIN_DIR . \&#039;dynamic-widgets/\&#039; );

// Ensure the dynamic widgets directory exists
if ( ! file_exists( CWB_WIDGETS_DIR ) ) {
    mkdir( CWB_WIDGETS_DIR, 0755, true );
}

// Register widgets dynamically
function cwb_register_widgets() {
    foreach ( glob( CWB_WIDGETS_DIR . \&#039;*.php\&#039; ) as $widget_file ) {
        include_once $widget_file;
        $class_name = basename( $widget_file, \&#039;.php\&#039; );
        if ( class_exists( $class_name ) ) {
            register_widget( $class_name );
        }
    }
}
add_action( \&#039;widgets_init\&#039;, \&#039;cwb_register_widgets\&#039; );

// Add the admin page under Appearance
function cwb_add_admin_page() {
    add_theme_page(
        __( \&#039;Custom Widgets\&#039;, \&#039;cwb\&#039; ),
        __( \&#039;Custom Widgets\&#039;, \&#039;cwb\&#039; ),
        \&#039;manage_options\&#039;,
        \&#039;custom-widget-builder\&#039;,
        \&#039;cwb_render_admin_page\&#039;
    );
}
add_action( \&#039;admin_menu\&#039;, \&#039;cwb_add_admin_page\&#039; );

// Render the admin page
function cwb_render_admin_page() {
    if ( isset( $_POST[\&#039;cwb_create_widget\&#039;] ) &amp;&amp; check_admin_referer( \&#039;cwb_create_widget_nonce\&#039; ) ) {
        $widget_name = sanitize_text_field( $_POST[\&#039;widget_name\&#039;] );
        $widget_content = sanitize_textarea_field( $_POST[\&#039;widget_content\&#039;] );
        $widget_type = sanitize_text_field( $_POST[\&#039;widget_type\&#039;] );

        $class_name = \&#039;CWB_Widget_\&#039; . preg_replace( \&#039;/[^A-Za-z0-9_]/\&#039;, \&#039;\&#039;, ucfirst( $widget_name ) );
        $widget_file = CWB_WIDGETS_DIR . $class_name . \&#039;.php\&#039;;

        $widget_code = \&quot;
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;textarea class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                      name=\\\"\\\" rows=\\\"5\\\">
        
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        &lt;?php\&quot;;
        }

        $widget_code .= \&quot;
    }

    public function update( \\$new_instance, \\$old_instance ) {
        \\$instance = array();
        \\$instance[\&#039;title\&#039;] = ( ! empty( \\$new_instance[\&#039;title\&#039;] ) ) ? sanitize_text_field( \\$new_instance[\&#039;title\&#039;] ) : \&#039;\&#039;;
\&quot;;

        if ( $widget_type === \&#039;Text\&#039; ) {
            $widget_code .= \&quot;        \\$instance[\&#039;content\&#039;] = ( ! empty( \\$new_instance[\&#039;content\&#039;] ) ) ? sanitize_textarea_field( \\$new_instance[\&#039;content\&#039;] ) : \&#039;\&#039;;\&quot;;
        } elseif ( $widget_type === \&#039;HTML\&#039; ) {
            $widget_code .= \&quot;        \\$instance[\&#039;content\&#039;] = ( ! empty( \\$new_instance[\&#039;content\&#039;] ) ) ? wp_kses_post( \\$new_instance[\&#039;content\&#039;] ) : \&#039;\&#039;;\&quot;;
        } elseif ( $widget_type === \&#039;Image\&#039; ) {
            $widget_code .= \&quot;        \\$instance[\&#039;image_url\&#039;] = ( ! empty( \\$new_instance[\&#039;image_url\&#039;] ) ) ? esc_url_raw( \\$new_instance[\&#039;image_url\&#039;] ) : \&#039;\&#039;;
        \\$instance[\&#039;image_alt\&#039;] = ( ! empty( \\$new_instance[\&#039;image_alt\&#039;] ) ) ? sanitize_text_field( \\$new_instance[\&#039;image_alt\&#039;] ) : \&#039;\&#039;;\&quot;;
        }

        $widget_code .= \&quot;
        return \\$instance;
    }
}
\&quot;;
        file_put_contents( $widget_file, $widget_code );
        echo \&#039;\' . __( \'Widget created successfully!\', \'cwb\' ) . \'\';
    }

    ?>
    
        
        
            
            
                
                    
                    
                        
                    
                
                
                    
                    
                        
                            
                            
                            
                        
                    
                
                
                    
                    
                        
                    
                
            
            
                
            
        
    
    &lt;?php
}

// Display all widgets at the bottom of the page
function cwb_display_widgets() {
    if ( ! is_admin() ) {
        echo \&#039;\';
        dynamic_sidebar( \'cwb_custom_widgets\' );
        echo \'\';
    }
}
add_action( \'wp_footer\', \'cwb_display_widgets\' );

// Register a custom sidebar for displaying widgets
function cwb_register_sidebar() {
    register_sidebar( array(
        \'name\'          => __( \'Custom Widgets\', \'cwb\' ),
        \'id\'            => \'cwb_custom_widgets\',
        \'before_widget\' => \'\',
        \'after_widget\'  => \'\',
        \'before_title\'  => \'\',
        \'after_title\'   => \'\',
    ) );
}
add_action( \'widgets_init\', \'cwb_register_sidebar\' );' );
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $content = ! empty( $instance['content'] ) ? $instance['content'] : '&lt;?php
/**
 * Plugin Name: Custom Widget Builder
 * Plugin URI: https://example.com/custom-widget-builder
 * Description: A plugin to build and manage custom widgets.
 * Version: 1.2.0
 * Author: Aqsa Mumtaz
 * Author URI: https://example.com
 * License: GPL2
 */

if ( ! defined( \&#039;ABSPATH\&#039; ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( \&#039;CWB_PLUGIN_DIR\&#039;, plugin_dir_path( __FILE__ ) );
define( \&#039;CWB_WIDGETS_DIR\&#039;, CWB_PLUGIN_DIR . \&#039;dynamic-widgets/\&#039; );

// Ensure the dynamic widgets directory exists
if ( ! file_exists( CWB_WIDGETS_DIR ) ) {
    mkdir( CWB_WIDGETS_DIR, 0755, true );
}

// Register widgets dynamically
function cwb_register_widgets() {
    foreach ( glob( CWB_WIDGETS_DIR . \&#039;*.php\&#039; ) as $widget_file ) {
        include_once $widget_file;
        $class_name = basename( $widget_file, \&#039;.php\&#039; );
        if ( class_exists( $class_name ) ) {
            register_widget( $class_name );
        }
    }
}
add_action( \&#039;widgets_init\&#039;, \&#039;cwb_register_widgets\&#039; );

// Add the admin page under Appearance
function cwb_add_admin_page() {
    add_theme_page(
        __( \&#039;Custom Widgets\&#039;, \&#039;cwb\&#039; ),
        __( \&#039;Custom Widgets\&#039;, \&#039;cwb\&#039; ),
        \&#039;manage_options\&#039;,
        \&#039;custom-widget-builder\&#039;,
        \&#039;cwb_render_admin_page\&#039;
    );
}
add_action( \&#039;admin_menu\&#039;, \&#039;cwb_add_admin_page\&#039; );

// Render the admin page
function cwb_render_admin_page() {
    if ( isset( $_POST[\&#039;cwb_create_widget\&#039;] ) &amp;&amp; check_admin_referer( \&#039;cwb_create_widget_nonce\&#039; ) ) {
        $widget_name = sanitize_text_field( $_POST[\&#039;widget_name\&#039;] );
        $widget_content = sanitize_textarea_field( $_POST[\&#039;widget_content\&#039;] );
        $widget_type = sanitize_text_field( $_POST[\&#039;widget_type\&#039;] );

        $class_name = \&#039;CWB_Widget_\&#039; . preg_replace( \&#039;/[^A-Za-z0-9_]/\&#039;, \&#039;\&#039;, ucfirst( $widget_name ) );
        $widget_file = CWB_WIDGETS_DIR . $class_name . \&#039;.php\&#039;;

        $widget_code = \&quot;
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;textarea class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                      name=\\\"\\\" rows=\\\"5\\\">
        
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        
            &lt;label for=\\\&quot;\\\">
            &lt;input class=\\\&quot;widefat\\\&quot; id=\\\&quot;\\\" 
                   name=\\\"\\\" type=\\\"text\\\" 
                   value=\\\"\\\">
        
        &lt;?php\&quot;;
        }

        $widget_code .= \&quot;
    }

    public function update( \\$new_instance, \\$old_instance ) {
        \\$instance = array();
        \\$instance[\&#039;title\&#039;] = ( ! empty( \\$new_instance[\&#039;title\&#039;] ) ) ? sanitize_text_field( \\$new_instance[\&#039;title\&#039;] ) : \&#039;\&#039;;
\&quot;;

        if ( $widget_type === \&#039;Text\&#039; ) {
            $widget_code .= \&quot;        \\$instance[\&#039;content\&#039;] = ( ! empty( \\$new_instance[\&#039;content\&#039;] ) ) ? sanitize_textarea_field( \\$new_instance[\&#039;content\&#039;] ) : \&#039;\&#039;;\&quot;;
        } elseif ( $widget_type === \&#039;HTML\&#039; ) {
            $widget_code .= \&quot;        \\$instance[\&#039;content\&#039;] = ( ! empty( \\$new_instance[\&#039;content\&#039;] ) ) ? wp_kses_post( \\$new_instance[\&#039;content\&#039;] ) : \&#039;\&#039;;\&quot;;
        } elseif ( $widget_type === \&#039;Image\&#039; ) {
            $widget_code .= \&quot;        \\$instance[\&#039;image_url\&#039;] = ( ! empty( \\$new_instance[\&#039;image_url\&#039;] ) ) ? esc_url_raw( \\$new_instance[\&#039;image_url\&#039;] ) : \&#039;\&#039;;
        \\$instance[\&#039;image_alt\&#039;] = ( ! empty( \\$new_instance[\&#039;image_alt\&#039;] ) ) ? sanitize_text_field( \\$new_instance[\&#039;image_alt\&#039;] ) : \&#039;\&#039;;\&quot;;
        }

        $widget_code .= \&quot;
        return \\$instance;
    }
}
\&quot;;
        file_put_contents( $widget_file, $widget_code );
        echo \&#039;\' . __( \'Widget created successfully!\', \'cwb\' ) . \'\';
    }

    ?>
    
        
        
            
            
                
                    
                    
                        
                    
                
                
                    
                    
                        
                            
                            
                            
                        
                    
                
                
                    
                    
                        
                    
                
            
            
                
            
        
    
    &lt;?php
}

// Display all widgets at the bottom of the page
function cwb_display_widgets() {
    if ( ! is_admin() ) {
        echo \&#039;\';
        dynamic_sidebar( \'cwb_custom_widgets\' );
        echo \'\';
    }
}
add_action( \'wp_footer\', \'cwb_display_widgets\' );

// Register a custom sidebar for displaying widgets
function cwb_register_sidebar() {
    register_sidebar( array(
        \'name\'          => __( \'Custom Widgets\', \'cwb\' ),
        \'id\'            => \'cwb_custom_widgets\',
        \'before_widget\' => \'\',
        \'after_widget\'  => \'\',
        \'before_title\'  => \'\',
        \'after_title\'   => \'\',
    ) );
}
add_action( \'widgets_init\', \'cwb_register_sidebar\' );';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'cwb' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" 
                   value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>"><?php _e( 'Content:', 'cwb' ); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>" 
                      name="<?php echo esc_attr( $this->get_field_name( 'content' ) ); ?>" rows="5"><?php echo esc_textarea( $content ); ?></textarea>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['content'] = ( ! empty( $new_instance['content'] ) ) ? wp_kses_post( $new_instance['content'] ) : '';
        return $instance;
    }
}
