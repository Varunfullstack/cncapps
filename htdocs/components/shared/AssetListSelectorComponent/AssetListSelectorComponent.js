import {Autocomplete} from "@material-ui/lab";
import APICustomers from "../../services/APICustomers";
import APIStandardText from "../../services/APIStandardText";
import React from 'react';

import striptags from "striptags";
import ToolTip from "../ToolTip";
import CNCCKEditor from "../CNCCKEditor";
import PropTypes from "prop-types";

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
            emptyAssetReasons: [],
            assets: [],
            maxUserNameLength: 0,
            maxComputerNameLength: 0,
            maxBiosVerLength: 0,
            selectedOption: null,
            isUnsupported: false
        }
        if (this.props.emptyAssetReason) {
            this.state.selectedOption = {isAsset: false, template: this.props.emptyAssetReason};
        }
        if (this.props.assetName) {

            const [, userName, biosVer] = (this.props.assetTitle || '').split(' ');
            this.state.selectedOption = {
                isAsset: true,
                name: this.props.assetName,
                lastUsername: userName == "undefined" ? "" : userName,
                biosVer: biosVer == "undefined" ? "" : biosVer,
                unsupported: this.props.unsupportedCustomerAsset
            };
        }
    }


    async componentDidMount() {
        const {customerId} = this.props;
        if (!customerId) {
            return;
        }
        await Promise.all([
            this.APICustomer.getCustomerAssets(customerId),
            this.APIStandardText.getOptionsByType("Missing Asset Reason")
        ]).then(([assets, emptyAssetReasons]) => {
            assets = assets.map((asset) => {
                if (
                    asset.biosName &&
                    (asset.biosName.indexOf("VMware") >= 0 ||
                        asset.biosName.indexOf("Virtual Machine") >= 0)
                ) {
                    asset.biosVer = "";
                }
                return asset;
            });
            emptyAssetReasons = emptyAssetReasons.map(x => ({
                ...x,
                template: striptags(x.template)
            })).sort((a, b) => a.template.localeCompare(b.template));

            const {maxComputerNameLength, maxUserNameLength, maxBiosVerLength} = assets.reduce(
                (acc, asset) => {

                    if (asset.name && asset.name.length > acc.maxComputerNameLength) {
                        acc.maxComputerNameLength = asset.name.length;
                    }
                    if (asset.lastUsername && asset.lastUsername.length > acc.maxUserNameLength) {
                        acc.maxUserNameLength = asset.lastUsername.length;
                    }
                    if (asset.biosVer && asset.biosVer.length > acc.maxBiosVerLength) {
                        acc.maxBiosVerLength = asset.biosVer.length;
                    }
                    return acc;
                }, {maxComputerNameLength: 0, maxUserNameLength: 0, maxBiosVerLength: 0})
            this.setState({emptyAssetReasons, assets, maxUserNameLength, maxComputerNameLength, maxBiosVerLength});
        })
    }

    getOptions() {

        const {emptyAssetReasons, assets} = this.state;
        return [
            ...assets.map(x => ({...x, isAsset: true})),
            ...emptyAssetReasons.map(x => ({...x, isAsset: false}))
        ];
    }

    stringSearch(haystack, needle) {
        if (!needle) {
            return true;
        }
        if (!haystack) {
            return false;
        }
        return haystack.toLowerCase().indexOf(needle.toLowerCase()) > -1;
    }

    getOptionText(option) {
        if (!option) {
            return "";
        }
        if (!option.isAsset) {
            return option.template.replace(/(<([^>]+)>)/gi, "");
        }
        return `${option.name} ${option.lastUsername || ""} ${option.biosVer || ""}`;
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
                (x.isAsset &&
                    (
                        this.stringSearch(x.name, inputValue) || this.stringSearch(x.lastUsername, inputValue) || this.stringSearch(x.assetTag, inputValue) || this.stringSearch(x.biosVer, inputValue)
                    )
                ) || (!x.isAsset && (this.stringSearch(x.name, inputValue) || this.stringSearch(x.template, inputValue)))

            );
        });
    }

    render() {
        const {maxUserNameLength, maxComputerNameLength, maxBiosVerLength, selectedOption} = this.state;
        const {showUnsupportedWhileSelected} = this.props;

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
                    {
                        showUnsupportedWhileSelected && selectedOption.unsupported ?
                            <ToolTip title="Asset is not covered by support contract"
                                     style={{display: "inline-block", verticalAlign: 'middle'}}
                            >
                                <i className="fa fa-2x fa-do-not-enter"
                                   style={{verticalAlign: "middle"}}
                                />
                            </ToolTip> : ''
                    }
                </div>
            )
        }

        return (
            <Autocomplete options={this.getOptions()}
                          filterOptions={(options, state) => this.filterOptions(options, state)}
                          getOptionLabel={(option) => this.getOptionText(option)}
                          clearOnBlur={false}
                          value={selectedOption}
                          renderOption={(value) => {
                              if (value.isAsset) {
                                  return (
                                      <React.Fragment>
                                          <div style={{
                                              display: "inline-block",
                                              fontSize: 12,
                                              fontFamily: "Arial",
                                              letterSpacing: "normal",
                                              whiteSpace: 'nowrap',
                                              minWidth: "2em"
                                          }}
                                          >
                                              {value.unsupported ? <i className="fa fa-2x fa-do-not-enter"
                                              /> : ''}
                                          </div>
                                          <div style={{
                                              display: "inline-block",
                                              minWidth: `${maxComputerNameLength + 4}ch`, fontSize: 12,
                                              fontFamily: "Arial",
                                              letterSpacing: "normal",
                                              whiteSpace: 'nowrap',
                                              paddingLeft: "1em"
                                          }}
                                          >
                                              {value.name}
                                          </div>
                                          <div style={{
                                              display: "inline-block",
                                              minWidth: `${maxUserNameLength + 4}ch`,
                                              fontSize: 12,
                                              fontFamily: "Arial",
                                              letterSpacing: "normal",
                                              whiteSpace: 'nowrap',
                                              paddingLeft: "1em"
                                          }}
                                          >
                                              {value.lastUsername}
                                          </div>
                                          <div style={{
                                              display: "inline-block",
                                              minWidth: `${maxBiosVerLength + 4}ch`,
                                              fontSize: 12,
                                              fontFamily: "Arial",
                                              letterSpacing: "normal",
                                              whiteSpace: 'nowrap',
                                              paddingLeft: "1em"
                                          }}
                                          >
                                              {value.biosVer}
                                          </div>

                                      </React.Fragment>
                                  )
                              }
                              return <React.Fragment>
                                  <span style={{
                                      fontSize: 12,
                                      fontFamily: "Arial",
                                      letterSpacing: "normal"
                                  }}
                                  >

                                  {value.template}
                                  </span>
                              </React.Fragment>
                          }}
                          onChange={(event, value, reason) => this.onChange(event, value, reason)}
                          renderInput={params => {
                              const {inputProps, InputLabelProps, InputProps} = params;
                              return (
                                  <div ref={InputProps.ref}>
                                      <label {...InputLabelProps} >
                                          <input {...inputProps} style={{width: "100%"}} className="form-control"/>
                                      </label>
                                  </div>
                              )
                          }}
            />
        );
    }
}

AssetListSelectorComponent.propTypes = {
    emptyAssetReason: PropTypes.string,
    assetName: PropTypes.string,
    assetTitle: PropTypes.string,
    customerId: PropTypes.number,
    onChange: PropTypes.func
};