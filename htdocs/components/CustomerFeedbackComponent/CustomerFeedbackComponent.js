"use strict";
import ReactDOM from 'react-dom';
import React from 'react';

import Spinner from "../shared/Spinner/Spinner";
import MainComponent from "../shared/MainComponent";
import moment from "moment";
import APICustomerFeedback from './services/APICustomerFeedback';
import '../style.css';
import CustomerSearch from '../shared/CustomerSearch';
import ToolTip from '../shared/ToolTip';
import APIUser from '../services/APIUser';
import Table from '../shared/table/table';
import './CustomerFeedbackComponent.css';
import Toggle from "../shared/Toggle";

class CustomerFeedbackComponent extends MainComponent {
    api = new APICustomerFeedback();
    apiUser = new APIUser();

    constructor(props) {
        super(props);
        this.state = {
            showSpinner: false,
            users: [],
            filter: {
                from: moment().subtract(1, 'M').format('YYYY-MM-DD'),
                to: '',
                customerID: '',
                engineerID: '',
                customerName: '',
                hd: true,
                es: true,
                sp: true,
                p: true,
            }
        }
    }

    componentDidMount = async () => {
        this.getData();
        this.apiUser.getActiveUsers().then(users => this.setState({users}));
    }

    getData = () => {
        const {filter} = this.state;
        this.api.getCustomerFeedback(
            filter.from,
            filter.to,
            filter.customerID,
            filter.engineerID,
            filter.hd,
            filter.es,
            filter.sp,
            filter.p,
        ).then(feedbacks => {
            this.setState({feedbacks});
        })
    }
    getSearchElement = () => {
        const {filter, users} = this.state;
        return <table>
            <tbody>
            <tr>
                <td>Customer</td>
                <td>
                    <CustomerSearch width={200}
                                    customerName={filter.customerName}
                                    onChange={(customer) => this.handleCustomer(customer)}
                    />
                </td>
            </tr>
            <tr>
                <td>Engineer</td>
                <td>
                    <select value={filter.engineerID}
                            onChange={(event) => this.setFilter('engineerID', event.target.value)}
                            className="input"
                    >
                        <option/>
                        {users.map(u => <option key={u.id}
                                                value={u.id}
                        >{u.name}</option>)}
                    </select>
                </td>
            </tr>
            <tr>
                <td>Date From</td>
                <td>
                    <input className="input"
                           type="date"
                           value={filter.from}
                           onChange={(event) => this.setFilter('from', event.target.value)}
                    />
                </td>
            </tr>
            <tr>
                <td>Date To</td>
                <td><input className="input"
                           type="date"
                           value={filter.to}
                           onChange={(event) => this.setFilter('to', event.target.value)}
                /></td>
            </tr>
            <tr>
                <td/>
                <td>
                    <div style={{display: "flex"}}>
                        <ToolTip title="Search"
                                 width={30}
                        >
                            <i className="fal fa-search fa-2x icon pointer"
                               onClick={() => this.getData()}
                            />
                        </ToolTip>
                        <ToolTip title="Reset"
                                 width={30}
                        >
                            <i className="fal fa-sync fa-2x icon pointer"
                               onClick={() => this.clearSearch()}
                            />
                        </ToolTip>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    }
    handleCustomer = (customer) => {
        if (customer) {
            const {filter} = this.state;
            filter.customerID = customer.id;
            filter.customerName = customer.name;
            console.log(filter);
            this.setState({filter});
        }
    }
    clearSearch = () => {
        const {filter} = this.state;
        filter.engineerID = '';
        filter.from = '';
        filter.to = '';
        filter.customerID = '';
        filter.customerName = '';
        filter.hd = true;
        filter.es = true;
        filter.sp = true;
        filter.p = true;
        this.setState({filter});

    }
    getSearchResultElement = () => {
        const {feedbacks} = this.state;
        const columns = [
            {
                path: "value",
                label: "",
                hdToolTip: "Comments",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-heart color-gray2 pointer",
                sortable: true,
                content: (feed) => {
                    switch (feed.value) {
                        case 1:
                            return <i className="fal fa-smile fa-2x"/>
                        case 2:
                            return <i className="fal fa-meh fa-2x "/>
                        case 3:
                            return <i className="fal fa-frown fa-2x "/>
                        default:
                            return '';
                    }
                }
                //className: "text-center",
            },
            {
                path: "problemID",
                label: "",
                hdToolTip: "Service Request",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: true,
                content: (feed) => <a href={`SRActivity.php?action=displayActivity&serviceRequestId=${feed.problemID}`}
                                      target="_blank"
                >{feed.problemID}</a>,
                className: "text-center",
            },
            {
                path: "cus_name",
                label: "",
                hdToolTip: "Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: true,
                // className: "text-center",
            },
            {
                path: "engineer",
                label: "",
                hdToolTip: "Engineer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
                sortable: true,
                width: 120
                // className: "text-center",
            },
            {
                path: "contactName",
                label: "",
                hdToolTip: "Contact",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-id-card-alt color-gray2 pointer",
                sortable: true,
                width: 120
                // className: "text-center",
            },
            {
                path: "comments",
                label: "",
                hdToolTip: "Comments",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: true,
                //className: "text-center",
            },
            {
                path: "createdAt",
                label: "",
                hdToolTip: "Date of feedback",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-calendar color-gray2 pointer",
                sortable: true,
                className: "text-center",
                content: feed => {
                    return moment(feed.createdAt).format('DD/MM/YYYY aa');
                }
            },
        ];
        return <Table id="myfeedback"
                      data={feedbacks || []}
                      columns={columns}
                      pk="id"
                      search={true}
        >
        </Table>
    }

    setFilterValue = (property, value) => {
        const {filter} = this.state;
        filter[property] = value;
        this.setState({filter});
    };

    render() {
        const {minHeight, filter} = this.state;
        return (
            <div id="main-container"
                 style={{minHeight: minHeight, marginBottom: 50}}
            >
                <Spinner show={this.state.showSpinner}/>
                <label className="mr-3 ml-5">HD</label>
                <Toggle checked={filter.hd}
                        onChange={() => this.setFilterValue("hd", !filter.hd)}
                />
                <label className="mr-3 ml-5">ES</label>
                <Toggle checked={filter.es}
                        onChange={() => this.setFilterValue("es", !filter.es)}
                />
                <label className="mr-3 ml-5">SP</label>
                <Toggle checked={filter.sp}
                        onChange={() => this.setFilterValue("sp", !filter.sp)}
                />
                <label className="mr-3 ml-5">P</label>
                <Toggle checked={filter.p}
                        onChange={() => this.setFilterValue("p", !filter.p)}
                />
                {this.getSearchElement()}
                {this.getSearchResultElement()}
            </div>
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainCustomerFeedback");
    ReactDOM.render(React.createElement(CustomerFeedbackComponent), domContainer);
});
