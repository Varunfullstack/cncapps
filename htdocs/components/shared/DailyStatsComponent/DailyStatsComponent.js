import MainComponent from "../MainComponent";
import {SRQueues} from "../../utils/utils";
import APISDManagerDashboard from "../../SDManagerDashboardComponent/services/APISDManagerDashboard";
import Spinner from '../Spinner/Spinner';
import React from 'react';

import './DailyStatsComponent.css';

class DailyStatsComponent extends MainComponent {
    el = React.createElement;
    apiSDManagerDashboard = new APISDManagerDashboard();
    intervalHandler;
    loading = true;

    constructor(props) {
        super(props);
        this.state = {
            summary: {
                prioritySummary: [],
                openSrTeamSummary: [],
                dailySourceSummary: [],
                raisedTodaySummary: {total: 0},
                fixedTodaySummary: {total: 0},
                nearSLASummary: {total: 0},
                reopenTodaySummary: {total: 0},
            },
            showSpinner: true,
        };
    }

    componentWillUnmount() {
        clearInterval(this.intervalHandler);
    }

    componentDidMount() {
        this.loadDashBoard();
        this.intervalHandler = setInterval(() => this.loadDashBoard(), 2 * 60 * 1000);
    }

    loadDashBoard = () => {
        this.apiSDManagerDashboard.getDailyStatsSummary().then((result) => {
            this.loading = false;
            this.setState({showSpinner: false, summary: result});
        });
    };

