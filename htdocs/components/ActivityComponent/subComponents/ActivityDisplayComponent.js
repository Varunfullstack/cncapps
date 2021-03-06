import APIActivity from "../../services/APIActivity.js";
import {Chars, dateFormatExcludeNull, maxLength, padEnd, params} from "../../utils/utils.js";
import ToolTip from "../../shared/ToolTip.js";
import MainComponent from "../../shared/MainComponent.js";
import * as React from 'react';
import {TimeBudgetElement} from "./TimeBudgetElement";
import ActivityFollowOn from "../../Modals/ActivityFollowOn";
import CNCCKEditor from "../../shared/CNCCKEditor";
import Toggle from "../../shared/Toggle";
import {InternalDocumentsComponent} from "./InternalDocumentsComponent";
import CustomerDocumentUploader from "./CustomerDocumentUploader";
import Modal from "../../shared/Modal/modal";
import Table from "../../shared/table/table";
import {LinkServiceRequestOrder} from "./LinkserviceRequestOrder.js";
import moment from "moment";
import {InternalNotes} from "./InternalNotesComponent";
import {TaskListComponent} from "./TaskListComponent";
import AdditionalChargeRequestModal from "./Modals/AdditionalTimeRequestModal";
import ExistingAdditionalChargeableWorkRequestModal from "./Modals/ExistingAdditionalChargeableWorkRequestModal";
import CallbackModal from "../../shared/CallbackModal/CallbackModal";
import {format} from "../../../../stencil/cncapps-components/src/utils/utils";
import * as PropTypes from "prop-types";
import {TEMPlATE_TYPES, TemplateModal} from "./Modals/TemplateModal";
import {ActivityHeaderComponent} from './ActivityHeaderComponent';

// noinspection EqualityComparisonWithCoercionJS
const emptyAssetReasonCharactersToShow = 30;


