import React from "react";
import Spinner from "../shared/Spinner/Spinner";
import moment from 'moment';
import ReactDOM from 'react-dom';
import './FixedServiceRequestCount.css';

export const SelectionState = {
    YEAR_TO_DATE: 'YEAR_TO_DATE',
    SPECIFIC_YEAR_MONTH: 'SPECIFIC_YEAR_MONTH',
    CUSTOM_DATES: 'CUSTOM_DATES',
}

const MYSQL_DATE_FORMAT = 'YYYY-MM-DD'

Number.prototype.pad = function (size) {
    let s = String(this);
    while (s.length < (size || 2)) {
        s = "0" + s;
    }
    return s;
}

Date.prototype.getMYSQLDate = function () {
    return this.getFullYear() + "-" + (this.getMonth() + 1).pad() + "-" + this.getDate().pad();
}

class FixedServiceRequestCountComponent extends React.Component {
    el = React.createElement;
    prefix = 'fixedServiceRequestCountComponent'

    constructor(props) {
        super(props);
        this.state = {
            yearMonth: moment().format('YYYY-MM'),
            selectedState: SelectionState.YEAR_TO_DATE,
            firstTimeFixData: null,
            fixedServiceRequestData: null,
            teamPerformanceData: null,
            fetchingData: true,
            startDate: moment().subtract(1, 'month'),
            endDate: moment()
        }
    }

    componentDidMount() {
        this.refetchData();
    }

    refetchData() {
        const startAndEndDates = this.getStartAndEndDates();
        this.fetchData(startAndEndDates.startDate, startAndEndDates.endDate);
    }

    fetchFirstTimeFixedData(startDate, endDate) {
        return fetch(`/FirstTimeFixReport.php?action=fetchData&startDate=${startDate}&endDate=${endDate}`,
            {
                method: 'GET',
            }
        )
            .then(x => x.json())
    }

    fetchFixedServiceRequestData(startDate, endDate) {
        return fetch('?action=GET_FIXED_SERVICE_REQUEST_DATA', {
            method: 'POST',
            body: JSON.stringify({
                startDate,
                endDate
            })
        })
            .then(r => r.json())
            .then(response => {
                if (response.status != 'ok') {
                    throw new Error('Failed to pull data');
                }
                return response.data;
            })

    }

    fetchTeamPerformanceData(startDate, endDate) {
        return fetch('?action=GET_TEAM_PERFORMANCE_DATA', {
            method: 'POST',
            body: JSON.stringify({
                startDate,
                endDate
            })
        })
            .then(r => r.json())
            .then(response => {
                if (response.status != 'ok') {
                    throw new Error('Failed to pull data');
                }
                return response.data;
            })
    }

    fetchData(startDate, endDate) {
        this.setState({fetchingData: true});
        Promise.all([
                this.fetchFirstTimeFixedData(startDate.format(MYSQL_DATE_FORMAT), endDate.format(MYSQL_DATE_FORMAT)),
                this.fetchFixedServiceRequestData(startDate.format(MYSQL_DATE_FORMAT), endDate.format(MYSQL_DATE_FORMAT)),
                this.fetchTeamPerformanceData(startDate.format(MYSQL_DATE_FORMAT), endDate.format(MYSQL_DATE_FORMAT))
            ]
        ).then(([firstTimeFixData, fixedServiceRequestData, teamPerformanceData]) => {
            this.setState({
                firstTimeFixData,
                fixedServiceRequestData,
                teamPerformanceData,
                fetchingData: false
            })
        })
    }

    getTeamName(teamId) {
        switch (teamId) {
            case 1:
                return 'Helpdesk';
            case 2:
                return 'Escalations';
            case 4:
                return 'Small Projects';
            case 5:
                return 'Projects';
        }
    }

    getTeamData(teamPerformanceData, teamId) {

        switch (teamId) {
            case 1:
                return {
                    avgFixHours: teamPerformanceData.hdTeamAvgFixHours,
                    avgSLAPercentage: teamPerformanceData.hdTeamAvgSLAPercentage
                }
            case 2:
                return {
                    avgFixHours: teamPerformanceData.esTeamAvgFixHours,
                    avgSLAPercentage: teamPerformanceData.esTeamAvgSLAPercentage
                }
            case 4:
                return {
                    avgFixHours: teamPerformanceData.spTeamAvgFixHours,
                    avgSLAPercentage: teamPerformanceData.spTeamAvgSLAPercentage
                }
            case 5:
                return {
                    avgFixHours: teamPerformanceData.pTeamAvgFixHours,
                    avgSLAPercentage: teamPerformanceData.pTeamAvgSLAPercentage
                }
        }
    }

