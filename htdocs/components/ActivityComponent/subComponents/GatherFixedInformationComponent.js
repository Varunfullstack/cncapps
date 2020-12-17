import MainComponent from "../../shared/MainComponent.js";
import StandardTextModal from "../../Modals/StandardTextModal.js";
import APIActivity from "../../services/APIActivity.js";
import APICustomers from "../../services/APICustomers.js";
import APIStandardText from "../../services/APIStandardText.js";
import CNCCKEditor from "../../shared/CNCCKEditor.js";
import ToolTip from "../../shared/ToolTip.js";
import Toggle from "../../shared/Toggle.js";
import {groupBy, params} from "../../utils/utils.js";

import React from 'react';
import CustomerDocumentUploader from "./CustomerDocumentUploader";

class GatherFixedInformationComponent extends MainComponent {
    el = React.createElement;
    apiActivity = new APIActivity();
    apiCustomer = new APICustomers();
    apiStandardText = new APIStandardText();
    modalTypes = {partsUsed: "partsUsed", sales: "sales"};

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            activity: null,
            rootCauses: [],
            contracts: [],
            groupedContracts: [],
            initialActivity: null,
            data: {
                managementReviewFlag: false,
                problemID: null,
                contractCustomerItemID: null,
                rootCauseID: null,
                resolutionSummary: null
            },
            showModal: false,
            modalType: null,
            salesOptions: [],
            templateTypeId: null
        };
    }

    componentDidMount = async () => {
        const activity = await this.apiActivity.getCallActivityBasicInfo(
            params.get("callActivityID")
        );
        const [rootCauses, customerContracts, documents, initialActivity] = await Promise.all([
            this.apiActivity.getRootCauses(),
            this.apiCustomer.getCustomerContracts(
                activity.customerID,
                activity.contractCustomerItemID,
                activity.linkedSalesOrderID > 0
            ),
            this.apiActivity.getDocuments(activity.callActivityID, activity.problemID),
            this.apiActivity.getInitialActivity(activity.problemID)
        ]);

        const {data} = this.state;
        data.rootCauseID = activity.rootCauseID;
        data.contractCustomerItemID = activity.contractCustomerItemID || "99";
        if (!params.get("resolutionSummary")) {
            data.resolutionSummaryDefault = initialActivity?.reason;
            data.resolutionSummary = data.resolutionSummaryDefault;
        }
        this.setState({
            data,
            activity,
            rootCauses: rootCauses,
            contracts: customerContracts,
            groupedContracts: groupBy(customerContracts, "renewalType"),
            documents: documents,
            initialActivity: initialActivity,
        })
    }

    getHeader = () => {
        const {el} = this;
        const {activity} = this.state;
        return el(
            "div",
            {className: "flex-row flex-center"},
            el(ToolTip, {
                title: "SR",
                content: el("a", {
                    className: "fal fa-hashtag fa-2x icon pointer m-4",
                    href: `SRActivity.php?action=displayActivity&serviceRequestId=${activity?.problemID}`,
                    target: "_blank",
                }),
            }),
            el(ToolTip, {
                title: "History",
                content: el("a", {
                    className: "fal fa-history fa-2x icon pointer m-4",
                    href: `Activity.php?problemID=${activity?.problemID}&action=problemHistoryPopup&htmlFmt=popup`,
                    target: "_blank",
                }),
            })
        );
    };
    getDetails = () => {
        const {el} = this;
        const {activity, data, initialActivity} = this.state;
        return el(
            "div",
            {className: "contianer-round flex-row"},
            el(
                "table",
                {width: "100%"},
                el(
                    "tbody",
                    null,
                    el(
                        "tr",
                        null,
                        el("td", {className: "display-label"}, "Customer"),
                        el("td", null, activity?.customerName)
                    ),

                    el(
                        "tr",
                        null,
                        el("td", {className: "display-label"}, "Contract"),
                        el("td", null, this.getContracts())
                    ),

                    el(
                        "tr",
                        null,
                        el("td", {className: "display-label"}, "Root Cause"),
                        el("td", null, this.getRootCause())
                    ),

                    el(
                        "tr",
                        null,
                        el("td", {className: "display-label"}, "Flag For Management Review"),
                        el("td", null, el(Toggle, {
                            checked: data.managementReviewFlag,
                            onChange: () => this.setValue("managementReviewFlag", !data.managementReviewFlag)
                        }))
                    ),

                    el(
                        "tr",
                        null,
                        el("td", {className: "display-label "}, "Summary of Resolution"),
                        el("td", null, el(CNCCKEditor, {
                            name:'summaryOfResolution',
                            minCharCount: activity.problemHideFromCustomerFlag == 'N' ? 160 : -1,
                            disableClipboard: true,
                            value: initialActivity?.reason,
                            onChange: ($event) => this.setValue("resolutionSummary", $event.editor.getData()),
                            type: "inline",

                        }))
                    ),
                )
            )
        );
    };

    async updateContract(contractCustomerItemID) {
        if (contractCustomerItemID) {
            const {contracts, activity} = this.state;

            const isPrepay = contracts.find(x => x.contractCustomerItemID == contractCustomerItemID).prepayContract;

            if (isPrepay) {
                const response = await fetch(`Activity.php?action=checkPrepay&problemID=${activity.problemID}`).then(res => res.json());
                if (response.hiddenCharges) {
                    this.alert("There are hidden chargeable activities on this request, you must change these if you are going to use the PrePay contact");
                    return;
                }
            }
        }
        this.setValue("contractCustomerItemID", contractCustomerItemID);
    }

    getContracts = () => {
        const {el} = this;
        const {groupedContracts, data, activity} = this.state;

        return el(
            "select",
            {
                key: "contracts",
                required: true,
                value: data?.contractCustomerItemID,
                onChange: (event) => this.updateContract(event.target.value)
                ,
                style: {width: "100%"},
            },
            el("option", {key: "empty", value: 99}, "Please select"),
            el("option", {
                key: "tandm",
                value: ""
            }, "T&M" + (activity.linkedSalesOrderID ? " - Must be selected because this is linked to a Sales Order" : '')),
            groupedContracts?.map((t, index) =>
                el(
                    "optgroup",
                    {key: t.groupName, label: t.groupName},
                    groupedContracts[index].items.map((i) =>
                        el(
                            "option",
                            {
                                key: i.contractCustomerItemID,
                                disabled: i.isDisabled,
                                value: i.contractCustomerItemID,
                            },
                            i.contractDescription
                        )
                    )
                )
            )
        );
    };

    updateRootCause(rootCauseId) {
        this.setValue("rootCauseID", rootCauseId);

        if (rootCauseId) {
            const {rootCauses} = this.state;
            const foundRootCause = rootCauses.find(x => x.id == rootCauseId);
            if (foundRootCause && foundRootCause.fixedText) {
                this.setState({
                    initialActivity: {
                        ...this.state.initialActivity,
                        reason: atob(foundRootCause.fixedText)
                    }
                })
            }
        }

    }

    getRootCause = () => {
        const {el} = this;
        const {rootCauses, data} = this.state;

        return el(
            "select",
            {
                key: "rootCauses",
                style: {maxWidth: 200, width: "100%"},
                required: true,
                value: data?.rootCauseID || "",
                onChange: (event) => this.updateRootCause(event.target.value),
            },
            el("option", {key: "empty", value: ""}, "Not known"),
            rootCauses?.map((t) =>
                el("option", {key: t.id, value: t.id}, t.description)
            )
        );
    };

    async deleteDocument(id) {
        const {documents, activity} = this.state;
        if (await this.confirm('Are you sure you want to remove this document?')) {
            await this.apiActivity.deleteDocument(activity.callActivityID, id);
            this.setState({documents: documents.filter(d => d.id !== id)});
        }
    }

    getDocuments = () => {
        const {documents, activity} = this.state;
        return <CustomerDocumentUploader
            onDeleteDocument={(id) => this.deleteDocument(id)}
            onFilesUploaded={() => this.handleDocumentsUploads()}
            serviceRequestId={activity.problemID}
            activityId={activity.callActivityID}
            documents={documents}
        />
    };
    handleDocumentsUploads = async () => {
        const {activity} = this.state;
        const documents = await this.apiActivity.getDocuments(activity.callActivityID, activity.problemID);
        this.setState({documents});
    }
    getActions = () => {
        const {el} = this;
        return el('div', {className: "flex-row"},
            el('button', {onClick: () => this.handleSave()}, "Fixed"),
            el('button', {
                onClick: () => this.setState({showModal: true, modalType: this.modalTypes.partsUsed}),
                className: "btn-info"
            }, "Parts Used"),
            el('button', {
                onClick: () => this.setState({showModal: true, modalType: this.modalTypes.sales}),
                className: "btn-info"
            }, "Sales Request")
        )
    };
    getPartsUsed = () => {
        const {showModal, modalType} = this.state;
        const {el} = this;

        return el(StandardTextModal,
            {
                options: [],
                show: showModal && modalType == this.modalTypes.partsUsed,
                title: "Parts Used",
                okTitle: "Send",
                onChange: this.handlePartsUsedReason,
                onCancel: () => this.hideModal('')
            });
    }
    handlePartsUsedReason = async (value) => {
        const {activity} = this.state;
        var object = {
            message: value,
            callActivityID: activity.callActivityID,
        };
        await this.apiActivity.sendPartsUsed(object);
        this.hideModal();
    }
    getSalesRequest = () => {
        const {showModal, modalType} = this.state;
        let {salesOptions} = this.state;
        const {el} = this;
        if (salesOptions.length == 0) {
            this.apiStandardText.getOptionsByType("Sales Request")
                .then(salesOptions => {
                    this.setState({salesOptions});
                })
        }

        return el(StandardTextModal,
            {
                options: salesOptions,
                show: showModal && modalType == this.modalTypes.sales,
                title: "Sales Request",
                okTitle: "Send",
                onChange: this.handleSalesReason,
                onTypeChange: this.handleSalesType,
                onCancel: () => this.hideModal('')
            });
    }
    handleSalesType = (typeId) => {

        this.setState({templateTypeId: typeId});
    }
    handleSalesReason = async (value) => {
        const {activity, templateTypeId} = this.state;
        const payload = new FormData();
        payload.append("message", value);
        payload.append("type", templateTypeId);
        await this.apiActivity.sendSalesRequest(
            activity.customerID,
            activity.problemID,
            payload
        );
        this.hideModal();
    }
    hideModal = () => {
        this.setState({showModal: false, modalType: null, templateTypeId: null})
    }
    handleSave = () => {
        const {activity, data} = this.state;

        if (data.contractCustomerItemID == "99") {
            this.alert("Please select contract");
            return;
        }
        if (!data.rootCauseID) {
            this.alert("Please select Root Cause");
            return;
        }
        if (!data.resolutionSummary) {
            this.alert("You must enter more text in the summary information");
            return;
        }
        if (activity.problemHideFromCustomerFlag == 'N' && data.resolutionSummary.length < 160) {
            this.alert("The resolution summary must have at least 160 characters");
            return;
        }
        data.problemID = activity.problemID;
        this.apiActivity.saveFixedInformation(data).then(result => {
            if (result.status) {
                if (data.managementReviewFlag) {
                    window.location = `Activity.php?problemID=${data.problemID}&action=gatherManagementReviewDetails`
                } else {
                    window.location = `CurrentActivityReport.php`
                }
            }
        });
    }

    render() {
        const {el} = this;
        const {activity} = this.state;
        return activity?.callActivityID ? el(
            "div",
            {style: {width: "90%"}},
            this.getAlert(),
            this.getHeader(),
            this.getConfirm(),
            this.getDetails(),
            this.getDocuments(),
            this.getActions(),
            this.getPartsUsed(),
            this.getSalesRequest()
        ) : null;
    }
}

export default GatherFixedInformationComponent;
