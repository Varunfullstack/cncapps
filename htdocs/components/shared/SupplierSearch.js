import MainComponent from "../shared/MainComponent.js";
import AutoComplete from "./AutoComplete/autoComplete.js";
import React from "react";
import {SupplierService} from "../services/SupplierService";

class SupplierSearch extends MainComponent {

    constructor(props) {
        super(props);
        this.state = {
            suppliers: [],
            loading: false,
        }
    }

    componentDidMount() {
        this.setState({loading: true}, async () => {
            let suppliers = await SupplierService.getSuppliersSummaryData();
            suppliers = suppliers.filter(x => x.active).sort((a, b) => a.name.localeCompare(b.name));
            this.setState({suppliers, loading: false});
        })
    }

    handleOnSelect = (value) => {
        if (this.props.onChange)
            this.props.onChange(value)
    }

    render() {
        const {suppliers, loading} = this.state;
        if (loading) {
            return <div className="loading"/>
        }

        return <AutoComplete
            errorMessage={"No Supplier found"}
            items={suppliers}
            displayLength={"40"}
            displayColumn={"name"}
            value={this.props.defaultText}
            pk={"id"}
            width={this.props.width || 300}
            onSelect={this.handleOnSelect}
            disabled={this.props.disabled}
        />
    }
}

export default SupplierSearch;