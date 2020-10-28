import React from "react";

import Modal from 'react-bootstrap/Modal';
import ModalHeader from "react-bootstrap/ModalHeader";
import ModalBody from 'react-bootstrap/ModalBody';
import ModalFooter from 'react-bootstrap/ModalFooter';

const AddCustomerNoteComponent = (props) => {
    const {note,show, onNoteUpdate, onClose, onAdd} = props;


    return (

        <Modal show={show}
               onHide={() => onClose()}
        >
            <ModalHeader closeButton>
                <h5 className="modal-title">Add Note</h5>
            </ModalHeader>
            <ModalBody>
                <div className="row">
                    <div className="col-lg-12">
                        <div className="form-group">
                            <textarea value={note}
                                      onChange={($event) => onNoteUpdate($event.target.value)}
                                      className="form-control mb-3"
                            />

                        </div>
                    </div>
                </div>
            </ModalBody>
            <ModalFooter className="modal-footer">
                <button type="button"
                        className="btn btn-sm secondary"
                        onClick={() => onClose()}
                >Close
                </button>
                <button type="button"
                        className="btn btn-sm btn-new"
                        disabled={!note}
                        onClick={() => onAdd()}
                >Add Note
                </button>
            </ModalFooter>
        </Modal>
    )
}
export default AddCustomerNoteComponent;