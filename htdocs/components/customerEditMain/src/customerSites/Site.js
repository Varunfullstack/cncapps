import React from 'react'
import What3WordsInput from "./dumbComponents/What3WordsInput";
import SiteSelect from "./dumbComponents/SiteSelect";
import SiteInput from "./dumbComponents/SiteInput";

export default class Site extends React.Component {

    constructor(props) {
        super(props);
        this.handleDeliverContact = this.handleDeliverContact.bind(this);
        this.handleInputChange = this.handleInputChange.bind(this);
    }

    handleInputChange($event) {
        const target = $event.target;
        let value = target.value;
        const name = target.name;
        if (target.type === 'checkbox') {
            value = target.checked;
        }
        this.props.updateSite(this.props.site.siteNo, {[name]: value});
    }

    checkDelete() {
        return confirm('Are you sure you want to delete this site?');
    }

    handleDeliverContact() {

    }

    renderFields() {
        const formStructure = [
            {
                name: "address1",
                size: 35,
                maxLength: 35,
                handleChange: ($event) => this.handleInputChange($event),
                required: true,
                labelWidth: "13%",
                inputWidth: "87%",
                labelText: "Site Address",
                value: this.props.site.address1,
            },
            {
                name: "address2",
                size: 35,
                maxLength: 35,
                handleChange: this.handleInputChange,
                labelText: " ",
                value: this.props.site.address2,
            },
            {
                name: "address3",
                size: 35,
                maxLength: 35,
                handleChange: this.handleInputChange,
                labelText: " ",
                value: this.props.site.address3
            },
            {
                name: "town",
                size: 25,
                maxLength: 25,
                handleChange: this.handleInputChange,
                labelText: "Town",
                value: this.props.site.town,
                required: true
            },
            {
                name: "county",
                size: 25,
                maxLength: 25,
                handleChange: this.handleInputChange,
                labelText: "County",
                value: this.props.site.county,
                required: true
            },
            {
                name: "what3Words",
                handleChange: this.handleInputChange,
                value: this.props.site.what3Words,
                component: What3WordsInput
            },
            {
                name: "postcode",
                size: 15,
                maxLength: 15,
                handleChange: this.handleInputChange,
                labelText: "Postcode",
                value: this.props.site.postcode,
                required: true
            },
            {
                name: "phone",
                size: 20,
                maxLength: 20,
                handleChange: this.handleInputChange,
                labelText: "Phone",
                value: this.props.site.phone,
                className: "telephoneValidator"
            },
            {
                name: "maxTravelHours",
                size: 5,
                maxLength: 5,
                type: 'number',
                handleChange: this.handleInputChange,
                labelText: "Max Travel Hours",
                value: this.props.site.maxTravelHours
            },
            {
                name: "invoiceSiteNo",
                type: 'radio',
                handleChange: (e) => this.props.changeInvoiceSiteNo(e.target.value),
                labelText: "Default Invoice",
                value: `${this.props.site.siteNo}`,
                checked: +this.props.invoiceSiteNo === +this.props.site.siteNo
            },
            {
                name: "deliverSiteNo",
                type: 'radio',
                handleChange: (e) => this.props.changedDeliverSiteNo(e.target.value),
                labelText: "Default Delivery",
                value: `${this.props.site.siteNo}`,
                checked: +this.props.deliverSiteNo === +this.props.site.siteNo
            },
            {
                name: "invoiceContact",
                handleChange: this.handleInputChange,
                labelText: "Invoice Contact",
                value: this.props.site.invoiceContact,
                options: this.props.contacts,
                optionLabelFn: x => `${x.firstName} ${x.lastName}`,
                component: SiteSelect,
            },
            {
                name: "deliveryContact",
                handleChange: this.handleInputChange,
                labelText: "Delivery Contact",
                value: this.props.site.deliveryContact,
                options: this.props.contacts,
                optionLabelFn: x => `${x.firstName} ${x.lastName}`,
                component: SiteSelect,
            },
            {
                name: "nonUKFlag",
                type: 'checkbox',
                handleChange: this.handleInputChange,
                labelText: "Non UK",
                value: '1',
                checked: this.props.site.nonUKFlag
            },
            {
                name: "active",
                type: 'checkbox',
                handleChange: this.handleInputChange,
                labelText: "Active",
                value: '1',
                checked: this.props.site.active
            },
        ]

        return formStructure.map(x => {
            const TagName = x.component || SiteInput;
            return (
                <TagName
                    name={x.name}
                    size={x.size}
                    type={x.type}
                    maxLength={x.maxLength}
                    handleChange={x.handleChange}
                    required={x.required}
                    labelWidth={x.labelWidth}
                    inputWidth={x.inputWidth}
                    labelText={x.labelText}
                    value={x.value}
                    options={x.options}
                    optionLabelFn={x.optionLabelFn}
                    checked={x.checked}
                    key={`${x.name}-${this.props.site.siteNo}`}
                />
            )
        })
    }

    render() {
        let {site, customerId} = this.props;
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
                {this.renderFields()}
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
