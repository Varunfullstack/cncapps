"use strict";

class ExpenseBreakdownYearToDate extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            approvalSubordinates: [],
            expenses: [],
            selectedEngineer: null
        };
    }

    handleChangeEngineer(value) {
        this.setState({selectedEngineer: value});
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
        const tableData = this.state.expenses.reduce((acc, expense) => {
            if (!(expense.expenseTypeDescription in acc)) {
                acc[expense.expenseTypeDescription] = new Array(currentDate.getMonth() + 2).fill(0);
            }
            const expenseMonth = expense.dateSubmitted.match(/\d{4}-0(\d)-\d{2}/)[1]
            acc[expense.expenseTypeDescription][expenseMonth] += expense.value;
            acc[expense.expenseTypeDescription][acc[expense.expenseTypeDescription].length - 1] += expense.value;
            totalRow[expenseMonth] += expense.value;
            totalRow[totalRow.length - 1] += expense.value;
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
                            value: this.state.selectedEngigneer || '',
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
                    {className: 'my-account', key: 'data-table'},
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
                                            }
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
                                        this.el('th', {key: expenseType + '-title'}, expenseType),
                                        ...tableData[expenseType].map((value, idx) => {
                                            return this.el(
                                                'td',
                                                {
                                                    style: {textAlign: "right"},
                                                    key: expenseType + idx
                                                },
                                                value.toFixed(2)
                                            )
                                        })
                                    ]
                                )
                            })
                        )
                    ]
                ),
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
