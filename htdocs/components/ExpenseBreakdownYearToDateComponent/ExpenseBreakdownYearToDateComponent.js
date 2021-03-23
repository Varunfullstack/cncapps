"use strict";
import React from 'react';
import ReactDOM from 'react-dom';
import moment from 'moment';

class ExpenseBreakdownYearToDateComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);

        this.state = {
            approvalSubordinates: [],
            expenses: [],
            selectedEngineer: null,
            selectedDetail: null,
            financialYearTotalMileage: 0,
            financialYearTotalValue: 0
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

    getFinancialStartAndEndDates() {
        const today = moment().hours(0).minutes(0).seconds(0);
        const sixthOfAprilThisYear = (moment()).month(3).date(6).hours(0).minutes(0).seconds(0);
        if (today.isSameOrAfter(sixthOfAprilThisYear)) {
            return {
                startDate: sixthOfAprilThisYear,
                endDate: today
            }
        }
        return {
            startDate: sixthOfAprilThisYear.subtract(1, 'year'),
            endDate: today
        }
    }


    fetchFinancialYearExpenses(engineerId) {
        const financialStartAndEndDates = this.getFinancialStartAndEndDates();

        let urlString = `?action=getExpensesData&exported=1&expenseTypeId=2&startDate=${financialStartAndEndDates.startDate.format('YYYY-MM-DD')}&endDate=${financialStartAndEndDates.endDate.format('YYYY-MM-DD')}`;
        if (engineerId) {
            urlString += `&engineerId=${engineerId}`;
        }

        return fetch(urlString)
            .then(res => res.json())
            .then(response => {
                this.updateFinancialYearTotalsStateFromResponse(response)
            });
    }

    updateFinancialYearTotalsStateFromResponse(response) {
        this.setState(
            response.data.reduce(
                (acc, expense) => {
                    acc.financialYearTotalMileage += +expense.mileage;
                    acc.financialYearTotalValue += +expense.value;
                    return acc;
                },
                {
                    financialYearTotalMileage: 0,
                    financialYearTotalValue: 0
                }
            )
        );
    }

    fetchYearToDateExpenses(engineerId = null) {
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
            this.fetchYearToDateExpenses(this.state.selectedEngineer);
            this.fetchFinancialYearExpenses(this.state.selectedEngineer);
        }
    }

    componentDidMount() {
        const {userId} = this.props;
        this.fetchApprovalSubordinates(userId);
        this.fetchYearToDateExpenses()
        this.fetchFinancialYearExpenses();
    }


    render() {
        const isApprover = this.state.approvalSubordinates.length;
        const currentDate = new Date();
        const totalRow = new Array(currentDate.getMonth() + 2).fill(0);
        const mileage = new Array(currentDate.getMonth() + 2).fill(0);
        const mileageDetail = new Array(currentDate.getMonth() + 2).fill(0).map(x => []);
        const tableData = this.state.expenses.reduce((acc, expense) => {
            if (!(expense.expenseTypeDescription in acc)) {
                acc[expense.expenseTypeDescription] = new Array(currentDate.getMonth() + 2).fill(0);
            }
            const expenseMonth = expense.dateSubmitted.match(/\d{4}-(\d{2})-\d{2}/)[1];
            acc[expense.expenseTypeDescription][expenseMonth - 1] += expense.value;
            acc[expense.expenseTypeDescription][acc[expense.expenseTypeDescription].length - 1] += expense.value;
            totalRow[expenseMonth - 1] += expense.value;
            totalRow[totalRow.length - 1] += expense.value;
            if (expense.expenseTypeDescription == 'Mileage') {
                mileage[expenseMonth - 1] += expense.mileage;
                mileage[mileage.length - 1] += expense.mileage;
                mileageDetail[expenseMonth - 1].push(expense);
            }
            return acc;
        }, {});

        const {startDate: financialStartDate, endDate: financialEndDate} = this.getFinancialStartAndEndDates();
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
                                    if (index == array.length - 1) {
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
                                                        cursor: expenseType == 'Mileage' ? 'pointer' : null
                                                    },
                                                    key: expenseType + idx,
                                                    onClick: $even => {
                                                        if (expenseType == 'Mileage') {
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
                this.el(
                    'div',
                    {className: 'financialYearMileage', key: 'financial-year-mileage'},
                    [
                        <div key="financialYearDates">
                            Financial
                            Year {financialStartDate.format('DD/MM/YYYY')} to {financialEndDate.format('DD/MM/YYYY')}
                        </div>,
                        this.el(
                            'div',
                            {className: 'financialYearMileage-totalMileage', key: 'totalMileage'},
                            `Financial Year Mileage: ${this.state.financialYearTotalMileage}`
                        ),
                        this.el(
                            'div',
                            {className: 'financialYearValue-totalValue', key: 'totalValue'},
                            `Financial Year Mileage Value: £${this.state.financialYearTotalValue.toFixed(2)}`
                        ),
                        this.el(
                            'div',
                            {key: 'disclaimer'},
                            "Values for the current month may include unapproved expenses"
                        )
                    ]
                ),
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

export default ExpenseBreakdownYearToDateComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById('react-expense-breakdown');
    ReactDOM.render(React.createElement(ExpenseBreakdownYearToDateComponent, {
        isApprover: domContainer.dataset.isApprover,
        userId: domContainer.dataset.userId
    }), domContainer);
})
