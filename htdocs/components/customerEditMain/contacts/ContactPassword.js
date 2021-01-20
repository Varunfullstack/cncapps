import React from "react";
import * as PropTypes from "prop-types";
import {Alert, Modal} from "react-bootstrap";
import ModalHeader from "react-bootstrap/ModalHeader";
import ModalBody from "react-bootstrap/ModalBody";
import ModalFooter from "react-bootstrap/ModalFooter";

const TOO_SHORT_PASSWORD_ERROR = "Too short";
const TOO_WEAK_PASSWORD_ERROR = "Too Weak";
const EMPTY_PASSWORD_ERROR = "Empty Password";
const NOT_EQUAL_PASSWORD_ERROR = "Not Equal";

const INITIAL_STATE = {
    showModal: false,
    isValid: false,
    password: "",
    repeatedPassword: "",
    passwordError: []
}

export class ContactPassword extends React.Component {

    constructor(props, context) {
        super(props, context);
        this.state = INITIAL_STATE;
        this.closeModal = this.closeModal.bind(this);
        this.showModal = this.showModal.bind(this);
        this.onPasswordSave = this.onPasswordSave.bind(this);
        this.onPasswordUpdate = this.onPasswordUpdate.bind(this);
    }

    closeModal() {
        this.setState(INITIAL_STATE);
    }

    async onPasswordSave() {
        try {
            const encryptedPasswordResponse = await fetch(`/Customer.php?action=encrypt&value=${this.state.password}`).then(res => res.json());
            this.props.onChange({target: {name: this.props.name, value: encryptedPasswordResponse.data}});
            this.closeModal();
        } catch (err) {

        }
    }

    checkPasswordEqual(password, repeatPassword) {
        if (!password || !repeatPassword) {
            return false;
        }

        if (password != repeatPassword) {
            return false;
        }
        return true;
    }

    getPasswordErrorValidation(password) {
        if (!password) {
            return null;
        }

        let strength = 0;
        if (password.length < 6) {
            return TOO_SHORT_PASSWORD_ERROR;
        }

        if (password.match(/([a-z])/)) strength += 1;
        if (password.match(/([A-Z])/)) strength += 1;
        if (password.match(/([0-9])/)) strength += 1;
        if (password.match(/([!,%&@#$^*?_~])/)) strength += 1;
        if (strength < 3) {
            return TOO_WEAK_PASSWORD_ERROR;
        }
        return null;
    }

    showModal() {
        this.setState({showModal: true});
    }

    passwordValidation() {
        const passwordState = {isValid: false, passwordError: []};
        const {password, repeatedPassword} = this.state;
        if (password) {
            if (!this.checkPasswordEqual(password, repeatedPassword)) {
                passwordState.passwordError.push(NOT_EQUAL_PASSWORD_ERROR);
            }
            const passwordValidationError = this.getPasswordErrorValidation(password);
            if (passwordValidationError) {
                passwordState.passwordError.push(passwordValidationError);
            }
            if (!passwordState.passwordError.length) {
                passwordState.isValid = true;
            }
        }

        this.setState({...passwordState});
    }

    onPasswordUpdate($event) {
        this.setState({[$event.target.name]: $event.target.value}, () => {
            this.passwordValidation()
        })
    }

    render() {
        const {showModal, isValid, password, repeatedPassword, passwordError} = this.state;
        const {value} = this.props;
        return (
            <React.Fragment>
                <button
                    onClick={this.showModal}
                    type="button"
                    className="form-control input-sm"
                >

                    <i className={`fal ${value ? "fa-lock" : "fa-lock-open"}`}> </i>
                </button>
                <Modal show={showModal}
                       onHide={this.closeModal}
                >
                    <ModalHeader closeButton>
                        <h5 className="modal-title">Assign Password</h5>
                    </ModalHeader>
                    <ModalBody>
                        {
                            passwordError.map(error => <Alert key={error}
                                                              variant="danger"
                            >{error}</Alert>)
                        }
                        <div className="form-group">
                            <label htmlFor="">Password</label>
                            <input name="password"
                                   type="password"
                                   value={password || ""}
                                   onChange={this.onPasswordUpdate}
                                   className="form-control input-sm"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="">Confirm Password</label>
                            <input name="repeatedPassword"
                                   type="password"
                                   value={repeatedPassword || ""}
                                   onChange={this.onPasswordUpdate}
                                   className="form-control input-sm"

                            />
                        </div>
                    </ModalBody>
                    <ModalFooter className="modal-footer">
                        <button type="button"
                                className="btn btn-sm secondary"
                                onClick={this.closeModal}
                        >Close
                        </button>
                        <button type="button"
                                className="btn btn-sm btn-new"
                                disabled={!isValid}
                                onClick={this.onPasswordSave}
                        >Ok
                        </button>
                    </ModalFooter>
                </Modal>
            </React.Fragment>
        )
    }
}

ContactPassword.propTypes = {
    onChange: PropTypes.func,
    name: PropTypes.string,
    value: PropTypes.string
};