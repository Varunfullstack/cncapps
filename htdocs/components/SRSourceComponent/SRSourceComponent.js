"use strict";
import AutoComplete from "../shared/AutoComplete/autoComplete";
import Table from '../shared/table/table';
import ToolTip from "../shared/ToolTip";
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
        return true;
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
        return el("div", {key: "searchForm"}, [
            this.getSearchElement(
                "Customer",
                el(AutoComplete, {
                    key: "CustomerID",
                    errorMessage: "No Customer found",
                    items: customers,
                    displayLength: "40",
                    displayColumn: "name",
                    pk: "id",
                    onSelect: handleOnSelect,
                }),
                search.errors["customer"] ? search.errors["customer"] : "",
                "customer"
            ),
            this.getSearchElement(
                "From",
                el("input", {
                    key: "dateFrom",
                    name: "dateFrom",
                    type: "date",
                    onChange: handleChange,
                    style: {width: 150},
                }),
                "",
                "dateFrom"
            ),
            this.getSearchElement(
                "To",
                el("input", {
                    key: "dateTo",
                    name: "dateTo",
                    type: "date",
                    onChange: handleChange,
                    style: {width: 150},
                }),
                "",
                "dateTo"
            ),
            this.getSearchElement(
                "",
                [
                    el('div', {style: {display: "flex", flexDirection: "row", width: "70"}},
                        el(ToolTip, {
                            key: "search", title: "Search", content:
                                el(
                                    "i",
                                    {
                                        key: "searchButton",
                                        className: "fal fa-search fa-2x pointer  icon m-5 ",
                                        onClick: handleSearch,
                                    },
                                ),
                        }),
                        search.result.length > 0
                            ? el(ToolTip, {
                                key: "export", title: "Export Results to CSV file", content: el(
                                "i",
                                {
                                    key: "exportButton",
                                    className: "fal fa-file-csv fa-2x pointer  icon m-5 ",
                                    onClick: () =>
                                        Utils.exportCSV(search.result, "ServiceRequests.csv"),
                                },
                                )
                            })
                            : null,
                    )
                ],
                "",
                "searchButton"
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
        const {resultSummary} = this.state.search;
        if (resultSummary && resultSummary.length) {
            let tableWidth = 100 * resultSummary.length;
            console.log(resultSummary);
            const total = resultSummary[resultSummary.length - 1][1];
            console.log(total);
            return <table key='summaryTable' className='table table-striped' style={{width: tableWidth}}>
                <thead key='summaryHead'>
                <tr key='summaryHeaderRow'>
                    {resultSummary.map((s, idx) => <th key={idx}>{this.getIconElement(s[0])}</th>)}
                </tr>
                </thead>
                <tbody key="summaryBody">
                <tr key="summaryDataRow">
                    {
                        resultSummary.map((s, idx) => <td key={idx} style={{textAlign: "center"}}>{s[1]}</td>)
                    }
                </tr>
                <tr key="percentageRow">
                    {
                        resultSummary.map((s, idx) => <td key={idx} style={{textAlign: "center"}}>{((s[1] / total)*100).toFixed(2)}%</td>)
                    }
                </tr>
                </tbody>
            </table>
        } else return null;

    }
    getIconElement = (name) => {
        const {el} = this;
        switch (name) {
            case "Email":
                return el(ToolTip, {
                    title: "Email",
                    content: el("i", {className: "fal fa-envelope fa-2x icon pointer"})
                });
            case "Portal":
                return el(ToolTip, {
                    title: "Portal",
                    content: el("i", {className: "icon-chrome_icon fa-2x icon pointer"})
                });
            case "Phone":
                return el(ToolTip, {title: "Phone", content: el("i", {className: "fal fa-phone fa-2x icon pointer"})});
            case "On site":
                return el(ToolTip, {
                    title: "On site",
                    content: el("i", {className: "fal fa-building fa-2x icon pointer"})
                });
            case "Alert":
                return el(ToolTip, {title: "Alert", content: el("i", {className: "fal fa-bell fa-2x icon pointer"})});
            case "Sales":
                return el(ToolTip, {
                    title: "Sales",
                    content: el("i", {className: "fal fa-shopping-cart fa-2x icon pointer"})
                });
            case "Manual":
                return el(ToolTip, {
                    title: "Manual",
                    content: el("i", {className: "fal fa-user-edit fa-2x icon pointer"})
                });
            case "Total":
                return el(ToolTip, {title: "Total", content: el("i", {className: "fal fa-sigma fa-2x icon pointer"})});
            default :
                return el(ToolTip, {
                    title: "Unknown",
                    content: el("i", {className: "fal fa-question fa-2x icon pointer"})
                });

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
                    if ((sr.status == 'C' || sr.status == 'F') && sr.pro_contract_cuino == null)
                        contractDisplay = "T&M";
                    else if ((sr.status == 'I' || sr.status == 'P') && sr.pro_contract_cuino == null) {
                        contractDisplay = '';
                    }
                    return this.el('label', {key: 'contractDisplay' + sr.CallReference}, contractDisplay);
                }
            },
            {path: "Priority", label: "Priority", sortable: true,},
        ];
        const {result} = this.state.search;
        if (result != null) {
            return this.el(Table, {
                key: 'resultTable',
                id: 'reaulttable',
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
            <div className="sr-source" key='something'>
                <Spinner show={showSpinner} key="spinner"/>
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

