import { useState, useEffect } from 'react';
import { RichText } from '@wordpress/block-editor';
import ReactDOM from 'react-dom';

// Define the component
const PrivateStudentNotesEditor = () => {
  const [noteContent, setNoteContent] = useState('');
  const [isLoading, setIsLoading] = useState(true); // Track loading state

  // Fetch the saved note content when the component mounts
  useEffect(() => {
    // Fetch the saved note content from the REST API
    fetch('/wp-json/private-student-notes/v1/get-note', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce, // Ensure the nonce is sent for security
      },
    })
      .then(response => response.json())
      .then(data => {
        if (data && data.note) {
          setNoteContent(data.note); // Set the fetched content into the state
        }
      })
      .catch(error => {
        console.error('Error fetching note:', error);
      })
      .finally(() => {
        setIsLoading(false); // Stop loading once the data is fetched
      });
  }, []); // Empty dependency array to run only on component mount

  // Function to save the note to the server
  const saveNoteToServer = (newContent) => {
    fetch('/wp-json/private-student-notes/v1/save-note', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce, // Nonce for security
      },
      body: JSON.stringify({
        note: newContent,
      }),
    })
      .then(response => response.json())
      .then(data => {
        console.log('Note saved:', data);
      })
      .catch(error => {
        console.error('Error saving note:', error);
      });
  };

  if (isLoading) {
    return <div>Loading...</div>; // Show loading text while fetching content
  }

  return (
    <>
      <RichText
        tagName="div"
        multiline="p"
        value={noteContent}  // Use state value to manage content
        onChange={(newContent) => {
          setNoteContent(newContent);  // Update the state when content changes
        }}
        placeholder="Add your private note here..."
        //allowedFormats={['core/bold', 'core/italic', 'core/link']}

      />
      <button 
        onClick={() => saveNoteToServer(noteContent)} // Save when the button is clicked
        className="save-note-button"
      >
        Save Note
      </button>
    </>
  );
};

// Initialize the React component and render it in the placeholder element
const renderEditor = () => {
  const editorElement = document.getElementById('private-student-note-editor');
  
  if (editorElement) {
    ReactDOM.render(<PrivateStudentNotesEditor />, editorElement); // Render the component into the placeholder element
  }
};

// Trigger the rendering on page load
window.addEventListener('load', renderEditor);
