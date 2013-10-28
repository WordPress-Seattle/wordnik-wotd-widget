<?php
/*
 Plugin Name: Wordnik Word of the Day Widget
 Plugin URI: http://www.wordnik.com
 Description: Simple widget to retrieve the Wordnik Word of the Day. 
 Version: 0.2
 Author: Ben Lobaugh
 Author URI: http://ben.lobaugh.net
 */



/*
 * SETUP THE WIDGET
 */
add_action( 'widgets_init', create_function( '', 'register_widget( "Wordnik_WOTD_Widget" );' ) );

class Wordnik_WOTD_Widget extends WP_Widget{

    
    /**
     * Widget constructor that sets up the widget object with the correct
     * options 
     */
    public function __construct() {
            parent::__construct(
                    'wordnik_wotd', // Base ID
                    'Wordnik Word of the Day', // Name
                    array( 'description' => __( 'Display the Wordnik Word of the Day', 'text_domain' ), ) // Args
            );
    }
    
    
    /**
     * Builds the wp-admin widget form in Apperances -> Widgets
     * 
     * @param Array $instance - Data from the current widget
     */
    public function form( $instance ) {
        // Setup default values if none currently exist
        $defaults = array( 'wordnik_api_key' => '' );
        
        // Check current instance for values. If none exist apply defaults
        $instance = wp_parse_args( (array) $instance, $defaults );
        
        $s .= "Wordnik API key: <input class='widefat' name='" . $this->get_field_name( 'wordnik_api_key' ) . "' type='text' value='" . 
                esc_attr( $instance['wordnik_api_key'] ) . "'>";

        echo $s;
    }
    
    /**
     * Determines how to display to site visitors
     * 
     * @param Array $args - WordPress specific actions (before_widget, after_widget, etc)
     * @param Array $instance - Widget form elements
     */
    public function widget( $args, $instance ) {
        extract( $args ); // Just because this is normal
        
        $word = wordnik_wotd_get_word( $instance['wordnik_api_key']);
        // Widget display HTML goes here
        ?>

            <?php echo $before_widget; 

            echo $before_title . "Wordnik Word of the Day" . $after_title;

            echo "<strong>{$word['word']}</strong>";
            echo "<p>{$word['definition']}</p>";
            echo "<p>Example usage: {$word['example']}</p>";
            

             echo $after_widget; ?>

        <?php
        // End of widget display HTML
        
    }
    
}


/**
 * Retrieves the word of the day from the api
 * 
 * @param String $day - Must be in format of YYYY-MM-DD
 */
function wordnik_wotd_get_word( $api_key, $day = '') {
    if($day == '') {
        // use today's date
        $day = date('Y-m-d');
    }
    
    $url = "http://api.wordnik.com//v4/words.json/wordOfTheDay?date=$day&api_key=$api_key";
    
    $response = wp_remote_get( $url );
    
    $word = json_decode( $response['body'], true );
    
    $arr = array(
        'word' => ucfirst($word['word']),
        'example' => $word['examples']['0']['text'],
        'definition' => $word['definitions']['0']['text'],
        'partOfSpeech' => $word['definitions']['0']['partOfSpeech']
    );
    
    return $arr;
}
