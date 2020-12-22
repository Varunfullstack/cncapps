import {Autocomplete} from "@material-ui/lab";
import APICustomers from "../../services/APICustomers";
import APIStandardText from "../../services/APIStandardText";
import React from 'react';
import {TextField} from "@material-ui/core";

import striptags from "striptags";

export const ASSET_SELECTED_TYPE = {
    NO_ASSET_REASON: "NO_ASSET_REASON",
    ASSET: "ASSET",
    CLEAR: "CLEAR"
}

export default class AssetListSelectorComponent extends React.PureComponent {
    APICustomer = new APICustomers();
    APIStandardText = new APIStandardText();
    static defaultProps = {
        onChange: () => null
    }

    constructor(props, context) {
        super(props, context);


        this.state = {
            noAssetReasons: [],
            assets: [],
            maxUserNameLength: 0,
            maxComputerNameLength: 0,
            selectedOption: null
        }
        if (this.props.noAssetReason) {
            this.state.selectedOption = {isAsset: false, template: this.props.noAssetReason};
        }
        if (this.props.assetName) {
            const [name, userName, biosVer] = this.props.assetTitle.split(' ');
            this.state.selectedOption = {isAsset: true, name: this.props.assetName, LastUsername: userName, BiosVer: biosVer};
        }
    }


    async componentDidMount() {
        const {customerId} = this.props;
        await Promise.all([
            this.APICustomer.getCustomerAssets(customerId),
            this.APIStandardText.getOptionsByType("Missing Asset Reason")
        ]).then(([assets, noAssetReasons]) => {
            assets = assets.map((asset) => {
                if (
                    asset.BiosName.indexOf("VMware") >= 0 ||
                    asset.BiosName.indexOf("Virtual Machine") >= 0
                ) {
                    asset.BiosVer = "";
                }
                return asset;
            });
            noAssetReasons = noAssetReasons.map(x => ({
                ...x,
                template: striptags(x.template)
            })).sort((a, b) => a.template.localeCompare(b.template));

            const {maxComputerNameLength, maxUserNameLength} = assets.reduce(
                (acc, asset) => {

                    if (asset.name.length > acc.maxComputerNameLength) {
                        acc.maxComputerNameLength = asset.name.length;
                    }
                    if (asset.LastUsername.length > acc.maxUserNameLength) {
                        acc.maxUserNameLength = asset.LastUsername.length;
                    }
                    return acc;
                }, {maxComputerNameLength: 0, maxUserNameLength: 0})
            this.setState({noAssetReasons, assets, maxUserNameLength, maxComputerNameLength});
        })
    }

    getOptions() {
        const {noAssetReasons, assets} = this.state;
        return [
            ...assets.map(x => ({...x, isAsset: true})),
            ...noAssetReasons.map(x => ({...x, isAsset: false}))
        ];
    }

    stringSearch(haystack, needle) {
        return haystack.toLowerCase().indexOf(needle.toLowerCase()) > -1;
    }

    getOptionText(option) {
        if (!option) {
            return "";
        }
        if (!option.isAsset) {
            return option.template.replace(/(<([^>]+)>)/gi, "");
        }

        return `${option.name} ${option.LastUsername} ${option.BiosVer}`;
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
            return (
                !x.isAsset ||
                (
                    this.stringSearch(x.name, inputValue) || this.stringSearch(x.LastUsername, inputValue) || this.stringSearch(x.assetTag, inputValue) || this.stringSearch(x.BiosVer, inputValue)
                )

            );
        });
    }

    render() {
        const {maxUserNameLength, maxComputerNameLength, selectedOption} = this.state;


        if (selectedOption) {
            return (
                <div>
                <span>
                    {this.getOptionText(selectedOption)}
                </span>
                    <button onClick={() => {
                        this.setState({selectedOption: null});
                    }}
                    >
                        X
                    </button>
                </div>
            )
        }

        return (
            <Autocomplete renderInput={(params) => <TextField {...params} label="Select an Asset"
                                                              variant="outlined"
            />}
                          options={this.getOptions()}
                          filterOptions={(options, state) => this.filterOptions(options, state)}
                          getOptionLabel={(option) => this.getOptionText(option)}
                          clearOnBlur={false}
                          debug={true}
                          value={selectedOption}
                          renderOption={(value, state) => {
                              if (value.isAsset) {
                                  return (
                                      <React.Fragment>
                                          <div style={{display: "inline-block", width: `${maxComputerNameLength}em`}}>
                                              {value.name}
                                          </div>
                                          <div style={{display: "inline-block", width: `${maxUserNameLength}em`}}>
                                              {value.LastUsername}
                                          </div>
                                          <div style={{display: "inline-block"}}>
                                              {value.BiosVer}
                                          </div>
                                      </React.Fragment>
                                  )
                              }
                              return <React.Fragment>
                                  {value.template}
                              </React.Fragment>
                          }}
                          onChange={(event, value, reason) => this.onChange(event, value, reason)}
            />
        );
    }

}