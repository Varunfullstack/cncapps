import React from 'react';
import Skeleton from "react-loading-skeleton";
import ReactDOM from 'react-dom';
import Select from "./Select";

class CustomerReviewComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customerReview: {
                toBeReviewedOnDate: '',
                toBeReviewedOnTime: '',
                toBeReviewedOnByEngineerId: '',
                toBeReviewedOnAction: ''
            },
            customerId: props.customerID,
            reviewEngineers: []
        };
        document.customerReviewComponent = this;
        this.handleToBeReviewedOnDateChange = this.handleToBeReviewedOnDateChange.bind(this);
        this.handleToBeReviewedOnTimeChange = this.handleToBeReviewedOnTimeChange.bind(this);
        this.handleToBeReviewedOnByEngineerId = this.handleToBeReviewedOnByEngineerId.bind(this);
        this.handleToBeReviewedOnActionChange = this.handleToBeReviewedOnActionChange.bind(this);
    }

    componentDidMount() {

        Promise.all([
            fetch('?action=getCustomerReviewData&customerID=' + this.props.customerID)
                .then(response => response.json())
                .then(response => this.setState({customerReview: response.data})),
            fetch('?action=getReviewEngineers')
                .then(response => response.json())
                .then(response => this.setState({
                    reviewEngineers: response.data.map(x => ({
                        label: x.cns_name,
                        value: x.cns_consno,
                    }))
                })),
        ])
            .then(allLoaded => {
                this.setState({loaded: true});
            })
    }

    handleToBeReviewedOnDateChange($event) {
        console.log($event);
        this.updateCustomerField('toBeReviewedOnDate', $event.target.value);
    }

    handleToBeReviewedOnTimeChange($event) {
        this.updateCustomerField('toBeReviewedOnTime', $event.target.value);
    }

    handleToBeReviewedOnByEngineerId(value) {
        this.updateCustomerField('toBeReviewedOnByEngineerId', value);
    }

    handleToBeReviewedOnActionChange($event) {
        this.updateCustomerField('toBeReviewedOnAction', $event.target.value);
    }

    updateCustomerField(field, value) {
        this.setState(prevState => {
            const customerReview = {...prevState.customerReview};
            customerReview[field] = value;
            return {customerReview};
        })
    }

    save() {
        return fetch('?action=updateCustomerReview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({...this.state.customerReview, customerId: this.state.customerId})
        })
            .then(response => response.json())
            .then(response => {
                console.log('customer data saved');
            })
    }

    render() {
        if (!this.state.loaded) {
            return this.el(
                Skeleton,
                null,
                this.el(
                    'div',
                    {
                        className: 'reviewSection',
                        border: 0,
                        cellPadding: 2,
                        cellSpacing: 1,
                        width: '100%',
                        height: '200px'
                    },
                )
            );
        }


        return this.el(
            'div',
            {className: 'reviewSection', border: 0, cellPadding: 2, cellSpacing: 1, width: '100%'},
            [

                this.el('div',
                    {
                        key: 'reviewSection'
                    },
                    [
                        'To be reviewed on ',
                        this.el(
                            'input',
                            {
                                type: 'date',
                                value: this.state.customerReview.toBeReviewedOnDate || '',
                                onChange: this.handleToBeReviewedOnDateChange,
                                key: 'dateInput'
                            }
                        ),
                        ' Time ',
                        this.el(
                            'input',
                            {
                                type: 'time',
                                value: this.state.customerReview.toBeReviewedOnTime || '',
                                onChange: this.handleToBeReviewedOnTimeChange,
                                key: 'timeInput'
                            }
                        ),
                        ' By ',
                        this.el(
                            Select,
                            {
                                options: this.state.reviewEngineers,
                                selectedOption: this.state.customerReview.toBeReviewedOnByEngineerId || '',
                                onChange: this.handleToBeReviewedOnByEngineerId,
                                key: 'reviewBy'
                            }
                        )
                    ]
                ),
                this.el('div',
                    {
                        key: 'actionSection'
                    },
                    this.el(
                        'textarea',
                        {
                            title: "Action to be taken",
                            cols: "120",
                            rows: "3",
                            //     defaultValue: 'test',
                            value: this.state.customerReview.toBeReviewedOnAction || '',
                            onChange: this.handleToBeReviewedOnActionChange,
                            //     key: 'actionInput'
                        }
                    )
                ),
            ]
        )
    }
}

export default CustomerReviewComponent;