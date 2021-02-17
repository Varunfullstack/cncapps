import CurrentActivityService from "../services/CurrentActivityService";
import React, { Fragment } from "react";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import APIStandardText from "../../services/APIStandardText";
import MainComponent from "../../shared/MainComponent";
import APICustomers from "../../services/APICustomers";
class CallBackModal extends MainComponent {
  apiCurrentActivityService = new CurrentActivityService();
  apiTemplate=new APIStandardText();
  apiCustomer=new APICustomers();

  constructor(props) {
    super(props);
    this.state={
        templateOptions:[],
        data:{
             description:"",
            time:moment().add(10,'minute').format("HH:mm"),
            date:moment().format("YYYY-MM-DD"),
            contactID:this.props.problem.contactID,
            customerID:this.props.problem.customerID,
            problemID:this.props.problem.problemID,
            contactName:this.props.problem.contactName,
            callActivityID:this.props.problem.callActivityID,
        },
        contcts:[]
    }
   }
  componentDidMount() {
  //this.apiTemplate.getOptionsByType("").then(templateOptions=>this.setState({templateOptions}))
  console.log('problem-----------------',this.props.problem.customerID,this.props.problem.contactID);
  this.apiCustomer.getCustomerContacts(this.props.problem.customerID).then(contcts=>{
    console.log(contcts);
    this.setState({contcts});
  })
  }
  handleClose = () => {    
    if(this.props.onClose)
        this.props.onClose();
  };
  getContent=()=>{
      const { data ,contcts} = this.state;
      console.log('contact',data.contactID);
      return (
        <div>
            
          <div className="form-group">
            <label>Callback date / time</label>            
            <div className="flex-row">
              <input
                type="date"
                className="modal-input"
                style={{ width: 120 }}
                value={data.date}
                onChange={(event) => this.setValue("date", event.target.value)}
              ></input>

              <input
                type="time"
                className="modal-input"
                style={{ width: 70 }}
                value={data.time}
                onChange={(event) => this.setValue("time", event.target.value)}
              ></input>
            </div>
          </div>

          <div className="form-group">
                <label>Contact</label>
                <select value={data.contactID}  onChange={(event) =>this.handleContactChange(event.target.value) }>
                    <option>                        
                    </option>
                    {contcts.map(c=><option key={c.id}  value={c.id}>{c.firstName+' '+ c.lastName}</option>)}
                </select>
            </div>

          <div className="form-group">
            <label>Reason for the call back</label>
            <textarea
              className="modal-input"
              style={{}}
              value={data.description }
              onChange={(event) =>
                this.setValue("description", event.target.value)
              }
            ></textarea>
          </div>
        </div>
      );
  }
  handleContactChange=(contactID)=>{
      const {contcts,data}=this.state;
      const contact=contcts.find(c=>c.id==contactID);
      data.contactID=contactID;
      data.contactName=contact.firstName+' '+contact.lastName;
      this.setState({data});
  }
  handleSave=()=>{
    const {data}=this.state;
   
        console.log(data);
    this.apiCurrentActivityService.addCallback(data).then(result=>{
        console.log(result);
    });

   }
  render() {
    if (!this.props.show) return null;
    return (
      <Modal
        width={600}
        show={this.props.show}
        title="Record Customer Call Back"
        content={<div key="content">
            {this.getContent()}
        </div>}
        footer={<div key="footer">
            <button onClick={this.handleSave}>Save</button>
            <button onClick={this.handleClose}>Cancel</button>
        </div>}
        onClose={() => this.handleClose}
      ></Modal>
    );
  }
}

export default CallBackModal;