    footerRow(rowList, teamPerformanceData, previousTeam) {
        const teamPerformanceValues = this.getTeamData(teamPerformanceData, previousTeam.teamId);
        rowList.push(
            (<tr key={`footer-${previousTeam.teamId}`}
                 className='teamFooter'
            >
                <td key={`footer-0-${previousTeam.teamId}`}> Team Total</td>
                <td key={`footer-1-${previousTeam.teamId}`}> {previousTeam.totalFixed}</td>
                <td key={`footer-2-${previousTeam.teamId}`}> {previousTeam.totalRaised}</td>
                <td key={`footer-3-${previousTeam.teamId}`}> {previousTeam.firstTimeFixRaised}</td>
                <td key={`footer-4-${previousTeam.teamId}`}> {previousTeam.firstTimeFixPercentAttempted}</td>
                <td key={`footer-5-${previousTeam.teamId}`}> {previousTeam.firstTimeFixPercentAchieved}</td>
                <td key={`footer-6-${previousTeam.teamId}`}> {previousTeam.timeRequests}</td>
                <td key={`footer-7-${previousTeam.teamId}`}> {previousTeam.changeRequests}</td>
                <td key={`footer-8-${previousTeam.teamId}`}> {previousTeam.operationalTasks}</td>
                <td key={`footer-9-${previousTeam.teamId}`}> {(+teamPerformanceValues.avgFixHours).toFixed(2)}</td>
                <td key={`footer-10-${previousTeam.teamId}`}> {(+teamPerformanceValues.avgSLAPercentage).toFixed(2)}</td>

            </tr>)
        );
        return rowList;
    }

