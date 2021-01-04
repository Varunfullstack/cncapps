import React from "react";

import Modal from 'react-bootstrap/Modal';
import ModalHeader from "react-bootstrap/ModalHeader";
import ModalBody from 'react-bootstrap/ModalBody';
import ModalFooter from 'react-bootstrap/ModalFooter';
import {FileInput} from "../FileInput";

const AddPortalCustomerDocumentComponent = (props) => {
    const {description, customerContract, mainContractOnly, file, show, onFieldUpdate, onClose, onAdd} = props;


    return (

        <Modal show={show}
               onHide={() => onClose()}
        >
            <ModalHeader closeButton>
                <h5 className="modal-title">Add Document</h5>
            </ModalHeader>
            <ModalBody>
                <div className="row">
                    <div className="col-12">
                        <div className="form-group">
                            <label htmlFor="portalDocumentsDescription">Description</label>
                            <input type="text"
                                   className="form-control input-sm"
                                   onChange={$event => onFieldUpdate('description', $event.target.value)}
                                   value={description}
                            />
                        </div>
                    </div>
                </div>
                <div className="row">
                    <div className="col-6">
                        <div className="form-group">
                            <label>Customer Contract</label>
                            <div className="form-group form-inline pt-1">
                                <label className="switch">
                                    <input type="checkbox"
                                           onChange={$event => onFieldUpdate('customerContract', $event.target.checked)}
                                           checked={customerContract}
                                    />
                                    <span className="slider round"/>
                                </label>
                            </div>

                        </div>
                    </div>
                    <div className="col-6">
                        <div className="form-group">
                            <label>Main Contract Only</label>
                            <div className="form-group form-inline pt-1">
                                <label className="switch">
                                    <input type="checkbox"
                                           onChange={$event => onFieldUpdate('mainContractOnly', $event.target.checked)}
                                           checked={mainContractOnly}
                                    />
                                    <span className="slider round"/>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
                <div className="row">
                    <div className="col-12">
                        <div className="form-group">
                            <a className="btn"
                               title="Select a file or drag and drop"

                            >
                                <i className="fal fa-picture-o"/>
                                <FileInput onChange={$event => onFieldUpdate('file', $event[0])}
                                           value={file ? [file] : []}
                                >

                                </FileInput>
                            </a>
                        </div>
                    </div>
                </div>
            </ModalBody>
            <ModalFooter className="modal-footer">
                <button type="button"
                        className="btn btn-sm secondary"
                        onClick={onClose}
                >Close
                </button>
                <button type="button"
                        className="btn btn-sm btn-new"
                        disabled={!description || !file}
                        onClick={onAdd}
                >Add Portal Document
                </button>
            </ModalFooter>


        </Modal>

    )
}
export default AddPortalCustomerDocumentComponent;