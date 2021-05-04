import APICustomers from "../../services/ApiCustomers.js";
import Spinner from "../../shared/Spinner/Spinner";
import MainComponent from "../../shared/MainComponent.js";
import React from 'react';
import APIStandardText from "../../services/APIStandardText";
import EditorFieldComponent from "../../shared/EditorField/EditorFieldComponent";
import AssetListSelectorComponent from "../../shared/AssetListSelectorComponent/AssetListSelectorComponent";
import {bigger, params, similarity} from "../../utils/utils.js";
import APIHeader from "../../services/APIHeader.js";

class CustomerSiteComponent extends MainComponent {
    el = React.createElement;
    apicustomer = new APICustomers();
    apiStandardText = new APIStandardText();
    suggestTimeOut = null;
    apiHeader = new APIHeader();

    constructor(props) {
        super(props);
        const {data} = this.props;
        this.state = {
            ...this.state,
            _showSpinner: false,
            sites: [],
            assets: [],
            noAssetStandardTextItems: [],
            emptyAssetReasonModalShowing: false,
            data: {
                reason: data.reason || "",
                reasonTemplate: data.reason || "",
                internalNotes: data.internalNotes || "",
                internalNotesTemplate: data.internalNotes || "",
                assetName: data.assetName || "",
                assetTitle: data.assetTitle || "",
                siteNo: data.siteNo || -1,
                emailSubjectSummary: data.emailSubjectSummary || params.get("emailSubject") || "",
                emptyAssetReason: data.emptyAssetReason || "",
            },
            suggestSR: [],
            keywordMatchingPercent: 0.5
        };
    }

    cleanupListener() {
        this.listenerCleanupFunc();
    }

    registerListener() {
        const beforeUnloadFn = (e) => {
            e.preventDefault(); // If you prevent default behavior in Mozilla Firefox prompt will always be shown
            // Chrome requires returnValue to be set
            e.returnValue = '';
        };
        window.addEventListener('beforeunload', beforeUnloadFn);
        this.listenerCleanupFunc = () => {
            window.removeEventListener('beforeunload', beforeUnloadFn);
        }
    }

    componentWillUnmount() {
        this.cleanupListener();
    }

