import APIActivity from "../../services/APIActivity.js";
import {Chars, maxLength, padEnd, params} from "../../utils/utils.js";
import Toggle from "../../shared/Toggle.js";
import Table from "../../shared/table/table"

import CKEditor from "../../shared/CKEditor.js";
import ToolTip from "../../shared/ToolTip.js";
import ActivityFollowOn from "../../Modals/ActivityFollowOn.js";
import MainComponent from "../../shared/MainComponent.js";
import * as React from 'react';
import Modal from "../../shared/Modal/modal";
import moment from "moment";
import CustomerDocumentUploader from "./CustomerDocumentUploader";
import {InternalDocumentsComponent} from "./InternalDocumentsComponent";

// noinspection EqualityComparisonWithCoercionJS
const emptyAssetReasonCharactersToShow = 30;

class ActivityDisplayComponent extends MainComponent {
    api = new APIActivity();

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
            currentActivity: null,
            _showModal: false,
            templateOptions: [],
            templateOptionId: null,
            templateDefault: '',
            templateValue: '',
            templateType: '',
            templateTitle: '',
            selectedChangeRequestTemplateId: null,
            filters: {
                showTravel: false,
                showOperationalTasks: false,
                showServerGuardUpdates: false,
                criticalSR: false,
                monitorSR: false,
                holdForQA: false
            }
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
        this.setState({filters, data: res, currentActivity: +res.callActivityID, currentUser});

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

    getHeader = () => {

        const {data} = this.state;
        return (
            <div style={{display: "flex", flexDirection: "column"}}>
                <a
                    className={data?.customerNameDisplayClass}
                    href={`Customer.php?action=dispEdit&customerId=${data?.customerId}`}
                    target="_blank"
                >
                    {data?.customerName + ", " +
                    data?.siteAdd1 + ", " +
                    data?.siteAdd2 + ", " +
                    data?.siteAdd3 + ", " +
                    data?.siteTown + ", " +
                    data?.sitePostcode}
                </a>
                <div>
                    <a href={`Customer.php?action=dispEdit&customerId=${data?.customerId}`}
                       target="_blank"
                    >
                        {data?.contactName + " "}
                    </a>
                    <a href={`tel:${data?.sitePhone}`}> {data?.sitePhone} </a>
                    {data?.contactPhone ? <label>DDI:</label> : null}
                    {data?.contactPhone ? (<a href={`tel:${data?.contactPhone}`}>{data?.contactPhone}</a>) : null}
                    {data?.contactMobilePhone ? <label> Mobile:</label> : null}
                    {data?.contactMobilePhone ?
                        <a href={`tel:${data?.contactMobilePhone}`}>{data?.contactMobilePhone}</a> : null
                    }
                    <a href={`mailto:${data?.contactEmail}?cc=support@cnc-ltd.co.uk&subject=${data?.serviceRequestEmailSubject}`}>
                        <i className="fal fa-envelope ml-5"/>
                    </a>
                </div>
                <p className='formErrorMessage mt-2'>{data?.contactNotes}</p>
                <p className='formErrorMessage mt-2'>{data?.techNotes}</p>
            </div>
        );
    }

