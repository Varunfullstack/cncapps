import MainComponent from "../../shared/MainComponent";
import React from 'react';
import {sort} from "../../utils/utils";
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

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (JSON.stringify(this.props.filter) !== JSON.stringify(prevProps.filter)) {
            this.getOptionData();
        }
    }

    getUsers = () => {
        const {users, summary} = this.state;
        const {hd, es, sp, p} = this.props.filter;
        let filteredUsers = [];
        if (hd + es + sp + p === 1) {
            filteredUsers.push({id: null, name: 'Unassigned', team: null});
        }
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
                                   key={item.id}
            >
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
        const {hd, es, sp, p} = this.props.filter;
        if (filter.option.id == 5) {
            if (!filter.customer.id) {
                return;
            }
        }

        let queue = this.getSelectedQueue(hd, es, sp, p);
        this.api.getUserProblemsSummary(filter.option.id, filter.customer.id, queue).then(summary => {
            if (filter.option.id < 5)
                filter.customer = {id: null, name: null};
            this.setState({summary: summary.data, filter})
        });
    }

    getSelectedQueue(hd, es, sp, p) {
        const teamQueue = {
            hd: 1,
            es: 2,
            sp: 3,
            p: 5
        }
        let queue = null;
        if (hd + es + sp + p === 1) {
            queue = (hd && teamQueue.hd) || (es && teamQueue.es) || (sp && teamQueue.sp) || (p && teamQueue.p);
        }
        return queue;
    }

    handleSelectCustomer = (customer) => {
        const {filter} = this.state;
        filter.customer = customer;

        this.setState({filter}, () => this.getOptionData())
    }
    handleMove = async (right = true) => {
        const {hd, es, sp, p} = this.props.filter;
        const {filter} = this.state;
        let res;
        let from = filter.fromUser.id;
        let to = filter.toUser.id;
        if (right) {
            res = await this.confirm(`Please confirm you want to assign ${filter.option.name} From ${filter.fromUser.name ?? 'Unassigned' } to ${filter.toUser.name ?? 'Unassigned' }`)
        } else {
            res = await this.confirm(`Please confirm you want to assign ${filter.option.name} From ${filter.toUser.name ?? 'Unassigned' } to ${filter.fromUser.name ?? 'Unassigned' }`)
            from = filter.toUser.id;
            to = filter.fromUser.id;
        }

        if (res) {
            let queue = this.getSelectedQueue(hd, es, sp, p);
            // apply move
            this.api.moveSR(from, to, filter.option.id, filter.customer.id, queue).then(res => {
                if (res.status)
                    this.getOptionData();
            })
        }
    }
    handleExchange = async () => {
        const {filter} = this.state;
        let from = filter.fromUser.id;
        let to = filter.toUser.id;
        const res = await this.confirm(`Please confirm you want to exchange ${filter.option.name} between ${filter.fromUser.name ?? 'Unassigned'} and ${filter.toUser.name?? 'Unassigned'}`);
        if (res) {
            const {hd, es, sp, p} = this.props.filter;
            let queue = this.getSelectedQueue(hd, es, sp, p);
            await this.api.moveSR(from, to, filter.option.id, filter.customer.id, queue, true);
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
                                                                                onChange={this.handleSelectCustomer}
                            ></CustomerSearch> : null}
                        </div>
                        <div
                            style={{display: "flex", justifyContent: "space-between", padding: 5}}
                        >
                            <ToolTip title="Move to left"
                                     width={30}
                            >
                                <li className="fal fa-chevron-double-left fa-2x pointer"
                                    onClick={() => this.handleMove(false)}
                                ></li>
                            </ToolTip>
                            <ToolTip title="Exchange"
                                     width={30}
                            >
                                <li className="fal fa-exchange fa-2x pointer"
                                    onClick={() => this.handleExchange()}
                                ></li>
                            </ToolTip>
                            <ToolTip title="Move to right"
                                     width={30}
                            >
                                <li className="fal fa-chevron-double-right fa-2x pointer"
                                    onClick={() => this.handleMove(true)}
                                ></li>
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

  