import React from 'react';
import ToolTip from "../../shared/ToolTip";
import APIActivity from "../../services/APIActivity";
import MainComponent from "../../shared/MainComponent";
import APISalesOrders from '../../services/APISalesOrders';
import Modal from '../../shared/Modal/modal';


export class LinkServiceRequestOrder extends MainComponent {
    api = new APISalesOrders();
    apiActivity=new APIActivity();
    constructor(props, context) {
        super(props, context);
        this.state = {
            ...this.state,    
           
            newOrdHeadID:'',
            salesOrders:[],
            serviceRequestID:this.props.serviceRequestID      
        }
    }
    componentDidUpdate(prevProps, prevState) {
       
    }
    componentDidMount() {
        if(this.props.customerId)
        this.api.getCustomerInitialSalesOrders(this.props.customerId).then(salesOrders=>{
            this.setState({salesOrders});
        });
    }
    handleClose=()=>{
      if(this.props.onClose)
      this.props.onClose();
    }
    getSalesOrderModal=()=>{
        const {newOrdHeadID,salesOrders}=this.state;
        const {show}=this.props;
         
        let title="Linked to Sales Order";    
        return (
          <Modal
            key="orderModal"
            title={title}
            onClose={this.handleClose}
            width={800}            
            show={show}
            content={
              <div key="content" style={{minHeight:200}}>
                <div className="flex-row">
                  <label>Order Number </label>
                  <input  type="number" value={newOrdHeadID} onChange={(event)=>this.setState({newOrdHeadID:event.target.value})}></input>
                </div>
                <table className="table table-striped">
                  <tbody>
                    {salesOrders.map(order=><tr style={{cursor:"pointer"}} key={order.orderID} onClick={()=>this.setState({newOrdHeadID:order.orderID})}>
                      <td>
                        <ToolTip title="Select Order">
                          <i className="fal fa-plus fa-2x icon pointer" style={{color:"white"}}></i>
                        </ToolTip>
                        
                      </td>
                      <td>
                        <a className="white" href={`/SalesOrder.php?action=displaySalesOrder&ordheadID=${order.orderID}`} target="_blank">
                        {order.orderID}
                        </a>                  
                      </td>
                      <td>{this.getCorrectDate(order.date)}</td>
                      <td>{order.firstComment}</td>
                    </tr>)}
                    
                  </tbody>
                </table>
              </div>
            }
            footer={
              <div key="footer">
                <button   onClick={this.handleUpdateSalesOrder}>Update</button>
                <button  onClick={this.handleClose}>Cancel</button>
              </div>
            }
          ></Modal>
        );
      }
      handleUpdateSalesOrder=()=>{
        const {newOrdHeadID,serviceRequestID}=this.state;
        if(newOrdHeadID=='')
        {
          this.alert("Please Enter Order Number");
          return;
        }
        this.apiActivity.linkSalesOrder(serviceRequestID,newOrdHeadID).then(result=>{
            this.setState({newOrdHeadID:'' });
            this.handleClose();         
        }).catch(ex=>{
          this.alert("Failed to save order");
        });
      
      }
      render(){
        return <div style={{height:400}}> 
                {this.getAlert()}
                {this.getSalesOrderModal()}
               </div> 
      }
}