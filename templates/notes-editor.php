<div id="private-student-notes-placeholder" class="">
    <form id="private-student-note-form" action="" method="post">
        <?php
        $editor_args = [
            'textarea_name' => 'private-student-notes', // Name attribute for the textarea
            'teeny' => true,                            // Use a minimal version of the editor
            'media_buttons' => false,                   // Hide the "Add Media" button
            'quicktags' => false,                       // Disable the Quicktags toolbar (Text mode buttons)
        ];
        echo wp_nonce_field( 'private_student_note_nonce', 'private_student_note_save_nonce', true, false );
        wp_editor( "", 'private-student-notes', $editor_args );
        ?>
        <br />
        <input name="save_student_note" class="" type="submit" value="<?php echo esc_attr__('Save Note', 'private-student-notes') ?>">
    </form>
</div>
