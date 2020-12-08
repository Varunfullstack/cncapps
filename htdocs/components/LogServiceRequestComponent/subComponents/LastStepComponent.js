import Toggle from "../../shared/Toggle.js";
import APICustomers from "../../services/ApiCustomers.js";
import {groupBy, SRQueues, TeamType} from "../../utils/utils.js";
import APIStandardText from "../../services/APIStandardText.js";
import StandardTextModal from "../../Modals/StandardTextModal.js";
import MainComponent from "../../shared/MainComponent.js";
import React from 'react';
import DragAndDropUploaderComponent from "../../shared/DragAndDropUploaderComponent/DragAndDropUploaderComponent";
import ToolTip from "../../shared/ToolTip";

class LastStepComponent extends MainComponent {
    el = React.createElement;

    apiCustomer = new APICustomers();
    apiStandardText = new APIStandardText();
    modalType = {
        notStartWorkReason: "notStartWorkReason",
        notFirstTimeFixReason: "notFirstTimeFixReason"
    }

    constructor(props) {
        super(props);
        const {data} = this.props;
        this.state = {
            ...this.state,
            checkList: [],
            noWorkOptions: [],
            notFirstTimeFixOptions: [],
            contacts: [],
            _showModal: false,
            requireAuthorize: false,
            modalType: null,
            data: {
                uploadFiles: [],
                repeatProblem: data.repeatProblem || false,
                criticalSRFlag: data.criticalSRFlag || false,
                hideFromCustomerFlag: data.hideFromCustomerFlag || false,
                monitorSRFlag: data.monitorSRFlag || false,
                priority: data.priority || -1,
                internalNotesAppend: data.internalNotesAppend || "",
                contactID: data.contactID || -1,
                startTime: data.startTime || "",
                date: data.date || "",
                queueNo: data.queueNo || -1,
                notStartWorkReason: data.notStartWorkReason || "",
                notStartWorkReasonTemplate: data.notStartWorkReasonTemplate || "",
                startWork: false,
                authorisedBy: "",
                internalDocuments: []
            },
        };
        this.fileUploader = new React.createRef();
    }

