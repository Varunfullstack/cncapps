import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './ReviewListComponent.css';
import APIReviewList from "./services/APIReviewList.js";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";

class ReviewListComponent extends MainComponent {
   api=new APIReviewList();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            reviews:[]   ,
            mode:"new"   ,
            data:{
                id:'',
                description:'',                 
            }
        };
    }

    componentDidMount() {      
        this.getData();
    }

    getData=()=>{
        this.api.getReviews().then(res=>{
            if(res.state)
            this.setState({reviews:res.data});
        });
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "description",
               label: "",
               hdToolTip: "Name",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-text color-gray2 pointer",
               sortable: true,
               //className: "text-center",                
            },             
            {
                path: "edit",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=> <i className="fal fa-2x fa-edit color-gray pointer" onClick={()=>this.showEditModal(type)}></i>,             
             },
             {
                path: "trash",
                label: "",
                hdToolTip: "Delete",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=>type.canDelete? <i className="fal fa-2x fa-trash-alt color-gray pointer" onClick={()=>this.handleDelete(type)}></i>:null,             
             }
        ];
    
        return <Table           
        style={{width:500,marginTop:20}}
        key="leadStatus"
        pk="id"
        columns={columns}
        data={this.state.reviews||[]}
        search={true}
        >
        </Table>
    }
    showEditModal=(data)=>{        
        this.setState({showModal:true,data,mode:'edit'});
    }
    handleDelete=async (review)=>{
        const conf=await this.confirm("Are you sure to delete this review?")
        if(conf)
        this.api.deleteReview(review.id).then(res=>{
            if(res.state)
            this.getData();
            else this.alert(res.error);
        })
    }
     
    handleNewReview=()=>{
        this.setState({mode:"new",showModal:true, data:{
            id:'',
            description:'',            
        }});
    }
    hideModal=()=>{
        this.setState({ showModal:false});
    }
    getModalElement=()=>{
        const {mode,data}=this.state;
        return <Modal 
        width={500}
        show={this.state.showModal}
        title={mode=="new"?"Add New Review":"Edit Review"}
        onClose={this.hideModal}
        content={
            <div key="content">

                <div className="form-group">
                  <label >Description</label>
                  <input value={data.description} type="text" name="" id="" className="form-control required" onChange={(event)=>this.setValue("description",event.target.value)} />                   
                </div>
            </div>        
        }
        footer={<div key="footer">
                <button onClick={this.handleSave}>Save</button>
                <button onClick={this.hideModal}>Cancel</button>
            </div>}
        >

        </Modal>
    }
    handleSave=()=>{
        const { data, mode } = this.state;
        if (data.description == "") {
          this.alert("Review name required.");
          return;
        }
        if (mode == "new") {
          this.api.addReview(data).then((result) => {
            if (result.state) {
              this.setState({ showModal: false });
             
            } else {
              this.alert(result.error);
            }
            this.getData();
          });
        }
        else if(mode=='edit')
        {
            this.api.updateReview(data).then((result) => {
              if (result.state) {
                this.setState({ showModal: false });              
              } else {
                this.alert(result.error);
              }
              this.getData();
            });
        }
    }
    render() { 
        return 'test';       
        return <div>
            <Spinner show={this.state.showSpinner}></Spinner>
            <ToolTip title="New Review" width={30}>
                <i className="fal fa-2x fa-plus color-gray1 pointer" onClick={this.handleNewReview}></i>
            </ToolTip>
            {this.getConfirm()}
            {this.getAlert()}
            {this.getModalElement()}
           {this.getDataTable()}
        </div>;
    }
}

export default ReviewListComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactReviewList");
    if (domContainer)
        ReactDOM.render(React.createElement(ReviewListComponent), domContainer);
});