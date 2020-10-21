"use strict";
import AutoComplete from "../shared/AutoComplete/autoComplete";
import Table from '../shared/table/table';
import * as Utils from '../utils/utils';
import React from 'react';
import ReactDOM from 'react-dom';
import Spinner from "../shared/Spinner/Spinner";

import '../style.css'

class SRSourceComponent extends React.Component {
    el = React.createElement;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {
            search: {
                customer: null,
                dateFrom: null,
                dateTo: null,
                errors: [],
                result: [],
                resultSummary: [],
            },
            customers: [],
            showSpinner: false
        };
    }

    /**
     * get customers list
     */
    componentDidMount() {
        fetch("/Customer.php?action=searchName")
            .then((res) => res.json())
            .then((data) => {
                this.setState({customers: data});
            });
    }

    /**
     * handle customer select
     * @param {customer} e
     */
    handleOnSelect = (e) => {
        const search = {...this.state.search};
        search.customer = e;
        this.setState({search}, () => this.valid());
    };

    /**
     * return Search React element
     * @param {string} label
     * @param {React.createElement} element
     * @param {string} error
     * @param elementKey
     */
    getSearchElement(label, element, error = null, elementKey = null) {
        const {el} = this;
        return el("div", {key: elementKey + 'row', className: "row"}, [
            el("div", {key: elementKey + 'label', className: "col-1 promptText"}, label),
            el("div", {key: elementKey + 'element', style: {width: 150}}, element),
            el("div", {key: elementKey + 'error', className: "col-5 error"}, error),
        ]);
    }

    /**
     * search for customer SR
     */
    handleSearch = () => {
        this.searchAPI();
    };
    searchAPI = (exportData = false) => {
        if (this.valid()) {
            this.setState({showSpinner: true});

            const {search} = this.state;
            fetch("?action=searchSR&&export=" + exportData, {
                method: "POST",
                body: JSON.stringify({
                    customerID: search.customer ? search.customer.id : null,
                    fromDate: search.dateFrom,
                    toDate: search.dateTo,
                }),
            })
                .then((response) => response.json())
                .then((result) => {
                    const updatedSearch = {...this.state.search};
                    updatedSearch.result = result;
                    updatedSearch.resultSummary = this.getSummary(result);
                    //console.log(result);
                    this.setState({search: updatedSearch, showSpinner: false});
                });
        }
    }

    handleChange = ({currentTarget: input}) => {
        const search = {...this.state.search};
        search[input.name] = input.value;
        this.setState({search}, () => this.valid());
    };
    valid = () => {
        let isValid = true;
        // const search = { ...this.state.search };
        // search.errors = [];
        // if (!search.customer){
        //    search.errors["customer"] = "Please select customer";
        //    isValid=false;
        // }
        // this.setState({ search });
        return isValid;
    };
    makeid = (length = 5) => {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    getSearchElements() {
        const {el, handleOnSelect, handleSearch, handleChange} = this;
        const {customers, search} = this.state;
        return el('div', {key: 'searchForm'}, [
            this.getSearchElement(
                "Customer",
                el(
                    AutoComplete,
                    {
                        key: 'CustomerID',
                        errorMessage: "No Customer found",
                        items: customers,
                        displayLength: "40",
                        displayColumn: "name",
                        pk: "id",
                        onSelect: handleOnSelect,
                    }
                ),
                search.errors["customer"] ? search.errors["customer"] : "", 'customer'
            ),
            this.getSearchElement(
                "From",
                el("input", {
                    key: "dateFrom",
                    name: "dateFrom",
                    type: "date",
                    onChange: handleChange,
                    style: {width: 150},
                }), '', 'dateFrom'
            ),
            this.getSearchElement(
                "To",
                el("input", {
                    key: "dateTo",
                    name: "dateTo",
                    type: "date",
                    onChange: handleChange,
                    style: {width: 150},
                }), '', 'dateTo'
            ),
            this.getSearchElement(
                "",
                [el("button", {key: "searchButton", onClick: handleSearch}, "Search"),
                    search.result.length > 0 ? el("button", {
                        key: "exportButton",
                        onClick: () => Utils.exportCSV(search.result, 'ServiceRequests.csv')
                    }, "Export") : null
                ]
                , '', 'searchButton'
            ),
        ]);
    }

    getSummary = (result) => {
        const el = this.el;
        let summary = {}
        for (let i = 0; i < result.length; i++) {
            if (result[i].raiseType != null)
                summary[result[i].raiseType] = !summary[result[i].raiseType] ? 1 : summary[result[i].raiseType] + 1;
            else
                summary['None'] = !summary['None'] ? 1 : summary['None'] + 1;
        }
        summary['Total'] = result.length;
        return Object.entries(summary);
    }
    getSummaryElements = () => {
        const {el, makeid} = this;
        const {resultSummary} = this.state.search;
        if (resultSummary) {
            let tableWidth = 100 * resultSummary.length;
            return el('table', {key: 'summaryTable', className: 'table table-striped', style: {width: tableWidth}}, [
                el('thead', {key: 'summaryHead'},
                    el('tr', {key: makeid()}, resultSummary.map(s => el('th', {key: makeid()},this.getIconElement(s[0]))))
                ),
                el('tbody', {key: 'summaryBody'},
                    el('tr', {key: makeid()}, resultSummary.map(s => el('td', {key: makeid()}, s[1])))
                )
            ])
        } else return null;

    }
    getIconElement=(name)=>{
        const {el}=this;
        switch (name) {
          case "Email":
            return el("i", { className: "fal fa-envelope fa-2x icon" });
          case "Portal":
            return el("i", { className: "fa fa-edge fa-2x icon" });
          case "Phone":
            return el("i", { className: "fal fa-phone fa-2x icon" });
          case "On site":
            return el("i", { className: "fal fa-building fa-2x icon" });
          case "Alert":
            return el("i", { className: "fal fa-bell fa-2x icon" });
          case "Sales":
            return el("i", { className: "fal fa-shopping-cart fa-2x icon" });            
        case "Manual":
            return el("i", { className: "fal fa-user-edit fa-2x icon" });
        case "Total":
            return el("i", { className: "fal fa-sigma fa-2x icon" });
           
        }
    }

    getSearchResultElement() {
        const columns = [
            {path: "raiseType", label: "Source", sortable: true,},
            {
                path: "CallReference", label: "Service Request", sortable: true,
                content: (sr) =>
                    this.el('a', {key: sr.inialActivity, href: sr.srLink, target: "_blank"}, sr.CallReference)
            },
            {path: "Customer", label: "Customer", sortable: true,},
            {path: "Contact", label: "Contact", sortable: true,},
            {
                path: "Contract", label: "Contract", sortable: true,
                content: (sr) => {
                    let contractDisplay = sr.Contract;
                    if ((sr.status === 'C' || sr.status === 'F') && sr.pro_contract_cuino === null)
                        contractDisplay = "T&M";
                    else if ((sr.status === 'I' || sr.status === 'P') && sr.pro_contract_cuino === null) {
                        contractDisplay = '';
                        //console.log(contractDisplay)

                    }
                    return this.el('label', {key: 'contractDisplay' + sr.CallReference}, contractDisplay);
                }
            },
            {path: "Priority", label: "Priority", sortable: true,},
        ];
        const {result} = this.state.search;
        if (result != null) {
            return this.el(Table, {
                key: 'reaulttable',
                data: result,
                columns: columns,
                defaultSortPath: 'CallReference',
                defaultSortOrder: 'asc',
                pk: 'CallReference'
            })
        } else return null;
    }

    render() {
        const {showSpinner} = this.state;
        return (
            <div className="sr-source">
                <Spinner show={showSpinner}/>
                {this.getSearchElements()}
                {this.getSummaryElements()}
                {this.getSearchResultElement()}
            </div>
        )
    }
}

export default SRSourceComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById("react_main_srsource");
    ReactDOM.render(React.createElement(SRSourceComponent), domContainer);
})