    componentDidMount = async () => {
        const result = await Promise.all([
            this.apiStandardText.getAllTypes(),
            this.apiCustomer.getCustomerContacts(this.props.data.customerID),
            this.apiStandardText.getOptionsByType(
                "Unable to offer First Time Fix reason"
            ),
            this.apiStandardText.getOptionsByType(
                "Not First Time Fix Reason"
            ),
        ]);

        const {data} = this.state;
        data.contactID = this.props.data.customer.con_contno;
        let requireAuthorize = this.checkContactNeedAuthorize(
            data.contactID,
            result[1]
        );
        this.setState({
            requireAuthorize,
            checkList: result[0],
            contacts: result[1],
            standardTextList: result[0],
            noWorkOptions: result[2],
            notFirstTimeFixOptions: result[3],
            data,
        });
    };
    getChkProblemBefore = () => {
        const {el, handleCheckBoxChange} = this;
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Have they had this problem before?")),
            el(
                "td",
                null,
                el(Toggle, {
                    name: "exitistingProblem",
                    checked: this.state.data.repeatProblem,
                    onChange: () => handleCheckBoxChange("repeatProblem"),
                })
            )
        );
    };
    getcriticalSRFlagBefore = () => {
        const {el, handleCheckBoxChange} = this;
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Critical SR")),
            el(
                "td",
                null,
                el(Toggle, {
                    name: "criticalSRFlag",
                    checked: this.state.data.criticalSRFlag,
                    onChange: () => handleCheckBoxChange("criticalSRFlag"),
                })
            )
        );
    };
    gethideFromCustomerFlag = () => {
        const {el, handleCheckBoxChange} = this;
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Hide Entire SR From Customer")),
            el(
                "td",
                null,
                el(Toggle, {
                    name: "hideFromCustomerFlag",
                    checked: this.state.data.hideFromCustomerFlag,
                    onChange: () => handleCheckBoxChange("hideFromCustomerFlag"),
                })
            )
        );
    };
    getmonitorSRFlag = () => {
        const {el, handleCheckBoxChange} = this;
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Monitor SR")),
            el(
                "td",
                null,
                el(Toggle, {
                    name: "monitorSRFlag",
                    checked: this.state.data.monitorSRFlag,
                    onChange: () => handleCheckBoxChange("monitorSRFlag"),
                })
            )
        );
    };
    handleCheckBoxChange = (prop) => {
        const {data} = this.state;
        data[prop] = !data[prop];
        this.setState({data});
    };
    setValue = (prop, value) => {
        const {data} = this.state;
        data[prop] = value;
        this.setState({data});
    };
    handleNext = () => {
        const {data} = this.state;
        data.userID = null;
        data.completeDate = null;
        if (this.isValid()) this.props.updateSRData(data, true);
    };
    getNextButton = () => {
        const {el} = this;
        const {customer} = this.props.data;
        return el(
            "div",
            {
                style: {
                    display: "flex",
                    flexDirection: "row",
                    justifyContent: "flex-start",
                    alignItems: "flex-start",
                    width: "100%",
                    marginTop: 20,
                },
            },
            null,
            el(
                "button",
                {onClick: this.handleNext, className: "float-right"},
                "Add To Queue"
            ),
            !this.state.data.startWork && customer.hasServiceDesk
                ? el(
                "button",
                {onClick: this.handleStartWork, className: "ml-5 float-right"},
                "Start Work"
                )
                : null
        );
    };
    handleStartWork = () => {
        const {data} = this.state;
        data.startWork = true;
        data.notStartWorkReason = "";
        data.notStartWorkReasonTemplate = "";
        data.completeDate = null;
        data.userID = null;
        this.setState({data});
        if (this.isValid()) {
            this.props.updateSRData(data, true);
        } else {
            this.setState({data: {...this.state.data, startWork: false}})
        }
    };
    getProblemPriority = () => {
        const {el, setValue} = this;
        const {data} = this.state;
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "How serious is this issue?")),
            el(
                "td",
                null,
                el(
                    "select",
                    {
                        value: data.priority,
                        onChange: (event) => setValue("priority", event.target.value),
                        style: {width: 200},
                    },
                    el("option", {value: -1}, "Select Priority"),
                    el("option", {value: 1}, "It's affecting everybody (P1)"),
                    el(
                        "option",
                        {value: 2},
                        "It's affecting more than just one person but they can work (P2)"
                    ),
                    el("option", {value: 3}, "It's only affecting me (P3)"),
                    el("option", {value: 4}, "This is a change and not a fault (P4)"),
                    el("option", {value: 5}, "This is a project work (P5)")
                )
            )
        );
    };
    handleCheckListChange = (value) => {
        const {data, checkList} = this.state;
        const index = checkList.findIndex((c) => c.id == value);
        if (index > -1) {
            data.internalNotesAppend = checkList[index].content;
        } else data.internalNotesAppend = "";
    };
    getCheckList = () => {
        const {el, setValue} = this;
        const {data, checkList} = this.state;
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Is a checklist needed?")),
            el(
                "td",
                null,
                el(
                    "select",
                    {
                        value: data.internalNotesId,
                        onChange: (event) => this.handleCheckListChange(event.target.value),
                        style: {width: 200},
                    },
                    el("option", {key: "i_1", value: -1}, "Select Standard Text "),
                    checkList.map((s) =>
                        el("option", {key: "i" + s.id, value: s.id}, s.title)
                    )
                )
            )
        );
    };
    getContactsElement = () => {
        const {el} = this;
        const {data, contacts} = this.state;
        const contactsGroup = groupBy(contacts, "siteTitle");
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Contact ")),
            el(
                "td",
                null,
                el(
                    "select",
                    {
                        value: data.contactID,
                        onChange: (event) => this.handleContactSelect(event.target.value),
                        style: {width: 200},
                    },
                    el("option", {key: "i_1", value: -1}, "Please Select "),
                    contactsGroup.map((group, index) => {
                        return el(
                            "optgroup",
                            {key: group.groupName, label: group.groupName},
                            contactsGroup[index].items.map((item) =>
                                el(
                                    "option",
                                    {key: "i" + item.id, value: item.id},
                                    item.name + " " + (item.startMainContactStyle || "")
                                )
                            )
                        );
                    })
                )
            )
        );
    };
    handleContactSelect = (contactID) => {
        const {data, contacts} = this.state;
        data.contactID = contactID;
        let requireAuthorize = this.checkContactNeedAuthorize(contactID, contacts);

        this.setState({data, requireAuthorize});
    };
    checkContactNeedAuthorize = (contactID, contacts) => {
        const {data} = this.state;
        let requireAuthorize;
        //contactID
        const contact = contacts.find((item) => item.id == contactID);

        if (contact?.startMainContactStyle == "- Delegate") requireAuthorize = true;
        else {
            requireAuthorize = false;
            data.authorisedBy = "";
        }
        return requireAuthorize;
    };
    getAuthorizeByElement = () => {
        const {requireAuthorize, contacts, data} = this.state;
        const {el} = this;
        if (!requireAuthorize) return null;

        const contactSupervisor = groupBy(
            contacts.filter((contact) => {
                return contact.startMainContactStyle == "*" || contact.startMainContactStyle == "- Supervisor";
            }),
            "siteTitle"
        );

        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Authorised By	")),
            el(
                "td",
                null,
                el(
                    "select",
                    {
                        value: data.authorisedBy,
                        onChange: (event) =>
                            this.setValue("authorisedBy", event.target.value),
                        style: {width: 200},
                    },
                    el("option", {key: "i_1", value: ""}, "Please Select"),
                    contactSupervisor.map((group, index) => {
                        return el(
                            "optgroup",
                            {key: group.groupName, label: group.groupName},
                            contactSupervisor[index].items.map((item) =>
                                el(
                                    "option",
                                    {key: "i" + item.id, value: item.id},
                                    item.name + " " + (item.startMainContactStyle || "")
                                )
                            )
                        );
                    })
                )
            )
        );
    };

    getQueueElement = () => {
        const {el} = this;
        const {data} = this.state;
        let queueFiltered = SRQueues;
        if (this.props.data.customer.hasPrepay == "1")
            queueFiltered = SRQueues.filter((q) => q.id !== 6);
        return el(
            "tr",
            null,
            el("td", null, el("label", null, "Queue ")),
            el(
                "td",
                null,
                el(
                    "select",
                    {
                        value: data.queueNo,
                        onChange: (event) => this.setValue("queueNo", event.target.value),
                        style: {width: 200},
                    },
                    el("option", {key: "empty", value: -1}, "Select Queue "),
                    queueFiltered.map((s) =>
                        el("option", {key: "i" + s.id, value: s.id}, s.name)
                    )
                )
            )
        );
    };
    getNotStartReasonElement = () => {
        const {el} = this;
        const {_showModal, modalType, noWorkOptions, data} = this.state;
        if (!_showModal) return null;
        return el(StandardTextModal, {
            show: _showModal && modalType == this.modalType.notStartWorkReason,
            options: noWorkOptions,
            value: data.notStartWorkReason,
            title: "Please provide a reason why you aren't offering a first time fix",
            okTitle: "OK",
            onChange: this.handleNoWorkReason,
            onCancel: () => this.setState({_showModal: false})
        });
    };
    handleNoWorkReason = (value) => {
        const {data} = this.state;
        data.notStartWorkReason = value;
        this.setState({data, _showModal: false});
    };
    getNotFirstTimeFixReasonElement = () => {
        const {el} = this;
        const {_showModal, modalType, notFirstTimeFixOptions, data} = this.state;
        if (!_showModal) return null;
        return el(StandardTextModal, {
            show: _showModal && modalType == this.modalType.notFirstTimeFixReason && data.notFirstTimeFixReason == null,
            options: notFirstTimeFixOptions,
            value: data.notFirstTimeFixReason,
            title: "Reason for not attempting a First Time Fix",
            okTitle: "OK",
            onChange: this.handleNotFirstTimeFixReason,
            onCancel: () => this.setState({_showModal: false, modalType: null})
        });
    };
    handleNotFirstTimeFixReason = (value) => {
        if (value !== "") {
            const {data} = this.state;
            data.notFirstTimeFixReason = value;
            this.setState({data, _showModal: false, modalType: null});
            this.handleNext();
        }

    };

    isValid = () => {
        const {data, requireAuthorize} = this.state;

        const {currentUser, customer} = this.props.data;
        if (data.contactID == -1) {
            this.alert("Please select contact");
            return false;
        }
        if (requireAuthorize && data.authorisedBy == "") {
            this.alert("Please Select Authorize By");
            return false;
        }

        if (data.priority == -1) {
            this.alert("Please select priority");
            return false;
        }
        if (data.queueNo == -1) {
            this.alert("Please select queue");
            return false;
        }

        if (data.reason == "") {
            this.alert("Please select queue");
            return false;
        }
        if (currentUser.teamLevel == 1 && data.queueNo == TeamType.Helpdesk && (!data.notFirstTimeFixReason) && customer.hasServiceDesk) {

            const _showModal = true;
            const modalType = this.modalType.notFirstTimeFixReason;
            this.setState({modalType, _showModal});
            return false;
        }

        return true;
    };

    getDocumentsElement() {
        return (
            <div style={{position: 'relative'}}>
                <h3>Upload Customer Documents</h3>
                <ToolTip width="15"
                         title="Documents here are visible to the customer in their portal."
                >
                    <i className="fal fa-info-circle mt-5 pointer icon"/>
                </ToolTip>
                <DragAndDropUploaderComponent onFilesChanged={(files, type) => this.handleFileSelected(files, type)}>
                </DragAndDropUploaderComponent>
                {this.getSelectedFilesElement()}
            </div>
        )
    }

    getInternalDocumentsElement() {
        return (
            <div style={{position: 'relative'}}>
                <h3>Upload Internal Documents</h3>
                <ToolTip width="15"
                         title="Documents here are not visible to the customer in their portal."
                >
                    <i className="fal fa-info-circle mt-5 pointer icon"/>
                </ToolTip>
                <DragAndDropUploaderComponent onFilesChanged={(files, type) => this.handleInternalDocumentAdded(files, type)}>
                </DragAndDropUploaderComponent>
                {this.getSelectedInternalDocuments()}
            </div>
        )
    }

    handleFileSelected(files) {
        this.setState({data: {...this.state.data, uploadFiles: [...files]}});
    };

    handleInternalDocumentAdded(files) {
        this.setState({data: {...this.state.data, internalDocuments: [...files]}});
    }

    getSelectedFilesElement() {
        const {uploadFiles} = this.state.data;
        const {el} = this;
        return el(
            "table",
            {className: "table table-striped", style: {maxWidth: 400}},
            el(
                "tbody",
                null,
                uploadFiles.map((file) =>
                    el(
                        "tr",
                        {key: file.name + 'tr'},
                        el("td", {key: file.name + 'td'}, file.name),
                        el("td", {key: file.name + 'trash'}, el("i", {
                            className: "fal fa-trash pointer icon float-right",
                            title: "delete file",
                            onClick: () => this.deleteDocument(file)
                        }))
                    )
                )
            )
        );
    }

    getSelectedInternalDocuments() {
        const {internalDocuments} = this.state.data;
        const {el} = this;
        return el(
            "table",
            {className: "table table-striped", style: {maxWidth: 400}},
            el(
                "tbody",
                null,
                internalDocuments.map((file) =>
                    el(
                        "tr",
                        {key: file.name + 'tr'},
                        el("td", {key: file.name + 'td'}, file.name),
                        el("td", {key: file.name + 'trash'}, el("i", {
                            className: "fal fa-trash pointer icon float-right",
                            title: "delete file",
                            onClick: () => this.deleteInternalDocument(file)
                        }))
                    )
                )
            )
        );
    };

    deleteDocument = (file) => {
        let {data} = this.state;
        data.uploadFiles = data.uploadFiles.filter(f => f.name !== file.name);
        this.setState({data});
    }
    deleteInternalDocument = (file) => {
        let {data} = this.state;
        data.internalDocuments = data.internalDocuments.filter(f => f.name !== file.name);
        this.setState({data});
    }
    getElements = () => {
        const {
            el,
            getChkProblemBefore,
            getProblemPriority,
            getCheckList,
            getcriticalSRFlagBefore,
            gethideFromCustomerFlag,
            getmonitorSRFlag,
        } = this;
        return el(
            "table",
            null,
            el(
                "tbody",
                null,
                getChkProblemBefore(),
                getcriticalSRFlagBefore(),
                gethideFromCustomerFlag(),
                getmonitorSRFlag(),
                this.getAuthorizeByElement(),
                this.getContactsElement(),
                getProblemPriority(),
                getCheckList(),
                this.getQueueElement()
            )
        );
    };

    render() {
        return (
            <div style={{width: 800}}>
                {this.getElements()}
                {this.getConfirm()}
                {this.getAlert()}
                {this.getDocumentsElement()}
                {this.getInternalDocumentsElement()}
                {this.getNotStartReasonElement()}
                {this.getNotFirstTimeFixReasonElement()}
                {this.getNextButton()}
            </div>
        );
    }
}

export default LastStepComponent;
