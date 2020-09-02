export const SelectionState = {
    YEAR_TO_DATE: 'YEAR_TO_DATE',
    SPECIFIC_YEAR_MONTH: 'SPECIFIC_YEAR_MONTH'
}

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
        const currentDateTime = new Date();
        const currentFormattedDate = currentDateTime.getFullYear() + "-" + (currentDateTime.getMonth() + 1).pad();
        this.state = {
            yearMonth: currentFormattedDate,
            selectedState: SelectionState.YEAR_TO_DATE,
            firstTimeFixData: null,
            fixedServiceRequestData: null,
            teamPerformanceData: null
        }
        this.fetchData();
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

    fetchData() {
        const {selectedState, yearMonth} = this.state;
        const currentDateTime = new Date();
        let startDate = currentDateTime.getFullYear() + "-01-01";
        let endDate = currentDateTime.getMYSQLDate();
        if (selectedState === SelectionState.SPECIFIC_YEAR_MONTH) {
            const [year, month] = yearMonth.split('-');
            startDate = (new Date(+year, +month, 1)).getMYSQLDate();
            endDate = (new Date(+year, 1 + (+month), 0)).getMYSQLDate();
        }

        Promise.all([
                this.fetchFirstTimeFixedData(startDate, endDate),
                this.fetchFixedServiceRequestData(startDate, endDate),
                this.fetchTeamPerformanceData(startDate, endDate)
            ]
        ).then(([firstTimeFixData, fixedServiceRequestData, teamPerformanceData]) => {
            this.setState({
                firstTimeFixData,
                fixedServiceRequestData,
                teamPerformanceData
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

    renderTable() {
        const {
            firstTimeFixData,
            fixedServiceRequestData,
            teamPerformanceData
        } = this.state
        if (!fixedServiceRequestData) {
            return;
        }
        const teams = {};
        let previousTeam = null;

        return this.el('table', {className: 'table table-striped'},
            [
                this.el('thead', {key: 'head'},
                    this.el('tr', null, [
                        this.el('td', {key: 'nothing'}),
                        this.el('td', {
                                key: 'srsFixedHeader',
                                title: "Number of SRs fixed in a calendar month"
                            }, 'SRs Fixed'
                        ),
                        this.el('td', {
                            key: 'srsRaisedHeader',
                            title: "Total SRs raised by engineer."
                        }, 'Total SRs Raised'),
                        this.el('td', {
                            key: 'firstTimeFixedRaisedHeader',
                            title: "Total FTF qualifying SRs raised, not CNC, source is phone call, and customer has correct contract."
                        }, 'Total FTF SRs Raised'),
                        this.el('td', {
                            key: 'firstTimeFixedPercentAttemptedHeader',
                            title: "First Time Fix attempted (helpdesk only)"
                        }, 'FTF Percent Attempted'),
                        this.el('td', {
                            key: 'firstTimeFixedAchievedHeader',
                            title: "First Time Fix Achieved (helpdesk only)"
                        }, 'FTF Percent Achieved'),
                        this.el('td', {
                            key: 'timeRequestsHeader',
                            title: "number of time requests submitted (includes resubmussion after requests denied)"
                        }, 'Time Requests'),
                        this.el('td', {
                            key: 'changeRequestsHeader',
                            title: "number of change requests submitted (includes resubmission after4 requests denied)"
                        }, 'Change Requests'),
                        this.el('td', {
                            key: 'operationalTasksHeader',
                            title: "number of operational tasks (escalation, deescalation, CR, TR, SLA change) done by TL"
                        }, 'Operational Tasks'),
                        this.el('td', {
                            key: 'teamSLAPercentHeader',
                            title: "team SLA percentage achieved"
                        }, 'Team SLA%'),
                        this.el('td', {
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
                                    const teamPerformanceValues = this.getTeamData(teamPerformanceData, previousTeam.teamId);
                                    acc.push(
                                        this.el('tr', {key: `footer-${previousTeam.teamId}`, className: 'teamFooter'},
                                            [
                                                this.el('td', {key: `footer-0-${previousTeam.teamId}`}, 'Team Total'),
                                                this.el('td', {key: `footer-1-${previousTeam.teamId}`}, previousTeam.totalFixed),
                                                this.el('td', {key: `footer-2-${previousTeam.teamId}`}, previousTeam.totalRaised),
                                                this.el('td', {key: `footer-3-${previousTeam.teamId}`}, previousTeam.firstTimeFixRaised),
                                                this.el('td', {key: `footer-4-${previousTeam.teamId}`}, previousTeam.firstTimeFixPercentAttempted),
                                                this.el('td', {key: `footer-5-${previousTeam.teamId}`}, previousTeam.firstTimeFixPercentAchieved),
                                                this.el('td', {key: `footer-6-${previousTeam.teamId}`}, previousTeam.timeRequests),
                                                this.el('td', {key: `footer-7-${previousTeam.teamId}`}, previousTeam.changeRequests),
                                                this.el('td', {key: `footer-8-${previousTeam.teamId}`}, previousTeam.operationalTasks),
                                                this.el('td', {key: `footer-9-${previousTeam.teamId}`}, (+teamPerformanceValues.avgFixHours).toFixed(2)),
                                                this.el('td', {key: `footer-10-${previousTeam.teamId}`}, (+teamPerformanceValues.avgSLAPercentage).toFixed(2)),
                                            ]
                                        )
                                    )
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
                                const teamPerformanceValues = this.getTeamData(teamPerformanceData, previousTeam.teamId);

                                acc.push(
                                    this.el('tr', {key: `footer-${previousTeam.teamId}`},
                                        [
                                            this.el('td', {key: `footer-0-${previousTeam.teamId}`}, 'Team Total'),
                                            this.el('td', {key: `footer-1-${previousTeam.teamId}`}, previousTeam.totalFixed),
                                            this.el('td', {key: `footer-2-${previousTeam.teamId}`}, previousTeam.totalRaised),
                                            this.el('td', {key: `footer-3-${previousTeam.teamId}`}, previousTeam.firstTimeFixRaised),
                                            this.el('td', {key: `footer-4-${previousTeam.teamId}`}, previousTeam.firstTimeFixPercentAttempted),
                                            this.el('td', {key: `footer-5-${previousTeam.teamId}`}, previousTeam.firstTimeFixPercentAchieved),
                                            this.el('td', {key: `footer-6-${previousTeam.teamId}`}, previousTeam.timeRequests),
                                            this.el('td', {key: `footer-7-${previousTeam.teamId}`}, previousTeam.changeRequests),
                                            this.el('td', {key: `footer-8-${previousTeam.teamId}`}, previousTeam.operationalTasks),
                                            this.el('td', {key: `footer-9-${previousTeam.teamId}`}, teamPerformanceValues.avgFixHours),
                                            this.el('td', {key: `footer-10-${previousTeam.teamId}`}, teamPerformanceValues.avgSLAPercentage),
                                        ]
                                    )
                                )
                            }
                            return acc;
                        }, [])
                    ]
                )
            ]
        )

    }

    componentDidUpdate(prevProps, prevState) {

        if (prevState.yearMonth !== this.state.yearMonth ||
            prevState.selectedState !== this.state.selectedState) {
            this.fetchData();
        }
    }

    render() {
        const {selectedState, yearMonth} = this.state;
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
                        selectedState === SelectionState.SPECIFIC_YEAR_MONTH ?
                            this.el('input', {
                                type: 'month',
                                value: yearMonth,
                                onChange: ($event) => {
                                    const value = $event.currentTarget.value;
                                    this.setState({yearMonth: value});
                                },
                                key: 'yearMonthInput'
                            }) : null
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
