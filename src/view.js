import React, { useEffect } from 'react';
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import BulletList from '@tiptap/extension-bullet-list';
import ReactDOM from 'react-dom';

const PrivateStudentNotesEditor = () => {
  // Initialize the TipTap editor with desired extensions
  const editor = useEditor({
    extensions: [StarterKit, Bold, Italic, BulletList],
    content: '',
  });

  // Fetch the saved note when the component loads
  useEffect(() => {
    fetch('/wp-json/private-student-notes/v1/get-note', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce, // Include nonce for authentication
      },
    })
      .then(response => response.json())
      .then(data => {
        if (data && data.note) {
          editor?.commands.setContent(data.note); // Load note into the editor
        }
      })
      .catch(error => console.error('Error fetching note:', error));
  }, [editor]);

  // Handle form submission via REST API
  const saveNoteToServer = () => {
    const noteContent = editor?.getHTML() || ''; // Get current editor content as HTML

    fetch('/wp-json/private-student-notes/v1/save-note', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce,
      },
      body: JSON.stringify({ note: noteContent }),
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Note saved successfully!');
        } else {
          console.error('Error saving note:', data);
        }
      })
      .catch(error => console.error('Error saving note:', error));
  };

  return (
    <div>
      {/* Toolbar */}
      <div style={{ marginBottom: '10px' }}>
        <button onClick={() => editor?.chain().focus().toggleBold().run()}><strong>B</strong></button>
        <button onClick={() => editor?.chain().focus().toggleItalic().run()}><em>I</em></button>
        <button onClick={() => editor?.chain().focus().toggleBulletList().run()}>• List</button>
      </div>

      {/* Editor */}
      <EditorContent
        editor={editor}
        style={{
          border: '1px solid #ccc',
          minHeight: '300px',
          padding: '10px',
        }}
      />

      {/* Save Button */}
      <button onClick={saveNoteToServer} style={{ marginTop: '10px' }}>
        Save Note
      </button>
    </div>
  );
};

// Render the React component in the placeholder
const renderEditor = () => {
  const editorElement = document.getElementById('private-student-note-editor');
  if (editorElement) {
    ReactDOM.render(<PrivateStudentNotesEditor />, editorElement);
  }
};

window.addEventListener('load', renderEditor);
