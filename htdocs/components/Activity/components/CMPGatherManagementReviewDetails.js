import MainComponent from "../../CMPMainComponent.js?v=1";
import APIActivity from "../../services/APIActivity.js?v=1";
import CKEditor from "../../utils/CKEditor.js?v=1";
import ToolTip from "../../utils/ToolTip.js?v=1";
import { params } from "../../utils/utils.js?v=1";

class CMPGatherManagementReviewDetails extends MainComponent {
  el = React.createElement;
  apiActivity = new APIActivity();

  constructor(props) {
    super(props);
    this.state = { ...this.state,description:""};
  }
  getElements = () => {
    const { el } = this;
    return el(
      "div",
      { style: { flex: 1,width:850,justifyContent:"flext-start"} },
      el(ToolTip, {
          width:50,
        title: "History",
        content: el("a", {
          className: "fal fa-history fa-2x icon pointer m-4",
          href: `Activity.php?problemID=${params.get(
            "problemID"
          )}&action=problemHistoryPopup&htmlFmt=popup`,
          target: "_blank",
        }),
      }     
      ),
      el('label',{className:"m-5",style:{fontSize:18,display:"block"}},"Why does this SR require review by management?"),
      el(CKEditor,{height:200,inline:true,onChange:(description)=>this.setState({description})}),
      el('button',{onClick:this.handleOnSave},"Save")
    );
  };
  handleOnSave=()=>{
   const { description } = this.state;
   if (description == "") {
     this.alert("Please enter reason for management review");
     return;
   }
   const payload={
       problemID:params.get("problemID"),
       description
   }
   this.apiActivity.saveManagementReviewDetails(payload).then(res=>{
       if(res.status)
       window.location=`CurrentActivityReport.php`
   })
  }
  render() {
    return this.el('div',null,
    this.getAlert(),
    this.getElements()
    );
  }
}
export default CMPGatherManagementReviewDetails;
