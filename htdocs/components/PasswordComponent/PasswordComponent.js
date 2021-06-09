import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './PasswordComponent.css';
import APIPassword from "./services/APIPassword.js";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Toggle from "../shared/Toggle.js";
import CustomerSearch from "../shared/CustomerSearch.js";
import {PASSWORD_DETAILS_CLOSE_REASON, PasswordDetails} from "./subComponents/PasswordDetails.js";
import {params} from "../utils/utils.js";

const newPasswordItemInitialState = {
    URL: '',
    archivedAt: '',
    archivedBy: '',
    level: '',
    notes: '',
    password: '',
    passwordID: '',
    serviceID: '',
    serviceName: '',
    sortOrder: '',
    username: '',
    salesPassword: false
};

class PasswordComponent extends MainComponent {
    api = new APIPassword();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            showModal: false,
            types: [],
            passwordItem: null,
            filter: {
                customer: null,
                showArchived: false,
                showHigherLevel: false
            },
            customerId: null,
            passwords: [],
            error: null,
            disabled: false

        };
    }

    componentDidMount() {
        this.checkParams();
        this.getData();
    }

    checkParams = () => {
        const customerId = params.get("customerID");
        if (customerId && customerId != '') {
            const {filter} = this.state;

            filter.customer = {
                id: customerId,
                showArchived: false,
                showHigherLevel: false
            }
            this.setState({filter, customerId, disabled: true});
        }
    }

    getData = () => {
        const {filter} = this.state;

        if (filter.customer && filter.customer.id)
            this.setState({showSpinner: true}, () => {
                this.api.getAllPasswords(filter.customer.id, filter.showArchived, filter.showHigherLevel)
                    .then(res => {
                        if (res.state)
                            this.setState({passwords: res.data, error: null, showSpinner: false});
                        else
                            this.setState({error: res.error, showSpinner: false});
                    });
            })
    }
    copyToClipboard = (item, prop) => {
        const {passwords} = this.state;
        const indx = passwords.map(p => p.passwordID).indexOf(item.passwordID);
        passwords.map(p => p.selectedColumn = null);
        passwords[indx].selectedColumn = prop;
        this.setState({passwords});

        const textArea = document.createElement('textarea');
        textArea.value = item[prop];
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
    getDataTable = () => {
        const {filter} = this.state;
        let columns = [
            {
                path: "username",
                label: "User Name",
                hdToolTip: "User Name",
                hdClassName: "text-center",
                sortable: true,
                content: (item) => {
                    return <div style={{display: "flex"}}>
                        <i className="fal fa-2x fa-copy color-gray pointer pr-1"
                           onClick={() => this.copyToClipboard(item, "username")}
                        />
                        <span className={"nowrap pointer " + (item.selectedColumn == "username" ? "clip-board" : "")}
                              id={item.passwordID + 'username'}
                        >{item.username}</span>

                    </div>
                },
            },
            {
                path: "serviceName",
                label: "Service",
                hdToolTip: "Service",
                hdClassName: "text-center",
                sortable: true,
                className: "nowrap",
            },
            {
                path: "password",
                label: "Password",
                hdToolTip: "Password",
                hdClassName: "text-center",
                sortable: true,
                content: (item) => {
                    return <div style={{display: "flex"}}>
                        <i className="fal fa-2x fa-copy color-gray pointer pr-1"
                           onClick={() => this.copyToClipboard(item, "password")}
                        />
                        <span className={"nowrap pointer " + (item.selectedColumn == "password" ? "clip-board" : "")}
                              id={item.passwordID + 'password'}
                        >{item.password}</span>

                    </div>
                },
            },
            {
                path: "notes",
                label: "Notes",
                hdToolTip: "Notes",
                hdClassName: "text-center",
                sortable: true,
            },
            {
                path: "URL",
                label: "URL",
                hdToolTip: "URL",
                hdClassName: "text-center",
                sortable: true,
                content: (password) => <a href={password.URL}
                                          target="_blank"
                >{password.URL}</a>
            },
            {
                path: "level",
                label: "Level",
                hdToolTip: "Level",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,

                //className: "text-center",                
            },

        ];
        if (filter.showArchived) {
            columns = [...columns,
                {
                    path: "archivedBy",
                    label: "Archived By	",
                    hdToolTip: "Archived By	",
                    hdClassName: "text-center",
                    //icon: "fal fa-2x fa-eye color-gray2 pointer",
                    sortable: true,
                    //content:(password)=><a href={password.URL} target="_blank">{password.URL}</a>
                    //className: "text-center",                
                },
                {
                    path: "archivedAt",
                    label: "Archived At",
                    hdToolTip: "Archived At",
                    hdClassName: "text-center",
                    //icon: "fal fa-2x fa-eye color-gray2 pointer",
                    sortable: true,
                    //className: "text-center",                
                },
            ]
        }
        if (!filter.showArchived) {
            columns = [...columns,
                {
                    path: "edit",
                    label: "",
                    hdToolTip: "Edit",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-edit color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    content: (type) => <i className="fal fa-2x fa-edit color-gray pointer"
                                          onClick={() => this.showEditModal(type)}
                    />,

                },
                {
                    path: "archive",
                    label: "",
                    hdToolTip: "Archive Password",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-archive color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    content: (type) => <i className="fal fa-2x fa-archive color-gray pointer"
                                          onClick={() => this.handleDelete(type)}
                    />,

                }
            ]
        }
        return <Table
            key="passwords"
            pk="passwordID"
            columns={columns}
            data={this.state.passwords || []}
            search={true}
        >
        </Table>
    }
    showEditModal = (passwordItem) => {
        this.setState({showModal: true, passwordItem});
    }
    handleDelete = async (type) => {
        const conf = await this.confirm("Are you sure to archive this password?");
        if (conf)
            this.api.archivePassword(type.passwordID).then((res) => {
                if (res.state) this.getData();
                else this.alert(res.error);
            });
    }

    handleNewPassword = () => {
        const {filter} = this.state;
        const passwordItem = {...newPasswordItemInitialState, customerID: filter.customer.id};
        this.setState({showModal: true, passwordItem});
    }
    handleCustomerSelect = (customer) => {
        const {filter} = this.state;
        filter.customer = customer;
        this.setState({filter}, () => this.getData())
    }
    getFilter = () => {
        const {filter, error, disabled} = this.state;
        return <div className="flex-row align-center">
            <CustomerSearch disabled={disabled}
                            customerID={filter?.customer?.id}
                            placeholder="Select Customer"
                            onChange={(customer) => this.handleCustomerSelect(customer)}
            />
            {!error && filter.customer ? <div>
                <label className="ml-3 mr-1">Show Archived</label>
                <Toggle checked={filter.showArchived}
                        onChange={() => this.setFilter("showArchived", !filter.showArchived, this.getData)}
                />
                <label className="ml-3 mr-1">Show Higher Level Passwords</label>
                <Toggle checked={filter.showHigherLevel}
                        onChange={() => this.setFilter("showHigherLevel", !filter.showHigherLevel, this.getData)}
                />
            </div> : null
            }
        </div>
    }
    handleModalClose = (reason) => {
        if (reason === PASSWORD_DETAILS_CLOSE_REASON.UPDATED) {
            this.getData();
        }
        this.setState({showModal: false});
    }

    render() {
        const {error, filter} = this.state;
        return <div className="flex-1">
            <Spinner show={this.state.showSpinner}/>

            {this.getConfirm()}
            {this.getAlert()}
            {this.getFilter()}
            {
                !error && filter.customer ? <ToolTip title="New Password"
                                                     width={30}
                >
                    <i className="fal fa-2x fa-plus color-gray1 pointer"
                       onClick={this.handleNewPassword}
                    />
                </ToolTip> : null
            }
            {!error && filter.customer ? this.getDataTable() : null}
            {error ? <h2 style={{color: "red"}}>{error}</h2> : null}

            {this.getPasswordDetails()}

        </div>;
    }

    getPasswordDetails() {
        const {filter, showModal, passwordItem} = this.state;
        if (!filter.customer || !passwordItem || !showModal) {
            return '';
        }
        return <PasswordDetails onClose={this.handleModalClose}
                                show={showModal}
                                passwordItem={passwordItem}
                                customerId={filter.customer.id}
        />;
    }
}

export default PasswordComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactPasswordComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(PasswordComponent), domContainer);
});