    getSummaryElement = () => {
        const {el} = this;
        const {summary} = this.state;
        if (this.loading)
            return null;
        const evenBackgroundColor = "#00628B";
        const eventTextColor = "#E6E6E6";

        return <table>
            <tbody>
            <tr>
                <td>{this.getOpenSrCard(summary.prioritySummary)}</td>
                <td>{this.getTeamSrCard(summary.openSrTeamSummary, evenBackgroundColor, eventTextColor)}</td>
                <td>{this.getDailySourceCard(summary.dailySourceSummary)}</td>
                <td>{this.getDailyInboundOutBoundCard(summary.inboundOutbound, evenBackgroundColor, eventTextColor)}</td>
                <td>{this.getTotalCardWithBiggerNumber("Near SLA", summary.nearSLASummary.total)}</td>
                <td>{this.getTotalCardWithBiggerNumber("Near Fix SLA Breach", summary.nearFixSLABreach, evenBackgroundColor, eventTextColor)}</td>

                <td>{
                    this.getTotalCardWithBiggerNumberA({
                            label: "Unique Customers",
                            total: summary.uniqueCustomerTodaySummary.total
                        },
                        evenBackgroundColor,
                        eventTextColor
                    )
                }</td>
                <td>
                    {
                        this.getTotalCardWithBiggerNumberA(
                            {label: "Near SLA", total: summary.nearSLASummary.total},
                            "#C6C6C6",
                            "#3C3C3C",
                            '/DailyStatsDashboard.php?tab=NEAR_SLA'
                        )
                    }
                </td>
                <td>
                    {
                        this.getTotalCardWithBiggerNumberA({
                                label: "Near Fix SLA Breach",
                                total: summary.nearFixSLABreach
                            },
                            evenBackgroundColor,
                            eventTextColor,
                            '/DailyStatsDashboard.php?tab=NEAR_FIX_SLA_BREACH'
                        )
                    }
                </td>
            </tr>
            <tr>
                <td>
                    {this.getTotalCardWithBiggerNumberA({
                            label: "Raised Today",
                            total: summary.raisedTodaySummary.total
                        },
                        evenBackgroundColor,
                        eventTextColor,
                        '/DailyStatsDashboard.php?tab=RAISED_ON'
                    )
                    }
                </td>
                <td>
                    {
                        this.getTotalCardWithBiggerNumberA({
                                label: "Today's Started",
                                total: summary.raisedStartTodaySummary.total,
                            },
                            "#C6C6C6",
                            "#3C3C3C",
                            '/DailyStatsDashboard.php?tab=STARTED_ON'
                        )
                    }
                </td>
                <td>
                    {
                        this.getTotalCardWithBiggerNumberA({
                                label: "Fixed Today",
                                total: summary.fixedTodaySummary.total
                            },
                            evenBackgroundColor,
                            eventTextColor,
                            '/DailyStatsDashboard.php?tab=FIXED_ON'
                        )
                    }
                </td>
                <td>
                    {
                        this.getTotalCardWithBiggerNumberA({
                                label: "Reopened Today",
                                total: summary.reopenTodaySummary.total
                            },
                            "#C6C6C6",
                            "#3C3C3C",
                            '/DailyStatsDashboard.php?tab=REOPENED_ON'
                        )
                    }
                </td>
                <td>
                    {
                        this.getTotalCardWithBiggerNumberA({
                                label: "Breached SLA",
                                total: summary.breachedSLATodaySummary.total
                            },
                            evenBackgroundColor,
                            eventTextColor,
                            '/DailyStatsDashboard.php?tab=BREACHED_ON'
                        )
                    }
                </td>
                <td>{this.getTotalCardWithBiggerNumber("Unique Customers", summary.uniqueCustomerTodaySummary.total)}</td>
            </tr>
            </tbody>
        </table>
    };
    getOpenSrCard = (data, backgroundColor = "#C6C6C6", textColor = "#3C3C3C") => {
        if (data.length > 0) {
            const {el} = this;
            const getPriorityData = (id) => {
                const obj = data.filter((d) => d.priority == id);
                if (obj.length > 0) return obj[0].total;
                else return 0;
            };
            const totalSR = data.reduce((prev, curr) => {
                prev = prev + parseInt(curr.total);
                return prev;
            }, 0);
            return el(
                "div",
                {className: "sd-card ", style: {backgroundColor: backgroundColor, color: textColor}},
                el("label", {className: "sd-card-title"}, "Open SRs"),
                el(
                    "table",
                    null,
                    el(
                        "tbody",
                        null,
                        el(
                            "tr",
                            null,
                            el("td", null, `P1  `),
                            el("td", null, getPriorityData(1))
                        ),
                        el(
                            "tr",
                            null,
                            el("td", null, `P2  `),
                            el("td", null, getPriorityData(2))
                        ),
                        el(
                            "tr",
                            null,
                            el("td", null, `P3  `),
                            el("td", null, getPriorityData(3))
                        ),
                        el(
                            "tr",
                            null,
                            el("td", null, `P4  `),
                            el("td", null, getPriorityData(4))
                        ),
                        el("tr", null, el("td", null, `Total  `), el("td", null, totalSR))
                    )
                )
            );
        } else return null;
    };
    getTeamSrCard = (data, backgroundColor = "#C6C6C6", textColor = "#3C3C3C") => {
        if (data.length > 0) {
            const {el} = this;
            const getTeamTitle = (id) => {
                const team = SRQueues.filter((t) => t.teamID == id);
                if (team.length > 0) return team[0].name;
            };
            const getTeamTotal = (id) => {
                const team = data.filter((t) => t.teamID == id);
                if (team.length > 0) return team[0].total;
            };
            const totalSR = data.reduce((prev, curr) => {
                prev = prev + parseInt(curr.total);
                return prev;
            }, 0);
            return el(
                "div",
                {className: "sd-card ", style: {backgroundColor: backgroundColor, color: textColor}},
                el("label", {className: "sd-card-title"}, "Team SRs"),
                el(
                    "table",
                    {style: {color: textColor}},
                    el(
                        "tbody",
                        null,
                        el(
                            "tr",
                            null,
                            el("td", null, getTeamTitle(1)),
                            el("td", null, getTeamTotal(1))
                        ),
                        el(
                            "tr",
                            null,
                            el("td", null, getTeamTitle(2)),
                            el("td", null, getTeamTotal(2))
                        ),
                        el(
                            "tr",
                            null,
                            el("td", null, getTeamTitle(4)),
                            el("td", null, getTeamTotal(4))
                        ),
                        el(
                            "tr",
                            null,
                            el("td", null, getTeamTitle(5)),
                            el("td", null, getTeamTotal(5))
                        ),
                        el("tr", null, el("td", null, `Total  `), el("td", null, totalSR))
                    )
                )
            );
        } else return null;
    };
    getDailySourceCard = (data, backgroundColor = "#C6C6C6", textColor = "#3C3C3C") => {
        if (data.length == 0) {
            data = [
                {description: "Email", total: "0"},
                {description: "Alert", total: "0"},
                {description: "Manual", total: "0"},
                {description: "Phone", total: "0"},
                {description: "On site", total: "0"},
                {description: "Sales", total: "0"}
            ]
        }
        if (data.length > 0) {
            const {el} = this;
            const dataDisplay = data.filter(
                (d) =>
                    d.description == "Phone" ||
                    d.description == "Email" ||
                    d.description == "Alert" ||
                    d.description == "Portal"
            );
            const dataOthers = data.filter(
                (d) =>
                    d.description !== "Phone" &&
                    d.description !== "Email" &&
                    d.description !== "Alert" &&
                    d.description !== "Portal"
            );

            const dataOthersTotal = dataOthers.reduce((prev, curr) => {
                prev = prev + parseInt(curr.total);
                return prev;
            }, 0);

            return el(
                "div",
                {className: "sd-card ", style: {backgroundColor: backgroundColor, color: textColor}},
                el("label", {className: "sd-card-title"}, "Daily SR Source"),
                el(
                    "table",
                    null,
                    el(
                        "tbody",
                        null,
                        dataDisplay.map((d) =>
                            el(
                                "tr",
                                {key: d.description},
                                el("td", null, d.description),
                                el("td", null, d.total)
                            )
                        ),
                        el(
                            "tr",
                            {},
                            el("td", null, "Others"),
                            el("td", null, dataOthersTotal)
                        )
                    )
                )
            );
        } else return null;
    };


