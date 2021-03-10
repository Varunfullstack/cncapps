import {Autocomplete} from "@material-ui/lab";
import React from 'react';
import {SupplierService} from "../../services/SupplierService";
import PropTypes from 'prop-types';


export default class SupplierSelectorComponent extends React.PureComponent {
    static defaultProps = {
        onChange: () => null
    }

    constructor(props, context) {
        super(props, context);
        this.state = {
            suppliers: [],
            selectedOption: null
        }
    }


    async componentDidMount() {
        const {supplierId} = this.props;
        const suppliers = await SupplierService.getSuppliersSummaryData()
        let selectedOption = null;
        if (supplierId) {
            selectedOption = suppliers.find(x => x.id === supplierId);
        }
        this.setState({suppliers: suppliers.sort((a, b) => a.name.localeCompare(b.name)), selectedOption});
    }

    getOptions() {
        const {suppliers} = this.state;
        return [
            ...suppliers.filter(x => x.active)
        ];
    }

    stringSearch(haystack, needle) {
        return haystack.toLowerCase().indexOf(needle.toLowerCase()) > -1;
    }

    getOptionText(option) {
        if (!option) {
            return "";
        }

        return option.name;
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
            return x.active && this.stringSearch(x.name, inputValue);
        });
    }

    render() {
        const {selectedOption} = this.state;


        if (selectedOption) {
            return (
                <div>
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
                                  {value.name}
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

SupplierSelectorComponent.propTypes = {
    supplierId: PropTypes.number,
    onChange: PropTypes.func.isRequired
}