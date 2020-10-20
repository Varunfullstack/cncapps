import React from "react";

import Modal from 'react-bootstrap/Modal';
import ModalHeader from "react-bootstrap/ModalHeader";
import ModalBody from 'react-bootstrap/ModalBody';
import ModalFooter from 'react-bootstrap/ModalFooter';
import CKEditor from '@ckeditor/ckeditor5-react';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'

const AddProjectModalComponent = (props) => {
    const {summary} = props;


    return (
        <Modal show={true}>
            <ModalHeader closeButton>
                <h5 className="modal-title">
                    Add Project
                </h5>
            </ModalHeader>
            <ModalBody>
                <div className="row">
                    <div className="col-6">
                        <div className="form-group">
                            <label htmlFor="description">Description</label>
                            <input id="description"
                                   type="text"
                                   className="form-control input-sm"
                            />
                        </div>
                    </div>
                </div>
                <div className="row">
                    <div className="col-12">
                        <CKEditor
                            editor={ClassicEditor}
                            data={summary}

                        />
                    </div>
                </div>
                <div className="row">
                    <div className="col-3">
                        <div className="form-group">
                            <label htmlFor="projectOpenedDate">Project Opened Date</label>
                            <input id="projectOpenedDate"
                                   type="text"
                                   className="form-control input-sm"
                            />
                        </div>
                    </div>
                </div>
            </ModalBody>
            <ModalFooter>
                <button type="button"
                        className="btn btn-sm secondary"
                        data-dismiss="modal"
                >Close
                </button>
                <button type="button"
                        className="btn btn-sm btn-new"
                >Add Project
                </button>
            </ModalFooter>
        </Modal>
    )
}
export default AddProjectModalComponent;