    renderTable() {
        const {
            firstTimeFixData,
            fixedServiceRequestData,
            teamPerformanceData,
            fetchingData
        } = this.state
        if (fetchingData) {
            return (
                <React.Fragment>
                    <Spinner key="spinner"
                             show={true}
                    />
                    <h1 key={"loading"}>
                        Please wait while loading data...
                    </h1>
                </React.Fragment>
            )
        }
        const teams = {};
        let previousTeam = null;

        return this.el('table', {className: 'table table-striped sticky-header'},
            [
                this.el('thead', {key: 'head'},
                    this.el('tr', null, [
                        this.el('th', {key: 'nothing'}),
                        this.el('th', {
                                key: 'srsFixedHeader',
                                title: "Number of SRs fixed in a calendar month"
                            }, 'SRs Fixed'
                        ),
                        this.el('th', {
                            key: 'srsRaisedHeader',
                            title: "Total SRs raised by engineer."
                        }, 'Total SRs Raised'),
                        this.el('th', {
                            key: 'firstTimeFixedRaisedHeader',
                            title: "Total FTF qualifying SRs raised, not CNC, source is phone call, and customer has correct contract."
                        }, 'Total FTF SRs Raised'),
                        this.el('th', {
                            key: 'firstTimeFixedPercentAttemptedHeader',
                            title: "First Time Fix attempted (helpdesk only)"
                        }, 'FTF Percent Attempted'),
                        this.el('th', {
                            key: 'firstTimeFixedAchievedHeader',
                            title: "First Time Fix Achieved (helpdesk only)"
                        }, 'FTF Percent Achieved'),
                        this.el('th', {
                            key: 'timeRequestsHeader',
                            title: "number of time requests submitted (includes resubmussion after requests denied)"
                        }, 'Time Requests'),
                        this.el('th', {
                            key: 'changeRequestsHeader',
                            title: "number of change requests submitted (includes resubmission after4 requests denied)"
                        }, 'Change Requests'),
                        this.el('th', {
                            key: 'operationalTasksHeader',
                            title: "number of operational tasks (escalation, deescalation, CR, TR, SLA change) done by TL"
                        }, 'Operational Tasks'),
                        this.el('th', {
                            key: 'teamSLAPercentHeader',
                            title: "team SLA percentage achieved"
                        }, 'Team SLA%'),
                        this.el('th', {
                            key: 'teamAverageFixHoursHeader',
                            title: "team average fix hours achieved"
                        }, 'Team Average Fix Hours'),
                    ])
                ),
                this.el(
                    'tbody',
                    {key: 'body'},
                    [
                        fixedServiceRequestData.reduce((acc, row, index, array) => {
                            if (!(row.teamId in teams)) {
                                const name = this.getTeamName(row.teamId);
                                teams[row.teamId] = {
                                    name,
                                    teamId: row.teamId,
                                    totalFixed: 0,
                                    totalRaised: 0,
                                    firstTimeFixRaised: null,
                                    firstTimeFixPercentAttempted: null,
                                    firstTimeFixPercentAchieved: null,
                                    timeRequests: 0,
                                    changeRequests: 0,
                                    operationalTasks: 0,
                                    engineers: []
                                };

                                if (row.teamId === 1) {
                                    teams[row.teamId].firstTimeFixRaised = firstTimeFixData.totalRaised;
                                    teams[row.teamId].firstTimeFixPercentAttempted = firstTimeFixData.firstTimeFixAttemptedPct;
                                    teams[row.teamId].firstTimeFixPercentAchieved = firstTimeFixData.firstTimeFixAchievedPct;
                                }

                                if (previousTeam) {
                                    this.footerRow(acc, teamPerformanceData, previousTeam);
                                }

                                acc.push(
                                    this.el('tr', {key: `header-${row.teamId}`, className: 'teamHeader'},
                                        [
                                            this.el('th', {key: `header-0-${row.teamId}`}, name),
                                            this.el('th', {key: `header-1-${row.teamId}`}),
                                            this.el('th', {key: `header-2-${row.teamId}`}),
                                            this.el('th', {key: `header-3-${row.teamId}`}),
                                            this.el('th', {key: `header-4-${row.teamId}`}),
                                            this.el('th', {key: `header-5-${row.teamId}`}),
                                            this.el('th', {key: `header-6-${row.teamId}`}),
                                            this.el('th', {key: `header-7-${row.teamId}`}),
                                            this.el('th', {key: `header-8-${row.teamId}`}),
                                            this.el('th', {key: `header-9-${row.teamId}`}),
                                            this.el('th', {key: `header-10-${row.teamId}`}),
                                        ]
                                    )
                                )
                            }
                            const currentTeam = teams[row.teamId];
                            if (!(row.userId in currentTeam.engineers)) {
                                currentTeam.engineers[row.userId] = row.userId;
                                currentTeam.totalFixed += +row.fixed;
                                currentTeam.totalRaised += +row.raised;
                                currentTeam.timeRequests += +row.timeRequests;
                                currentTeam.changeRequests += +row.changeRequests;
                                currentTeam.operationalTasks += +row.operationalTasks;
                                const foundFirstTimeFixData = firstTimeFixData.engineers.find(x => x.id == row.userId);

                                let firstTimeFixTotalRaised = null;
                                let firstTimeFixPct = null;
                                let attemptedFirstTimeFixPct = null;
                                if (foundFirstTimeFixData && foundFirstTimeFixData.totalRaised) {
                                    firstTimeFixTotalRaised = foundFirstTimeFixData.totalRaised;
                                    firstTimeFixPct = ((foundFirstTimeFixData.firstTimeFix / foundFirstTimeFixData.totalRaised) * 100).toFixed(2)
                                    attemptedFirstTimeFixPct = ((foundFirstTimeFixData.attemptedFirstTimeFix / foundFirstTimeFixData.totalRaised) * 100).toFixed(2)
                                }

                                acc.push(
                                    this.el(
                                        'tr',
                                        {key: `engineer-${row.userId}`},
                                        [
                                            this.el('td', {key: `engineer-0-${row.userId}`}, row.userName),
                                            this.el('td', {key: `engineer-1-${row.userId}`}, row.fixed),
                                            this.el('td', {key: `engineer-2-${row.userId}`}, row.raised),
                                            this.el('td', {key: `engineer-3-${row.userId}`}, foundFirstTimeFixData ? firstTimeFixTotalRaised : null),
                                            this.el('td', {key: `engineer-4-${row.userId}`}, foundFirstTimeFixData ? firstTimeFixPct : null),
                                            this.el('td', {key: `engineer-5-${row.userId}`}, foundFirstTimeFixData ? attemptedFirstTimeFixPct : null),
                                            this.el('td', {key: `engineer-6-${row.userId}`}, row.timeRequests),
                                            this.el('td', {key: `engineer-7-${row.userId}`}, row.changeRequests),
                                            this.el('td', {key: `engineer-8-${row.userId}`}, row.operationalTasks),
                                            this.el('td', {key: `engineer-9-${row.userId}`}),
                                            this.el('td', {key: `engineer-10-${row.userId}`}),
                                        ]
                                    )
                                )
                            }
                            previousTeam = currentTeam;
                            if (index == array.length - 1) {
                                this.footerRow(acc, teamPerformanceData, previousTeam);
                            }
                            return acc;
                        }, [])
                    ]
                )
            ]
        )

    }

    getStartAndEndDates() {
        const {selectedState, yearMonth, startDate, endDate} = this.state;
        switch (selectedState) {
            case "YEAR_TO_DATE":
                return {
                    startDate: moment().startOf("year"),
                    endDate: moment()
                }
            case "SPECIFIC_YEAR_MONTH":
                const startingDate = moment(yearMonth, 'YYYY-MM').startOf("month");
                return {
                    startDate: startingDate,
                    endDate: startingDate.clone().endOf('month')
                }

            case "CUSTOM_DATES":
                return {
                    startDate: moment(startDate, MYSQL_DATE_FORMAT),
                    endDate: moment(endDate, MYSQL_DATE_FORMAT)
                }
        }
    }

