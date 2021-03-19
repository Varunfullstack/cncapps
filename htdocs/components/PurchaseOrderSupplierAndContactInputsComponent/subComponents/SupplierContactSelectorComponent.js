import {Autocomplete} from "@material-ui/lab";
import React from 'react';
import PropTypes from 'prop-types';
import {SupplierService} from "../../services/SupplierService";


export default class SupplierContactSelectorComponent extends React.PureComponent {
    static defaultProps = {
        onChange: () => null
    }

    constructor(props, context) {
        super(props, context);
        this.state = {
            supplierContacts: [],
            selectedOption: null
        }
    }


    async updateState(allowDefault) {
        const {supplierId, supplierContactId, onChange} = this.props;

        let selectedOption = null;
        let supplierContacts = [];
        if (supplierId) {
            const supplier = await SupplierService.getSupplierById(supplierId);
            supplierContacts = supplier.contacts;
            if (supplierContactId) {
                selectedOption = supplier.contacts?.find(x => x.id === supplierContactId);
                onChange(selectedOption);
            } else {
                if (allowDefault) {
                    selectedOption = supplier.contacts?.find(x => x.id === supplier.mainSupplierContactId);
                    onChange(selectedOption);
                }
            }
        }
        this.setState({selectedOption, supplierContacts});
    }

    async componentDidMount() {
        await this.updateState();
    }

    async componentDidUpdate(prevProps, prevState, snapshot) {
        if (prevProps.supplierId !== this.props.supplierId || prevProps.supplierContactId !== this.props.supplierContactId) {
            await this.updateState(prevProps.supplierId !== this.props.supplierId);
        }
    }

    getOptions() {
        const {supplierContacts} = this.state;
        return supplierContacts.filter(x => x.active);
    }

    stringSearch(haystack, needle) {
        return haystack.toLowerCase().indexOf(needle.toLowerCase()) > -1;
    }

    getOptionText(option) {
        if (!option) {
            return "";
        }

        return `${option.firstName} ${option.lastName}`
    }

    onChange(event, value, reason) {
        if (reason === 'select-option') {
            this.setState({selectedOption: value});
        }
        if (reason === 'clear') {
            this.setState({selectedOption: null});
        }
        this.props.onChange(value);
    }

    filterOptions(options, {inputValue}) {
        return options.filter(x => {
            return x.active && (this.stringSearch(x.firstName, inputValue) || this.stringSearch(x.lastName, inputValue));
        });
    }

    render() {
        const {selectedOption} = this.state;
        const {supplierId} = this.props;
        if (!supplierId) {
            return "Please select a supplier first";
        }


        if (selectedOption) {
            return (
                <div style={{display: "inline-block"}}>
                <span>
                    {this.getOptionText(selectedOption)}
                </span>
                    <button onClick={() => {
                        this.setState({selectedOption: null});
                        this.props.onChange(null);
                    }}
                    >
                        X
                    </button>
                </div>
            )
        }

        return (
            <Autocomplete options={this.getOptions()}
                          filterOptions={(options, state) => this.filterOptions(options, state)}
                          getOptionLabel={(option) => this.getOptionText(option)}
                          clearOnBlur={false}
                          value={selectedOption}
                          renderOption={(value, state) => {
                              return <React.Fragment>
                                  <span style={{
                                      fontSize: 12,
                                      fontFamily: "Arial",
                                      letterSpacing: "normal"
                                  }}
                                  >
                                  {this.getOptionText(value)}
                                  </span>
                              </React.Fragment>
                          }}
                          onChange={(event, value, reason) => this.onChange(event, value, reason)}
                          renderInput={params => {
                              const {inputProps, InputLabelProps, InputProps} = params;
                              return (
                                  <div ref={InputProps.ref}>
                                      <label {...InputLabelProps} >
                                          <input {...inputProps} style={{width: "100%"}}/>
                                      </label>
                                  </div>
                              )
                          }}
            />
        );
    }

}

SupplierContactSelectorComponent.propTypes = {
    supplierId: PropTypes.number,
    onChange: PropTypes.func.isRequired
}