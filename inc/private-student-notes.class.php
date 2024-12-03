<?php

class Private_Student_Notes {
    
    public function __construct() {
        // Register the REST API route
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
    }

    private function user_can_edit_private_notes(){
        return true;
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            return in_array( $user->roles[0], [ 'administrator', 'editor', 'subscriber' ], true );
        } else {
            return new WP_Error( 'unauthorized', 'User not logged in', [ 'status' => 401 ] );
        }
    }

    public function localize_script(){
        wp_localize_script(
            'vip-learn-private-student-notes-view-script', // Handle of the script that needs the data
            'wpApiSettings', // The JavaScript object name
            array(
                'nonce' => wp_create_nonce( 'wp_rest' ), // WordPress REST API nonce for security
            )
        );
    }

    // Render the content of the block on the front-end
    public static function render_private_student_note_editor( ) {
        if ( ! is_user_logged_in() ) {
            return ''; // Return empty if the user is not logged in
        }
        ob_start();
        echo '<div id="private-student-note-editor" class=""></div>';
        return ob_get_clean();
    }

    // Register REST API routes for saving the note
    public function register_rest_routes() {
        register_rest_route( 'private-student-notes/v1', '/get-note', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_note' ],
            'permission_callback' => function() {
                return $this->user_can_edit_private_notes();
            },
        ] );
        register_rest_route( 'private-student-notes/v1', '/save-note', [
            'methods' => 'POST',
            'callback' => [ $this, 'save_note' ],
            'permission_callback' => function() {
                return $this->user_can_edit_private_notes();
            },
        ] );
    }

    // Get the note via REST API
    public function get_note() {
        $user_id = get_current_user_id(); // Get the current logged-in user ID
    
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User not logged in', ['status' => 401]);
        }
    
        $note = $this->escape_except_allowed_tags( get_user_meta($user_id, '_private_student_note', true) );
        
        return rest_ensure_response([
            'note' => $note ? $note : '', // Return an empty string if no note exists
        ]);
    }

    // Save the note via REST API
    public function save_note( WP_REST_Request $request ) {

        $user_id = get_current_user_id();

        if ( !$user_id ) {
            return new WP_Error('unauthorized', 'User not logged in', ['status' => 401]);
        }

        $max_length = 10000; // Set the max length

        $note = $this->sanitize_note_content( $request->get_param( 'note' ) );

        // Check the note length
        if ( strlen( $note ) > $max_length ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Note exceeds the maximum allowed length of ' . $max_length . ' characters.',
            ), 400 );
        }

        if ( empty( $note ) ) {
            return new WP_Error( 'invalid_data', 'Invalid note data', [ 'status' => 400 ] );
        }

        if( update_user_meta( $user_id, '_private_student_note', $note ) ) {
            return new WP_REST_Response( 
                [
                    'success' => true,
                    'message' => 'Note saved successfully'
                ], 
                200 
            );
        } else {
            return new WP_REST_Response( 
                [
                    'success' => false,
                    'message' => 'Failed to save the note.'
                ], 
                500 
            );
        }
    }

    /**
     * @desc sanitizes note content - HTML written into notes will be escaped 
     * as html entities by the Editor, which we want to keep. The only allowed 
     * HTML tags which the Editor uses to display the note are the ones in the array below.
     */
    private function sanitize_note_content( $content ) {
        $allowed_tags = array(
            'p'      => array(),
            'em'     => array(),
            'strong' => array(),
            'ul'     => array(),
            'li'     => array(),
        );
    
        $sanitized_content =  wp_kses( $content, $allowed_tags );

        // Strip all attributes from the allowed tags
        $sanitized_content = preg_replace( '/<(p|em|strong|ul|li)\b[^>]*>/', '<$1>', $sanitized_content );

        return $sanitized_content;

    }

    private function escape_except_allowed_tags( $content ) {
        // Define the allowed tags
        $allowed_tags = array(
            'p'      => array(),
            'em'     => array(),
            'strong' => array(),
            'ul'     => array(),
            'li'     => array(),
        );
    
        // Convert all remaining tags to HTML entities
        return preg_replace_callback(
            '/<(\/?)([^>]+)>/',
            function ( $matches ) use ( $allowed_tags ) {
                // If the tag is in the allowed list, return it as is
                $tag_name = strtolower( explode( ' ', $matches[2] )[0] );
                if ( array_key_exists( $tag_name, $allowed_tags ) ) {
                    return $matches[0];
                }
                // Otherwise, escape the tag
                return htmlspecialchars( $matches[0] );
            },
            $content
        );
    }
}
