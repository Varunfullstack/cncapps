import React from 'react'

class Site extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        let {site, customerId, contacts, defaultInvoice, defaultDelivery, changeDefaultInvoice, changedDefaultDelivery, addContactToSite} = this.props;
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
                        <span style="color: black">{site.siteNo}</span>
                        {
                            site.siteNo !== 0
                                ?
                                <a href={`/Customer.php?action=deleteSite&customerID=${customerId}&siteNo=${site.siteNo}`}
                                   onClick="if(!confirm('Are you sure you want to delete this site?')) return(false)"
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
                        <input value={site.address1}
                               size="35"
                               maxLength="35"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">&nbsp;</td>
                    <td className="content">
                        <input value={site.address2}
                               size="35"
                               maxLength="35"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">&nbsp;</td>
                    <td className="content">
                        <input value={site.address3}
                               size="35"
                               maxLength="35"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Town</td>
                    <td className="content">
                        <input value={site.town}
                               size="25"
                               maxLength="25"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">County</td>
                    <td className="content">
                        <input value={site.county}
                               size="25"
                               maxLength="25"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Postcode</td>
                    <td className="content">
                        <input value={site.postcode}
                               size="15"
                               maxLength="15"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">What3Words</td>
                    <td className="content">
                        <input title="[word].[word].[word] format required"
                               value={site.what3Words}
                               pattern="^\w+\.\w+\.\w+$"
                               size="30"
                               className="what3WordsInput"
                        />
                        <span className="what3WordsLinkHolder">
                        <a target="_blank"
                           href={site.what3Words ? "https://what3words.com/" + value : '#'}
                        >
                            <img src={site.what3Words ? "/images/w3w_SymbolTransparentBackground_RGB_Red.png" : '/images/w3w_SymbolTransparentBackground_RGB_Black.png'}
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
                        <input value={site.phone}
                               size="20"
                               maxLength="20"
                               className="telephoneValidator"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Max Travel Hours</td>
                    <td className="content">
                        <input value={site.maxTravelHours}
                               size="5"
                               maxLength="5"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Default Invoice</td>
                    <td className="content">
                        <input type="radio"
                               value={site.siteNo}
                               checked={defaultInvoice === site.siteNo}
                               onChange={changeDefaultInvoice(site.siteNo)}
                               name="defaultInvoice"
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Default Delivery</td>
                    <td className="content">
                        <input type="radio"
                               value={site.siteNo}
                               checked={defaultDelivery === site.siteNo}
                               name="defaultDelivery"
                               onChange={changedDefaultDelivery(site.siteNo)}
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Invoice Contact</td>
                    <td>
                        <select
                            value={site.invoiceContact}
                        >
                            {
                                contacts.map(x => {
                                    return <option value={x.id}>{x.name}</option>
                                })
                            }
                        </select>
                    </td>
                </tr>
                <tr>
                    <td className="content">Delivery Contact</td>
                    <td>
                        <select value={site.deliveryContact}>
                            {
                                contacts.map(x => {
                                    return <option value={x.id}>{x.name}</option>
                                })
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
                        />
                    </td>
                </tr>
                <tr>
                    <td className="content">Active</td>
                    <td className="content">
                        <input type="checkbox"
                               value="Y"
                               checked={site.active}
                        />
                    </td>
                </tr>
                <tr>
                    <td colSpan="2"
                        className="content"
                    >
                        <button type="button"
                                onClick={addContactToSite(site.siteNo)}
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
