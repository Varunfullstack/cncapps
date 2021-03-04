import React, {Component, Fragment} from "react";

import './ServiceRequestSummary.css';

export class ServiceRequestSummary extends Component {

    getSummaryData(data) {
        const summaryData = {
            total: 0,
            initial: 0,
            serviceRequestsPerEngineerMap: {},
            unassignedInitial: 0,
            unassignedTotal: 0,
            futureInitial: 0,
            futureTotal: 0
        }


        const summary = data.reduce((acc, item) => {
            acc.total++;
            const isInitial = item.status == 'I'
            acc.initial += isInitial;
            if (item.engineerName) {
                const key = item.engineerName.match(/\b(\w)/g).join('');
                if (!(key in acc.serviceRequestsPerEngineerMap)) {
                    acc.serviceRequestsPerEngineerMap[key] = {initial: 0, total: 0};
                }
                acc.serviceRequestsPerEngineerMap[key].total++;
                acc.serviceRequestsPerEngineerMap[key].initial += isInitial;
            } else {
                acc.unassignedInitial += isInitial;
                acc.unassignedTotal++;
            }
            if (item.alarmDateTime) {
                acc.futureTotal++;
                acc.futureInitial += isInitial;
            }
            return acc;
        }, summaryData);

        const mapAsSortedArray = Object.keys(summary.serviceRequestsPerEngineerMap)
            .sort((a, b) => a.localeCompare(b))
            .reduce((acc, key) => {
                    acc.push({...summary.serviceRequestsPerEngineerMap[key], label: key})
                    return acc;
                },
                []
            );

        return {
            total: summary.total,
            initial: summary.initial,
            serviceRequestsPerEngineer: mapAsSortedArray,
            unassignedInitial: summary.unassignedInitial,
            unassignedTotal: summary.unassignedTotal,
            futureInitial: summary.futureInitial,
            futureTotal: summary.futureTotal,
        };
    }

    render() {
        const {data} = this.props;
        if (!data.length) {
            return "";
        }

        const summaryData = this.getSummaryData(data);
        return (
            <div className="service-request-summary">
                <dt>
                    Initial / Total:
                </dt>
                <dd>
                    {summaryData.initial || "-"}/{summaryData.total}
                </dd>
                {
                    summaryData.serviceRequestsPerEngineer.map((item, indx) =>
                        <Fragment key={"f" + indx}>
                            <dt key={indx}>{item.label}:</dt>
                            <dd key={'t' + indx}>{item.initial || "-"}/{item.total}</dd>
                        </Fragment>
                    )
                }
                <dt>
                    Unassigned:
                </dt>
                <dd>
                    {summaryData.unassignedInitial || '-'}/{summaryData.unassignedTotal}
                </dd>
                <dt>
                    Future:
                </dt>
                <dd>
                    {summaryData.futureInitial || '-'}/{summaryData.futureTotal}
                </dd>
            </div>
        )
    }

}