    getActions = () => {
        const {el} = this;
        const {data, currentUser} = this.state;

        return el('div', {
                className: "activities-container",
                style: {display: "flex", flexDirection: "row", justifyContent: "center", alignItems: "center"}
            },
            data?.problemStatus !== "C" ? el(ToolTip, {
                title: "Follow On",
                content: el('i', {className: "fal fa-play fa-2x m-5 pointer icon", onClick: this.handleFollowOn})
            }) : null,
            el(ToolTip, {
                title: "History",
                content: el('a', {
                    className: "fal fa-history fa-2x m-5 pointer icon",
                    href: `Activity.php?action=problemHistoryPopup&problemID=${data?.problemID}&htmlFmt=popup`,
                    target: "_blank"
                })
            }),
            el(ToolTip, {
                title: "Passwords",
                content: el('a', {
                    className: "fal fa-unlock-alt fa-2x m-5 pointer icon",
                    href: `Password.php?action=list&customerID=${data?.customerId}`,
                    target: "_blank"
                })
            }),
            this.getSpacer(),
            data?.canEdit == 'ALL_GOOD' ? el(ToolTip, {
                title: "Edit",
                content: el('a', {
                    className: "fal fa-edit fa-2x m-5 pointer icon",
                    href: `SRActivity.php?action=editActivity&callActivityID=${data?.callActivityID}`
                })
            }) : null,
            data?.canEdit !== 'ALL_GOOD' ? el(ToolTip, {
                title: data?.canEdit,
                content: el('i', {className: "fal fa-edit fa-2x m-5 pointer icon-disable"})
            }) : null,
            (data?.canDelete && data?.problemStatus !== "C") ? el(ToolTip, {
                title: data?.activities.length == 1 ? "Delete Request" : "Delete Activity",
                content: el('i', {
                    className: "fal fa-trash-alt fa-2x m-5 pointer icon",
                    onClick: () => this.handleDelete(data)
                })
            }) : null,
            !data?.canDelete ? el(ToolTip, {
                title: "Delete Activity",
                content: el('i', {className: "fal fa-trash-alt fa-2x m-5 pointer  icon-disable",})
            }) : null,
            this.getSpacer(),
            data?.linkedSalesOrderID ? el(ToolTip, {
                title: "Sales Order",
                content: el('a', {
                    className: "fal fa-tag fa-2x m-5 pointer icon",
                    href: `SalesOrder.php?action=displaySalesOrder&ordheadID=${data?.linkedSalesOrderID}`,
                    target: "_blank"
                })
            }) : null,
            !data?.linkedSalesOrderID ? el(ToolTip, {
                title: "Sales Order",
                content: el('a', {
                    className: "fal fa-tag fa-2x m-5 pointer icon",
                    onClick: () => this.handleSalesOrder(data?.callActivityID)
                })
            }) : null,
            data?.linkedSalesOrderID ? el(ToolTip, {
                title: "Unlink Sales order",
                content: el('a', {
                    className: "fal fa-unlink fa-2x m-5 pointer icon",
                    onClick: () => this.handleUnlink(data?.linkedSalesOrderID, data?.problemID, data?.callActivityID)
                })
            }) : null,
            el(ToolTip, {
                title: "Renewal Information",
                content: el('a', {
                    className: "fal fa-tasks fa-2x m-5 pointer icon",
                    href: `RenewalReport.php?action=produceReport&customerID=${data?.customerId}`,
                    target: "_blank"
                })
            }),
            // el(ToolTip,{title:"Generate Password",content: el('a',{className:"fal fa-magic fa-2x m-5 pointer icon",onClick:this.handleGeneratPassword})}),
            el(ToolTip, {
                title: "Contracts",
                content: el('a', {
                    className: "fal fa-file-contract fa-2x m-5 pointer icon",
                    href: `Activity.php?action=contractListPopup&customerID=${data?.customerId}`,
                    target: "_blank"
                })
            }),

            this.getSpacer(),
            el(ToolTip, {
                title: "Contact SR History",
                content: el('a', {
                    className: "fal fa-id-card fa-2x m-5 pointer icon",
                    onClick: () => this.handleContactSRHistory(data?.contactID)
                })
            }),
            el(ToolTip, {
                title: "Third Party Contacts",
                content: el('a', {
                    className: "fal fa-users fa-2x m-5 pointer icon",
                    href: `ThirdPartyContact.php?action=list&customerID=${data?.customerId}`,
                    target: "_blank"
                })
            }),
            this.getSpacer(),
            this.shouldShowExpenses(data, currentUser) ? el(ToolTip, {
                title: "Expenses",
                content: el('a', {
                    className: "fal fa-coins fa-2x m-5 pointer icon",
                    href: `Expense.php?action=view&callActivityID=${data?.callActivityID}`
                })
            }) : this.getSpacer(),
            data?.problemStatus !== "C" ? el(ToolTip, {
                title: "Add Travel",
                content: el('a', {
                    className: "fal fa-car fa-2x m-5 pointer icon",
                    href: `Activity.php?action=createFollowOnActivity&callActivityID=${data?.callActivityID}&callActivityTypeID=22`
                })
            }) : null,
            currentUser.isSDManager && data?.problemHideFromCustomerFlag == 'Y' ? el(ToolTip, {
                title: "Unhide SR",
                content: el('i', {
                    className: "fal fa-eye-slash fa-2x m-5 pointer icon",
                    onClick: () => this.handleUnhideSR(data)
                })
            }) : this.getSpacer(),
            el(ToolTip, {
                title: "Calendar",
                content: el('a', {
                    className: "fal fa-calendar-alt fa-2x m-5 pointer icon",
                    href: `Activity.php?action=addToCalendar&callActivityID=${data?.callActivityID}`
                })
            }),
            el(ToolTip, {
                title: "Time Breakdown",
                content: el('a', {
                    className: "fal fa-calculator-alt fa-2x m-5 pointer icon",
                    onClick: () => window.open(`Popup.php?action=timeBreakdown&problemID=${data?.problemID}`, 'popup', 'width=800,height=400')
                })
            }),
            data?.allowSCRFlag == 'Y' ? el(ToolTip, {
                title: "Send client a visit confirmation email",
                content: el('i', {
                    className: "fal fa-envelope fa-2x m-5 pointer icon",
                    onClick: () => this.handleConfirmEmail(data)
                })
            }) : this.getSpacer(),
        );
    }

