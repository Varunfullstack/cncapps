import MainComponent from "../../shared/MainComponent";
import React from "react";
import APIPassword from "../services/APIPassword";
import Modal from "../../shared/Modal/modal";

export const PASSWORD_DETAILS_CLOSE_REASON = {
    CANCELLED: 'CANCELLED',
    UPDATED: 'UPDATED'
}

export class PasswordDetails extends MainComponent {
    api = new APIPassword();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            passwordItem: this.props.passwordItem,
            services: [],
            passwordLevels: [
                {level: 0, description: "No Access"},
                {level: 1, description: "Helpdesk Access"},
                {level: 2, description: "Engineer Access"},
                {level: 3, description: "Senior Engineer Access"},
                {level: 4, description: "Team Lead Access"},
                {level: 5, description: "Management Access"}
            ]
        }
    }

    componentDidMount() {
        this.getServices();
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (this.props.customerId !== prevProps.customerId || JSON.stringify(this.props.passwordItem) !== JSON.stringify(prevProps?.passwordItem)) {
            this.getServices();
            if(JSON.stringify(this.props.passwordItem) !== JSON.stringify(this.state.passwordItem)){
                this.setState({passwordItem: this.props.passwordItem});
            }
        }
    }

    getServices = () => {
        const {services, passwordItem} = this.state;
        const {customerId} = this.props;
        if (services.length == 0 && customerId)
            this.api.getServices(customerId, passwordItem.passwordID).then(res => {
                this.setState({services: res.data});
            })
    }

    setValue = (prop, value) => {
        this.setState({passwordItem: {...this.state.passwordItem, [prop]: value}})
    }

    getModalElement = () => {
        const {passwordItem, services, passwordLevels} = this.state;

        return <Modal
            width={500}
            show={this.props.show}
            title={passwordItem.passwordID ? "Edit Password" : "Add New Password"}
            onClose={this.hideModal}
            content={
                <div key="content">

                    <div className="form-group">
                        <label>User Name</label>
                        <input value={passwordItem.username || ''}
                               type="text"
                               className="form-control"
                               onChange={(event) => this.setValue("username", event.target.value)}
                        />
                    </div>
                    <div className="form-group">
                        <label>Service</label>
                        <select className="form-control"
                                value={passwordItem.serviceID || ''}
                                onChange={(event) => this.setValue("serviceID", event.target.value)}
                        >
                            <option>No Service</option>
                            {services.map(s => <option key={s.id}
                                                       value={s.id}
                            >{s.name}</option>)}
                        </select>
                    </div>
                    <div className="form-group">
                        <label>Password</label>
                        <input value={passwordItem.password || ''}
                               type="text"
                               className="form-control"
                               onChange={(event) => this.setValue("password", event.target.value)}
                        />
                    </div>
                    <div className="form-group">
                        <label>Notes</label>
                        <input value={passwordItem.notes || ''}
                               type="text"
                               className="form-control"
                               onChange={(event) => this.setValue("notes", event.target.value)}
                        />
                    </div>
                    <div className="form-group">
                        <label>URL</label>
                        <input value={passwordItem.URL || ''}
                               type="text"
                               className="form-control"
                               onChange={(event) => this.setValue("URL", event.target.value)}
                        />
                    </div>

                    <div className="form-group">
                        <label>Level</label>
                        <select className="form-control required"
                                value={passwordItem.level}
                                onChange={(event) => this.setValue("level", event.target.value)}
                        >
                            <option>Select a Level</option>
                            {passwordLevels.map(s => <option key={s.level}
                                                             value={s.level}
                            >{s.description}</option>)}
                        </select>
                    </div>
                    <div className="form-group">
                        <label>Sales password
                            <input value={1}
                                   checked={passwordItem.salesPassword}
                                   type="checkbox"
                                   onChange={(event) => this.setValue("salesPassword", event.target.checked)}
                            />
                        </label>
                    </div>
                </div>
            }
            footer={<div key="footer">
                <button className="btn btn-secondary"
                        onClick={() => window.open('Password.php?action=generate&htmlFmt=popup', 'reason', 'scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0')}
                > Generate Password
                </button>
                <button onClick={this.handleSave}>Save</button>
                <button onClick={this.hideModal}> Cancel</button>
            </div>}
        >

        </Modal>
    }
    hideModal = (reason = PASSWORD_DETAILS_CLOSE_REASON.CANCELLED) => {
        if (this.props.onClose)
            this.props.onClose(reason);
    }
    handleSave = () => {
        const {passwordItem} = this.state;

        if (passwordItem.level === "") {
            this.alert("Level required.");
            return;
        }
        passwordItem.customerID = this.props.passwordItem.customerID;

        this.api.updatePassword(passwordItem)
            .then((result) => {
                if (result.state) {
                    this.setState({showModal: false});
                    this.hideModal(PASSWORD_DETAILS_CLOSE_REASON.UPDATED);
                } else {
                    this.alert(result.error);
                }
            });


    }

    render() {
        if (!this.props.show)
            return null;
        return <div>
            {this.getAlert()}
            {this.getModalElement()}
        </div>
    }
}