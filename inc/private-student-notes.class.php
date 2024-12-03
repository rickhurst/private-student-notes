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

    // Render the content of the block on the front-end
    public static function render_private_student_note_editor( ) {
        if ( ! is_user_logged_in() ) {
            return ''; // Return empty if the user is not logged in
        }

        ob_start();
        include plugin_dir_path( __FILE__ ) . '../templates/notes-editor.php';
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
    
        $note = get_user_meta($user_id, '_private_student_note', true);
        
        return rest_ensure_response([
            'note' => $note ? $note : '', // Return an empty string if no note exists
        ]);
    }

    // Save the note via REST API
    public function save_note( WP_REST_Request $request ) {

        $user_id = get_current_user_id();
        $note = $request->get_param( 'note' );

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
}