    shouldShowExpenses(data, currentUser) {
        return data?.activityTypeHasExpenses && (data.userID == currentUser.id || currentUser.globalExpenseApprover || currentUser.isExpenseApprover);
    }

    getSpacer = () => {
        return this.el('span', {style: {width: 35}})
    }
    handleConfirmEmail = async (data) => {
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
        let deleteActivity = false;
        if (data.activities.length == 1) {
            if (await this.confirm('Deleting this activity will remove all traces of this Service Request from the system. Are you sure?'))
                deleteActivity = true;
        } else if (await this.confirm('Delete this activity?'))
            deleteActivity = true;
        if (deleteActivity)
            this.api.deleteActivity(data.callActivityID).then(res => this.goPrevActivity())

    }
    handleFollowOn = async () => {
        this.setState({showFollowOn: true});
    }

    handleGeneratPassword = () => {
        window.open("Password.php?action=generate&htmlFmt=popup", 'reason', 'scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0');
    }
    handleSalesOrder = (callActivityID) => {

        const w = window.open(`Activity.php?action=editLinkedSalesOrder&htmlFmt=popup&callActivityID=${callActivityID}`, 'reason', 'scrollbars=yes,resizable=yes,height=150,width=250,copyhistory=no, menubar=0');
        w.onbeforeunload = () => this.loadCallActivity();
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
                el('select', {value: currentActivity, onChange: this.handleActivityChange},
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
                currentUser.isSDManager ? this.getToggle("QA", 'holdForQA') : null,
                this.getToggle("Critical SR", 'criticalSR'),
                this.getToggle("Monitor SR", 'monitorSR'),
                this.getToggle("Travel", "showTravel"),
                this.getToggle("Operational Tasks", "showOperationalTasks"),
                this.getToggle("ServerGuard Updates", "showServerGuardUpdates"),
                el('label', {className: "ml-5"}, 'Activity hours: '),
                el('label', null, data?.totalActivityDurationHours),
                el('label', {className: "ml-5"}, 'Chargeable hours: '),
                el('label', null, data?.chargeableActivityDurationHours)),
            // this.getOnsiteActivities(data?.onSiteActivities)
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
        const {el} = this;
        if (data?.problemHideFromCustomerFlag == 'Y')
            return this.el('div', {style: {display: "flex", justifyContent: "center", alignItems: "center"}},
                el('h1', {style: {color: "red"}}, "Hidden From Customer")
            )
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
    getDetialsElement = () => {
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
    getNotesElement = () => {
        const {el} = this;
        const {data} = this.state;
        return el(
            "div",
            {className: "round-container"},
            el(
                "div",
                {className: "flex-row"},
                el(
                    "label",
                    {className: "label mt-5 mr-3 ml-1 mb-5", style: {display: "block"}},
                    "Internal Notes"
                ),
                el(ToolTip, {
                    width: 15,
                    title:
                        "These are internal notes only and not visible to the customer. These are per Service Request.",
                    content: el("i", {
                        className: "fal fa-info-circle mt-5 pointer icon",
                    }),
                })
            ),
            el("div", {
                dangerouslySetInnerHTML: {__html: data?.internalNotes},
            })
        );
    }
    getCustomerNotesElement = () => {
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

    async deleteDocument(id) {
        const {data} = this.state;
        if (await this.confirm('Are you sure you want to remove this document?')) {
            await this.api.deleteDocument(this.state.currentActivity, id);
            data.documents = data.documents.filter(d => d.id !== id);
            this.setState({data});
        }
    }

    getContentElement = () => {
        const {data} = this.state;
        const {el} = this;


        return el('div', {className: "activities-container"},
            el('table', {style: {width: "100%"}},
                el('tbody', null,
                    el('tr', null,
                        el('td', {className: "display-label", style: {width: "80px"}}, "Status"),
                        el('td', {className: "display-content"}, data?.problemStatusDetials + this.getAwaitingTitle(data)),
                        el('td', {className: "display-label"}, data?.authorisedBy ? "Authorised by" : ''),
                        el('td', {className: "display-content"}, data?.authorisedBy),
                        el('td', {className: "display-label"}, "Type"),
                        el('td', {colSpan: 3, className: "nowrap"}, data?.activityType),
                    ),

                    el('tr', null,
                        el('td', {className: "display-label"}, "Priority"),
                        el('td', {className: "display-content"}, data?.priority),
                        el('td', {style: {textAlign: "center"}, colSpan: 1}, data?.problemHideFromCustomerFlag == "Y" ?
                            el("label", {
                                style: {
                                    color: "red",
                                    fontWeight: "bold",
                                    fontSize: 14
                                }
                            }, "Entire SR hidden from customer") : null
                        ),
                        el('td', null),

                        el('td', {className: "display-label"}, "Date"),
                        el('td', {colSpan: 3, className: "display-content"}, moment(data?.date).format("DD/MM/YYYY")),
                    ),

                    el('tr', null,
                        el('td', {className: "display-label"}, "Contract"),
                        el('td', {className: "display-content"}, data?.contractType),
                        el('td', {className: "display-label"}, "Completed On"),
                        el('td', {className: "display-content"}, data?.completeDate ? moment(data?.completeDate).format("DD/MM/YYYY") : null),

                        el('td', {className: "display-label"}, "Time From"),
                        el('td', {style: {width: 10}}, data?.startTime),
                        el('td', {className: "display-label", style: {width: 10}}, data?.endTime ? "To" : ""),
                        el('td', null, data?.endTime),
                    ),

                    el('tr', null,
                        el('td', {className: "display-label"}, "Root Cause"),
                        el('td', {className: "display-content"}, data?.rootCauseDescription),
                        el('td', {className: "display-label"}, "Top-Up Value"),
                        el('td', null, data?.curValue),
                        el('td', {className: "display-label"}, "User"),
                        el('td', {colSpan: 3, className: "display-content"}, data?.engineerName),
                    ),

                    el('tr', null,
                        el('td', {colSpan: 4}),
                        el('td', {className: "display-label"}, "Asset"),
                        el('td', {colSpan: 3}, data?.assetName || (data?.emptyAssetReason) || ''),
                    ),

                    data?.currentUser ? el('tr', null,
                        el('td', {
                            colSpan: 8,
                            style: {backgroundColor: data?.currentUserBgColor, textAlign: "center"}
                        }, data?.currentUser),
                    ) : null,
                )));

    }
    getAwaitingTitle = (data) => {
        if (data?.problemStatus !== "F" && data?.problemStatus !== "C") {
            if (data?.awaitingCustomerResponseFlag == 'N')
                return " - Awaiting CNC";
            else if (data?.awaitingCustomerResponseFlag == 'Y')
                return " - On Hold";
            else
                return "";
        } else return "";

    }

    handleUpload() {
        const {currentActivity} = this.state;
        this.loadCallActivity(currentActivity);
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
                    "Internal Notes"
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
    // Parts used, change requestm and sales request
    handleTemplateChanged = (event) => {

        const id = event.target.value;
        const {templateOptions} = this.state;
        let templateDefault;
        let templateOptionId = null;
        let templateValue = '';
        if (id >= 0) {
            const op = templateOptions.filter(s => s.id == id)[0];
            templateDefault = op.template;
            templateValue = op.template;
            templateOptionId = op.id;
        } else {
            templateDefault = '';
        }
        this.setState({templateDefault, templateOptionId, templateValue});
    }
    handleTemplateValueChange = (value) => {
        this.setState({templateValue: value})
    }
    handleTemplateSend = async (type) => {
        const {templateValue, templateOptionId, data, currentActivity} = this.state;
        if (templateValue == '') {
            this.alert('Please enter detials');
            return;
        }
        const payload = new FormData();
        payload.append("message", templateValue);
        payload.append("type", templateOptionId);
        switch (type) {
            case "changeRequest":
                await this.api.sendChangeRequest(data.problemID, payload);
                this.alert('Change Request Sent');
                break;
            case "partsUsed":
                const object = {
                    message: templateValue,
                    callActivityID: currentActivity,
                };
                const result = await this.api.sendPartsUsed(object);
                this.alert('Parts Used Sent');
                break;
            case "salesRequest":
                await this.api.sendSalesRequest(
                    data.customerId,
                    data.problemID,
                    payload
                );
                this.alert('Sales Request Sent');
                break;
        }
        this.loadCallActivity(currentActivity);
        this.setState({_showModal: false})
    }
    getTemplateModal = () => {
        const {templateDefault, templateOptions, _showModal, templateTitle, templateType} = this.state;
        const {el} = this;
        return el(
            Modal, {
                width: 900, key: templateType, onClose: () => this.setState({_showModal: false}),
                title: templateTitle, show: _showModal,
                content: el('div', {key: 'conatiner'},
                    templateOptions.length > 0 ? el('select', {onChange: this.handleTemplateChanged},
                        el('option', {key: 'empty', value: -1}, "-- Pick an option --"),
                        templateOptions.map(s => el('option', {key: s.id, value: s.id}, s.name))) : null,
                    el(CKEditor, {
                        key: 'salesRequestEditor', id: 'salesRequest', value: templateDefault
                        , onChange: this.handleTemplateValueChange
                    })
                ),
                footer: el('div', {key: "footer"},
                    el('button', {onClick: () => this.handleTemplateSend(templateType)}, "Send"),
                    el('button', {onClick: () => this.setState({_showModal: false})}, "Cancel"),
                )
            }
        )
    }
    handleTemplateDisplay = async (type) => {
        let options = [];
        let templateTitle = '';
        switch (type) {
            case "salesRequest":
                options = await this.api.getSalesRequestOptions();
                templateTitle = "Sales Request";
                break;
            case "changeRequest":
                options = await this.api.getChangeRequestOptions();
                templateTitle = "Change Request";
                break;
            case "partsUsed":
                templateTitle = "Parts Used";
                break;
        }
        const templateDefault = '';
        this.setState({templateOptions: options, _showModal: true, templateType: type, templateTitle, templateDefault})
    }
    //-------------end template
    getFooter = () => {
        return (
            <div className="activities-container">
                <button className="m-5 btn-info"
                        onClick={() => this.handleTemplateDisplay("partsUsed")}
                >
                    Parts Used
                </button>
                <button className="m-5 btn-info"
                        onClick={() => this.handleTemplateDisplay("salesRequest")}
                >
                    Sales Request
                </button>
                <button className="m-5 btn-info"
                        onClick={() => this.handleTemplateDisplay("changeRequest")}
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

    render() {
        const {data} = this.state;
        return (
            <div style={{width: "90%"}}>
                {this.getAlert()}
                {this.getConfirm()}
                {this.getPrompt()}
                {this.getFollowOnElement()}
                {this.getProjectsElement()}
                {this.getHeader()}
                {this.getActions()}
                {this.getActivitiesElement()}
                {this.getContentElement()}
                {this.getDetialsElement()}
                {this.getCustomerNotesElement()}
                {this.getNotesElement()}
                <CustomerDocumentUploader
                    onDeleteDocument={(id) => this.deleteDocument(id)}
                    onFilesUploaded={() => this.handleUpload()}
                    serviceRequestId={data?.problemID}
                    activityId={data?.callActivityID}
                    documents={data?.documents}
                />
                <InternalDocumentsComponent serviceRequestId={data?.problemID}/>
                {this.getExpensesElement()}
                {this.getTemplateModal()}
                {this.getFooter()}
            </div>
        );
    }

    loadLastActivityInServiceRequest(serviceRequestId) {
        return this.api.getLastActivityInServiceRequest(serviceRequestId).then(res => {
            return this.loadCallActivity(res.data);
        })
    }
}

export default ActivityDisplayComponent;