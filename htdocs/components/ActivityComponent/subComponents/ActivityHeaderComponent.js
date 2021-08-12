import React from 'react';
import ToolTip from '../../shared/ToolTip';

import './ActivityHeaderComponent.css'

export class ActivityHeaderComponent extends React.Component {

    constructor(props, context) {
        super(props, context);
    }
    getHeader = (data) => {

      
        if (!data) {
            return '';
        }
        return (
            <div style={{display: "flex", flexDirection: "column"}}>
                <div style={{display: "flex", alignItems: "center"}}>
                    <a
                        className={data?.customerNameDisplayClass}
                        href={`Customer.php?action=dispEdit&customerID=${data?.customerId}`}
                        target="_blank"
                    >
                        {data?.customerName + ", " +
                        data?.siteAdd1 + ", " +
                        data?.siteAdd2 + ", " +
                        data?.siteAdd3 + ", " +
                        data?.siteTown + ", " +
                        data?.sitePostcode}
                    </a>
                    {data.what3Words ?
                        <ToolTip
                            title="What3words"
                            width={30}
                            content={<a
                                className="fal fa-map-marker-alt fa-x m-5 pointer icon"
                                href={`https://what3words.com/${data?.what3Words}`}
                                target="_blank"
                                rel="noreferrer"></a>
                            }
                        /> : null
                    }
                </div>

                <div>
                    <a href={`Customer.php?action=dispEdit&customerID=${data?.customerId}`}
                       target="_blank"
                    >
                        {data?.contactName + " "}
                    </a>
                    <a href={`tel:${data?.sitePhone}`}> {data?.sitePhone} </a>
                    {data?.contactPhone ? <label>DDI:</label> : null}
                    {data?.contactPhone ? (<a href={`tel:${data?.contactPhone}`}>{data?.contactPhone}</a>) : null}
                    {data?.contactMobilePhone ? <label> Mobile:</label> : null}
                    {data?.contactMobilePhone ?
                        <a href={`tel:${data?.contactMobilePhone}`}>{data?.contactMobilePhone}</a> : null
                    }
                    <a href={`mailto:${data?.contactEmail}?cc=support@cnc-ltd.co.uk&subject=Service Request ${data?.problemID} - ${data.serviceRequestEmailSubject} - Update`}
                       target="_blank"
                    >
                        <i className="fal fa-envelope ml-5"/>
                    </a>
                </div>
                <p className='formErrorMessage mt-2'>{data?.contactNotes}</p>
                <p className='formErrorMessage mt-2'>{data?.techNotes}</p>
            </div>
        );
    }
    render() {
        const {serviceRequestData} = this.props;
        return this.getHeader(serviceRequestData);         
    }
    
}