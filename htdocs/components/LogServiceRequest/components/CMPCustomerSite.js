import SVCLogService from "../SVCLogService.js";
import CKEditor from "../../utils/CKEditor.js";

//import   CKEditor from "ckeditor5-react";
//import * as ClassicEditor from '../../npm/node_modules/@ckeditor/ckeditor5-build-classic/build/ckeditor.js';

class CMPCustomerSite extends React.Component {
  el = React.createElement;
  api = new SVCLogService();
  constructor(props) {
    super(props);
    const {data}=this.props;
    this.state = {
      sites: [],
      assets: [],
      data: {
        reason: data.reason||'',
        internalNotes: data.internalNotes||'',
        asset:data.asset||'',
        site:data.site||'',
      },
    };
  }
  componentDidMount() {
    const { el, api } = this;
    // load customer Sites
    api.getCustomerSites(this.props.customerId).then((res) => {
      //console.log(res);
      let selectedSiteId = "";
      if (res.length == 1) selectedSiteId = res[0].id;
      this.setState({ sites: res, selectedSiteId });
    });
    // load customer assets
    api.getCustomerAssets(this.props.customerId).then((res) => {
      //console.log("assets", res);
      this.setState({ assets: res });
    });
  }
  getSitesElement = () => {
    const { sites, data } = this.state;
    const { el, handleSiteChange } = this;
    //console.log("selectedSiteId", selectedSiteId);
    return el(
      "div",
      null,
      el("label", { className: "site-label" }, "Site"),
      el(
        "select",
        {
          value: data.site,
          onChange: handleSiteChange,
          className: "site-select",
        },
        el("option", { key: "default", value: "" }),
        sites.map((s) =>
          el("option", { value: s.id, key: `site${s.id}` }, s.title)
        )
      )
    );
  };

  handleSiteChange = (event) => {
    const {data}=this.state;
    data.site=event.target.value;
    console.log(event.target.value);
    this.setState({ data });
  };
  getAssetElement = () => {
    const { assets } = this.state;
    const { el, handleAssetChange } = this;
    return el(
      "div",
      null,
      el("label", { className: "site-label" }, "Asset"),
      el(
        "select",
        { onChange: handleAssetChange, className: "site-select",value:this.state.data.asset },
        el("option", { key: "default", value: "" }),
        assets.map((s) =>
          el("option", { value: s.name, key: `asset${s.name}` }, s.name)
        )
      )
    );
  };
  handleAssetChange = (event) => {
    const {data}=this.state;
    data.asset=event.target.value
    console.log("assets", event.target.value);
    this.setState({ data});
  };
  handleReasonChange = (text, field) => {    
    const {data}=this.state;
    data[field]=text;
    this.setState({ data});
  };
  getNotesElement = () => {
    const { el, handleReasonChange } = this;
    return el(
      "div",
      null,
      el("label", { className: "site-label" }, "Details"),
      el(CKEditor, {
        id: "reason",
        value: this.state.data.reason,
        onChange: (data) => handleReasonChange(data, "reason"),
      }),
      el(
        "div",
        { style: { marginTop: 10 } },
        el("label", {}, "Internal Notes"),
        el(CKEditor, {
          id: "internalNotes",
          value: this.state.data.internalNotes,
          onChange: (data) => handleReasonChange(data, "internalNotes"),
        })
      )
    );
  };
  handleNext=()=>{
    let {data}=this.state;    
    data.nextStep=4;
    this.props.updateSRData(data);
  }
  getNextButton=()=>{
      const {el,handleNext}=this;
      return el('div',null,
      el('button',{onClick:handleNext,className:"float-right"},"Next >"))
  }
  render() {
    const { el, getSitesElement, getAssetElement, getNotesElement ,getNextButton} = this;
    return el(
      "div",
      null,
      getSitesElement(),
      getAssetElement(),
      getNotesElement(),
      getNextButton()
    );
  }
}
 
export default CMPCustomerSite;