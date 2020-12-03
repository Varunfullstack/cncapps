import React from 'react';

import './ActivityHeaderComponent.css'

export class ActivityHeaderComponent extends React.Component {

    constructor(props, context) {
        super(props, context);
    }

    render() {
        const {serviceRequestData} = this.props;

        if (!serviceRequestData) {
            return '';
        }

        return (
            <h2 className="activity-header-component">
                <span className="company-info">
                    <a
                        className={serviceRequestData.customerNameDisplayClass}
                        href={`Customer.php?action=dispEdit&customerId=${serviceRequestData.customerId}`}
                        target="_blank"
                    >
                    {`${serviceRequestData.customerName}, ${serviceRequestData.siteAdd1}, ${serviceRequestData.siteAdd2}, ${serviceRequestData.siteAdd3}, ${serviceRequestData.siteTown}, ${serviceRequestData.sitePostcode}`}
                </a>
                </span>
                <span className="contact-info">
                    <a href={`Customer.php?action=dispEdit&customerId=${serviceRequestData.customerId}`}
                       target="_blank"
                    >
                                {`${serviceRequestData.contactName} `}
                    </a>
                    <span className='contactPhone'>
                        <label>Site:</label>
                        <a href={`tel:${serviceRequestData.sitePhone}`}> {serviceRequestData.sitePhone} </a>
                    </span>
                    {serviceRequestData.contactPhone ?
                        <span className="contactPhone">
                            <label>DDI:</label>
                            <a href={`tel:${serviceRequestData.contactPhone}`}>{serviceRequestData.contactPhone}</a>
                        </span> : null
                    }
                    {serviceRequestData.contactMobilePhone ?
                        <span className="contactPhone">
                                <label> Mobile:</label>
                                <a href={`tel:${serviceRequestData.contactMobilePhone}`}>{serviceRequestData.contactMobilePhone}</a>
                            </span>
                        : null
                    }
                    <a href={`mailto:${serviceRequestData.contactEmail}?subject=Service Request ${serviceRequestData.problemID} - ${serviceRequestData.serviceRequestEmailSubject} - Update`}>
                                <i className="fal fa-envelope ml-5"/>
                    </a>
                </span>

                <p className='formErrorMessage mt-2'>{serviceRequestData.contactNotes}</p>
                <p className='formErrorMessage mt-2'>{serviceRequestData.techNotes}</p>
            </h2>

        )
    }
}