"use strict";

class ExpenseBreakdownYearToDate extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            approvalSubordinates: [],
            expenses: [],
            selectedEngineer: null,
            selectedDetail: null,
            financialYearExpenses: null,
        };
    }

    handleChangeEngineer(value) {
        this.setState({selectedEngineer: '' + value});
    }

    fetchApprovalSubordinates(userId) {
        return fetch('/User.php?action=getApprovalSubordinates&superiorId=' + userId)
            .then(res => res.json())
            .then(response => {
                this.setState({approvalSubordinates: response.data});
            })
    }

    fetchExpenses(engineerId = null) {
        let url = '?action=getYearToDateExpenses';
        if (engineerId) {
            url += '&engineerId=' + engineerId
        }
        return fetch(url)
            .then(res => res.json())
            .then(response => {
                this.setState({expenses: response.data});
            })
    }

    componentDidUpdate(prevProps, prevState) {
        if (this.props.userId !== prevProps.userId) {
            this.fetchApprovalSubordinates();
        }
        if (this.state.selectedEngineer !== prevState.selectedEngineer) {
            console.log(this.state.selectedEngineer, prevState.selectedEngineer);
            this.fetchExpenses(this.state.selectedEngineer);
        }
    }

    componentDidMount() {
        const {userId} = this.props;
        this.fetchApprovalSubordinates(userId);
        this.fetchExpenses()
    }


    render() {
        const isApprover = this.state.approvalSubordinates.length > 1;
        const currentDate = new Date();
        // if is approver we render a dropdown, that we are going to populate from the active users
        // if not we won't have the selector as it's the guy's data
        const totalRow = new Array(currentDate.getMonth() + 2).fill(0);
        const mileage = new Array(currentDate.getMonth() + 2).fill(0);
        const mileageDetail = new Array(currentDate.getMonth() + 2).fill(0).map(x => []);
        const tableData = this.state.expenses.reduce((acc, expense) => {
            if (!(expense.expenseTypeDescription in acc)) {
                acc[expense.expenseTypeDescription] = new Array(currentDate.getMonth() + 2).fill(0);
            }
            const expenseMonth = expense.dateSubmitted.match(/\d{4}-0(\d)-\d{2}/)[1]
            acc[expense.expenseTypeDescription][expenseMonth] += expense.value;
            acc[expense.expenseTypeDescription][acc[expense.expenseTypeDescription].length - 1] += expense.value;
            totalRow[expenseMonth] += expense.value;
            totalRow[totalRow.length - 1] += expense.value;
            if (expense.expenseTypeDescription === 'Mileage') {

                mileage[expenseMonth] += expense.mileage;
                mileage[mileage.length - 1] += expense.mileage;
                mileageDetail[expenseMonth].push(expense);
            }
            return acc;
        }, {});

        const monthNames = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ]

        tableData['Total'] = totalRow;
        return this.el(
            'div',
            {},
            [
                !isApprover ? '' :
                    this.el(
                        'select',
                        {
                            value: this.state.selectedEngineer || '',
                            onChange: ($event) => {
                                this.handleChangeEngineer($event.target.value)
                            },
                            key: 'engineer-selector'
                        },
                        [
                            this.el(
                                'option',
                                {
                                    key: 'AllEngineers',
                                    value: ''
                                },
                                'All Engineers'
                            ),
                            ...this.state.approvalSubordinates.map(x => {
                                return this.el(
                                    'option',
                                    {
                                        key: 'engineer-' + x.userID,
                                        value: x.userID
                                    },
                                    x.name
                                )
                            })
                        ]
                    ),
                this.el(
                    "table",
                    {className: 'table table-striped', key: 'data-table'},
                    [
                        this.el(
                            'thead',
                            {
                                key: 'header'
                            },
                            this.el(
                                'tr',
                                {},
                                new Array(currentDate.getMonth() + 3).fill(0).map((value, index, array) => {
                                    if (!index) {
                                        return this.el(
                                            'th',
                                            {
                                                key: 'descriptionHeader'
                                            },
                                            "Expense Type"
                                        )
                                    }
                                    if (index === array.length - 1) {
                                        return this.el(
                                            'th',
                                            {
                                                key: 'TotalHeader',
                                                style: {
                                                    textAlign: 'center'
                                                },
                                            },
                                            'Total'
                                        )
                                    }
                                    return this.el(
                                        'th',
                                        {
                                            key: monthNames[index - 1] + "-header",
                                            style: {
                                                textAlign: 'center'
                                            },
                                        },
                                        monthNames[index - 1]
                                    )

                                })
                            )
                        ),
                        this.el(
                            'tbody',
                            {
                                key: 'body'
                            },
                            Object.keys(tableData).map(expenseType => {
                                return this.el(
                                    'tr',
                                    {
                                        key: expenseType + '-row'
                                    },
                                    [
                                        this.el('th', {key: expenseType + '-title'}, expenseType == "Mileage" ? "Mileage (Miles)" : expenseType),
                                        ...tableData[expenseType].map((value, idx) => {
                                            return this.el(
                                                'td',
                                                {
                                                    style: {
                                                        textAlign: "right",
                                                        cursor: expenseType === 'Mileage' ? 'pointer' : null
                                                    },
                                                    key: expenseType + idx,
                                                    onClick: $even => {
                                                        if (expenseType === 'Mileage') {
                                                            this.setState({selectedDetail: idx});
                                                        }
                                                    },
                                                },
                                                value.toFixed(2) + (expenseType == "Mileage" ? ` (${mileage[idx]})` : '')
                                            )
                                        })
                                    ]
                                )
                            })
                        )
                    ]
                ),
                this.state.financialYearExpenses ?
                    null :
                    null,
                this.state.selectedDetail !== null ?
                    this.el(
                        "div",
                        {className: 'detail-table', key: 'detail-table'},
                        [
                            this.el('h3', {key: 'month-name'},
                                monthNames[this.state.selectedDetail]
                            ),
                            this.el(
                                'table',
                                {
                                    className: 'table table-stripped',
                                    key: 'detail-table'
                                },
                                [
                                    this.el(
                                        'thead',
                                        {key: 'detail-header'},
                                        this.el(
                                            'tr',
                                            {},
                                            [
                                                this.el(
                                                    'th',
                                                    {key: 'date-column'},
                                                    'Date'
                                                ),
                                                this.el(
                                                    'th',
                                                    {key: 'customer-column'},
                                                    'Customer'
                                                ),
                                                this.el(
                                                    'th',
                                                    {key: 'site-column'},
                                                    'Site'
                                                ),
                                                this.el(
                                                    'th',
                                                    {key: 'miles-column'},
                                                    'Miles'
                                                ),
                                                this.el(
                                                    'th',
                                                    {key: 'value-column'},
                                                    'Value'
                                                ),
                                            ]
                                        )
                                    ),
                                    this.el(
                                        'tbody',
                                        {key: 'detail-body'},
                                        mileageDetail[this.state.selectedDetail].map(expense => {
                                            return this.el(
                                                'tr',
                                                {key: expense.id},
                                                [
                                                    this.el(
                                                        'td',
                                                        {key: 'date-column'},
                                                        expense.dateSubmitted
                                                    ),
                                                    this.el(
                                                        'td',
                                                        {key: 'customer-column'},
                                                        expense.customerName
                                                    ),
                                                    this.el(
                                                        'td',
                                                        {key: 'site-column'},
                                                        expense.siteTown
                                                    ),
                                                    this.el(
                                                        'td',
                                                        {key: 'miles-column'},
                                                        expense.mileage
                                                    ),
                                                    this.el(
                                                        'td',
                                                        {key: 'value-column'},
                                                        expense.value
                                                    ),
                                                ]
                                            )
                                        })
                                    ),
                                ]
                            )
                        ],
                    ) : null
            ]
        )
    }
}

export default ExpenseBreakdownYearToDate;

const domContainer = document.querySelector('#react-expense-breakdown');
ReactDOM.render(React.createElement(ExpenseBreakdownYearToDate, {
    isApprover: domContainer.dataset.isApprover,
    userId: domContainer.dataset.userId
}), domContainer);