    getTotalCardWithBiggerNumber = (cardInfo, backgroundColor = "#C6C6C6", textColor = "#3C3C3C", linkOpen = null) => {
        return this.getTotalCard(cardInfo.label, cardInfo.total, backgroundColor, textColor, 'total-big', linkOpen);
    }

    getTotalCard = (label, total, backgroundColor = "#C6C6C6", textColor = "#3C3C3C", totalClass = 'total', linkOpen = null) => {
        return (
            <div className="sd-card " style={{backgroundColor: backgroundColor, color: textColor}}
                 onClick={() => {
                     if (linkOpen) {
                         window.open(linkOpen, '_blank');
                     }
                 }}
            >
                <label className="sd-card-title">{label}</label>
                <label className={totalClass}>{total}</label>
            </div>
        );
    };

    getDailyStatsLink = () => {
        const {el} = this;
        return el('i', {
            className: "fal fa-expand-arrows fa-2x pointer",
            onClick: () => window.open('popup.php?action=dailyStats', 'popup', 'width=1250,height=600')
        })
    }
    getDailyInboundOutBoundCard = (data,
                                   backgroundColor = "#C6C6C6",
                                   textColor) => {
        return <div className="sd-card" style={{backgroundColor: backgroundColor, color: textColor}}>
            <label className="sd-card-title">Daily Contact</label>
            <table style={{color: textColor}}>
                <tbody>
                <tr>
                    <td>Inbound</td>
                    <td>{data.inbound}</td>
                </tr>
                <tr>
                    <td>Outbound</td>
                    <td>{data.outbound}</td>
                </tr>
                </tbody>
            </table>
        </div>

    };

    render() {
        return (
            <div className={this.props.className}
            >
                <Spinner key="spinner"
                         show={this.state.showSpinner}
                />
                {this.getSummaryElement()},
                {this.getDailyStatsLink()}
            </div>
        );
    }

}

export default DailyStatsComponent;