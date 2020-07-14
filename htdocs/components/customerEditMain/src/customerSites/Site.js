import React from 'react'

export default class Site extends React.Component {
    constructor(props) {
        super(props);
        this.handleDeliverContact = this.handleDeliverContact.bind(this);
    }

    checkDelete() {
        return confirm('Are you sure you want to delete this site?');
    }

    handleDeliverContact() {

    }

    render() {
        let {site, customerId, contacts, invoiceSiteNo, deliverSiteNo, changeInvoiceSiteNo, changedDeliverSiteNo} = this.props;
        return (
            <table className="content"
                   border="0"
                   cellPadding="2"
                   cellSpacing="1"
                   width="10%"
            >
                <tbody>
                <tr>
                    <td className="headerDarkgrey"
                        colSpan="2"
                    >
                        <span style={{color: 'black'}}>{site.siteNo}</span>&nbsp;
                        {
                            site.canDelete
                                ?
                                <a href={`/Customer.php?action=deleteSite&customerID=${customerId}&siteNo=${site.siteNo}`}
                                   onClick={this.checkDelete}
                                >Delete Site</a>
                                :
                                ''
                        }

                    </td>
                </tr>
                <tr>
                    <td className="content"
                        width="13%"
                    >Site Address
                    </td>
                    <td className="content"
                        width="87%"
                    >
                        <input value={site.address1 ? site.address1 : ''}
                               size="35"
                               maxLength="35"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">&nbsp;</td>
                    <td className="content">
                        <input value={site.address2 ? site.address2 : ''}
                               size="35"
                               maxLength="35"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">&nbsp;</td>
                    <td className="content">
                        <input value={site.address3 ? site.address3 : ''}
                               size="35"
                               maxLength="35"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Town</td>
                    <td className="content">
                        <input value={site.town ? site.town : ''}
                               size="25"
                               maxLength="25"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">County</td>
                    <td className="content">
                        <input value={site.county ? site.county : ''}
                               size="25"
                               maxLength="25"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Postcode</td>
                    <td className="content">
                        <input value={site.postcode ? site.postcode : ''}
                               size="15"
                               maxLength="15"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">What3Words</td>
                    <td className="content">
                        <input title="[word].[word].[word] format required"
                               value={site.what3Words ? site.what3Words : ''}
                               pattern="^\w+\.\w+\.\w+$"
                               size="30"
                               className="what3WordsInput"
                               readOnly
                        />
                        <span className="what3WordsLinkHolder">
                            <a target="_blank"
                               href={site.what3Words ? "https://what3words.com/" + site.what3Words : '#'}
                            >
                                <img src={site.what3Words ? '/images/w3w_SymbolTransparentBackground_RGB_Black.png' : "/images/w3w_SymbolTransparentBackground_RGB_Red.png"}
                                     height="30"
                                     alt="what3WordsLogo"
                                />
                            </a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td className="content">Phone</td>
                    <td className="content">
                        <input value={site.phone ? site.phone : ''}
                               size="20"
                               maxLength="20"
                               className="telephoneValidator"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Max Travel Hours</td>
                    <td className="content">
                        <input value={site.maxTravelHours ? site.maxTravelHours : ''}
                               size="5"
                               maxLength="5"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Default Invoice</td>
                    <td className="content">
                        <input type="radio"
                               value={site.siteNo !== null ? site.siteNo : ''}
                               checked={invoiceSiteNo == site.siteNo}
                               onChange={(e) => changeInvoiceSiteNo(e.target.value)}
                               name="invoiceSiteNo"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Default Delivery</td>
                    <td className="content">
                        <input type="radio"
                               value={site.siteNo !== null ? site.siteNo : ''}
                               checked={deliverSiteNo == site.siteNo}
                               name="deliverSiteNo"
                               onChange={(e) => changedDeliverSiteNo(e.target.value)}
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Invoice Contact</td>
                    <td>
                        <select
                            value={site.invoiceContact ? site.invoiceContact : ''}
                            onChange={this.handleDeliverContact}
                        >
                            {
                                contacts.length ?
                                    contacts.map(x => {
                                        return <option value={x.id}
                                                       key={`invoiceContact-${x.id}`}
                                        >{`${x.firstName} ${x.lastName}`}</option>
                                    }) :
                                    ''
                            }
                        </select>
                    </td>
                </tr>
                <tr>
                    <td className="content">Delivery Contact</td>
                    <td>
                        <select value={site.deliveryContact ? site.deliveryContact : ''}
                                onChange={this.handleDeliverContact}
                        >
                            {
                                contacts.length ?
                                    contacts.map(x => {
                                        return <option value={x.id}
                                                       key={`deliveryContact-${x.id}`}
                                        >{`${x.firstName} ${x.lastName}`}</option>
                                    }) :
                                    ''
                            }
                        </select>
                    </td>
                </tr>
                <tr>
                    <td className="content">Non UK</td>
                    <td className="content">
                        <input type="checkbox"
                               checked={site.nonUKFlag === 'Y'}
                               title="Check to show this site is overseas and not in the UK"
                               value="Y"
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Active</td>
                    <td className="content">
                        <input type="checkbox"
                               value="Y"
                               checked={site.active}
                               readOnly
                        />
                    </td>
                </tr>
                <tr>
                    <td colSpan="2"
                        className="content"
                    >
                        <button type="button"
                        >
                            Add Contact
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        );
    }
}
