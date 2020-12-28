import React from 'react';
import Autocomplete, {createFilterOptions} from '@material-ui/lab/Autocomplete';
import APICustomers from "../../services/APICustomers";
import {sort} from "../../utils/utils";

export class CustomerAssetSelector extends React.PureComponent {

    apiCustomer = new APICustomers();


    constructor(props, context) {
        super(props, context);
        this.state = {
            assets: []
        }
    }

    componentDidMount() {
        const {customerId} = this.props;
        Promise.all(
            this.getAssets(customerId),
            this.getNoAssetOptions()
        )

    }


    getAssets(customerId) {
        this.apiCustomer.getCustomerAssets(customerId).then((assets) => {
            assets = sort(assets, "name");
            assets = assets.map((asset) => {
                if (
                    asset.BiosName.indexOf("VMware") >= 0 ||
                    asset.BiosName.indexOf("Virtual Machine") >= 0
                ) {
                    asset.BiosVer = "";
                }
                return asset;
            });
            this.setState({assets});
        });
    }

    filterOptions = createFilterOptions({
        stringify: option => {
            return option.label;
        }
    })

    render() {


        return (
            <Autocomplete
                filterOptions={}
                options={}
            />

        );
    }

    getNoAssetOptions() {
        return fetch("StandardText.php?action=getByType&type=Missing Asset Reason")
            .then(res => res.json())
    }
}