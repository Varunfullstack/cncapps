import APIActivity from "../../services/APIActivity.js";
import APICallactType from "../../services/APICallactType.js";
import {getContactElementName, groupBy, isEmptyTime, params, pick} from "../../utils/utils.js";
import ToolTip from "../../shared/ToolTip.js";
import APICustomers from "../../services/APICustomers.js";
import APIUser from "../../services/APIUser.js";
import CountDownTimer from "../../shared/CountDownTimer.js";
import MainComponent from "../../shared/MainComponent.js";
import APIStandardText from "../../services/APIStandardText.js";
import React, {Fragment} from 'react';
import moment from "moment";
import StandardTextModal from "../../Modals/StandardTextModal";
import {TeamType} from "../../utils/utils";
import CNCCKEditor from "../../shared/CNCCKEditor";
import Modal from "../../shared/Modal/modal";
import Toggle from "../../shared/Toggle";
import {ActivityHeaderComponent} from "./ActivityHeaderComponent";
import CustomerDocumentUploader from "./CustomerDocumentUploader";
import {InternalDocumentsComponent} from "./InternalDocumentsComponent";
import AssetListSelectorComponent from "../../shared/AssetListSelectorComponent/AssetListSelectorComponent";
import EditorFieldComponent from "../../shared/EditorField/EditorFieldComponent";
import {TimeBudgetElement} from "./TimeBudgetElement";
import {LinkServiceRequestOrder} from "./LinkserviceRequestOrder.js";
import {ActivityType} from "../../shared/ActivityTypes";
import {InternalNotes} from "./InternalNotesComponent";
import {TaskListComponent} from "./TaskListComponent";
import AdditionalChargeRequestModal from "./Modals/AdditionalTimeRequestModal";
import ExistingAdditionalChargeableWorkRequestModal from "./Modals/ExistingAdditionalChargeableWorkRequestModal";
import CallbackModal from "../../shared/CallbackModal/CallBackModal";
import {TEMPlATE_TYPES, TemplateModal} from "./Modals/TemplateModal";

// noinspection EqualityComparisonWithCoercionJS
const hiddenAndCustomerNoteAlertMessage = `Customer note must be empty when the activity or entire SR is hidden.`;


class ActivityEditComponent extends MainComponent {
    el = React.createElement;
    api = new APIActivity();
    apiCustomer = new APICustomers();
    apiUser = new APIUser();
    apiCallactType = new APICallactType();
    apiStandardText = new APIStandardText();
    activityStatus = {
        Fixed: "Fixed",
        CustomerAction: "CustomerAction",
        CncAction: "CncAction",
        Escalate: "Escalate",
        Update: "Update"
    };

