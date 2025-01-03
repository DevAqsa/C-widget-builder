<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CWB_Custom_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'cwb_custom_widget', // Base ID
            __( 'Custom Widget', 'cwb' ), // Name
            array( 'description' => __( 'A Custom Widget', 'cwb' ) ) // Args
        );
    }

    // Frontend display.
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        echo '<p>' . esc_html( $instance['content'] ) . '</p>';
        echo $args['after_widget'];
    }

    // Backend form.
    public function form( $instance ) {
        $title   = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Default Title', 'cwb' );
        $content = ! empty( $instance['content'] ) ? $instance['content'] : __( 'Default Content', 'cwb' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'cwb' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>"><?php _e( 'Content:', 'cwb' ); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'content' ) ); ?>"><?php echo esc_textarea( $content ); ?></textarea>
        </p>
        <?php
    }

    // Save widget settings.
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title']   = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['content'] = ( ! empty( $new_instance['content'] ) ) ? sanitize_textarea_field( $new_instance['content'] ) : '';
        return $instance;
    }
}
