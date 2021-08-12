import React, {Fragment} from "react";
import MainComponent from "../../shared/MainComponent";
import Toggle from "../../shared/Toggle";
import APICustomers from "../../services/APICustomers";
import Modal from "../../shared/Modal/modal";


export class ContactEditModalComponent extends MainComponent {
    api = new APICustomers();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            data: {
                id: "",
                customerID: "",
                title: "",
                position: "",
                firstName: "",
                lastName: "",
                email: "",
                phone: "",
                mobilePhone: "",
                fax: "",
                portalPassword: "",
                mailshot: "",
                mailshot2Flag: "",
                mailshot3Flag: "",
                mailshot8Flag: "",
                mailshot9Flag: "",
                mailshot11Flag: "",
                notes: "",
                failedLoginCount: "",
                reviewUser: "",
                hrUser: "",
                supportLevel: "",
                initialLoggingEmail: "",
                othersInitialLoggingEmailFlag: "",
                othersWorkUpdatesEmailFlag: "",
                othersFixedEmailFlag: "",
                pendingLeaverFlag: "",
                pendingLeaverDate: "",
                specialAttentionContactFlag: "",
                linkedInURL: "",
                pendingFurloughAction: "",
                pendingFurloughActionDate: "",
                pendingFurloughActionLevel: "",
                siteNo: "",
                active: "",
            },
            sites: [],
            supportLevelOptions: [
                "main",
                "supervisor",
                "support",
                "delegate",
                "furlough",
            ],
        }
    }

    componentDidMount() {
        this.setState({data: this.props.contact});
        this.api.getCustomerSites(this.props.contact.customerID, "Y").then((sites) => {
            this.setState({sites});
        });
    }

    getYNFlag = (label, prop, disabled = false) => {
        const {data} = this.state;
        return (
            <Fragment>
                <td className="text-right">{label}</td>
                <td>
                    <Toggle
                        disabled={disabled}
                        checked={data[prop] === "Y"}
                        onChange={() => this.setValue(prop, disabled ? data[prop] : (data[prop] === "Y" ? "N" : "Y"))}
                    />
                </td>
            </Fragment>
        );
    };
    getBooleanFlag = (label, prop, disabled = false) => {
        const {data} = this.state;
        return (
            <Fragment>
                <td className="text-right">{label}</td>
                <td>
                    <Toggle
                        disabled={disabled}
                        checked={data[prop]}
                        onChange={() => this.setValue(prop, disabled ? data[prop] : data[prop] ? 0 : 1)}
                    />
                </td>
            </Fragment>
        );
    };

    render() {
        const {title, width, show, onSave, onClose} = this.props;
        const {data} = this.state;
        return (
            <Modal
                width={width}
                title={title}
                show={show}
                content={
                    <div key="content" id="contactformdata">
                        <table className="table">
                            <tbody>
                            <tr>
                                <td className="text-right">Site</td>
                                <td>
                                    <select
                                        required
                                        value={data.siteNo}
                                        onChange={(event) =>
                                            this.setValue("siteNo", event.target.value)
                                        }
                                        className="form-control"
                                    >
                                        {this.state.sites.map((site, index) => {
                                            return (
                                                <option key={site.id} value={site.id}>
                                                    {site.add1}
                                                </option>
                                            );
                                        })}
                                    </select>
                                </td>
                                <td className="text-right">Title</td>
                                <td>
                                    <input
                                        required
                                        value={data.title || ""}
                                        onChange={(event) =>
                                            this.setValue("title", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td className="text-right">First</td>
                                <td>
                                    <input
                                        required
                                        value={data.firstName || ""}
                                        onChange={(event) =>
                                            this.setValue("firstName", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                                <td className="text-right">Last</td>
                                <td>
                                    <input
                                        required
                                        value={data.lastName || ""}
                                        onChange={(event) =>
                                            this.setValue("lastName", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td className="text-right">Position</td>
                                <td>
                                    <input
                                        value={data.position || ""}
                                        onChange={(event) =>
                                            this.setValue("position", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                                <td className="text-right">Email</td>
                                <td>
                                    <input
                                        required
                                        value={data.email || ""}
                                        onChange={(event) =>
                                            this.setValue("email", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td className="text-right">Phone</td>
                                <td>
                                    <input
                                        value={data.phone || ""}
                                        onChange={(event) =>
                                            this.setValue("phone", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                                <td className="text-right">Mobile</td>
                                <td>
                                    <input
                                        value={data.mobilePhone || ""}
                                        onChange={(event) =>
                                            this.setValue("mobilePhone", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td className="text-right">Linkedin</td>
                                <td>
                                    <input
                                        value={data.linkedInURL || ""}
                                        onChange={(event) =>
                                            this.setValue("linkedInURL", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                                <td className="text-right">Notes</td>
                                <td>
                                    <input
                                        value={data.notes || ""}
                                        onChange={(event) =>
                                            this.setValue("notes", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                            </tr>
                            <tr>

                                <td className="text-right">Support Level</td>
                                <td>
                                    <select
                                        required
                                        name="supportLevel"
                                        value={data.supportLevel}
                                        onChange={(event) =>
                                            this.setValue("supportLevel", event.target.value)
                                        }
                                        className="form-control"
                                    >
                                        {this.state.supportLevelOptions.map((level) => {
                                            return (
                                                <option key={level} value={level}>
                                                    {level.replace(/^(.)|\s(.)/g, (x) => x.toUpperCase())}
                                                </option>
                                            );
                                        })}
                                    </select>
                                </td>
                                <td className="text-right"> Pending Leaver Date</td>
                                <td>
                                    <input
                                        type="date"
                                        value={data.pendingLeaverDate || ""}
                                        onChange={(event) =>
                                            this.setValue("pendingLeaverDate", event.target.value)
                                        }
                                        className="form-control"
                                    />
                                </td>
                            </tr>
                            <tr>
                                {this.getYNFlag(
                                    "Special Attention",
                                    "specialAttentionContactFlag"
                                )}
                                {this.getYNFlag("Company Information Reports", "mailshot9Flag")}
                            </tr>
                            <tr>
                                {this.getYNFlag("Daily SR Reports", "mailshot11Flag")}
                                {this.getYNFlag("Newsletter", "mailshot3Flag")}
                            </tr>
                            <tr>
                                {this.getYNFlag(
                                    "Send Others Initial Logging Email",
                                    "othersInitialLoggingEmailFlag",
                                    data.supportLevel == "support" || data.supportLevel == "delegate"
                                )}
                                {this.getYNFlag("Mailshot", "sendMailshotFlag")}
                            </tr>
                            <tr>
                                {this.getYNFlag(
                                    "Send Others Fixed Email",
                                    "othersFixedEmailFlag",
                                    data.supportLevel == "support" || data.supportLevel == "delegate"
                                )}
                            </tr>
                            <tr>
                                {this.getYNFlag("Pending Leaver", "pendingLeaverFlag")}
                                {this.getYNFlag("PrePay TopUp Notifications", "mailshot8Flag")}
                            </tr>
                            <tr>
                                {this.getYNFlag("Attends Review Meeting", "reviewUser")}
                                {this.getYNFlag("Receive Invoices", "mailshot2Flag")}
                            </tr>
                            <tr>
                                {this.getYNFlag("HR User to edit contacts", "hrUser")}
                            </tr>
                            <tr>
                                {this.getBooleanFlag("Active", "active")}
                            </tr>
                            </tbody>
                        </table>
                    </div>
                }
                footer={
                    <div key="footer">
                        <button onClick={() => onSave(data)}>Save</button>
                        <button onClick={onClose}>
                            Cancel
                        </button>
                    </div>
                }
                onClose={onClose}
            />

        )
    }
}