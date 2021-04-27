import MainComponent from "../../shared/MainComponent";
import React from 'react';
import {equal, sort} from "../../utils/utils";
import APISDManagerDashboard from "../services/APISDManagerDashboard";
import APIUser from "../../services/APIUser";
import ToolTip from "../../shared/ToolTip";
import CustomerSearch from "../../shared/CustomerSearch";

export default class MassAssignmentComponent extends MainComponent {
    api = new APISDManagerDashboard();
    apiUser = new APIUser();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            users: [],
            params: {},
            filter: {
                fromUser: {id: null, name: null},
                toUser: {id: null, name: null},
                option: {id: null, name: null},
                customer: {id: null, name: null}
            },
            summary: []
        };
    }

    componentDidMount() {
        this.apiUser.getActiveUsers().then(users => {
            this.setState({users});
        });
    }

    getUsers = () => {
        const {users, summary} = this.state;
        const {hd, es, sp, p} = this.props.filter;
        let filteredUsers = [];
        if (hd)
            filteredUsers = [
                ...filteredUsers,
                ...users.filter((u) => u.teamId == 1),
            ];
        if (es)
            filteredUsers = [
                ...filteredUsers,
                ...users.filter((u) => u.teamId == 2),
            ];
        if (sp)
            filteredUsers = [
                ...filteredUsers,
                ...users.filter((u) => u.teamId == 4),
            ];
        if (p)
            filteredUsers = [
                ...filteredUsers,
                ...users.filter((u) => u.teamId == 5),
            ];
        filteredUsers.map(u => {
            u.total = 0;
        });
        if (summary && summary.length > 0)
            filteredUsers.map(u => {
                const totals = summary.filter(s => s.id == u.id);
                if (totals.length > 0)
                    u.total = summary.filter(s => s.id == u.id)[0].total || 0;
                return u;
            });
        filteredUsers = sort(filteredUsers, 'name')
        return filteredUsers;

    }

    getList = (items, value = null, onClick) => {
        return <ul className="custom-list">
            {items.map(item => <li onClick={() => onClick(item)}
                                   className={"custom-list-item " + (item.id == value ? " selected" : "")}
                                   key={item.id}>
                <div style={{display: "flex", justifyContent: "space-between"}}>
                    <p>{item.name}</p>
                    <p>{item.total}</p>
                </div>
            </li>)}
        </ul>
    }

    handleFromSelect = (user) => {
        this.setFilter("fromUser", user);
    }
    handleToSelect = (user) => {
        this.setFilter("toUser", user);
    }
    handleOptionSelect = (option) => {
        this.setFilter("option", option);
        this.getOptionData();
    }
    getOptionData = () => {
        const {filter} = this.state;

        this.api.getUserProblemsSummary(filter.option.id, filter.customer.id).then(summary => {
            if (filter.option.id < 5)
                filter.customer = {id: null, name: null};
            this.setState({summary: summary.problems, filter})
        });
    }
    handleSelectCustomer = (customer) => {
        const {filter} = this.state;
        filter.customer = customer;

        this.setState({filter}, () => this.getOptionData())
    }
    handleMove = async (right = true) => {
        const {filter} = this.state;
        let res = false;
        let from = filter.fromUser.id;
        let to = filter.toUser.id;
        if (right) {
            res = await this.confirm(`Please confirm you want to assign ${filter.option.name} From ${filter.fromUser.name} to ${filter.toUser.name}`)
        } else {
            res = await this.confirm(`Please confirm you want to assign ${filter.option.name} From ${filter.toUser.name} to ${filter.fromUser.name}`)
            from = filter.toUser.id;
            to = filter.fromUser.id;
        }
        if (res) {
            // apply move
            this.api.moveSR(from, to, filter.option.id, filter.customer.id).then(res => {
                if (res.status)
                    this.getOptionData();
            })
        }
    }
    handleExchange = async () => {
        const {filter} = this.state;
        let res = false;
        let from = filter.fromUser.id;
        let to = filter.toUser.id;
        res = await this.confirm(`Please confirm you want to exchange ${filter.option.name} between ${filter.fromUser.name} and ${filter.toUser.name}`);
        if (res) {
            // apply move
            await this.api.moveSR(from, -1, filter.option.id, filter.customer.id);
            await this.api.moveSR(to, from, filter.option.id, filter.customer.id);
            await this.api.moveSR(-1, to, filter.option.id, filter.customer.id);
            this.getOptionData();
        }
    }

    render() {
        const {filter} = this.state;
        const users = this.getUsers();
        const options = [
            {id: 1, name: "All SRs"},
            {id: 2, name: "All Unstarted SRs"},
            {id: 3, name: "All SRs In Progress"},
            {id: 4, name: "All SRs On Hold"},
            {id: 5, name: "All Customer SRs"},
        ];
        return (
            <div>
                {this.getConfirm()}
                <div
                    className="flex-row"
                    style={{width: 800, alignItems: "center"}}
                >
                    <div className="text-center">
                        <h3>From</h3>
                        {this.getList(users, filter.fromUser.id, this.handleFromSelect)}
                    </div>

                    <div className="  text-center">
                        {this.getList(options, filter.option.id, this.handleOptionSelect)}
                        <div className="text-left">
                            {this.state.filter.option.id == 5 ? <CustomerSearch width={250}
                                                                                onChange={this.handleSelectCustomer}></CustomerSearch> : null}
                        </div>
                        <div
                            style={{display: "flex", justifyContent: "space-between", padding: 5}}
                        >
                            <ToolTip title="Move to left" width={30}>
                                <li className="fal fa-chevron-double-left fa-2x pointer"
                                    onClick={() => this.handleMove(false)}></li>
                            </ToolTip>
                            <ToolTip title="Exchange" width={30}>
                                <li className="fal fa-exchange fa-2x pointer"
                                    onClick={() => this.handleExchange()}></li>
                            </ToolTip>
                            <ToolTip title="Move to right" width={30}>
                                <li className="fal fa-chevron-double-right fa-2x pointer"
                                    onClick={() => this.handleMove(true)}></li>
                            </ToolTip>
                        </div>
                    </div>

                    <div className=" text-center">
                        <h3>To</h3>
                        {this.getList(users, filter.toUser.id, this.handleToSelect)}
                    </div>
                </div>
            </div>
        );
    }

}

  