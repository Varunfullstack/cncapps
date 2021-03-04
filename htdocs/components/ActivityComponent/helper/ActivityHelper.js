export class ActivityHelper{
    getSalesOrderModal=()=>{
        const {showSalesOrder,newOrdHeadID,originalOrder,salesOrders}=this.state;
        let title="Linked to Sales Order";
        if(originalOrder)
        title="Linked to original Sales Order";
        return (
          <Modal
            key="orderModal"
            title={title}
            onClose={() => this.setState({ showSalesOrder: false })}
            width={800}
            show={showSalesOrder}
            content={
              <div key="content">
                <div className="flex-row">
                  <label>Order Number </label>
                  <input  type="number" value={newOrdHeadID} onChange={(event)=>this.setState({newOrdHeadID:event.target.value})}></input>
                </div>
                <table className="table table-striped">
                  <tbody>
                    {salesOrders.map(order=><tr style={{cursor:"pointer"}} key={order.orderID} onClick={()=>this.setState({newOrdHeadID:order.orderID})}>
                      <td>
                        <ToolTip title="Select Order">
                          <i className="fal fa-check fa-2x icon pointer" style={{color:"white"}}></i>
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
                <button  onClick={() => this.setState({ showSalesOrder: false })}>Cancel</button>
              </div>
            }
          ></Modal>
        );
      }
}