import React from "react";
import {Autocomplete} from "@material-ui/lab";
import PropTypes from "prop-types";
import APIItems from "../../ItemsComponent/services/APIItems";

export const CHANGE_REASON = {
    INITIALIZATION: 'INITIALIZATION'
}
export default class ItemSelectorComponent extends React.PureComponent {
    static defaultProps = {
        onChange: () => null
    }

    api = new APIItems();

    constructor(props, context) {
        super(props, context);
        this.state = {
            items: [],
            selectedOption: null
        }
    }

    componentDidUpdate(prevProps, prevState, snapshot) {

        if (prevProps.itemId !== this.props.itemId) {
            let selectedOption = null;
            if (this.props.itemId) {
                const {itemId} = this.props;
                const {items} = this.state;
                selectedOption = items.find(x => x.itemID === itemId);
                this.props.onChange(selectedOption, CHANGE_REASON.INITIALIZATION);
            }
            this.setState({selectedOption});
        }
    }


    async componentDidMount() {
        const {itemId} = this.props;
        const {data: items} = await this.api.getItems(100000, 1, 'description', 'asc', '', false);
        let selectedOption = null;
        if (itemId) {
            selectedOption = items.find(x => x.itemID === itemId);
            this.props.onChange(selectedOption, CHANGE_REASON.INITIALIZATION);
        }
        this.setState({items: items.sort((a, b) => a.description.localeCompare(b.name)), selectedOption});
    }

    getOptions() {
        const {items} = this.state;
        return [
            ...items.filter(x => x.discontinuedFlag !== 'Y')
        ];
    }

    stringSearch(haystack, needle) {
        return haystack.toLowerCase().indexOf(needle.toLowerCase()) > -1;
    }

    getOptionText(option) {
        if (!option) {
            return "";
        }

        return option.description;
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
            return x.discontinuedFlag !== 'Y' && this.stringSearch(x.description, inputValue);
        });
    }

    render() {
        const {selectedOption} = this.state;
        if (selectedOption) {
            return (
                <div style={{display: 'inline-block'}}>
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
                                  {value.description}
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


ItemSelectorComponent.propTypes = {
    itemId: PropTypes.number,
    onChange: PropTypes.func.isRequired
}