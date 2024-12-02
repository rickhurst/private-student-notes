/******/ (() => { // webpackBootstrap
/*!*********************!*\
  !*** ./src/view.js ***!
  \*********************/
document.addEventListener('DOMContentLoaded', function () {
  // Get the form element and the editor content field
  const form = document.getElementById('private-student-note-form');

  // Fetch the existing note content when the page loads
  fetch('/wp-json/private-student-notes/v1/get-note', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': wpApiSettings.nonce // Include nonce for authentication
    }
  }).then(response => response.json()).then(data => {
    if (data && data.note) {
      // Wait for TinyMCE to initialize
      if (typeof tinymce !== 'undefined') {
        // Ensure TinyMCE is fully initialized before setting content
        const editor = tinymce.activeEditor;
        if (editor) {
          console.log('got editor');
          console.log(data.note);
          //editor.on('init', function () {
          console.log('init issit');
          editor.setContent(data.note); // Set the fetched content into the editor
          //});
        } else {
          console.error('TinyMCE active editor is not initialized.');
        }
      } else {
        console.error('TinyMCE is not initialized.');
      }
    }
  }).catch(error => {
    console.error('Error fetching note:', error);
  });

  // Handle form submission
  form.addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission behavior

    // Get the content from the active wp_editor (TinyMCE) field
    const editor = tinymce.activeEditor;
    if (editor) {
      const noteContent = editor.getContent(); // Retrieve the editor content

      // Get the nonce from the wpApiSettings object
      const nonce = wpApiSettings.nonce;

      // Prepare the data to send via AJAX
      const data = {
        note: noteContent
      };

      // Send the data to the REST API endpoint via a POST request
      fetch('/wp-json/private-student-notes/v1/save-note', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce // Add the nonce to the request headers
        },
        body: JSON.stringify(data)
      }).then(response => response.json()).then(data => {
        if (data.success) {
          console.log('Note saved successfully!');
        } else {
          console.log('There was an error saving the note.');
        }
      }).catch(error => {
        console.error('Error:', error);
        alert('There was an error saving the note.');
      });
    }
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map