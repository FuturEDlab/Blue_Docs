import Image from "next/image";

const docbot = require('/app/documentationbot.js');
const submission = "!submitmd Title='This is a document.' Description='This is something sbout the document.'";

export default function Home() {

  // Parse the submission command string.
  docbot.parseSubmission(submission);

  return (
    <div id="doc-list">

      <div className="doc-text-container">
        <div className="doc-component">
          <p className="doc-title">This is a document! alwkfnalwfnal lskenflasenfeajkabf aowds</p>
          <p className="doc-author">Author: Erion</p>
          <p className="doc-date-created">Modified: 5/24/2025 5:00PM</p>
          <p className="doc-desc">Here is a little bit about the document. alkjfnal alkwfna lakdna;kdma dlaknda; ;aklnda;k dalkdn</p>
        </div>
      </div>

      <div className="doc-text-container">
        <div className="doc-component">
          <p className="doc-title">This is a document! alwkfnalwfnal lskenflasenfeajkabf aowds</p>
          <p className="doc-author">Author: Erion</p>
          <p className="doc-date-created">Modified: 5/24/2025 5:00PM</p>
          <p className="doc-desc">Here is a little bot about the document. alkjfnal alkwfna lakdna;kdma dlaknda; ;aklnda;k dalkdn</p>
        </div>
      </div>

      <div className="doc-text-container">
        <div className="doc-component">
          <p className="doc-title">This is a document! alwkfnalwfnal lskenflasenfeajkabf aowds</p>
          <p className="doc-author">Author: Erion</p>
          <p className="doc-date-created">Modified: 5/24/2025 5:00PM</p>
          <p className="doc-desc">Here is a little bot about the document. alkjfnal alkwfna lakdna;kdma dlaknda; ;aklnda;k dalkdn</p>
        </div>
      </div>

    </div>

  );
 
}

// to do: add doc(title, link, desc, )
