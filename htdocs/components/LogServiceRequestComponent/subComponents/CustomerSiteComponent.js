import CKEditor from "../../shared/CKEditor.js";
import APICustomers from "../../services/APICutsomer.js";
import Spinner from "../../shared/Spinner/Spinner";
import {padEnd, sort} from "../../utils/utils.js";
import MainComponent from "../../shared/MainComponent.js";
import React from 'react';

class CustomerSiteComponent extends MainComponent {
    el = React.createElement;
    apiCutsomer = new APICustomers();

    constructor(props) {
        super(props);
        const {data} = this.props;
        this.state = {
            ...this.state,
            _showSpinner: false,
            sites: [],
            assets: [],
            data: {
                reason: data.reason || "",
                reasonTemplate: data.reason || "",
                internalNotes: data.internalNotes || "",
                internalNotesTemplate: data.internalNotes || "",
                assetName: data.assetName || "",
                assetTitle: data.assetTitle || "",
                siteNo: data.siteNo || -1,
                emailSubjectSummary: data.emailSubjectSummary || "",
                emptyAssetReason: data.emptyAssetReason || "",
            },
        };
    }

    componentDidMount = async () => {
        const {apiCutsomer} = this;
        const {data} = this.state;
        this.showSpinner();
        const result = await Promise.all([
            apiCutsomer.getCustomerSites(this.props.customerId),
            apiCutsomer.getCustomerAssets(this.props.customerId),
        ]);

        const sites = result[0];
        let assets = sort(result[1], "name");
        if (sites.length == 1) data.siteNo = sites[0].id;
        assets = assets.map((asset) => {
            if (
                asset.BiosName.indexOf("VMware") >= 0 ||
                asset.BiosName.indexOf("Virtual Machine") >= 0
            ) {
                asset.BiosVer = "";
            }
            return asset;
        });
        this.setState({sites, data, assets, _showSpinner: false});
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
        const {data, assets} = this.state;
        if (value != "") {
            const index = assets.findIndex((a) => a.name == value);
            //  console.log(value,index,assets[index]);
            const asset = assets[index];
            data.assetName = value;
            data.assetTitle =
                asset.name + " " + asset.LastUsername + " " + asset.BiosVer;
        } else {
            data.assetName = "";
            data.assetTitle = "";
        }
        this.setState({data});
    };
    getAssetElement = () => {
        const {assets} = this.state;
        const {el} = this;
        return el(
            "div",
            null,
            el("label", {className: "site-label"}, "Asset"),
            el(
                "select",
                {
                    onChange: (event) => this.handleAssetSelect(event.target.value),
                    className: "site-select",
                    value: this.state.data.assetName,
                },
                el("option", {key: "default", value: ""}),
                assets.map((s) =>
                    el(
                        "option",
                        {
                            value: s.name,
                            key: `asset${s.name}`,
                            dangerouslySetInnerHTML: {__html: padEnd(s.name, 110, "&nbsp;") + padEnd(s.LastUsername, 170, "&nbsp;") + " " + s.BiosVer}
                        }
                    )
                )
            )
        );
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
                onChange: (event) =>
                    this.setValue("emailSubjectSummary", event.target.value),
                value: this.state.data.emailSubjectSummary,
            })
        );
    };

    getNotesElement = () => {
        const {el} = this;
        return el(
            "div",
            null,
            el("label", {className: "site-label"}, "Details"),

            el(CKEditor, {
                id: "reason",
                value: this.state.data.reason,
                inline: true,
                height: 200,
                onChange: (data) => this.setValue("reasonTemplate", data),
            }),
            el(
                "div",
                {style: {marginTop: 30}},
                el("label", {className: "mt-5"}, "Internal Notes"),
                el(CKEditor, {
                    id: "internalNotes",
                    value: this.state.data.internalNotes,
                    inline: true,
                    height: 150,
                    onChange: (data) => this.setValue("internalNotesTemplate", data),
                })
            )
        );
    };
    handleNext = async () => {
        let {data} = this.state;
        data.nextStep = 4;
        data.reason = data.reasonTemplate;
        data.internalNotes = data.internalNotesTemplate;
        console.log(data);
        if (data.siteNo == -1) {
            this.alert("Please select customer site");
            return;
        }
        if (data.assetName == "" && data.emptyAssetReason == "") {
            const emptyAssetReason = await this.prompt(
                "Please provide a reason for not specifying an asset", 600
            );
            if (!emptyAssetReason) return;
            else {
                data.emptyAssetReason = emptyAssetReason;
                this.setValue("emptyAssetReason", emptyAssetReason);
            }
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
            getNextButton(),
        );
    }
}

export default CustomerSiteComponent;