    componentDidMount = async () => {
        this.registerListener();
        const {apicustomer} = this;
        const {data} = this.state;
        this.showSpinner();
        let [sites] = await Promise.all([
            apicustomer.getCustomerSites(this.props.customerId),
        ]);

        if (sites.length == 1) data.siteNo = sites[0].id;

        this.setState({sites, data, _showSpinner: false});
        this.apiHeader.getKeywordMatchingPercent().then(res => {

            this.setState({keywordMatchingPercent: res / 100})
        })
    };
    showSpinner = () => {
        this.setState({_showSpinner: true});
    };
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    };
    getSitesElement = () => {
        const {sites, data} = this.state;
        const {el} = this;
        return el(
            "div",
            null,
            el("label", {className: "site-label"}, "Site"),
            el(
                "select",
                {
                    value: data.siteNo,
                    onChange: (event) => this.setValue("siteNo", event.target.value),
                    className: "site-select",
                },
                el("option", {key: "default", value: -1}),
                sites.map((s) =>
                    el("option", {value: s.id, key: `site${s.id}`}, s.title)
                )
            )
        );
    };

    setValue = (label, value) => {
        const {data} = this.state;
        data[label] = value;
        this.setState({data});
    };
    handleAssetSelect = (value) => {
        const {data} = this.state;
        data.assetName = "";
        data.assetTitle = "";
        data.emptyAssetReason = "";
        if (value) {
            if (value.isAsset) {
                data.assetName = value.name;
                data.assetTitle = value.name + " " + value.LastUsername + " " + value.BiosVer;
            } else {
                data.emptyAssetReason = value.template;
            }
        }
        this.setState({data});
        this.checkSuggestSR();
    };
    getAssetElement = () => {
        const {customerId} = this.props;
        return (
            <div>

                <label className="site-label">
                    Asset
                </label>
                <div style={{display: 'inline-block', width: "500px"}}>
                    <AssetListSelectorComponent customerId={customerId}
                                                onChange={value => this.handleAssetSelect(value)}
                    />
                </div>
            </div>
        )
    };
    getEmailSubjectSummary = () => {
        const {el} = this;
        return el(
            "div",
            null,
            el("label", {className: "site-label"}, "Email Subject Summary"),
            el("input", {
                maxLength: 50,
                style: {width: 292, margin: 2},
                className: 'spellcheck',
                onChange: (event) => {
                    this.setValue("emailSubjectSummary", event.target.value);
                    this.checkSuggestSR();
                },
                value: this.state.data.emailSubjectSummary,
            })
        );
    };

    getNotesElement = () => {
        return (
            <React.Fragment>
                <div>
                    <label className="site-label">
                        Details
                    </label>
                    <EditorFieldComponent name="reason"
                                          value={this.state.data.reason}
                                          onChange={(value) => this.handleReasonChange(value)}
                    />
                </div>
                <div>
                    <label className="site-label">
                        Internal Notes
                    </label>
                    <EditorFieldComponent name="internalNotes"
                                          value={this.state.data.internalNotes}
                                          onChange={(value) => this.setValue("internalNotesTemplate", value)}
                    />
                </div>
            </React.Fragment>
        );
    };
    handleReasonChange = (value) => {
        this.setValue("reasonTemplate", value);

        this.checkSuggestSR();
    }
    checkSuggestSR = async () => {
        if (this.suggestTimeOut)
            clearTimeout(this.suggestTimeOut);
        this.suggestTimeOut = setTimeout(async () => {
            const {data} = this.state;
            const {customerSR} = this.props.data;
            let assetSR = [];
            if (data.assetName) {
                const {customerSR} = this.props.data;
                assetSR = customerSR.filter(p => p.assetName == data.assetName);
            }
            for (let i = 0; i < customerSR.length; i++) {
                const reasonPerc = await similarity(data.reasonTemplate, customerSR[i].reason);
                const emailSubjectSummaryPerc = await similarity(data.emailSubjectSummary, customerSR[i].emailSubjectSummary);
                customerSR[i]["percent"] = bigger([
                    reasonPerc,
                    emailSubjectSummaryPerc,
                ]);
            }
            let suggestSRFinal = customerSR.filter((p) => p.percent > this.state.keywordMatchingPercent);
            for (let i = 0; i < assetSR.length; i++) {
                if (suggestSRFinal.filter(p => p.activityID == assetSR[i].activityID).length == 0)
                    suggestSRFinal.push(assetSR[i])
            }
            if (!data.assetName && !data.reasonTemplate && !data.emailSubjectSummaryPerc)
                suggestSRFinal = [];
            this.setState({suggestSR: suggestSRFinal});
        }, 1000)

    }
    handleNext = async () => {
        let {data} = this.state;
        data.nextStep = 4;
        data.reason = data.reasonTemplate;
        data.internalNotes = data.internalNotesTemplate;
        const hasGrammaticalErrors = await this.editorHasProblems();
        if (hasGrammaticalErrors) {
            return;
        }
        if (data.siteNo == -1) {
            this.alert("Please select customer site");
            return;
        }
        if (!data.assetName && !data.emptyAssetReason) {
            this.alert("Please select an asset or a reason");
            return;
        }
        if (data.emailSubjectSummary == "") {
            this.alert("You must enter Email Subject Summary");
            return;
        }
        if (data.reason == "") {
            this.alert("Please enter details");
            return;
        }

        this.props.updateSRData(data);
    };

    getNextButton = () => {
        const {el, handleNext} = this;
        return el(
            "div",
            null,
            el("button", {onClick: handleNext, className: "float-left"}, "Next >")
        );
    };
    getSuggestSR = () => {
        const {suggestSR} = this.state;
        if (suggestSR.length > 0) {
            return <div className="flex-column" style={{
                position: "absolute",
                marginTop: -100,
                marginLeft: 860,
                background: "white",

                minHeight: 100
            }}>
                <div style={{
                    backgroundColor: "#58585a",
                    color: "white", padding: 5
                }}>
                    Related Service Requests That Might Be Relevant
                </div>
                {suggestSR.map(p => <div key={p.activityID} style={{padding: 5,}}>

                    <a href={`SRActivity.php?action=displayActivity&serviceRequestId=${p.problemID}`} target="_blank"
                       rel="noreferrer">
                        {this.getProblemStatus(p.status)} {p.reason}
                    </a>
                </div>)}
            </div>
        } else
            return null;
    }

    getProblemStatus(code) {
        switch (code) {
            case "I":
                return "INITIAL: ";
            case "P":
                return "IN PROGRESS: ";
            case "F":
                return "FIXED: ";
        }
    }

    openProblemHistory = (problemId) => {
        window.open(
            'Activity.php?action=problemHistoryPopup&problemID=' + problemId + '&htmlFmt=popup',
            'reason',
            'scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0')
    }

    render() {
        const {_showSpinner} = this.state;
        const {
            el,
            getNextButton,
            getSitesElement,
            getAssetElement,
            getNotesElement,
        } = this;
        return el(
            "div",
            {style: {width: 850}},
            el(Spinner, {show: _showSpinner}),
            this.getPrompt(),
            this.getAlert(),
            getSitesElement(),
            getAssetElement(),
            this.getEmailSubjectSummary(),
            getNotesElement(),
            this.getSuggestSR(),
            getNextButton(),
        );
    }
}

export default CustomerSiteComponent;