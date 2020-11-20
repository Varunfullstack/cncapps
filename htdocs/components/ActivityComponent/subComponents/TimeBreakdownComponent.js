import MainComponent from "../../shared/MainComponent.js";
import * as React from "react";
import APIActivity from "../../services/APIActivity.js";
import {groupBy, params} from "../../utils/utils.js";
import Spinner from "../../shared/Spinner/Spinner.js";

class TimeBreakdownComponent extends MainComponent {
    el = React.createElement;
    api = new APIActivity();

    constructor(props) {
        super(props);
        this.state = {data: [], loading: true};
    }

    componentDidMount() {
        if (params.get("problemID"))
            this.api.getTimeBreakdown(params.get("problemID")).then((data) => {
                data = data.map(d => {
                    return {...d, sname: d.firstName[0] + d.lastName[0]}
                })

                this.setState({data, loading: false});
                console.log('data', data);
            });
    }

    getTimeBreakdownElement = () => {
        const {data} = this.state;
        const dataGroup = groupBy(data, "cat_desc");
        const users = data.map(d => d.sname).reduce((prev, cur) => {
            if (prev.indexOf(cur) == -1)
                prev.push(cur);
            return prev;
        }, []);
        if (data.length == 0)
            return <div style={{display: "flex", justifyContent: "center", alignItems: "center"}}><h2>There has been no
                time logged against this request yet.</h2></div>

        return (
            <div
                style={{
                    display: "flex",
                    flexDirection: "column",
                    justifyContent: "center",
                    alignItems: "center",
                }}
            >
                <table className="table oddRows">
                    <thead>
                    <tr>
                        <th className="text-align-left">Activity</th>
                        {users.map((u) => (
                            <th className="text-align-left"
                                key={u}
                            >{u}</th>
                        ))}
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    {dataGroup.map((g) => (
                        <tr key={g.groupName}>
                            <td>{g.groupName}</td>
                            {users.map((u) => (
                                <td key={u}
                                    className="text-align-left"
                                    style={{color: this.getColor(g.items, u)}}
                                >
                                    {this.getUserTime(g.items, u)}
                                </td>
                            ))}
                            <td className="text-align-left">{this.getActivityTypeTime(g.groupName)}</td>
                        </tr>
                    ))}
                    <tr style={{height: 15}}></tr>
                    <tr>
                        <td>In hours Total</td>
                        {users.map((u) => (
                            <td className="text-align-left"
                                key={u}
                                style={{color: ""}}
                            >
                                {this.getUserTotalInHour(u)}
                            </td>
                        ))}
                    </tr>
                    <tr>
                        <td>Out of hours Total</td>
                        {users.map((u) => (
                            <td className="text-align-left"
                                key={u}
                                style={{color: ""}}
                            >
                                {this.getUserTotalOutHour(u)}
                            </td>
                        ))}
                    </tr>
                    <tr style={{height: 15}}></tr>

                    <tr>
                        <td style={{textAlign: "left"}}>Total</td>
                        {users.map((u) => (
                            <td className="text-align-left"
                                key={u}
                                style={{textAlign: "left"}}
                            >
                                {this.getUserTotal(u)}
                            </td>
                        ))}
                    </tr>
                    </tbody>
                </table>
                <table className="table oddRows"
                       style={{marginTop: 20}}
                >
                    <tbody style={{fontWeight: "bold"}}>
                    <tr>
                        <td>Chargeable Total (in hours)</td>
                        <td>{this.getTotalInHour()}</td>
                    </tr>
                    <tr>
                        <td>Chargeable Total (out of hours)</td>
                        <td>{this.getTotalOutHour()}</td>
                    </tr>
                    <tr style={{height: 15}}/>
                    <tr>
                        <td>Grand Total (in hours)</td>
                        <td>{this.getGrandTotalInHour()}</td>
                    </tr>

                    <tr>
                        <td>Grand Total (out of hours)</td>
                        <td>{this.getGrandTotalOutHour()}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        );
    };

    getActivityTypeTime(activityType) {
        const {data} = this.state;
        const type = data.filter(u => u.cat_desc == activityType);
        if (type.length > 0) {
            return type.reduce((prev, curr) => {
                if (curr)
                    prev += parseFloat(curr.inHours) + parseFloat(curr.outHours);
                return prev;
            }, 0).toFixed(2);

        } else
            return "0.00";
    }

    getUserTime(users, userName) {
        const user = users.filter(u => u.sname == userName);
        if (user.length > 0)
            return (parseFloat(user[0].inHours) + parseFloat(user[0].outHours)).toFixed(2);
        else return "0.00"
    }

    getColor(users, userName) {
        const user = users.filter(u => u.sname == userName);
        let value = 0;
        if (user.length > 0)
            value = (parseFloat(user[0].inHours) + parseFloat(user[0].outHours)).toFixed(2);
        if (value <= 0)
            return '#cccccc';
        else return '000';
    }

    getUserTotal = (sname) => {
        const {data} = this.state;
        const items = data.filter(u => u.sname == sname);
        //console.log(items);
        const sum = items.reduce((prev, cur) => {
            if (cur)
                prev += parseFloat(cur.inHours) + parseFloat(cur.outHours);
            return prev;
        }, 0);
        //console.log(sum);
        return sum.toFixed(2);
    }

    getTotalTime() {
        const {data} = this.state;
        return data.reduce((prev, cur) => {
            if (cur)
                prev += parseFloat(cur.inHours) + parseFloat(cur.outHours);
            return prev;
        }, 0).toFixed(2);
    }

    getTotalInHour() {
        const {data} = this.state;
        return data.reduce((prev, cur) => {
            if (cur && (cur.caa_callacttypeno == 4 || cur.caa_callacttypeno == 8))
                return prev + parseFloat(cur.inHours);
            else return prev;
        }, 0).toFixed(2);
    }

    getGrandTotalInHour() {
        const {data} = this.state;
        return data.reduce((prev, cur) => {
            if (cur)
                return prev + parseFloat(cur.inHours);
            else return prev;
        }, 0).toFixed(2);
    }

    getUserTotalInHour(userName) {
        const {data} = this.state;
        return data.reduce((prev, cur) => {
            if (cur && cur.sname == userName)
                return prev + parseFloat(cur.inHours);
            else return prev;
        }, 0).toFixed(2);
    }

    getGrandTotalOutHour() {
        const {data} = this.state;
        return data.reduce((prev, cur) => {
            if (cur)
                return prev + parseFloat(cur.outHours);
            else return prev;
        }, 0).toFixed(2);
    }

    getTotalOutHour() {
        const {data} = this.state;
        return data.reduce((prev, cur) => {
            if (cur && (cur.caa_callacttypeno == 4 || cur.caa_callacttypeno == 8))
                return prev + parseFloat(cur.outHours);
            else return prev;
        }, 0).toFixed(2);
    }

    getUserTotalOutHour(userName) {
        const {data} = this.state;
        return data.reduce((prev, cur) => {
            if (cur && cur.sname == userName)
                return prev + parseFloat(cur.outHours);
            else return prev;
        }, 0).toFixed(2);
    }

    render() {
        return <div>
            <Spinner show={this.state.loading}/>
            {this.getTimeBreakdownElement()}
        </div>
    }
}

export default TimeBreakdownComponent;