class ActivityDisplayComponent extends MainComponent {
    api = new APIActivity();
    additionalTimeRequestResolve;
    additionalTimeRequestReject;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            currentUser: {
                globalExpenseApprover: 0,
                isExpenseApprover: 0,
                isSDManager: false
            },
            data: null,
            _loadedData: false,
            currentActivity: null,
            templateType: null,
            selectedChangeRequestTemplateId: null,
            showSalesOrder: false,
            filters: {
                showTravel: false,
                showOperationalTasks: false,
                showServerGuardUpdates: false,
                criticalSR: false,
                monitorSR: false,
                holdForQA: false
            },
            showAdditionalTimeRequestModal: false,
            showCallbackModal: false,
        }
    }

    componentDidMount() {
        this.loadFilterSession();
        if (params.get('serviceRequestId')) {
            this.loadLastActivityInServiceRequest(params.get('serviceRequestId'));
        } else {
            setTimeout(() => this.loadCallActivity(params.get('callActivityID')), 10);
        }
    }

    loadCallActivity = async (callActivityID) => {
        const {filters} = this.state;
        const typeId = await this.api.getCallActivityTypeId(callActivityID);

        switch (typeId) {
            case 60: //Operational Task
                filters.showOperationalTasks = true;
                break;
            case 55: //server update
                filters.showServerGuardUpdates = true;
                break;
            case 22: // enginner travel
                filters.showTravel = true;
                break;
        }
        const currentUser = await this.api.getCurrentUser();
        const res = await this.api.getCallActivityDetails(callActivityID, filters);

        res.activities = res.activities.map(a => {
            a.date = a.dateEngineer.split('-')[0];
            a.enginner = a.dateEngineer.split('-')[1];
            return a;
        })
        filters.monitorSR = res.monitoringFlag == "1";
        filters.criticalSR = res.criticalFlag == "1";
        filters.holdForQA = res.holdForQA;
        this.setState({filters, data: res, currentActivity: +res.callActivityID, currentUser, _loadedData: true});
        return '';

    }
    getProjectsElement = () => {
        const {data} = this.state;
        if (!(data && data.projects.length > 0)) {
            return null;
        }
        return (
            <div style={{display: "flex", flexDirection: "row", alignItems: "center", marginTop: -20}}>
                <h3 className="mr-5">
                    Projects
                </h3>
                {
                    data.projects.map(p => (
                            <a key={p.projectID}
                               href={p.editUrl}
                               target='_blank'
                               className="link-round mr-4"
                            >{p.description}
                            </a>
                        )
                    )
                }
            </div>
        );
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

    getActions = () => {
        const {data, currentUser} = this.state;
        return <div>
            {
                data?.problemStatus !== "C" && data?.problemStatus !== "F" ?
                    <div style={{marginBottom: -40}}>
                        <ToolTip title="SR currently assigned to"
                                 width={150}
                        >
                            <div style={{display: "flex", alignItems: "center"}}>
                                <i className="fal fa-user-hard-hat fa-2x m-5 pointer icon"></i>
                                <label>
                                    {
                                        data?.requestEngineerName
                                    }
                                </label>
                            </div>
                        </ToolTip>
                    </div> : null
            }
            <div
                className="activities-container"
                style={{display: "flex", flexDirection: "row", justifyContent: "center", alignItems: "center"}}
            >
                {data?.problemStatus !== "C" ? <ToolTip
                    title="Call Back"
                    content={<a
                        className="fal fa-phone fa-2x m-5 pointer icon"
                        onClick={data?.problemStatus === 'F' ? this.showAlertModal : this.showCallbackModal}
                    />
                    }
                /> : null
                }
                {this.getSpacer()}
                {
                    data?.problemStatus !== "C" ? <ToolTip
                            title="Follow On"
                            content={<i className="fal fa-play fa-2x m-5 pointer icon"
                                        onClick={this.handleFollowOn}
                            />}
                        />
                        : null
                }
                <ToolTip
                    title="History"
                    content={<a
                        className="fal fa-history fa-2x m-5 pointer icon"
                        href={`Activity.php?action=problemHistoryPopup&problemID=${data?.problemID}&htmlFmt=popup`}
                        target="_blank"
                    />
                    }
                />

                <ToolTip
                    title="Passwords"
                    content={<a
                        className="fal fa-unlock-alt fa-2x m-5 pointer icon"
                        href={`Password.php?action=list&customerID=${data?.customerId}`}
                        target="_blank"
                    />
                    }
                />

                {this.getSpacer()}
                {data?.canEdit == 'ALL_GOOD' ? <ToolTip
                    title="Edit"
                    content={<a
                        className="fal fa-edit fa-2x m-5 pointer icon"
                        href={`SRActivity.php?action=editActivity&callActivityID=${data?.callActivityID}`}
                    />
                    }
                /> : null
                }


                {data?.canEdit !== 'ALL_GOOD' ? <ToolTip
                    title={data?.canEdit}
                    content={<i className="fal fa-edit fa-2x m-5 pointer icon-disable"/>
                    }
                /> : null}

                {(data?.canDelete) ? <ToolTip
                        title={data?.activities.length == 1 ? "Delete Request" : "Delete Activity"}
                        content={<i
                            className="fal fa-trash-alt fa-2x m-5 pointer icon"
                            onClick={() => this.handleDelete(data)}
                        />}
                    />
                    : null
                }
                {this.getSpacer()}
                {data?.linkedSalesOrderID ? <ToolTip
                        title="Sales Order"
                        content={<a
                            className="fal fa-tag fa-2x m-5 pointer icon"
                            href={`SalesOrder.php?action=displaySalesOrder&ordheadID=${data?.linkedSalesOrderID}`}
                            target="_blank"
                        />}
                    />
                    : null}
                {!data?.linkedSalesOrderID ? <ToolTip
                    title="Sales Order"
                    content={<a
                        className="fal fa-tag fa-2x m-5 pointer icon"
                        onClick={() => this.handleSalesOrder(data?.callActivityID, data?.problemID)}
                    />
                    }
                /> : null}
                {data?.linkedSalesOrderID ? <ToolTip
                    title="Unlink Sales order"
                    content={<a
                        className="fal fa-unlink fa-2x m-5 pointer icon"
                        onClick={() => this.handleUnlink(data?.linkedSalesOrderID, data?.problemID, data?.callActivityID)}
                    />
                    }
                /> : null}
                <ToolTip
                    title="Renewal Information"
                    content={<a
                        className="fal fa-tasks fa-2x m-5 pointer icon"
                        href={`RenewalReport.php?action=produceReport&customerID=${data?.customerId}`}
                        target="_blank"
                    />
                    }
                />


                <ToolTip title="Generate Password"
                         content={<a className="fal fa-magic fa-2x m-5 pointer icon"
                                     onClick={this.handleGeneratePassword}
                         />}
                />
                <ToolTip
                    title="Contracts"
                    content={<a
                        className="fal fa-file-contract fa-2x m-5 pointer icon"
                        href={`Activity.php?action=contractListPopup&customerID=${data?.customerId}`}
                        target="_blank"
                    />
                    }
                />

                {this.getSpacer()}
                <ToolTip
                    title="Contact SR History"
                    content={<a
                        className="fal fa-id-card fa-2x m-5 pointer icon"
                        onClick={() => this.handleContactSRHistory(data?.contactID)}
                    />}
                />
                <ToolTip
                    title="Third Party Contacts"
                    content={<a
                        className="fal fa-users fa-2x m-5 pointer icon"
                        href={`ThirdPartyContact.php?action=list&customerID=${data?.customerId}`}
                        target="_blank"
                    />
                    }
                />

                {this.getSpacer()}
                {this.shouldShowExpenses(data, currentUser) ? <ToolTip
                    title="Expenses"
                    content={<a
                        className="fal fa-coins fa-2x m-5 pointer icon"
                        href={`Expense.php?action=view&callActivityID=${data?.callActivityID}`}
                    />
                    }
                /> : this.getSpacer()}
                {data?.problemStatus !== "C" ? <ToolTip
                    title="Add Travel"
                    content={<a
                        className="fal fa-car fa-2x m-5 pointer icon"
                        href={`Activity.php?action=createFollowOnActivity&callActivityID=${data?.callActivityID}&callActivityTypeID=22`}
                    />
                    }
                /> : null}
                {currentUser.isSDManager && data?.problemHideFromCustomerFlag == 'Y' ? <ToolTip
                        title="Unhide SR"
                        content={<i
                            className="fal fa-eye-slash fa-2x m-5 pointer icon"
                            onClick={() => this.handleUnhideSR(data)}
                        />}
                    />
                    : this.getSpacer()}
                <TimeBudgetElement
                    currentUserTeamId={currentUser?.teamID}
                    hdRemainMinutes={data?.hdRemainMinutes}
                    esRemainMinutes={data?.esRemainMinutes}
                    imRemainMinutes={data?.imRemainMinutes}
                    projectRemainMinutes={data?.projectRemainMinutes}
                    onExtraTimeRequest={() => this.handleExtraTime(data)}
                />
                {this.getSpacer()}
                <ToolTip
                    title="Calendar"
                    content={<a
                        className="fal fa-calendar-alt fa-2x m-5 pointer icon"
                        href={`Activity.php?action=addToCalendar&callActivityID=${data?.callActivityID}`}
                    />
                    }
                />
                <ToolTip
                    title="Time Breakdown"
                    content={<a
                        className="fal fa-calculator-alt fa-2x m-5 pointer icon"
                        onClick={() => window.open(`Popup.php?action=timeBreakdown&problemID=${data?.problemID}`, 'popup', 'width=800,height=400')}
                    />
                    }
                />
                {data?.isOnSiteActivity ? <ToolTip
                        title="Send client a visit confirmation email"
                        content={<i
                            className="fal fa-envelope fa-2x m-5 pointer icon"
                            onClick={() => this.handleConfirmEmail(data)}
                        />}
                    />
                    : this.getSpacer()}
                {this.renderChargeableWorkIcon()}
                {this.renderForceCompletionAction()}
            </div>
        </div>
    }

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
            const {
                reason,
                timeRequested,
                selectedContactId,
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
                let message = error;
                if (typeof (error) === 'object' && "message" in error) {
                    message = error.message;
                }
                this.alert(`Failed to save request:${message}`);
            }
        } catch (rejectedPromise) {

        }
    }
    handleCurrentChargeableWorkRequest = async () => {
        const shouldReload = await this.showAdditionalTimeRequestModal();
        if (shouldReload) {
            const {currentActivity} = this.state;
            await this.loadCallActivity(currentActivity);
        }
    }

    shouldShowExpenses(data, currentUser) {
        return data?.activityTypeHasExpenses && (data.userID == currentUser.id || currentUser.globalExpenseApprover || currentUser.isExpenseApprover);
    }

    getSpacer = () => {
        return <span style={{width: "35px"}}/>
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
    handleConfirmEmail = async (data) => {
        if (!data.customerNotes) {
            this.alert('Please enter Customer Summary information in the activity before sending a visit confirmation.');
            return;
        }

        if (await this.confirm('Are you sure you want to send the client a confirmation email?')) {
            await this.api.sendActivityVisitEmail(data.callActivityID);
        }
    }
    handleUnhideSR = async (data) => {
        if (data?.isSDManager && data?.problemHideFromCustomerFlag == 'Y') {
            if (await this.confirm('This will unhide the SR from the customer and can\'t be undone, are you sure?')) {
                await this.api.unHideSrActivity(data.callActivityID);
                data.problemHideFromCustomerFlag = 'N';
                this.setState({data});
            }
        }
    }
    handleDelete = async (data) => {

        const isLastActivity = data.activities.length === 1
        let message = 'Delete this activity?';

        if (isLastActivity) {
            message = 'Deleting this activity will remove all traces of this Service Request from the system. Are you sure?';
        }
        if (!await this.confirm(message)) {
            return;
        }
        this.api.deleteActivity(data.callActivityID).then(res => {
            if (isLastActivity) {
                window.location = 'CurrentActivityReport.php';
                return;
            }
            this.goPrevActivity()
        })

    }
    handleFollowOn = async () => {
        this.setState({showFollowOn: true});
    }

    handleGeneratePassword = () => {
        window.open("Password.php?action=generate&htmlFmt=popup", 'reason', 'scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0');
    }
    handleSalesOrder = async (activityId, serviceRequestId) => {
        this.setState({showSalesOrder: true});
    }
    handleUnlink = async (linkedSalesOrderID, serviceRequestId, activityId) => {
        const res = await this.confirm(`Are you sure you want to unlink this request to Sales Order ${linkedSalesOrderID}`);
        if (res) {
            await this.api.unlinkSalesOrder(serviceRequestId);
            await this.loadCallActivity(activityId);
        }
    }

    handleContactSRHistory(contactID) {
        window.open(`Activity.php?action=displayServiceRequestForContactPopup&contactID=${contactID}&htmlFmt=popup`, 'reason', 'scrollbars=yes,resizable=yes,height=400,width=1225,copyhistory=no, menubar=0');
    }

    saveFilterSession = () => {
        const {filters} = this.state;
        sessionStorage.setItem('displayActivityFilter', JSON.stringify(filters));
    }
    loadFilterSession = () => {
        const item = sessionStorage.getItem('displayActivityFilter');
        let {filters} = this.state;
        if (item) {
            const showTravel = params.get("toggleIncludeTravel") == "1";
            const showOperationalTasks = params.get("toggleIncludeOperationalTasks") == "1";
            const showServerGuardUpdates = params.get("toggleIncludeServerGuardUpdates") == "1";
            filters = JSON.parse(item);
            filters = {
                ...filters,
                showTravel,
                showOperationalTasks,
                showServerGuardUpdates
            }
        }
        this.setState({filters})
    }
    handleTogaleChange = async (filter) => {
        const {filters, currentActivity, data} = this.state;
        filters[filter] = !filters[filter];
        this.setState({filters});
        const problemID = data.problemID;
        if (filter === "criticalSR")
            await this.api.setActivityCritical(currentActivity);
        if (filter == "monitorSR")
            await this.api.setActivityMonitoring(currentActivity);
        if (filter === "holdForQA")
            await this.api.setProblemHoldForQA(problemID);
        this.saveFilterSession();
        this.loadCallActivity(currentActivity);
    }
    getToggle = (label, filter) => {
        const {filters} = this.state;
        const {el} = this;
        return el('div', {className: "m-5", style: {display: "flex", alignItems: "center", justifyContent: "center"}},
            el(Toggle, {onChange: () => this.handleTogaleChange(filter), checked: filters[filter], name: filter}),
            el("label", {className: "ml-4 nowrap"}, label)
        )
    }

    handleActivityChange = (event) => {
        const callActivityID = event.target.value;
        this.loadCallActivity(callActivityID);
        this.setState({currentActivity: callActivityID});
    }
    getCurrentActivityIndxElement = (data, currentActivity) => {
        const {el} = this;
        if (!data)
            return null;
        const indx = data.activities.findIndex(a => a.callActivityID == +currentActivity);
        return el('div', {className: "ml-5"}, el('strong', null, (indx + 1)), el('label', null, ` of ${data.activities.length}`))
    }
    goNextActivity = () => {
        const {data, currentActivity} = this.state;
        let index = data.activities.findIndex(a => a.callActivityID == +currentActivity);
        if (index < (data.activities.length - 1)) {
            index++;
            this.setState({currentActivity: data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        }

    }
    goPrevActivity = () => {
        const {data, currentActivity} = this.state;
        let index = data.activities.findIndex(a => a.callActivityID == +currentActivity);
        if (index > 0) {
            index--;
            this.setState({currentActivity: data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        }

    }
    goLastActivity = () => {
        const {data, currentActivity} = this.state;
        let index = data.activities.findIndex(a => a.callActivityID == +currentActivity);
        if (index !== (data.activities.length - 1)) {
            index = data.activities.length - 1;
            this.setState({currentActivity: data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        }
    }
    goFirstActivity = () => {
        const {data, currentActivity} = this.state;
        let index = data.activities.findIndex(a => a.callActivityID == +currentActivity);
        if (index !== 0) {
            index = 0;
            this.setState({currentActivity: data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        }
    }
    getOnsiteActivities = (onSiteActivities) => {
        const {el} = this;

        if (onSiteActivities && onSiteActivities.length > 0) {
            let columns = [
                {
                    path: "title",
                    label: "On-site Activities Within 10 Days",
                    sortable: false,
                    content: (activity) => el('a', {
                        href: `SRActivity.php?action=displayActivity&callActivityID=${activity.callActivityID}`,
                        target: "_blank"
                    }, activity.title)
                },
            ]
            return el('div', {style: {width: 300}}, el(Table, {
                id: "onSiteActivities",
                data: onSiteActivities || [],
                columns: columns,
                pk: "callActivityID",
                search: false,
            }));
        } else return null;
    }
    getActivitiesElement = () => {
        const {data, currentActivity, currentUser} = this.state;
        const {el} = this;

        const dateLen = maxLength(data?.activities || [], 'date') + 10;
        const engineerLen = maxLength(data?.activities || [], 'enginner') + 10;
        const contactName = maxLength(data?.activities || [], 'contactName') + 10;
        const indx = data?.activities.findIndex(a => +a.callActivityID == +currentActivity);

        return el('div', {className: "activities-container"},
            el('div', {style: {width: "100%", display: "flex", alignItems: "center", justifyContent: "center"}},
                el(ToolTip, {
                    title: "First ",
                    content: el('i', {
                        className: "fal  fa-step-backward icon font-size-4 mr-4 ml-4 pointer",
                        onClick: this.goFirstActivity
                    })
                }),
                el(ToolTip, {
                    title: "Previous",
                    content: el('i', {
                        className: "fal  fa-backward icon font-size-4 pointer",
                        style: {fontSize: 21},
                        onClick: this.goPrevActivity
                    })
                }),
                el('select', {value: currentActivity || "", onChange: this.handleActivityChange},
                    indx == -1 ? el('option', {value: null}, "") : null,
                    data?.activities.map(a =>
                        el('option', {
                            key: "cl" + a.callActivityID, value: a.callActivityID,

                            dangerouslySetInnerHTML: {
                                __html: this.getActivityChangeOptionText(a, dateLen, engineerLen, contactName)
                            }
                        }))
                ),
                el(ToolTip, {
                    title: "Next",
                    content: el('i', {
                        className: "fal  fa-forward icon font-size-4 mr-4 ml-4 pointer",
                        style: {fontSize: 21},
                        onClick: this.goNextActivity
                    })
                }),
                el(ToolTip, {
                    title: "Last",
                    content: el('i', {
                        className: "fal  fa-step-forward icon font-size-4 pointer",
                        onClick: this.goLastActivity
                    })
                }),
                this.getCurrentActivityIndxElement(data, currentActivity)
            ),
            el('div', {style: {display: "flex", flexDirection: "row", alignItems: "center", justifyContent: "center"}},
                (currentUser.isSDManager || currentUser.serviceRequestQueueManager) ? this.getToggle("QA", 'holdForQA') : null,
                this.getToggle("Critical SR", 'criticalSR'),
                this.getToggle("Monitor SR", 'monitorSR'),
                this.getToggle("Travel", "showTravel"),
                this.getToggle("Operational Tasks", "showOperationalTasks"),
                this.getToggle("ServerGuard Updates", "showServerGuardUpdates"),
                el('label', {className: "ml-5"}, 'Request Hours: '),
                el('label', null, data?.totalActivityDurationHours),
                el('label', {className: "ml-5"}, 'Chargeable: '),
                el('label', null, data?.chargeableActivityDurationHours),
                <label className="ml-5">
                    Awaiting CNC:
                </label>,
                <label>
                    {data?.workingHours}
                </label>,
                <label className="ml-5">
                    On Hold:
                </label>,
                <label>
                    {data?.openHours - data?.workingHours < 0 ? 0 : (data?.openHours - data?.workingHours).toFixed(2)}
                </label>
            ),
        );
    }

    getActivityChangeOptionText(a, dateLen, engineerLen, contactName) {
        return padEnd(a.callActivityID, 50, Chars.WhiteSpace)
            + padEnd(a.date, dateLen, Chars.WhiteSpace)
            + padEnd(a.enginner, engineerLen, Chars.WhiteSpace)
            + padEnd(a.contactName, contactName, Chars.WhiteSpace)
            + (a.activityType || '');
    }

    getHiddenSRElement = (data) => {
        if (data?.problemHideFromCustomerFlag !== 'Y' && data?.hideFromCustomerFlag !== 'Y') {
            return;
        }
        return <label style={{color: "red", fontWeight: "bold", fontSize: 14}}
        >
            {data?.hideFromCustomerFlag === 'Y' ? 'Activity hidden from customer' : 'Entire SR hidden from customer'}
        </label>
    }
    getElement = (label, text, bgcolor) => {
        const {el} = this;
        return el('div', {style: {flexBasis: 320, marginTop: 3, backgroundcolor: bgcolor}},
            label ? el('label', {
                style: {
                    display: "inline-block",
                    width: 80,
                    textAlign: "right",
                    color: "#992211",
                    whiteSpace: "nowrap"
                }
            }, label) : null,
            el('label', {style: {textAlign: "left", whiteSpace: "nowrap", marginLeft: 5}}, text),
        )
    }
    getDetailsElement = () => {
        const {el} = this;
        const {data} = this.state;
        return el("div", {className: "flex-row"},
            data?.reason ? el('div', {className: "round-container flex-2 mr-5"},

                el('div', {className: "flex-row"},
                    el(
                        "label",
                        {className: "label  mt-5 mr-3 ml-1 mb-5", style: {display: "block"}},
                        "Activity Notes"
                    ),
                    el(ToolTip, {
                        width: 15,
                        title: "These notes will be available for the customer to see in the portal but will not be sent in an email.",
                        content: el("i", {className: "fal fa-info-circle mt-5 pointer icon"})
                    })
                ),

                el('div', {dangerouslySetInnerHTML: {__html: data?.reason}}),
            ) : null,
            data?.reason ? el('div', {className: "round-container flex-1"},
                el('div', {className: "flex-row"},
                    el(
                        "label",
                        {className: "label  mt-5 mr-3 ml-1  mb-5", style: {display: "block"}},
                        "CNC Next Action"
                    ),
                    el(ToolTip, {
                        width: 15,
                        title: "These are internal notes only and not visible to the customer. These are per activity.",
                        content: el("i", {className: "fal fa-info-circle mt-5 pointer icon"})
                    })
                ), el('div', {dangerouslySetInnerHTML: {__html: data?.cncNextAction}}),
            ) : null
        );

    }

    getcustomerNotesElement = () => {
        const {el} = this;
        const {data} = this.state;
        return el('div', {className: "round-container"},
            el('div', {className: "flex-row"},
                el(
                    "label",
                    {className: "label  mt-5 mr-3 ml-1 mb-5", style: {display: "block"}},
                    "Customer Summary"
                ),
                el(ToolTip, {
                    width: 15,
                    title: "This information will be sent to the customer in an email unless the entire Service Request is hidden.",
                    content: el("i", {className: "fal fa-info-circle mt-5 pointer icon"})
                })
            ), el('div', {dangerouslySetInnerHTML: {__html: data?.customerNotes}})
        );
    }

    getContentElement = () => {
        const {data} = this.state;
        const {el} = this;


        return (
            <div className="activities-container">
                <table style={{width: '100%'}}>
                    <tbody>
                    <tr>
                        <td className="display-label"
                            style={{width: "80px"}}
                        >Status
                        </td>
                        <td className="display-content">{data?.problemStatusDetials + this.getAwaitingTitle(data)}</td>
                        <td className="display-label">{data?.authorisedBy ? "Authorised by" : ''}</td>
                        <td className="display-content">{data?.authorisedBy}</td>
                        <td className="display-label">Type</td>
                        <td colSpan="3"
                            className="nowrap display-content"

                        >
                            <div style={{display: "flex", alignItems: "center"}}>
                                <label className="mr-3">{data?.activityType}</label>
                                {this.getInboundIcon()}
                            </div>
                        </td>
                    </tr>


                    <tr>
                        <td className="display-label">Priority</td>
                        <td className="display-content">{data?.priority}</td>
                        <td style={{textAlign: "center"}}
                            colSpan="1"
                        >
                            {this.getHiddenSRElement(data)}
                        </td>
                        <td/>
                        <td className="display-label">Date</td>
                        <td colSpan="3"
                            className="display-content"
                        > {moment(data?.date).format("DD/MM/YYYY")}</td>
                    </tr>

                    <tr>
                        <td className="display-label">Contract</td>
                        <td className="display-content">{data?.contractType}</td>
                        <td className="display-label">Completed On</td>
                        <td className="display-content">{data?.completeDate ? moment(data?.completeDate).format("DD/MM/YYYY") : null}</td>

                        <td className="display-label">Time From</td>
                        <td style={{width: 10}}>{data?.startTime}</td>
                        <td className="display-label"
                            style={{width: 10}}
                        >{data?.endTime ? "To" : ""}</td>
                        <td>{data?.endTime}</td>
                    </tr>

                    <tr>
                        <td className="display-label">Root Cause</td>
                        <td className="display-content">{data?.rootCauseDescription}</td>
                        <td className="display-label">Top-Up Value</td>
                        <td>{data?.curValue}</td>
                        <td className="display-label">User</td>
                        <td colSpan="3"
                            className="display-content"
                        >{data?.engineerName}</td>
                    </tr>

                    <tr>
                        <td className="display-label">Summary</td>
                        <td className="display-content"
                            colSpan="3"
                        >{data?.emailsubjectsummary}</td>
                        <td className="display-label">Asset</td>
                        <td colSpan="3"
                            className="nowrap"
                        >
                            {this.getAssetName(data)}
                            {data.automateMachineID?<i className="fal fa-cog ml-5 pointer" 
                            onClick={()=>window.open(`https://serverguard.cnc-ltd.co.uk/automate/computer/${data.automateMachineID}/normal-tiles`,"_target")}
                            
                            ></i>:null}
                        </td>
                    </tr>
                    {
                        data?.currentUser ? (
                            <tr>
                                <td
                                    colSpan="8"
                                    style={{backgroundColor: data?.currentUserBgColor, textAlign: "center"}}
                                > {data?.currentUser}</td>
                            </tr>

                        ) : null
                    }
                    </tbody>
                </table>
            </div>
        )
    }

    getAssetName(data) {
        if (!data) {
            return '';
        }
        if (data.emptyAssetReason) {
            return data.emptyAssetReason;
        }
        return (
            <React.Fragment>
                <span>
                    {data?.assetName}
                </span>
                {
                    data.unsupportedCustomerAsset ? <i className="fa  fa-do-not-enter"
                                                       style={{verticalAlign: "middle", paddingLeft: "0.5em"}}
                    /> : ''
                }
            </React.Fragment>
        )

    }

    getAwaitingTitle = (data) => {
        if (!(data?.problemStatus !== "F" && data?.problemStatus !== "C")) {
            return "";
        }
        if (data?.awaitingCustomerResponseFlag == 'N')
            return " - Awaiting CNC";

        if (data?.awaitingCustomerResponseFlag == 'Y') {
            const dateTime = dateFormatExcludeNull(`${data.alarmDate} ${data.alarmTime}:00`, null, 'DD/MM/YYYY HH:mm')
            return ` - On Hold until ${dateTime}`;
        }
        return "";

    }

    getExpensesElement = () => {
        const {data, currentUser} = this.state;
        const {el} = this;
        const totalExpenses = data?.expenses.map(e => e.value).reduce((p, c) => p + c, 0);
        if (!this.shouldShowExpenses(data, currentUser)) {
            return '';
        }

        let columns = [
            {
                path: "expenseType",
                label: "Expense",
                sortable: false,
                footerContent: (c) => el('label', null, 'Total')
            },
            {
                path: "mileage",
                label: "Miles",
                sortable: false,
            },
            {
                path: "value",
                label: "Amount",
                sortable: false,
                footerContent: (c) => el('label', null, totalExpenses)
            },
            {
                path: "vatFlag",
                label: "VAT included",
                sortable: false,
            },
        ]

        return el(
            "div",
            {className: "round-container"},
            el(
                "div",
                {className: "flex-row"},
                el(
                    "label",
                    {className: "label mt-5 mr-3 ml-1 mb-5", style: {display: "block"}},
                    "Expenses"
                ),
                el(ToolTip, {
                    width: 15,
                    title:
                        "These are the Expenses associated with this activity.",
                    content: el("i", {
                        className: "fal fa-info-circle mt-5 pointer icon",
                    }),
                })
            ),
            el(Table, {
                id: "expenses",
                data: data?.expenses || [],
                columns: columns,
                pk: "id",
                search: false,
                hasFooter: true
            })
        );
    }

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
                           onClose={
                               (isSent) => {
                                   if (isSent) this.loadCallActivity(activityId);
                                   this.setState({templateType: null})
                               }
                           }
                           customerId={customerId}
                           serviceRequestId={serviceRequestId}
                           activityId={activityId}
            />
        )
    }
    handleTemplateDisplay = async (type) => {
        this.setState({templateType: type});
    }

    getFooter = () => {
        return (
            <div className="activities-container">
                <button className="m-5 btn-info"
                        onClick={() => this.handleTemplateDisplay(TEMPlATE_TYPES.partsUsed)}
                >
                    Parts Used
                </button>
                <button className="m-5 btn-info"
                        onClick={() => this.handleTemplateDisplay(TEMPlATE_TYPES.salesRequest)}
                >
                    Sales Request
                </button>
                <button className="m-5 btn-info"
                        onClick={() => this.handleTemplateDisplay(TEMPlATE_TYPES.changeRequest)}
                >
                    Change Request
                </button>
            </div>
        )
    }
    getFollowOnElement = () => {
        const {data, showFollowOn} = this.state;
        const startWork = data?.problemStatus == 'I' && data?.serverGuard == 'N' && data?.hideFromCustomerFlag == 'N';
        return showFollowOn ? this.el(ActivityFollowOn, {
            startWork,
            key: "followOnModal",
            callActivityID: data.callActivityID,
            onCancel: () => this.setState({showFollowOn: false})
        }) : null;
    }
    handleSalesOrderClose = () => {
        this.setState({showSalesOrder: false});
        this.loadCallActivity(this.state.currentActivity);
    }

    getTaskListElement() {
        const {data} = this.state;
        if (!data || data.entire) {
            return '';
        }
        return (
            <TaskListComponent serviceRequestId={data.problemID}/>
        );
    }

    onNoteAdded = () => {
        this.loadCallActivity(this.state.currentActivity)
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
    handleCallbackClose = () => {
        this.setState({showCallbackModal: false});
    }

    getInboundIcon = () => {
        const {data} = this.state;
        switch (data.Inbound) {
            case true:
                return (
                    <ToolTip title="Inbound Contact" width={15}>
                        <i className="fal fa-sign-in pointer icon"></i>
                    </ToolTip>
                );
            case false:
                return (
                    <ToolTip title="Outbound Contact" width={15}>
                        <i className="fal fa-sign-out  pointer icon"></i>
                    </ToolTip>
                );
            default:
                return null;
        }
    }

    render() {
        const {data, showSalesOrder, _loadedData} = this.state;

        if (!_loadedData) {
            return <div className="loading"/>
        }

        return (
            <div style={{width: "90%"}}>
                {this.getAdditionalChargeModal()}
                {this.getAlert()}
                {this.getConfirm()}
                {this.getPrompt()}
                {this.getFollowOnElement()}
                {this.getProjectsElement()}

                <ActivityHeaderComponent serviceRequestData={data}/>

                {this.getCallbackModal()}
                {this.getActions()}
                {this.getActivitiesElement()}
                {this.getContentElement()}
                {this.getDetailsElement()}
                {this.getcustomerNotesElement()}
                <InternalNotes serviceRequestId={data?.problemID}/>
                {this.getTaskListElement()}
                <CustomerDocumentUploader serviceRequestId={data?.problemID}/>
                <InternalDocumentsComponent serviceRequestId={data?.problemID}/>
                {this.getExpensesElement()}
                {this.getTemplateModal()}
                {this.getFooter()}
                {showSalesOrder ? <LinkServiceRequestOrder serviceRequestID={data.problemID}
                                                           customerId={data?.customerId}
                                                           show={showSalesOrder}
                                                           onClose={this.handleSalesOrderClose}
                /> : null}
            </div>
        );
    }

    loadLastActivityInServiceRequest(serviceRequestId) {
        return this.api.getLastActivityInServiceRequest(serviceRequestId).then(res => {
            return this.loadCallActivity(res.data);
        })
    }

    handleAdditionalTimeRequestModalOnCancel = () => {
        if (this.additionalTimeRequestReject) {
            this.additionalTimeRequestReject();
        }
        this.hideAdditionalTimeRequestModal();
    };
    handleExistingAdditionalChargeableWorkRequestModalOnClose = (closingValue) => {
        if (this.additionalTimeRequestResolve) {
            this.additionalTimeRequestResolve(closingValue);
        }
        this.hideAdditionalTimeRequestModal();
    };
    showAlertModal = () => {
        this.alert('The Service Request must be reopened before a call back can be logged.');
    }

    showCallbackModal = () => {
        this.setState({showCallbackModal: true});
    };
    forceClosingSR = async () => {
        const {filters, data} = this.state;
        if (filters.holdForQA) {
            this.alert('Please clear the QA flag before marking this Service Request as complete.');
            return;
        }
        const answer = await this.confirm('Please confirm you want to mark this Service Request as completed.')
        if (!answer) {
            return;
        }
        try {
            const res = await this.api.forceCloseServiceRequest(data.problemID);
            window.location.reload();
        } catch (error) {
            console.log(error);
            let message = "Failed to close service request";
            if ('error' in error) {
                message = error.error.message;
            }
            this.alert(message);
        }
    };

    renderForceCompletionAction = () => {
        const {data} = this.state;

        if (!data.isAllowedForceClosingSR || data.problemStatus !== 'F' || ![1, 2, 3].includes(data.priorityNumber)) {
            return null;
        }
        return (
            <ToolTip title={'Force Early SR Completion'}>
                <a className={`fal fa-door-closed fa-2x m-5 pointer icon`} onClick={this.forceClosingSR}/>
            </ToolTip>
        )
    };
}

export default ActivityDisplayComponent;