    additionalTimeRequestResolve;
    additionalTimeRequestReject;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            customerContactActivityDurationThresholdValue: null,
            remoteSupportActivityDurationThresholdValue: null,
            activityDurationWarned: false,
            _activityLoaded: false,
            contacts: [],
            sites: [],
            priorities: [],
            currentContact: null,
            originalContact: null,
            currentUser: null,
            allowLeaving: false,
            showAdditionalTimeRequestModal: false,
            data: {
                curValue: "",
                reasonTemplate: "",
                reason: "",
                internalNotes: [],
                date: "",
                alarmDate: "",
                alarmTime: "",
                contactNotes: "",
                completeDate: "",
                techNotes: "",
                projects: [],
                submitAsOvertime: 0,
                cncNextAction: "",
                cncNextActionTemplate: "",
                customerNotes: "",
                customerNotesTemplate: "",
                priorityChangeReason: "",
                emptyAssetReason: "",
                emptyAssetReasonNotify: false,
                Inbound: null
            },
            currentActivity: "",
            templateType: null,
            contactNotes: "",
            callActTypes: [],
            notSDManagerActivityTypes: [],
            users: [],
            contracts: [],
            priorityReasons: [],
            filters: {
                showTravel: false,
                showOperationalTasks: false,
                showServerGuardUpdates: false,
                criticalSR: false,
                monitorSR: false,
            },
            showSalesOrder: false,
            showCallbackModal: false,
        };
    }

    handleCallbackClose = () => {
        this.setState({showCallbackModal: false});
    }
    getCallbackModal = () => {
        const {showCallbackModal, data} = this.state;
        if (!showCallbackModal)
            return null;
        return <CallbackModal key="modal"
                              show={showCallbackModal}
                              onClose={this.handleCallbackClose}
                              contactID={data.contactID}
                              customerID={data.customerId}
                              problemID={data.problemID}
                              contactName={data.contactName}
        >
        </CallbackModal>
    }

    componentDidMount() {
        this.loadCallActivity(params.get("callActivityID"));

        window.addEventListener('beforeunload', (e) => {
            if (!this.state.allowLeaving) {
                e.preventDefault(); // If you prevent default behavior in Mozilla Firefox prompt will always be shown
                // Chrome requires returnValue to be set
                e.returnValue = '';
            }
        })

        // lodaing lookups
        Promise.all([
            this.apiCallactType.getAll(),
            this.apiUser.getActiveUsers(),
            this.api.getPriorities(),
            this.api.getRootCauses(),
            this.apiUser.getCurrentUser(),
            this.apiStandardText.getOptionsByType("Priority Change Reason")
        ]).then(async ([activityTypes, activeUsers, priorities, rootCauses, currentUser, priorityChangeReasonStandardTextItems]) => {
            const notSDManagerActivityTypes = activityTypes.filter(c => c.visibleInSRFlag === 'Y');

            this.setState({
                callActTypes: activityTypes,
                notSDManagerActivityTypes,
                users: activeUsers,
                priorities,
                rootCauses,
                currentUser,
                priorityReasons: priorityChangeReasonStandardTextItems
            });
        });
    }


    componentWillUnmount() {
    }

    //------------API
    loadCallActivity(callActivityID) {
        const {filters} = this.state;

        this.api.getCallActivityDetails(callActivityID, filters).then((res) => {
            filters.monitorSR = res.monitoringFlag == "1";
            filters.criticalSR = res.criticalFlag == "1";
            res.reasonTemplate = res.reason;
            res.cncNextActionTemplate = res.cncNextAction;
            res.customerNotesTemplate = res.customerNotes;
            res.callActTypeIDOld = res.callActTypeID;
            res.orignalPriority = res.priority;
            const session = this.getSessionActivity(res.callActivityID);
            if (session) {
                res.customerNotes = session.customerNotesTemplate || res.customerNotes;
                res.cncNextAction = session.cncNextActionTemplate || res.cncNextAction;
                res.reason = session.reasonTemplate || res.reason;
                res.customerNotesTemplate = session.customerNotesTemplate || res.customerNotesTemplate;
                res.cncNextActionTemplate = session.cncNextActionTemplate || res.cncNextActionTemplate;
                res.reasonTemplate = session.reasonTemplate || res.reasonTemplate;
            }
            Promise.all([
                this.api.getCustomerContactActivityDurationThresholdValue(),
                this.api.getRemoteSupportActivityDurationThresholdValue(),
                this.apiCustomer.getCustomerContacts(res.customerId),
                this.apiCustomer.getCustomerSites(res.customerId),
                this.api
                    .getCustomerContracts(
                        res.customerId,
                        res.contractCustomerItemID,
                        res.linkedSalesOrderID > 0
                    )
                    .then((contractsResponse) => {
                        return groupBy(contractsResponse, "renewalType");
                    })
            ]).then(([customerContactActivityDurationThresholdValue, remoteSupportActivityDurationThresholdValue, contacts, sites, contracts]) => {
                const currentContact = contacts.find((c) => c.id == res.contactID);

                contacts = contacts.filter(x => x.id === res.contactID || (x.supportLevel && x.supportLevel != 'furlough' && x.active));

                this.setState({
                    customerContactActivityDurationThresholdValue,
                    remoteSupportActivityDurationThresholdValue,
                    activityDurationWarned: false,
                    filters,
                    data: res,
                    currentActivity: res.callActivityID,
                    contacts,
                    sites,
                    contracts,
                    _activityLoaded: true,
                    currentContact,
                    originalContact: currentContact
                }, () => setTimeout(() => this.checkContactNotesAlert(), 2000));
            });
        });
    }

    // update>
    async updateActivity(autoSave = false) {
        const data = {...this.state.data};
        this.setState({allowLeaving: true});
        data.reason = data.reasonTemplate;
        data.cncNextAction = data.cncNextActionTemplate;
        data.customerNotes = data.customerNotesTemplate;
        data.priority = this.state.priorities.find((p) => p.name == data.priority).id;

        delete data.activities;
        delete data.onSiteActivities;
        const finalData = pick(data, [
            "callActivityID",
            "alarmDate",
            "alarmTime",
            "callActTypeID",
            "curValue",
            "contactID",
            "date",
            "siteNo",
            "startTime",
            "endTime",
            "userID",
            "contactNotes",
            "techNotes",
            "reason",
            "nextStatus",
            "escalationReason",
            "customerNotes",
            "cncNextAction",
            "priority",
            "priorityChangeReason",
            "assetName",
            "assetTitle",
            "rootCauseID",
            "contractCustomerItemID",
            "hideFromCustomerFlag",
            "submitAsOvertime",
            "emptyAssetReason",
            "completeDate",
            "Inbound",
            "automateMachineID"
        ]);
        this.api
            .updateActivity(finalData)
            .then((response) => {
                //return; // update>
                if (response.error) this.alert(response.error);
                else {
                    if (!autoSave) {
                        if (response.redirectTo) document.location = response.redirectTo;
                        else
                            document.location = `SRActivity.php?action=displayActivity&callActivityID=${data.callActivityID}`;
                    }
                }
            })
            .catch((ex) => {
                this.alert(ex.error);
            });
    }

    async isValid(data) {

        if (!this.isHiddenFromCustomer(data)) {
            const hasGrammaticalErrors = await this.editorHasProblems();
            if (hasGrammaticalErrors) {
                return false;
            }
        }
        const callActType = this.state.callActTypes.find((c) => c.id == data.callActTypeID);
        if (!callActType) {
            this.alert("Please select activity type");
            return false;
        }

        if (callActType.activityNotesRequired === 'Y' && !data.reasonTemplate) {
            this.alert("Please Enter Activity Notes");
            return false;
        }

        if (!data.callActTypeID) {
            this.alert("Please select Activity Type");
            return false;
        }
        if (data.siteNo == "-1") {
            this.alert("Please select Customer Site");
            return false;
        }
        if (!data.contactID) {
            this.alert("Please select Contact");
            return false;
        }

        data.callActType = callActType;

        if (callActType.description.indexOf("FOC") == -1 &&
            data.siteMaxTravelHours == -1) {
            this.alert("Travel hours need entering for this site");
            return false;
        }

        if (data.originalContact !== data.currentContact && !data.supportLevel) {
            this.alert("Not a nominated support contact");
            return false;
        }
        if (data.curValueFlag == "Y" && data.curValue == 0) {
            this.alert("Please enter value");
            return false;
        } else {
            if (
                callActType &&
                callActType.reqReasonFlag == "Y" &&
                !data.reason.trim()
            ) {
                this.alert("Please Enter Activity Notes");
                return false;
            }
            if (data.contractCustomerItemID && data.projectId) {
                this.alert("Project work must be logged under T&M");
                return false;
            }
            if (data.callActTypeID !== 51) {
                const firstActivity = data.activities[0];
                const startDate =
                    moment(data.date).format("YYYY-MM-DD") + " " + data.startTime;
                const firstActivityDate =
                    moment(firstActivity.date).format("YYYY-MM-DD") +
                    " " +
                    firstActivity.startTime;
                if (moment(startDate) < moment(firstActivityDate)) {
                    this.alert("Date/time must be after Initial activity");
                    return false;
                }
            }

            if (callActType.requireCheckFlag === 'N' && callActType.onSiteFlag === 'N' && !data.endTime) {
                data.endTime = moment().format('HH:mm');
            }

            if (data.endTime) {
                const duration = moment.duration(
                    moment(data.date + " " + data.endTime).diff(
                        moment(data.date + " " + data.startTime)
                    )
                );
                const durationHours = duration.asHours();
                if (data.endTime < data.startTime) {
                    this.alert("End time must be after start time!");
                    return false;
                }
                if (data.callActType.id == 11 && durationHours > this.state.customerContactActivityDurationThresholdValue) {
                    const response = await this.confirm(`This Customer Contact is over ${this.state.customerContactActivityDurationThresholdValue} hours, are you sure this is the correct activity type?`);
                    if (!response) {
                        return false;
                    }
                }

                if (data.callActType.id == 8 && durationHours > this.state.remoteSupportActivityDurationThresholdValue) {
                    if (!await this.confirm(`This Remote Support is over ${this.state.remoteSupportActivityDurationThresholdValue} hours, did you mean to put in these times for the activity?`)) {
                        return false;
                    }
                }
            }
        }

        if (data.nextStatus === this.activityStatus.Escalate) {
            if (
                ["I", "F", "C"].indexOf(data.problemStatus) === -1 &&
                !data.escalationReason
            ) {
                this.alert("Please provide an escalate reason");
                return false;
            }
        }
        if (callActType && !isEmptyTime(data.startTime) && !isEmptyTime(data.endTime)) {
            const startDt = moment(data.date + " " + data.startTime);
            const endDt = moment(data.date + " " + data.endTime);
            const actTypeMinTime = callActType.minMinutesAllowed;
            const timeDiff = endDt.diff(startDt, 'm');
            if (timeDiff < actTypeMinTime) {
                this.alert(`The minimum number of minutes for ${callActType.description} is ${actTypeMinTime}, you must either log more time or choose a different activity type`)
                return false;

            }
        }

        if (!data.assetName && !data.emptyAssetReason) {
            this.alert("Please select an asset or a reason");
            return false;
        }

        return true;
    }

    setValue = (label, value) => {
        const autoUpdateFields = [
            'cncNextActionTemplate',
            'reasonTemplate',
            'customerNotesTemplate',
        ]

        const {data} = this.state;
        data[label] = value;
        this.setState({data}, () => {
            if (autoUpdateFields.indexOf(label) > -1) {
                this.saveToSessionStorage();
            }
        });
    };
    //-----------------Template
    getProjectsElement = () => {
        const {data} = this.state;
        const {el} = this;
        if (data?.projects?.length > 0) {
            return el(
                "div",
                {
                    style: {
                        display: "flex",
                        flexDirection: "row",
                        alignItems: "center",
                        marginTop: -20,
                    },
                    key: "projects"
                },
                el("h3", {className: "mr-5"}, "Projects "),
                data.projects.map((p) =>
                    el(
                        "a",
                        {key: p.projectID, href: p.editUrl, className: "link-round mr-4", target: '_blank'},
                        p.description
                    )
                )
            );
        } else return null;
    };

    showCallbackModal = () => {
        this.setState({showCallbackModal: true});
    };

    getActions = () => {
        const {el} = this;
        const {data, currentUser} = this.state;
        return el(
            "div",
            {
                style: {
                    display: "flex",
                    flexDirection: "row",
                    justifyContent: "center",
                    alignItems: "center",
                    width: 930,
                },
            },
            data?.problemStatus !== "C" ? <ToolTip
                title="Call Back"
                content={<a
                    className="fal fa-phone fa-2x m-5 pointer icon"
                    onClick={this.showCallbackModal}
                />
                }
            /> : null,
            this.getEmptyAction(),
            el(ToolTip, {
                title: "History",
                content: el("a", {
                    className: "fal fa-history fa-2x m-5 pointer icon",
                    href: `Activity.php?action=problemHistoryPopup&problemID=${data?.problemID}&htmlFmt=popup`,
                    target: "_blank",
                }),
            }),
            el(ToolTip, {
                title: "Passwords",
                content: el("a", {
                    className: "fal fa-unlock-alt fa-2x m-5 pointer icon",
                    href: `Password.php?action=list&customerID=${data?.customerId}`,
                    target: "_blank",
                }),
            }),
            this.getEmptyAction(),
            data?.linkedSalesOrderID
                ? el(ToolTip, {
                    title: "Sales Order",
                    content: el("a", {
                        className: "fal fa-tag fa-2x m-5 pointer icon",
                        href: `SalesOrder.php?action=displaySalesOrder&ordheadID=${data?.linkedSalesOrderID}`,
                        target: '_blank'
                    }),
                })
                : null,
            data?.linkedSalesOrderID
                ? el(ToolTip, {
                    title: "Unlink Sales Order",
                    content: el("a", {
                        className: "fal fa-unlink fa-2x m-5 pointer icon",
                        onClick: () =>
                            this.handleUnlink(
                                data?.callActivityID,
                                data?.linkedSalesOrderID,
                                data?.problemID
                            ),
                    }),
                })
                : null,
            !data?.linkedSalesOrderID
                ? el(ToolTip, {
                    title: "Sales Order",
                    content: el("a", {
                        className: "fal fa-tag fa-2x m-5 pointer icon",
                        href: "#",
                        onClick: () => this.handleSalesOrder(data?.callActivityID, data?.problemID),
                    }),
                })
                : null,
            el(ToolTip, {
                title: "Renewal Information",
                content: el("a", {
                    className: "fal fa-tasks fa-2x m-5 pointer icon",
                    href: `RenewalReport.php?action=produceReport&customerID=${data?.customerId}`,
                    target: "_blank",
                }),
            }),
            <ToolTip title="Generate Password"
                     content={<a className="fal fa-magic fa-2x m-5 pointer icon"
                                 onClick={this.handleGeneratePassword}
                     />}
            />,
            el(ToolTip, {
                title: "Contracts",
                content: el("a", {
                    className: "fal fa-file-contract fa-2x m-5 pointer icon",
                    href: `Activity.php?action=contractListPopup&customerID=${data?.customerId}`,
                    target: "_blank",
                }),
            }),
            this.getEmptyAction(),
            el(ToolTip, {
                title: "Contact SR History",
                content: el("a", {
                    className: "fal fa-id-card fa-2x m-5 pointer icon",
                    onClick: () => this.handleContactSRHistory(data?.contactID),
                }),
            }),
            el(ToolTip, {
                title: "Third Party Contacts",
                content: el("a", {
                    className: "fal fa-users fa-2x m-5 pointer icon",
                    href: `ThirdPartyContact.php?action=list&customerID=${data?.customerId}`,
                    target: "_blank",
                }),
            }),

            this.getEmptyAction(),
            (<TimeBudgetElement
                currentUserTeamId={currentUser?.teamID}
                hdRemainMinutes={data?.hdRemainMinutes}
                esRemainMinutes={data?.esRemainMinutes}
                imRemainMinutes={data?.imRemainMinutes}
                projectRemainMinutes={data?.projectRemainMinutes}
                onExtraTimeRequest={() => this.handleExtraTime(data)}
            />),
            this.getEmptyAction(),
            data.hdRemainMinutes ?
                el(ToolTip, {
                    title: "Countdown Timer",
                    content: el(CountDownTimer, {
                        seconds: (this.getTimeBudget() * 60 + 60),
                        hideSeconds: true,
                        hideMinutesTitle: true
                    })
                }) : null,
            this.renderChargeableWorkIcon()
        );
    };

    renderChargeableWorkIcon = () => {
        const {data} = this.state;
        if (!data || data.problemHideFromCustomerFlag == 'Y') {
            return '';
        }
        let title = "Additional Charges";
        let icon = "fa-envelope-open-dollar";
        let handler = this.handleRequestCustomerApproval;
        if (data.chargeableWorkRequestId) {
            title = "Chargeable request in process";
            icon = "fa-hands-usd";
            handler = this.handleCurrentChargeableWorkRequest
        }
        return (
            <ToolTip title={title}
                     content={<a className={`fal ${icon}  fa-2x m-5 pointer icon`}
                                 onClick={handler}
                     />}
            />
        )
    }
    handleCurrentChargeableWorkRequest = async () => {
        const shouldReload = await this.showAdditionalTimeRequestModal();
        if (shouldReload) {
            const {currentActivity} = this.state;
            await this.loadCallActivity(currentActivity);
        }
    }

    showAdditionalTimeRequestModal = async () => {
        return new Promise((resolve, reject) => {
            this.setState({showAdditionalTimeRequestModal: true});
            this.additionalTimeRequestResolve = resolve;
            this.additionalTimeRequestReject = reject;
        })
    }

    handleRequestCustomerApproval = async () => {
        const {problemID: serviceRequestId} = this.state.data;
        try {
            let {
                reason,
                selectedContactId,
                timeRequested,
                selectedAdditionalChargeId
            } = await this.showAdditionalTimeRequestModal();
            try {
                await this.api.addAdditionalTimeRequest(serviceRequestId, reason, timeRequested, selectedContactId, selectedAdditionalChargeId);
                const {currentActivity} = this.state;
                await this.loadCallActivity(currentActivity);
                let defaultAlertText = 'Request Sent';
                if (selectedAdditionalChargeId) {
                    defaultAlertText = 'Saved successfully';
                }
                this.alert(defaultAlertText);
            } catch (error) {
                this.alert(error);
            }
        } catch (rejectedPromise) {

        }
    }

    getEmptyAction() {
        return this.el("div", {style: {width: 20}});
    }

    handleExtraTime = async (data) => {

        const reason = await this.prompt(
            "Please provide your reason to request additional time",
            600,
            data.cncNextAction, false, 50
        );
        if (!reason) {
            return;
        }
        await this.api.activityRequestAdditionalTime(
            data.callActivityID,
            reason
        );
        this.alert("Additional time has been requested");
    };


    getActionsButtons = () => {
        const {data, currentUser} = this.state;

        const renderActionButtons = () => {
            if (data?.callActType !== 59) {
                return <Fragment>
                    <button onClick={() => this.setNextStatus(this.activityStatus.CncAction)}>CNC Action</button>
                    <button onClick={() => this.setNextStatus(this.activityStatus.Fixed)}>Fixed</button>
                    <button onClick={() => this.setNextStatus(this.activityStatus.CustomerAction)}>On Hold</button>
                </Fragment>
            }
            return "";
        }

        const renderTimeInput = () => {
            if (!data.callActivityID) {
                return ''
            }


            return (<input className="form-control"
                           style={{width:75}}
                           type="time"
                           key="alarmTime"
                           value={data?.alarmTime || ""}
                           onChange={($event) => this.setValue("alarmTime", $event.target.value)}
            />)
        }
        const renderUpdateCancelButtons = () => {
            const isInitialActivityAndServiceRequestNotStarted = data?.callActTypeID === ActivityType.INITIAL && data?.problemStatus === 'I';
            const isCurrentUserSDManagerOrServiceRequestQueueManager = currentUser?.isSDManager || currentUser?.serviceRequestQueueManager;
            if (isInitialActivityAndServiceRequestNotStarted || isCurrentUserSDManagerOrServiceRequestQueueManager) {
                return <Fragment>
                    <button onClick={() => this.setNextStatus("Update")}
                    >Update
                    </button>
                </Fragment>
            }
        }

        return <div style={{
            display: "flex",
            flexDirection: "row",
            justifyContent: "center",
            alignItems: "center",
            width: 1100,
        }}
        >
            {renderActionButtons()}
            <label className="m-2">
                Future Action
            </label>
            <input 
            type="date" className="form-control" style={{width:150}}
                   value={data?.alarmDate || ""}
                   onChange={(event) => this.setValue("alarmDate", event.target.value)}
            />
            {renderTimeInput()}
            <button onClick={() => this.handleTemplateDisplay(TEMPlATE_TYPES.changeRequest)}
                    className="btn-info"
            > Change Request
            </button>
            <button onClick={() => this.handleTemplateDisplay(TEMPlATE_TYPES.salesRequest)}
                    className="btn-info"
            > Sales Request
            </button>
            <button onClick={() => this.handleTemplateDisplay(TEMPlATE_TYPES.partsUsed)}
                    className="btn-info"
            > Parts Used
            </button>
            {renderUpdateCancelButtons()}
            <button onClick={() => this.handleCancel(data)}
            >
                Cancel
            </button>
        </div>

    }
    handleCancel = async (data) => {
        let text = "Are you sure you want to cancel?";
        let willDelete = false;
        if (params.get("isFollow")) {
            text = "This will delete the activity, please confirm.";
            willDelete = true;
        }

        if (await this.confirm(text)) {
            this.setState({allowLeaving: true});
            if (willDelete)
                this.api.deleteActivity(data.callActivityID).then(() => {
                    document.location = `SRActivity.php?action=displayActivity&serviceRequestId=${data.problemID}`;
                })
            else
                document.location = `SRActivity.php?action=displayActivity&callActivityID=${data.callActivityID}`;
        }
    };

    saveToSessionStorage() {
        const {data} = this.state;
        const activityEdit = {
            id: data.callActivityID,
            cncNextActionTemplate: data.cncNextActionTemplate,
            reasonTemplate: data.reasonTemplate,
            customerNotesTemplate: data.customerNotesTemplate,
        }
        let activities = this.getSessionNotes().filter(a => a.id !== data.callActivityID);
        activities.push(activityEdit);
        sessionStorage.setItem("activityEdit", JSON.stringify(activities));
    }

    getSessionNotes = () => {
        sessionStorage.getItem("activityEdit");
        return JSON.parse(sessionStorage.getItem("activityEdit")) || [];
    }
    getSessionActivity = (id) => {
        return this.getSessionNotes().find(a => a.id == id);
    }
    setNextStatus = async (status, autoSave = false) => {
        const {data, callActTypes} = this.state;
        data.nextStatus = status;
        const type = callActTypes.find(c => c.id == data.callActTypeID);

        if (!await this.isValid(data)) {
            return;
        }

        switch (status) {
            case this.activityStatus.CncAction: {
                //Field Name] is required for [Activity Type] when the next action is [Update type]
                const cncValid = await this.checkCncAction(data, type);
                if (!cncValid)
                    return;
                break;
            }

            case this.activityStatus.CustomerAction: {
                //holding
                //Field Name] is required for [Activity Type] when the next action is [Update type]
                const holdValid = await this.checkOnHold(data, type);
                if (!holdValid)
                    return;
                break;
            }
            case this.activityStatus.Fixed:
                const hasPendingCallbacks = await this.api.checkServiceRequestPendingCallbacks(data.problemID);
                if (hasPendingCallbacks) {
                    return this.alert('This request has an outstanding call back and that must be completed / cancelled before marking this as Fixed');
                }
                if (!await this.confirm("Are you sure this SR is fixed?")) return false;
                //return;
                break;
            case this.activityStatus.Escalate:
                if (data.problemStatus == "P") {
                    const escalationReason = await this.prompt(
                        "Please provide your reason for escalating this SR(Required)"
                    );
                    if (!escalationReason) {
                        return false;
                    }
                    data.escalationReason = escalationReason;
                }
                break;
            case this.activityStatus.Update:
                //Field Name] is required for [Activity Type] when the next action is [Update type]
                if (!await this.checkCncAction(data, type) && !await this.checkOnHold(data, type))
                    return;
                break;
        }
        this.setState({data}, () => this.updateActivity(autoSave));
    };
    checkCncAction = async (data, type) => {

        if (this.checkNextCNCActionRequired(type, data)) {
            this.alert(`CNC Next Action is required for ${type.description} when the next action is CNC Action`)
            return false;
        }
        if (this.checkOptionalCNCActionAndEmptyDescription(type, data)) {
            if (!await this.confirm(`Are you sure you don't want to put an entry for CNC Next Action?`))
                return false;
        }
        if (!this.isHiddenFromCustomer(data)) {
            if (this.checkcustomerNotesRequired(type, data)) {
                this.alert(`Customer Summary are required for ${type.description} when the next action is CNC Action`)
                return false;
            }
            if (this.checkcustomerNotesOptionalAndEmptyDescription(type, data)) {
                if (!await this.confirm(`Are you sure you don't want to put an entry for Customer Summary?`))
                    return false;
            }
        }
        if (this.checkNotHiddenFromCustomerAndcustomerNoteset(data)) {
            this.alert(hiddenAndCustomerNoteAlertMessage);
            return false;
        }
        return true;
    }

    checkNotHiddenFromCustomerAndcustomerNoteset(data) {
        return this.isHiddenFromCustomer(data) && data.customerNotesTemplate;
    }

    checkcustomerNotesOptionalAndEmptyDescription(type, data) {
        return type && type.catRequireCustomerNoteCNCAction == 2 && !data.customerNotesTemplate;
    }

    checkcustomerNotesRequired(type, data) {
        return type && type.catRequireCustomerNoteCNCAction == 1 && !data.customerNotesTemplate;
    }

    isHiddenFromCustomer(data) {
        return data.hideFromCustomerFlag == 'Y' || data.problemHideFromCustomerFlag == 'Y';
    }

    checkOptionalCNCActionAndEmptyDescription(type, data) {
        return type && type.catRequireCNCNextActionCNCAction == 2 && !data.cncNextActionTemplate;
    }

    checkNextCNCActionRequired(type, data) {
        return type && type.catRequireCNCNextActionCNCAction == 1 && !data.cncNextActionTemplate;
    }

    checkOnHold = async (data, type) => {

        if (this.checkNextCNCActionRequiredOnHold(type, data)) {
            this.alert(`CNC Next Action is required for ${type.description} when the next action is On Hold`)
            return false;
        }
        if (this.checkNextCNCActionOptionalAndDesctiptionEmptyOnHold(type, data)) {
            if (!await this.confirm(`Are you sure you don't want to put an entry for CNC Next Action?`))
                return false;

        }

        if (!this.isHiddenFromCustomer(data)) {
            if (this.checkcustomerNotesRequiredOnHold(type, data)) {
                this.alert(`Customer Summary are required for ${type.description} when the next action is On Hold`)
                return false;
            }
            if (this.checkcustomerNotesOptionalAndEmptyDescriptionOnHold(type, data)) {
                if (!await this.confirm(`Are you sure you don't want to put an entry for Customer Summary?`))
                    return false;
            }
        }
        if (this.checkNotHiddenFromCustomerAndcustomerNoteset(data)) {
            this.alert(hiddenAndCustomerNoteAlertMessage);
            return false;
        }

        if (!data.alarmTime) {
            this.alert("Please provide a future date & time");
            return false;
        }

        if (!data.alarmDate) {
            this.alert("Please provide a future date & time");
            return false;
        }

        const dateMoment = moment(`${data.alarmDate} ${data.alarmTime}`, 'YYYY-MM-DD HH:mm');

        if (
            !dateMoment.isValid() ||
            dateMoment.isSameOrBefore(moment(), "minute")
        ) {
            this.alert("Please provide a future date & time");
            return false;
        }
        return true;
    }

    checkcustomerNotesOptionalAndEmptyDescriptionOnHold(type, data) {
        return type && type.catRequireCustomerNoteOnHold == 2 && !data.customerNotesTemplate;
    }

    checkcustomerNotesRequiredOnHold(type, data) {
        return type && type.catRequireCustomerNoteOnHold == 1 && !data.customerNotesTemplate;
    }

    checkNextCNCActionOptionalAndDesctiptionEmptyOnHold(type, data) {
        return type && type.catRequireCNCNextActionOnHold == 2 && !data.cncNextActionTemplate;
    }

    checkNextCNCActionRequiredOnHold(type, data) {
        return type && type.catRequireCNCNextActionOnHold == 1 && !data.cncNextActionTemplate;
    }


    handleSalesOrder = async (callActivityID, serviceRequestId) => {
        this.setState({showSalesOrder: true});

    };
    handleUnlink = async (callActivityID, linkedSalesOrderID, serviceRequestId) => {
        const res = await this.confirm(
            `Are you sure you want to unlink this request to Sales Order ${linkedSalesOrderID}`
        );
        if (res) {
            await this.api.unlinkSalesOrder(serviceRequestId);
            this.loadCallActivity(callActivityID);
        }
    };

    handleContactSRHistory(contactID) {
        window.open(
            `Activity.php?action=displayServiceRequestForContactPopup&contactID=${contactID}&htmlFmt=popup`,
            "reason",
            "scrollbars=yes,resizable=yes,height=400,width=1225,copyhistory=no, menubar=0"
        );
    }

    getElement = (key, label, text, bgcolor) => {
        const {el} = this;
        return [
            el(
                "td",
                {
                    key,
                    style: { backgroundcolor: bgcolor, textAlign: "right"},
                },
                label
                    ? el(
                    "label",
                    {style: {width: 80, color: "#992211", whiteSpace: "nowrap"}},
                    label
                    )
                    : null
            ),
            el(
                "td",
                {
                    key: key + 2,
                    style: { backgroundcolor: bgcolor, textAlign: "left"},
                },
                el(
                    "label",
                    {style: {textAlign: "left", whiteSpace: "nowrap", marginLeft: 15}},
                    text
                )
            ),
        ];
    };
    getElementControl = (key, label, content, bgcolor) => {
        const {el} = this;
        return [
            el(
                "td",
                {
                    key,
                    style: { backgroundcolor: bgcolor, textAlign: "right"},
                },
                label
                    ? el(
                    "label",
                    {style: {width: 80, color: "#992211", whiteSpace: "nowrap"}},
                    label
                    )
                    : null
            ),
            el(
                "td",
                {
                    key: key + 2,
                    style: { backgroundcolor: bgcolor, textAlign: "left", paddingRight: 15},
                },
                content
            ),
        ];
    };
    getDetialsElement = (data) => {
        const {el} = this;
        return el(
            "div",
            null,
            el(
                "label",
                {
                    style: {
                        display: "block",
                        color: "#992211",
                        marginTop: 10,
                        marginBottom: 5,
                    },
                },
                "Details"
            ),
            el("div", {dangerouslySetInnerHTML: {__html: data?.reason}})
        );
    };

    getTypeElement = () => {
        const {el} = this;
        const {data, callActTypes, notSDManagerActivityTypes, currentUser} = this.state;
        const selectedActivityType = callActTypes.find((t) => t.id == data.callActTypeID);
        const isEnabled = currentUser?.isSDManager || (!currentUser?.isSDManager && selectedActivityType && selectedActivityType.visibleInSRFlag === 'Y') || params.get("isFollow");
        let activityTypesToShow = notSDManagerActivityTypes;
        if (!isEnabled || currentUser?.isSDManager) {
            activityTypesToShow = callActTypes;
        }

        return this.getElementControl(
            "Type",
            "Type",
            el(
                "div", {style: {display: "flex", flexDirection: "row"}},
                el(
                    "select",
                    {
                        className:"form-control",
                        disabled: !isEnabled,
                        required: true,
                        value: data?.callActTypeID || "",
                        onChange: (event) => this.handleTypeChange(event.target.value),
                        
                    },
                    el("option", {key: "empty", value: ""}, "Please select"),
                    activityTypesToShow.map((t) =>
                        el("option", {key: t.id, value: t.id}, t.description)
                    )
                ),
                this.getInboundIcon()
            )
        );
    };

    handleTypeChange = (value) => {
        this.setValue("callActTypeID", value);
        if (value == '11')
            this.setState({showInboundOutboundModal: true});
        else
            this.setValue("Inbound", null);
    }
    getInboundOutBoundModal = () => {
        const {data} = this.state;
        const Inbound = data.Inbound == null ? false : data.Inbound;
        const Outbound = data.Inbound == null ? false : !data.Inbound;
        return <Modal
            width={300}
            show={this.state.showInboundOutboundModal}
            title="Select contact type"
            footer={<div key="footerActions">
                <button onClick={() => this.setState({showInboundOutboundModal: false})}>OK</button>
                <button onClick={() => this.setState({showInboundOutboundModal: false, Inbound: null})}>Cancel</button>
            </div>}
        >
            <div style={{display: 'flex', flexDirection: 'row', justifyContent: "space-between"}}>
                <div>
                    <label className="mr-2">Inbound</label>
                    <Toggle checked={Inbound} onChange={() => this.setValue("Inbound", true)}></Toggle>
                </div>
                <div>
                    <label className="mr-2">Outbound</label>
                    <Toggle checked={Outbound} onChange={() => this.setValue("Inbound", false)}></Toggle>
                </div>
            </div>
        </Modal>
    }
    getInboundIcon = () => {
        const {data} = this.state;
        switch (data.Inbound) {
            case true:
                return (
                    <ToolTip title="Inbound Contact" width={15}>
                        <i onClick={() =>
                            this.setState({showInboundOutboundModal: true})
                        } className="fal fa-sign-in pointer icon"></i>
                    </ToolTip>
                );
            case false:
                return (
                    <ToolTip title="Outbound Contact" width={15}>
                        <i onClick={() =>
                            this.setState({showInboundOutboundModal: true})
                        } className="fal fa-sign-out  pointer icon"></i>

                    </ToolTip>
                );
            default:
                return null;
        }
    }
    getContactsElement = () => {
        const {el} = this;
        const {data, contacts, currentContact} = this.state;
        const contactsGroup = groupBy(contacts, "siteTitle");
        return el('div', {style: {display: "flex", flexDirection: "row", border: 0, marginRight: -6, padding: 0}}, el(
            "select",
            {
                className:"form-control" ,
                key: "contacts",
                value: data.contactID,
                onChange: (event) => this.handleContactChange(event.target.value),                
            },
            el("option", {key: "empty", value: ""}, "Please Select "),
            contactsGroup.map((group, index) => {
                return el(
                    "optgroup",
                    {key: group.groupName + index, label: group.groupName},
                    contactsGroup[index].items.map((item) =>
                        el(
                            "option",
                            {key: "i" + item.id, value: item.id},
                            getContactElementName(item)
                        )
                    )
                );
            })
            ),
            currentContact?.notes ? el(ToolTip, {
                title: currentContact.notes,
                content: el('i', {className: "fal fa-2x fa-file-alt color-gray2 pointer"})
            }) : null
        );
    };
    handleContactChange = (id) => {
        const {data, contacts} = this.state;
        const currentContact = contacts.find((c) => c.id == id);
        data.contactID = id;
        data.contactName = `${currentContact.firstName} ${currentContact.lastName}`
        data.contactPhone = currentContact.phone;
        data.contactMobilePhone = currentContact.mobilePhone;
        data.contactEmail = currentContact.email;
        data.contactNotes = currentContact.notes;

        this.setState({data, currentContact});
    };

    getSites = () => {
        const {el} = this;
        const {data, sites} = this.state;

        return el(
            "select",
            {
                key: "sites",
                required: true,
                value: data?.siteNo,
                onChange: (event) => this.setValue("siteNo", event.target.value),
                className:"form-control",
            },
            el("option", {key: "empty", value: "-1"}, "Please select"),
            sites?.map((t) => el("option", {key: t.id, value: t.id}, t.title))
        );
    };
    getTimeElement = () => {
        const {data} = this.state;
        const renderStartTimeInput = () => {
            if (!data.callActivityID) {
                return '';
            }
            return <input className="form-control" style={{width:90}} type="time"
                          key="startTime"
                          disabled={data?.isInitalDisabled}
                          value={data?.startTime || ""}
                          onChange={($event) => this.setValue("startTime", $event.target.value)}
            />
        }
        const renderEndTimeInput = () => {
            if (!data.callActivityID) {
                return '';
            }
            return <input type="time" className="form-control" style={{width:90}}
                          key="endTime"
                          disabled={data?.isInitalDisabled}
                          value={data?.endTime || ""}
                          onChange={($event) => this.setValue("endTime", $event.target.value)}
            />
        }

        return <div style={{
            display: "flex",
            flexDirection: "row",
            justifyContent: "space-between",
            alignItems: "center",
        }}
        >
            {renderStartTimeInput()}
            <label className="m-2"
                   style={{color: "#992211", whiteSpace: "nowrap"}}
            >To</label>
            {renderEndTimeInput()}
            <span onClick={() => {
                this.setValue("endTime", moment().format('HH:mm'))
            }}
            >
                <i className="fal fa-clock"/>
            </span>
        </div>
    };
    getPriority = () => {
        const {data, priorities} = this.state;
        return (
            <select key="priorities"
                    disabled={!data.canChangePriorityFlag}
                    required={true}
                    value={data?.priority}
                    onChange={(event) => this.setValue("priority", event.target.value)}
                    className="form-control"
            >
                <option key="empty"
                        value={null}
                >
                    Please select
                </option>
                {
                    priorities?.map((t) => <option key={t.id}
                                                   value={t.name}
                    >{t.name}</option>)
                }
            </select>
        );
    };
    getUsersElement = () => {
        const {data, users} = this.state;

        return (
            <select
                key={"users"}
                required={true}
                value={data?.userID}
                onChange={(event) => this.setValue("userID", event.target.value)}
                className="form-control"
            >

                <option
                    key={"empty"}
                    value={null}
                >
                    Please select
                </option>
                {
                    users?.map((t) => <option
                            key={t.id}
                            value={t.id}
                        >{t.name}</option>
                    )
                }
            </select>
        );
    };
    getContracts = () => {
        const {data, contracts} = this.state;

        return (
            <select

                key={"contracts"}
                required={true}
                disabled={!data?.changeSRContractsFlag}
                value={data?.contractCustomerItemID || ""}
                onChange={(event) => this.setValue("contractCustomerItemID", event.target.value)}
                className="form-control"
            >
                <option
                    key={"empty"}
                    value={99}
                >Please select
                </option>
                <option
                    key={"tandm"}
                    value={""}
                >T&M
                </option>
                {
                    contracts?.map((t, index) => (
                            <optgroup
                                key={t.groupName}
                                label={t.groupName}
                            >
                                {

                                    contracts[index].items.map((i) =>

                                        <option key={i.contractCustomerItemID}
                                                disabled={i.isDisabled}
                                                value={i.contractCustomerItemID}
                                        >
                                            {i.contractDescription}
                                        </option>
                                    )
                                }
                            </optgroup>
                        )
                    )
                }
            </select>
        );
    };
    getRootCause = () => {

        const {data, rootCauses} = this.state;

        return (
            <select
                className="form-control"
                key={"rootCauses"}
                disabled={!data.canChangePriorityFlag}
                style={{  }}
                value={data?.rootCauseID || ""}
                onChange={(event) => this.setValue("rootCauseID", event.target.value)}
            >
                <option
                    key={"empty"}
                    value={""}
                >
                    Not known
                </option>

                {
                    rootCauses?.map((t) =>
                        <option
                            key={t.id}
                            value={t.id}
                        >
                            {t.description}
                        </option>
                    )
                }
            </select>
        );
    };
    getContentElement = () => {
        const {data} = this.state;
        const {el} = this;

        return el(
            "table",
            {className: "activities-edit-container"},
            el(
                "tbody",
                {},
                el(
                    "tr",
                    null,
                    this.getElementControl("Site", "Site", this.getSites()),
                    data?.authorisedBy
                        ? this.getElement(
                        "Authorisedby",
                        "Authorised by ",
                        data?.authorisedBy
                        )
                        : this.getElement("emp1"),
                    this.getTypeElement(),
                ),
                el(
                    "tr",
                    null,
                    this.getElementControl("Contact", "Contact",
                        this.getContactsElement(),
                    ),
                    data?.problemHideFromCustomerFlag == "N" ? el(
                        "td",
                        {style: {textAlign: "right"}},
                        el("label", {className: "label"}, "Hide From Customer"),
                    ) : null,
                    data?.problemHideFromCustomerFlag == "Y" ? el(
                        "td",
                        {style: {textAlign: "right"}, colSpan: 2},
                        <label style= {{color: "red", fontSize: 14,fontWeight:"bold"}}>Entire SR hidden from customer</label>                        
                    ) : null,
                    data?.problemHideFromCustomerFlag == "N" ? el(
                        "td",
                        {key: "td2"},
                        el(Toggle, {
                            disabled: data?.problemHideFromCustomerFlag == "Y",
                            checked: data?.hideFromCustomerFlag == "Y",
                            onChange: ($event) => {
                                this.setValue(
                                    "hideFromCustomerFlag",
                                    $event ? "Y" : "N"
                                )
                            }
                        }),
                    ) : null,
                    this.getElementControl(
                        "Date",
                        "Date",
                        el("input", {
                            className:"form-control",
                            type: "date",
                            disabled: data?.isInitalDisabled,
                            value: data?.date,
                            onChange: (event) => this.setValue("date", event.target.value),
                             
                        })
                    ),
                ),
                el(
                    "tr",
                    null,
                    this.getElementControl("Priority", "Priority", this.getPriority()),
                    el(
                        "td", {style: {textAlign: "right"}},
                        el("label", {className: "label"}, "Submit as Overtime"),
                    ),
                    el(
                        "td", null,
                        el(Toggle, {
                            checked: data?.submitAsOvertime,
                            onChange: () =>
                                this.setValue("submitAsOvertime", !data?.submitAsOvertime),
                        })
                    ),
                    this.getElementControl("Timefrom", "Time from", this.getTimeElement()),
                ),
                el(
                    "tr",
                    null,
                    this.getElementControl("Contract", "Contract", this.getContracts()),
                    this.getElementControl(
                        "CompletedOn",
                        "Completed On",
                        el("input", {
                            disabled: data?.problemStatus !== "F",
                            title: "Date when this request should be set to completed",
                            type: "date",
                            value: data?.completeDate || "",
                            className:"form-control" ,                            
                            onChange: (event) => this.setValue("completeDate", event.target.value),

                        })
                    ),
                    this.getElementControl("User", "User", this.getUsersElement()),
                ),
                el(
                    "tr",
                    null,
                    this.getElementControl("RootCause", "Root Cause", this.getRootCause()),
                    this.getElementControl(
                        "Top-UpValue",
                        "Top-Up Value",
                        el("input", {
                            required: true,
                            min: 0,
                            type: "number",
                            value: data?.curValue,
                            className:"form-control",
                            onChange: (event) =>
                                this.setValue("curValue", event.target.value),
                        })
                    ),
                    this.getElementControl("Asset", "Asset", this.getAssetsElement())
                ),
            )
        );
    };


    getTemplateModal = () => {
        const {
            templateType,
            data: {customerId, problemID: serviceRequestId},
            currentActivity: activityId,
        } = this.state;

        if (!templateType) {
            return '';
        }
        return (
            <TemplateModal key={templateType}
                           templateType={templateType}
                           onClose={() => this.setState({templateType: null})}
                           customerId={customerId}
                           serviceRequestId={serviceRequestId}
                           activityId={activityId}
            />
        )
    };
    handleTemplateDisplay = async (type) => {
        this.setState({templateType: type});
    };

    getActivityNotes() {
        const {el} = this;
        const {data} = this.state;

        return el(
            "div",
            {className: "flex-row"},
            el('div', {className: "round-container flex-2 mr-5 flex-column"},
                el('div', {className: "flex-row", style: {flex: "0 1 auto"}},
                    el(
                        "label",
                        {className: "label m-5 mr-2", style: {display: "block"}},
                        "Activity Notes"
                    ),
                    el(ToolTip, {
                        width: 5,
                        title: "These notes will be available for the customer to see in the portal but will not be sent in an email.",
                        content: el("i", {className: "fal fa-info-circle mt-5 pointer icon"})
                    })
                ),
                this.state._activityLoaded
                    ?
                    <EditorFieldComponent name="reason"
                                          value={data?.reason || ""}
                                          onChange={(value) => {
                                              this.setValue("reasonTemplate", value)
                                          }}
                    />
                    : null
            ),
            el('div', {className: "round-container flex-1"},
                el('div', {className: "flex-row", style: {flex: "0 1 auto"}},
                    el(
                        "label",
                        {className: "label m-5 mr-2", style: {display: "block"}},
                        "CNC Next Action"
                    ),
                    el(ToolTip, {
                        width: 5,
                        title: "These are internal notes only and not visible to the customer. These are per activity.",
                        content: el("i", {className: "fal fa-info-circle mt-5 pointer icon"})
                    })
                ),
                this.state._activityLoaded
                    ?
                    <EditorFieldComponent name="cncNextAction"
                                          value={data?.cncNextAction || ""}
                                          onChange={(value) => {
                                              this.setValue("cncNextActionTemplate", value)
                                          }}
                    />
                    : null
            )
        );
    }

    getCustomerNotes() {
        const {
            el
        }

            = this;
        const
            {
                data
            }
                = this.state;
        return el(
            "div",
            {className: "round-container flex-column flex-1", style: {padding: 5}},
            el('div', {className: "flex-row"},
                el(
                    "label",
                    {className: "label m-5 mr-2", style: {display: "block"}},
                    "Customer Summary"
                ),
                el(ToolTip, {
                    width: 5,
                    title: "This information will be sent to the customer in an email unless the entire Service Request is hidden.",
                    content: el("i", {className: "fal fa-info-circle mt-5 pointer icon"})
                })
            ),
            this.state._activityLoaded
                ?
                <EditorFieldComponent name="customerNotes"
                                      value={data?.customerNotes || ""}
                                      onChange={(value) => this.setValue("customerNotesTemplate", value)}
                />
                : null
        );
    }

    getTaskList() {
        const {data} = this.state;
        if (!data) {
            return '';
        }

        return (
            <TaskListComponent serviceRequestId={data.problemID}/>
        );
    }

    getTimeBudget = () => {
        const {data, currentUser} = this.state;
        switch (currentUser?.teamID) {
            case TeamType.Helpdesk:
                return data?.hdRemainMinutes;
            case TeamType.Escalations:
                return data?.esRemainMinutes;
            case TeamType.SmallProjects:
                return data?.imRemainMinutes;
            case TeamType.Projects:
                return data?.projectRemainMinutes;
            default:
                return 0;
        }
    }
    checkContactNotesAlert = () => {
        const {data, currentUser} = this.state;
        const key = "contactNotesAlert";
        // get from local storage
        let jsonString = localStorage.getItem(key);
        let userObj = {userID: currentUser.id, contactID: data.contactID, notes: data.contactNotes};
        let alertObject;
        const today = moment().format("YYYY-MM-DD");
        if (data.contactNotes && data.contactNotes !== "") {
            if (!jsonString) {
                this.alert(data.contactNotes, 500, "Contact Note");
                alertObject = {
                    date: today,
                    items: [
                        userObj
                    ]
                }
            } else {
                alertObject = JSON.parse(jsonString);
                if (alertObject.date !== today)// clear if not today
                {
                    alertObject = {
                        date: today,
                        items: [
                            userObj
                        ]
                    }
                } else {
                    //check if he seen this notes today;
                    const found = alertObject.items.find(i => i.userID == currentUser.id
                        && i.contactID == data.contactID
                        && i.notes == data.contactNotes);
                    if (!found) {
                        alertObject.items.push(userObj);
                        this.alert(userObj.notes);
                    }
                }
            }
            localStorage.setItem(key, JSON.stringify(alertObject));
        }
    }
    getPriorityChangeReason = () => {
        const {data, priorityReasons} = this.state;
        const {el} = this;
        return el(StandardTextModal,
            {
                options: priorityReasons,
                value: data.priorityChangeReason,
                show: data.orignalPriority !== data.priority,
                title: "Priority change reason - Customer will be notified",
                okTitle: "OK",
                onChange: this.handlePriorityTemplateChange,
                onCancel: () => this.handlePriorityTemplateChange('')
            });
    }

    handlePriorityTemplateChange = (value) => {
        const {data} = this.state;
        if (value !== "" && value !== undefined) {
            const payload = {
                callActivityID: data.callActivityID,
                priorityChangeReason: value,
                priority: this.state.priorities.filter(
                    (p) => p.name == data.priority
                )[0].id
            }
            this.api.changeProblemPriority(payload).then(result => {
                if (result) {
                    data.priorityChangeReason = null;
                    data.orignalPriority = data.priority;
                    this.setState({data});
                } else {
                    data.priority = data.orignalPriority;
                    this.setState({data});
                    this.alert("Priority didn't changed ")
                }
            })
        } else {
            data.priority = data.orignalPriority;
            this.setState({data});
            this.alert("You must provide the reason of priority change");
        }
    }
    getAssetsElement = () => {
        const {data} = this.state;
        if (!data || !data.customerId) {
            return '';
        }
        return <div className="flex-row">
            <AssetListSelectorComponent
            emptyAssetReason={data.emptyAssetReason}
            assetName={data.assetName}
            assetTitle={data.assetTitle}
            customerId={data.customerId}
            unsupportedCustomerAsset={data.unsupportedCustomerAsset}
            onChange={value => this.handleAssetSelect(value)}
            showUnsupportedWhileSelected={true}
        />
            {data.automateMachineID?<i className="fal fa-cog ml-5 pointer fa-2x" 
                            onClick={()=>window.open(`https://serverguard.cnc-ltd.co.uk/automate/computer/${data.automateMachineID}/normal-tiles`,"_target")}
                            
                            ></i>:null}
            </div>
    }
    handleAssetSelect = (value) => {
        const {data} = this.state;
        data.assetName = "";
        data.assetTitle = "";
        data.emptyAssetReason = "";        
        if (value) {
            if (value.isAsset) {
                data.assetName = value.name;
                data.assetTitle = value.name + " " + value.LastUsername + " " + value.BiosVer;
                data.automateMachineID=value.ComputerID;
            } else {
                data.emptyAssetReason = value.template;
            }
        }
        this.setState({data});
    };
    handleSalesOrderClose = () => {
        this.setState({showSalesOrder: false});
        this.loadCallActivity(this.state.currentActivity);
    }

    handleAdditionalTimeRequestModalOnChange = (data) => {
        if (this.additionalTimeRequestResolve) {
            this.additionalTimeRequestResolve(data);
        }
        this.hideAdditionalTimeRequestModal();
    }

    hideAdditionalTimeRequestModal = () => {
        this.setState({showAdditionalTimeRequestModal: false})
    }

    getAdditionalChargeModal = () => {
        const {data, showAdditionalTimeRequestModal} = this.state;

        if (!data || !showAdditionalTimeRequestModal) {
            return '';
        }
        if (data.chargeableWorkRequestId) {
            return (
                <ExistingAdditionalChargeableWorkRequestModal
                    key="existingAdditionalChargeRequest"
                    chargeableWorkRequestId={data.chargeableWorkRequestId}
                    show={showAdditionalTimeRequestModal}
                    onClose={this.handleExistingAdditionalChargeableWorkRequestModalOnClose}
                />
            )
        }
        return (
            <AdditionalChargeRequestModal key="additionalTimeRequestModal"
                                          show={showAdditionalTimeRequestModal}
                                          onChange={this.handleAdditionalTimeRequestModalOnChange}
                                          onCancel={this.handleAdditionalTimeRequestModalOnCancel}
                                          serviceRequestData={data}
            />
        )
    }
    handleExistingAdditionalChargeableWorkRequestModalOnClose = (closingValue) => {
        if (this.additionalTimeRequestResolve) {
            this.additionalTimeRequestResolve(closingValue);
        }
        this.hideAdditionalTimeRequestModal();
    };
    handleGeneratePassword = () => {
        window.open("Password.php?action=generate&htmlFmt=popup", 'reason', 'scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0');
    }

    render() {
        const {data, showSalesOrder, _activityLoaded} = this.state;

        if (!_activityLoaded) {
            return <div className="loading"/>
        }

        return (
            <div style={{width: "90%"}}>
                {this.getAdditionalChargeModal()}
                {this.getInboundOutBoundModal()}
                {this.getAlert()}
                {this.getConfirm()}
                {this.getPrompt()}
                {this.getPriorityChangeReason()}
                {this.getProjectsElement()}
                {this.getCallbackModal()}
                <ActivityHeaderComponent serviceRequestData={data}/>
                <div className="activities-edit-container">
                    {this.getActions()}
                </div>
                <div className="activities-edit-container">
                    {this.getActionsButtons()}
                </div>
                {this.getContentElement()}
                {this.getActivityNotes()}
                {this.getCustomerNotes()}
                <InternalNotes serviceRequestId={data.problemID}/>
                {this.getTaskList()}
                <CustomerDocumentUploader serviceRequestId={data?.problemID}/>
                <InternalDocumentsComponent serviceRequestId={data?.problemID}/>
                {this.getTemplateModal()}
                {showSalesOrder ? <LinkServiceRequestOrder serviceRequestID={data.problemID}
                                                           customerId={data?.customerId}
                                                           show={showSalesOrder}
                                                           onClose={this.handleSalesOrderClose}
                /> : null}
            </div>
        );
    }

    handleAdditionalTimeRequestModalOnCancel = () => {
        if (this.additionalTimeRequestReject) {
            this.additionalTimeRequestReject();
        }
        this.hideAdditionalTimeRequestModal();
    };
}

export default ActivityEditComponent;
