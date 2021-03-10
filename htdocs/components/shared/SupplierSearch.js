import MainComponent from "../shared/MainComponent.js";
import AutoComplete from "./AutoComplete/autoComplete.js";
import React from "react";
import {SupplierService} from "../services/SupplierService";

class SupplierSearch extends MainComponent {

    constructor(props) {
        super(props);
        this.state = {suppliers: []}
    }

    componentDidMount() {
        SupplierService.getSuppliersSummaryData().then(suppliers => this.setState({suppliers}))
    }

    handleOnSelect = (value) => {
        if (this.props.onChange)
            this.props.onChange(value)
    }

    render() {
        return <AutoComplete
            errorMessage={"No Supplier found"}
            items={this.state.suppliers}
            displayLength={"40"}
            displayColumn={"name"}
            pk={"id"}
            width={this.props.width || 300}
            onSelect={this.handleOnSelect}
        />
    }
}

export default SupplierSearch;