    componentDidUpdate(prevProps, prevState) {
        if (this.hasStateChanged(prevState)) {
            this.refetchData()
        }
    }

    hasStateChanged(prevState) {
        const {yearMonth, selectedState, startDate, endDate} = this.state;
        return prevState.yearMonth !== yearMonth ||
            prevState.selectedState !== selectedState
            || prevState.startDate !== startDate || prevState.endDate !== endDate;
    }

    render() {
        const {selectedState, yearMonth, startDate, endDate} = this.state;
        return this.el(
            'div',
            {
                className: `${this.prefix}-container`,
                key: 'container',
            },
            [
                this.el(
                    'div',
                    {
                        className: `${this.prefix}-selectors`,
                        key: 'selectors',
                    },
                    [
                        this.el(
                            'label',
                            {
                                className: `${this.prefix}-yearToDateLabel`,
                                key: 'yearToDateLabel',
                            },
                            [
                                this.el(
                                    'input',
                                    {
                                        type: 'radio',
                                        name: 'selection',
                                        checked: selectedState === SelectionState.YEAR_TO_DATE,
                                        key: 'yearToDateInput',
                                        onChange: () => {
                                            this.setState({selectedState: SelectionState.YEAR_TO_DATE});
                                        }
                                    }
                                ),
                                this.el(
                                    'span',
                                    {
                                        key: 'yearToDateLabelSpan'
                                    },
                                    'Year To Date'
                                )
                            ]
                        ),
                        this.el(
                            'label',
                            {
                                className: `${this.prefix}-specificMonthLabel`,
                                key: 'specificMonthLabel',
                            },
                            [
                                this.el(
                                    'input',
                                    {
                                        type: 'radio',
                                        name: 'selection',
                                        checked: selectedState === SelectionState.SPECIFIC_YEAR_MONTH,
                                        key: 'specificMonthInput',
                                        onChange: () => {
                                            this.setState({selectedState: SelectionState.SPECIFIC_YEAR_MONTH});
                                        }
                                    }
                                ),
                                this.el(
                                    'span',
                                    {
                                        key: 'specificMonthLabelSpan'
                                    },
                                    'Specific Month'
                                )
                            ]
                        ),
                        this.el(
                            'label',
                            {
                                className: `${this.prefix}-customDatesLabel`,
                                key: 'customDatesLabel',
                            },
                            [
                                this.el(
                                    'input',
                                    {
                                        type: 'radio',
                                        name: 'selection',
                                        checked: selectedState === SelectionState.CUSTOM_DATES,
                                        key: 'customDatesInput',
                                        onChange: () => {
                                            this.setState({selectedState: SelectionState.CUSTOM_DATES});
                                        }
                                    }
                                ),
                                this.el(
                                    'span',
                                    {
                                        key: 'customDatesLabelSpan'
                                    },
                                    'Custom Dates'
                                )
                            ]
                        ),
                        selectedState === SelectionState.SPECIFIC_YEAR_MONTH ?
                            this.el('input', {
                                type: 'month',
                                value: yearMonth,
                                onChange: ($event) => {
                                    const value = $event.currentTarget.value;
                                    this.setState({yearMonth: value});
                                },
                                key: 'yearMonthInput'
                            }) :
                            selectedState === SelectionState.CUSTOM_DATES ?
                                (
                                    <React.Fragment key="datesContainer">
                                        <input type="date"
                                               value={startDate.format(MYSQL_DATE_FORMAT)}
                                               key="startDate"
                                               onChange={($event) => {
                                                   this.setState({startDate: moment($event.currentTarget.value, MYSQL_DATE_FORMAT)})
                                               }}
                                        />
                                        <input type="date"
                                               value={endDate.format(MYSQL_DATE_FORMAT)}
                                               key="endDate"
                                               onChange={($event) => {
                                                   this.setState({endDate: moment($event.currentTarget.value, MYSQL_DATE_FORMAT)})
                                               }}
                                        />
                                    </React.Fragment>
                                )
                                : null
                    ]
                ),
                this.el(
                    'div',
                    {
                        className: `${this.prefix}-table`,
                        key: 'table-container'
                    },
                    this.renderTable()
                )
            ]
        );
    }
}

export default FixedServiceRequestCountComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#react-fixed-service-request-report');
    ReactDOM.render(React.createElement(FixedServiceRequestCountComponent), domContainer);
})