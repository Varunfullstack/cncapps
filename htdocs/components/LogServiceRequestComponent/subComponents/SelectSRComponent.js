import RadioButtons, {RadioButtonsType} from "../../shared/radioButtons";
import Spinner from "../../shared/Spinner/Spinner";
import CustomerSRComponent from "./CustomerSRComponent.js";
import SVCCustomers from "../../services/ApiCustomers.js";
import React, {Fragment} from 'react';

const CONTACT_SR_TAB = 'COSR';

const CUSTOMER_SR_TAB = 'CUSR';

const CONTACT_FIXED_TAB = 'CFSR';

class SelectSRComponent extends React.Component {
    el = React.createElement;
    apicustomer = new SVCCustomers();

    tabs = [];

    constructor(props) {
        super(props);
        this.state = {
            srType: 1,
            activeTab: CONTACT_SR_TAB,
            contactSR: [],
            contactFixedSR: [],
            customerSR: [],
            currentItems: [],
            _showSpinner: true,
            showContactColumn: false
        };
        this.initTaps();
    }

    componentDidMount() {
        this.getExistingSR(this.props.customerId, this.props.contactId);
    }

    showSpinner = () => {
        this.setState({_showSpinner: true});
    }
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    }
    initTaps = () => {
        this.tabs = [
            {id: 1, title: 'Contact SR', code: CONTACT_SR_TAB, order: 1, display: true, icon: null},
            {id: 2, title: 'Customer SR', code: CUSTOMER_SR_TAB, order: 2, display: true, icon: null},
            {id: 3, title: 'Contact Fixed SR', code: CONTACT_FIXED_TAB, order: 3, display: true, icon: null},
        ];
    }
    getTabsElement = () => {

        const {el, isActive, setActiveTab, tabs} = this;
        return el("div", {key: "tab", className: "tab-container"},
            tabs.sort((a, b) => a.order > b.order ? 1 : -1).map(t => {
                if (t.display)
                    return el(
                        "i",
                        {
                            key: t.code,
                            className: isActive(t.code) + " nowrap",
                            onClick: () => setActiveTab(t.code),
                        },
                        t.title,
                        t.icon ? el("span", {
                            className: t.icon, style: {
                                fontSize: "12px",
                                marginTop: "-12px",
                                marginLeft: "-5px",
                                position: "absolute",
                                color: "#000"
                            }
                        }) : null
                    );
                else return null;
            }));
    };
    isActive = (code) => {
        const {activeTab} = this.state;
        if (activeTab == code) return "active";
        else return "";
    };
    setActiveTab = (code) => {
        let nextState = {activeTab: code, currentItems: this.state.currentItems};
        switch (code) {
            case CONTACT_SR_TAB:
                nextState.currentItems = this.state.contactSR;
                nextState.showContactColumn = false;
                break;
            case CONTACT_FIXED_TAB:
                nextState.currentItems = this.state.contactFixedSR;
                nextState.showContactColumn = false;
                break;
            case CUSTOMER_SR_TAB:
                nextState.currentItems = this.state.customerSR;
                nextState.showContactColumn = true;
        }

        this.setState(nextState);
    };

    handleSrTypeChange = (srType) => {
        if (srType == 2)
            this.props.updateSRData({nextStep: 3,customerSR:this.state.customerSR})
        this.setState({srType});
    }
    getSRTypeElement = () => {
        const {el, handleSrTypeChange} = this;
        const items = [{id: 1, name: 'Existing Request'}, {id: 2, name: 'New Service Request'}];
        return el("div", null, el(RadioButtons, {
            items,
            mode: RadioButtonsType.horizontal,
            center: true,
            value: 1,
            onChange: handleSrTypeChange
        }));
    }
    getExistingSR = (customerId, contactId) => {
        this.showSpinner();
        this.apicustomer.getCustomerSR(customerId).then(data => {
            const customerSR = data
            const contactRelatedServiceRequests = data.filter(s => s.contactId == contactId);
            const contactSR = contactRelatedServiceRequests.filter(x => x.status !== 'F');
            const contactFixedSR = contactRelatedServiceRequests.filter(s => s.status == "F");
            this.setState({customerSR, contactSR, contactFixedSR, currentItems: contactSR, _showSpinner: false});
        });
    }
    openProblemHistory = (problemId) => {
        window.open(
            'Activity.php?action=problemHistoryPopup&problemID=' + problemId + '&htmlFmt=popup',
            'reason',
            'scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0')
    }
    newSrActivity = (problemId, activityId) => {
        window.location = "Activity.php?action=createFollowOnActivity&callActivityID=" + activityId;

    }

    render() {
        const {getSRTypeElement, getTabsElement, openProblemHistory, newSrActivity} = this;
        const {_showSpinner, showContactColumn, currentItems} = this.state;
        return (
            <Fragment>
                <Spinner show={_showSpinner}/>
                <div>
                    {getSRTypeElement()}
                    {getTabsElement()}
                    <CustomerSRComponent items={currentItems}
                                         openProblemHistory={openProblemHistory}
                                         newSrActivity={newSrActivity}
                                         showContactColumn={showContactColumn}
                    />
                </div>
            </Fragment>
        )
    }
}

export default SelectSRComponent;