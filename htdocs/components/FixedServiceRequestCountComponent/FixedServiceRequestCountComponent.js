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

    renderTable() {
        const {tableData} = this.state;
        if (!tableData || !Object.keys(tableData).length) {
            return null;
        }

        const {
            firstTimeFixData,
            fixedServiceRequestData,
            teamPerformanceData
        } = tableData;

        const teams = {};
        fixedActivitiesData.forEach(row => {
            if (!(row.teamId in teams)) {
                teams[row.teamId] = {
                    name: this.getTeamName(row.teamId),
                    totalFixed: 0,
                    totalRaised: 0,
                    firstTimeFixed
                };
            }
        })


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
                                            this.fetchData();
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
                                            this.fetchData();
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
                                    this.fetchData();
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
