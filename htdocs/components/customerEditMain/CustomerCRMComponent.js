import React from "react";
import { params } from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import ToolTip from "../shared/ToolTip";
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle";
import { Fragment } from "react";
import CKEditor from "ckeditor4-react";

export default class CustomerCRMComponent extends MainComponent {
  api = new APICustomers();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      customerId: null,
      leadStatuses: [],
      reset: false,
      showModal: false,
      isNew: true,
      data: { ...this.getInitData() },
      filter: {
        showInActive: false,
      },
      showSpinner: false,
    };
  }
  
  componentDidMount() {
    const customerId = params.get("customerID");
    this.getData();
    this.api.getLeadStatuses().then((res) => {
        this.setState({ leadStatuses:res.data });
      });
    this.api.getCustomerData(this.props.customerId).then(data=>{
        console.log("customer",data);
        this.setState({data})
    },error=>{
        this.alert("Error in get customer data");
        console.log(error);
    });
  }
  getData = () => {
    //this.setState({ showSpinner: true });
    //const customerId = params.get("customerID");    
  };

  getInitData() {
    return {
      id: "",
      customerID: params.get("customerID"),
      leadStatusId: "",
      mailshotFlag: "",
      dateMeetingConfirmed: "",
      meeting_datetime: "",
      inviteSent: "",
      reportProcessed: "",
      reportSent: "",
      rating: "",
      opportunity_deal: "",
    };
  }


  handleUpdateGenericField = ($event) => {
    //this.updateCustomerField($event.target.name, $event.target.value);
    this.setValue($event.target.name, $event.target.value);
  }

  handleSave=()=>{
    const {data}=this.state;
    this.api.updateCustomer(data).then(res=>{
        if(res.status)
        {
            this.alert("Data saved successfully");
            this.getCustomerData();
        }
        else
        this.alert("Data not saved successfully");

    },error=>{
        console.log(error);
        this.alert("Data not saved successfully");
    })
    console.log(data);
  }

  getCustomerData=()=>{
    this.api.getCustomerData(this.props.customerId).then(data=>{
        console.log("customer",data);
        this.setState({data})
    },error=>{
        this.alert("Error in get customer data");
        console.log(error);
    });
    
  getLeadInformationCard=()=>{       
    const { data } = this.state; 
    return  <div className="card m-5">
        <div className="card-header">
        <h3>Lead Information</h3>
        </div>
        <div className="card-body">
            <table>
            <tbody>
                <tr>
                    <td>
                        <div>Lead Status</div>
                    </td>
                    <td>
                    <select
                        required
                        value={data.leadStatusId}
                        onChange={(event) =>
                            this.setValue("leadStatusId", event.target.value)
                        }
                        className="form-control"
                        >
                        {this.state.leadStatuses.map((leadStatus, index) => {
                            return (
                            <option key={leadStatus.id} value={leadStatus.id}>
                                {leadStatus.name}
                            </option>
                            );
                        })}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>Email Opt Out</div>
                    </td>
                    <td>
                    <Toggle
                    checked={data.mailshotFlag === 1}
                    onChange={() =>
                        this.setValue(
                        "mailshotFlag",
                        data.mailshotFlag === 1 ? 0 : 1
                        )
                    }
                    ></Toggle>  
                    </td>
                </tr>
                <tr>
                    <td>
                        <label >Meeting Confirmed</label>
                    </td>
                   <td>
                   <input
                        type="date"
                        value={data.dateMeetingConfirmed || ""}
                        onChange={($event) =>
                            this.handleUpdateGenericField($event)
                        }
                        size="10"
                        maxLength="10"                          
                        className="form-control input-sm"
                        name="dateMeetingConfirmed"
                        style={{maxWidth:170}}
                        />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                        Meeting Date</label>
                    </td>
                    <td>
                   <input
                        type="datetime-local"
                        value={data.meeting_datetime || ""}
                        onChange={($event) =>
                            this.handleUpdateGenericField($event)
                        }
                        size="10"
                        maxLength="10"                          
                        className="form-control input-sm"
                        name="meeting_datetime"
                        style={{maxWidth:170}}
                        />
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>Invite Sent</div>
                    </td>
                    <td>
                    <Toggle
                        checked={data.inviteSent === 1}
                        onChange={() =>
                            this.setValue(
                            "inviteSent",
                            data.inviteSent === 1 ? 0 : 1
                            )
                        }
                        ></Toggle>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>Report Processed</div>
                    </td>
                    <td>
                    <Toggle
                        checked={data.reportProcessed === 1}
                        onChange={() =>
                            this.setValue(
                            "reportProcessed",
                            data.reportProcessed === 1 ? 0 : 1
                            )
                        }
                        ></Toggle>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>Report Sent</div>
                    </td>
                    <td>
                    <Toggle
                        checked={data.reportSent === 1}
                        onChange={() =>
                            this.setValue(
                            "reportSent",
                            data.reportSent === 1 ? 0 : 1
                            )
                        }
                        ></Toggle>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>Rating</div>
                    </td>
                    <td>
                    <input
                        value={data.rating || ""}
                        onChange={(event) =>
                            this.setValue("rating", event.target.value)
                        }
                        className="form-control"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
    }

  getOpportunityDealCard=()=>{   
        CKEditor.editorUrl = '/ckeditor/ckeditor.js'

        const ckeditorConfig = {
            contentsCss: '/screen.css',
            toolbarStartupExpanded: false,
            disableNativeSpellChecker: false,
            toolbar: 'CNCToolbar',
            toolbar_CNCToolbar:
                [
                    ['Source', '-', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'TextColor', 'BGColor'],
                    ['NumberedList', 'BulletedList'],
                    ['Table'],
                    ['Format', 'Font', 'FontSize'],
                    ['Anchor', 'Link'],
                    ['Undo', 'Redo']
                ],
            extraPlugins: 'font,wordcount',
            fontSize_sizes: '8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt',
            wordcount: {
                showParagraphs: false,
                showCharCount: true,
            },
        };
            
        const { data } = this.state;
        return <div className="card m-5">
        <div className="card-header">
            <h3>Opportunity Deal</h3>
        </div>
        <div className="card-body">
            <CKEditor config={ckeditorConfig}
            value={data.opportunity_deal || ""}
            onChange={($event) =>
                this.handleUpdateGenericField($event)
            }
            />
        </div>
    </div>
  }

  getCards=()=>{       
        
    return <div className="row" style={{margin:2}}>
        <div className="col-md-6">
            {this.getLeadInformationCard()}           
        </div>
        <div className="col-md-6">
            {this.getOpportunityDealCard()}
        </div>
    </div>
    }

  render() {
    if (this.state.showSpinner)
      return <Spinner show={this.state.showSpinner} />;

    return (
      <div>
        {this.getCards()}
        {this.getConfirm()}
        {this.getAlert()}
        <button onClick={this.handleSave} className="ml-5">Save</button>
      </div>
    );
  }
}
