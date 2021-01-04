import React from "react";

import Modal from 'react-bootstrap/Modal';
import ModalHeader from "react-bootstrap/ModalHeader";
import ModalBody from 'react-bootstrap/ModalBody';
import ModalFooter from 'react-bootstrap/ModalFooter';

const AddSiteComponent = (props) => {
    const {addressLine, town, postcode, phone, maxTravelHours, show, onFieldUpdate, onClose, onAdd} = props;


    return (

        <Modal show={show}
               onHide={() => onClose()}
        >
            <ModalHeader closeButton>
                <h5 className="modal-title">Add Site</h5>
            </ModalHeader>
            <ModalBody>
                <div className="row">
                    <div className="col-lg-4">
                        <div className="form-group">

                            <label>Site Address</label>
                            <input value={addressLine}
                                   onChange={($event) => onFieldUpdate('addressLine', $event.target.value)}
                                   size="35"
                                   maxLength="35"
                                   className="form-control mb-3"
                            />

                        </div>
                    </div>

                    <div className="col-lg-4">
                        <label htmlFor="town">Town</label>
                        <div className="form-group">
                            <input
                                value={town}
                                onChange={($event) => onFieldUpdate('town', $event.target.value)}
                                size="25"
                                maxLength="25"
                                className="form-control input-sm"
                            />
                        </div>
                    </div>

                    <div className="col-lg-4">
                        <label htmlFor="postcode">Postcode</label>
                        <div className="form-group">
                            <input
                                value={postcode}
                                onChange={($event) => onFieldUpdate('postcode', $event.target.value)}
                                size="15"
                                maxLength="15"
                                className="form-control input-sm"
                            />
                        </div>
                    </div>
                    <div className="col-lg-4">
                        <label htmlFor="phone">Phone</label>
                        <div className="form-group">
                            <input
                                value={phone}
                                onChange={($event) => onFieldUpdate('phone', $event.target.value)}
                                size="20"
                                maxLength="20"
                                className="form-control input-sm"
                            />
                        </div>
                    </div>
                    <div className="col-lg-4">
                        <label>Max Travel Hours</label>
                        <div className="form-group">
                            <input value={maxTravelHours}
                                   onChange={($event) => onFieldUpdate('maxTravelHours', $event.target.value)}
                                   size="5"
                                   maxLength="5"
                                   min="1"
                                   step="0.25"
                                   type="number"
                                   className="form-control input-sm"
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
                        disabled={
                            !addressLine ||
                            !town ||
                            !postcode ||
                            !phone ||
                            !maxTravelHours
                        }
                        onClick={() => onAdd()}
                >Add Site
                </button>
            </ModalFooter>
        </Modal>
    )
}
export default AddSiteComponent;