<?php
/**
 * @Package Wordpress
 * @SubPackage Widgets
 *
 * Plugin Name: TinyLetter Widget
 * Description: Displays a TinyLetter subscription form
 * Version: 1.0.0
 * Author: SYN-ACK
 * Author URI: http://syn-ack.se
 *
 */

defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * Initializing
 *
 * The directory separator is different between linux and microsoft servers.
 * Thankfully php sets the DIRECTORY_SEPARATOR constant so that we know what
 * to use.
 */
defined("DS") or define("DS", DIRECTORY_SEPARATOR);

/**
 * Actions and Filters
 *
 * Register any and all actions here. Nothing should actually be called
 * directly, the entire system will be based on these actions and hooks.
 */
add_action( 'widgets_init', create_function( '', 'register_widget("TinyLetter_Widget");' ) );


/**
 * TinyLetter widget class
 */
class TinyLetter_Widget extends WP_Widget {

  function __construct() {

    $locale = get_locale();
    if( !empty( $locale ) ) {
      $mofile = dirname(__FILE__) . "/lang/" .  $locale . ".mo";
      if(@file_exists($mofile) && is_readable($mofile))
        load_textdomain('synack', $mofile);
    }

    $widget_ops = array('classname' => 'widget_tinyletter', 'description' => __('Displays a TinyLetter subscription form', 'synack'));

    $control_ops = array('width' => 250, 'height' => 150);

    parent::__construct('tinyletter', __('TinyLetter'), $widget_ops, $control_ops);
  }

  function widget( $args, $instance ) {
    extract($args);
    $title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
    $url = apply_filters( 'widget_url', empty($instance['url']) ? '' : $instance['url'], $instance, $this->id_base);
    $text = apply_filters( 'widget_text', $instance['text'], $instance );

    if ( !empty( $url ) ) {

      echo $before_widget;

      if ( !empty( $title ) ) {
        echo $before_title . $title . $after_title;
      }
    ?>

    <div class="widget-content">

    <?php
      if ( !empty( $text ) ) {
        echo '<p>'.$text.'</p>';
      }
    ?>

        <form action="<?php echo $url; ?>" method="post" target="popupwindow" onsubmit="window.open('<?php echo $url; ?>', popupwindow, scrollbars=yes,width=800,height=600);return true">
          <input type="hidden" value="1" name="embed">
          <p>
            <label for="emailaddress"><?php _e('Your email address', 'synack'); ?></label>
            <input type="email" name="emailaddress" placeholder="<?php _e('foo@bar.com', 'synack'); ?>">
          </p>
          <p>
            <input type="submit" value="<?php _e('Subscribe', 'synack'); ?>">
          </p>
          <?php
            if ( $instance['attribution'] ) {
              echo '<small><a href="http://tinyletter.com" title="'.__('A TinyLetter email newsletter', 'synack').'">'.__('A TinyLetter email newsletter', 'synack').'</a></small>';
            }
          ?>
      </form>
    </div>

    <?php
      echo $after_widget;
    }
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['url'] = strip_tags($new_instance['url']);
    $instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
    $instance['attribution'] = isset($new_instance['attribution']);
    return $instance;
  }

  function form( $instance ) {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'url' => '', 'text' => '' ) );
    $title = strip_tags($instance['title']);
    $url = strip_tags($instance['url']);
    $text = esc_textarea($instance['text']);
?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" placeholder="<?php _e('Newsletter', 'synack'); ?>">
    </p>

    <p>
      <textarea class="widefat" rows="4" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" placeholder="<?php _e('Optional text before the form', 'synack'); ?>"><?php echo $text; ?></textarea>
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('URL'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo esc_attr($url); ?>" placeholder="http://tinyletter.com/&hellip;">
      <small><?php _e('You will find the URL to your newsletter <a href="https://tinyletter.com/publicize/" title="Visit this link to find your newsletter URL">here</a> after you have registered and logged in to TinyLetter.', 'synack'); ?></small>
    </p>

    <p>
      <input id="<?php echo $this->get_field_id('attribution'); ?>" name="<?php echo $this->get_field_name('attribution'); ?>" type="checkbox" <?php checked(isset($instance['attribution']) ? $instance['attribution'] : 1); ?>>&nbsp;<label for="<?php echo $this->get_field_id('attribution'); ?>"><?php _e('Show TinyLetter attribution'); ?></label>
    </p>
<?php
  }
}
