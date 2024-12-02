<div id="private-student-notes-placeholder" class="">
    <form id="private-student-note-form" action="" method="post">
        <?php
        //$content = "placeholder"; // Initial content for wp_editor
        echo wp_nonce_field( 'private_student_note_nonce', 'private_student_note_save_nonce', true, false );
        wp_editor( "", 'private-student-notes' );
        ?>
        <br />
        <input name="save_student_note" class="" type="submit" value="<?php echo esc_attr__('Save Note', 'private-student-notes') ?>">
    </form>
</div>
