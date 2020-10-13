import {Component, createElement} from "react";
import {render} from 'react-dom';

class OutOfHoursReportComponent extends Component {
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
}


export default OutOfHoursReportComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#react-out-of-hours-report');
    render(createElement(OutOfHoursReportComponent), domContainer);
})