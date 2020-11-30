import React from 'react';

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
            <React.Fragment>
                <a
                    className={serviceRequestData.customerNameDisplayClass}
                    href={`Customer.php?action=dispEdit&customerId=${serviceRequestData.customerId}`}
                    target="_blank"
                >
                    {`${serviceRequestData.customerName}, ${serviceRequestData.siteAdd1}, ${serviceRequestData.siteAdd2}, ${serviceRequestData.siteAdd3}, ${serviceRequestData.siteTown}, ${serviceRequestData.sitePostcode}`}
                </a>
                <div>
                    <a href={`Customer.php?action=dispEdit&customerId=${serviceRequestData.customerId}`}
                       target="_blank"
                    >
                        {`${serviceRequestData.contactName} `}
                    </a>
                    <a href={`tel:${serviceRequestData.sitePhone}`}> {serviceRequestData.sitePhone} </a>
                    {serviceRequestData.contactPhone ?
                        <span className="contactPhone">
                            <label>DDI:</label>
                            <a href={`tel:${serviceRequestData.contactPhone}`}>{serviceRequestData.contactPhone}</a>
                            {serviceRequestData.contactMobilePhone ?
                                <React.Fragment>

                                    <label> Mobile:</label>
                                    <a href={`tel:${serviceRequestData.contactMobilePhone}`}>{serviceRequestData.contactMobilePhone}</a>
                                </React.Fragment>
                                : null
                            }
                        </span>
                        :
                        null
                    }


                    <a href={`mailto:${serviceRequestData.contactEmail}?subject=${serviceRequestData.serviceRequestEmailSubject}`}>
                        <i className="fal fa-envelope ml-5"/>
                    </a>
                </div>
                <p className='formErrorMessage mt-2'>{serviceRequestData.contactNotes}</p>
                <p className='formErrorMessage mt-2'>{serviceRequestData.techNotes}</p>
            </React.Fragment>

        )
    }
}