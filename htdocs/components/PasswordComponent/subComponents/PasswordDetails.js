import MainComponent from "../../shared/MainComponent";
import React from "react";
import APIPassword from "../services/APIPassword";
import Modal from "../../shared/Modal/modal";

export class PasswordDetails extends MainComponent {
    api = new APIPassword();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            mode: "insert",
            data: {
                ...this.getInitData()
            },
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

    getInitData() {
        return {
            URL: '',
            archivedAt: null,
            archivedBy: null,
            level: '',
            notes: '',
            password: '',
            passwordID: null,
            serviceID: '',
            serviceName: '',
            sortOrder: '',
            username: '',
        };
    }

    static getDerivedStateFromProps(props, state) {
        if (props.data && state.data && props.data.passwordID != state.data.passwordID)
            state.data = {...props.data};
        return state;

    }

    getServices = () => {
        const {services, data} = this.state;
        const {filter} = this.props;
        if (services.length == 0 && filter.customer)
            this.api.getServices(filter.customer.id, data.passwordID).then(res => {
                this.setState({services: res.data});
            })
    }
    getModalElement = () => {
        const {data, services, passwordLevels} = this.state;
        const {mode} = this.props;
        return <Modal
            width={500}
            show={this.props.show}
            title={mode == "new" ? "Add New Password" : "Edit Password"}
            onClose={this.hideModal}
            content={
                <div key="content">

                    <div className="form-group">
                        <label>User Name</label>
                        <input value={data.username}
                               type="text"
                               name=""
                               id=""
                               className="form-control"
                               onChange={(event) => this.setValue("username", event.target.value)}
                        />
                    </div>
                    <div className="form-group">
                        <label>Service</label>
                        <select className="form-control"
                                value={data.serviceID}
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
                        <input value={data.password}
                               type="text"
                               name=""
                               id=""
                               className="form-control"
                               onChange={(event) => this.setValue("password", event.target.value)}
                        />
                    </div>
                    <div className="form-group">
                        <label>Notes</label>
                        <input value={data.notes}
                               type="text"
                               name=""
                               id=""
                               className="form-control"
                               onChange={(event) => this.setValue("notes", event.target.value)}
                        />
                    </div>
                    <div className="form-group">
                        <label>URL</label>
                        <input value={data.URL}
                               type="text"
                               name=""
                               id=""
                               className="form-control"
                               onChange={(event) => this.setValue("URL", event.target.value)}
                        />
                    </div>
                    <div className="form-group">
                        <label>Level</label>
                        <select className="form-control required"
                                value={data.level}
                                onChange={(event) => this.setValue("level", event.target.value)}
                        >
                            <option>Select a Level</option>
                            {passwordLevels.map(s => <option key={s.level}
                                                             value={s.level}
                            >{s.description}</option>)}
                        </select>
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
    hideModal = () => {
        if (this.props.onClose)
            this.props.onClose({...this.state.data});
        this.setState({data: {...this.getInitData()}})
    }
    handleSave = () => {
        const {data} = this.state;
        const {mode} = this.props;

        if (data.level == "") {
            this.alert("Level required.");
            return;
        }
        data.customerID = this.props.data.customerID;
        this.api.updatePassword(data).then((result) => {
            if (result.state) {
                this.setState({showModal: false, services: []});
                this.hideModal();
                //this.getServices();
            } else {
                this.alert(result.error);
            }
        });


    }

    render() {
        this.getServices();
        if (!this.props.show)
            return null;
        return <div>
            {this.getAlert()}
            {this.getModalElement()}
        </div>
    }
}