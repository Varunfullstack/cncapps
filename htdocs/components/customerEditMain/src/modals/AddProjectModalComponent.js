import React from "react";

import Modal from 'react-bootstrap/Modal';
import ModalHeader from "react-bootstrap/ModalHeader";
import ModalBody from 'react-bootstrap/ModalBody';
import ModalFooter from 'react-bootstrap/ModalFooter';
import CKEditor from 'ckeditor4-react';

const AddProjectModalComponent = (props) => {
    const {description, summary, openedDate, show, onNewProjectFieldUpdate, onClose, onAddProject} = props;
    CKEditor.editorUrl = '/ckeditor/ckeditor.js'

    console.log(description, summary, openedDate);

    const ckeditorConfig = {
        contentsCss: '/screen.css',
        toolbarStartupExpanded: false,
        disableNativeSpellChecker: false,
        toolbar: 'CNCToolbar',
        toolbar_CNCToolbar:
            [
                ['Source', '-', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'TextColor', 'BGColor'],
                ['NumberedList', 'BulletedList'],
                ['Table'],
                ['Format', 'Font', 'FontSize'],
                ['Anchor', 'Link'],
                ['Undo', 'Redo']
            ],
        extraPlugins: 'font,wordcount',
        fontSize_sizes: '8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt',
        wordcount: {
            showParagraphs: false,
            showCharCount: true,
        },
    };

    return (
        <Modal show={show}
               onHide={() => onClose()}
        >
            <ModalHeader closeButton>
                <h5 className="modal-title">
                    Add Project
                </h5>
            </ModalHeader>
            <ModalBody>
                <div className="row">
                    <div className="col-12">
                        <div className="form-group">
                            <label htmlFor="description">Description *</label>
                            <input id="description"
                                   type="text"
                                   className="form-control input-sm"
                                   value={description}
                                   onChange={$event => onNewProjectFieldUpdate('description', $event.target.value)}
                            />
                        </div>
                    </div>
                </div>
                <div className="row">
                    <div className="col-12">
                        <div className="form-group">
                            <label>Summary *</label>
                        </div>
                        <CKEditor data={summary}
                                  config={ckeditorConfig}
                                  onChange={$event => onNewProjectFieldUpdate('summary', $event.editor.getData())}
                        />
                    </div>
                </div>
                <div className="row">
                    <div className="col-12">
                        <div className="form-group">
                            <label htmlFor="projectOpenedDate">Project Opened Date *</label>
                            <input id="projectOpenedDate"
                                   type="date"
                                   className="form-control input-sm"
                                   value={openedDate}
                                   onChange={$event => onNewProjectFieldUpdate('openedDate', $event.target.value)}
                            />
                        </div>
                    </div>
                </div>
            </ModalBody>
            <ModalFooter>
                <button type="button"
                        className="btn btn-sm secondary"
                        onClick={() => {
                            onClose()
                        }}
                >Close
                </button>
                <button type="button"
                        className="btn btn-sm btn-new"
                        disabled={!summary || !description || !openedDate}
                        onClick={() => {
                            onAddProject();
                        }}
                >Add Project
                </button>
            </ModalFooter>
        </Modal>
    )
}
export default AddProjectModalComponent;