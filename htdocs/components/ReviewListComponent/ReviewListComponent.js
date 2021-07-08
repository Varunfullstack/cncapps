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
            filter: {
                limit: 100,
                page: 1,
                orderBy: 'customerName',
                orderDir: 'asc',
                q: '',
                discontinued: false
            },
            reset: false,
            items: [],
            showSpinner: false,
            showModal: false,
            isNew: true,
            data: {...this.getInitData()},
        };
    }

    componentDidMount() {
        this.getData();
        window.addEventListener('scroll', this.handleScroll, true);
    }

    componentWillUnmount() {
        window.removeEventListener('scroll', this.handleScroll);
    }

    getInitData() {
        return {
            customerName: '',
            contactName: '',
            contactEmail: '',
            contactPhone: '',
            leadStatus: '',
            contactPhone: '',
            reviewDate:'',
            reviewTime:'',
            latestUpdate:'',
            reviewUserName:'',
            customerId:'',
        };
    }

    getData_old=()=>{
        this.api.getReviews().then(res=>{
            if(res.data)
            this.setState({reviews:res.data});
        });
    }

    getData = (noSpinner = false) => {
        const {filter, reset, items} = this.state;
        if (!noSpinner)
            this.setState({showSpinner: true});
        this.api.getReviews(filter.limit, filter.page, filter.orderBy, filter.orderDir, filter.q, filter.discontinued)
            .then(res => {
                if (!reset)
                    this.setState({reviews: [...items, ...res.data], showSpinner: false});
                else
                    this.setState({reviews: res.data, showSpinner: false});

            })
    }

    handleScroll = (event) => {
        const {filter} = this.state;
        let scrollTop = window.scrollY;
        let docHeight = document.body.offsetHeight;
        let winHeight = window.innerHeight;
        let scrollPercent = scrollTop / (docHeight - winHeight);
        let scrollPercentRounded = Math.round(scrollPercent * 100);
        if (scrollPercentRounded > 70) {
            if (this.scrollTimer) clearTimeout(this.scrollTimer);
            this.scrollTimer = setTimeout(() => {
                filter.page++;
                this.setState({filter, reset: false}, () => this.getData(true));
            }, 500);
        }
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "customerName",
               label: "Customer",
               hdToolTip: "Name",
               hdClassName: "text-center",
               sortable: true,
               content:(review)=> <a href={`/CustomerCRM.php?action=displayEditForm&customerID=${review.customerId}`}>{ review.customerName }</a>             

            }, 
            {
                path: "contactName",
                label: "Contact",
                hdToolTip: "Name",
                hdClassName: "text-center",
                sortable: true,
             },
             {
                path: "contactEmail",
                label: "Email",
                hdToolTip: "Email",
                hdClassName: "text-center",
                sortable: true,
             },
             {
                path: "contactPhone",
                label: "Phone",
                hdToolTip: "Phone",
                hdClassName: "text-center",
                sortable: true,
             },
             {
                path: "leadStatus",
                label: "Status",
                hdToolTip: "status",
                hdClassName: "text-center",
                sortable: true,
             },
             {
                path: "reviewDate",
                label: "Date",
                hdToolTip: "Date",
                hdClassName: "text-center",
                sortable: true,
             }, 
             {
                path: "reviewTime",
                label: "Time",
                hdToolTip: "Time",
                hdClassName: "text-center",
                sortable: true,
             },
             {
                path: "lastUpdate",
                label: "Last Update",
                hdToolTip: "Last Update",
                hdClassName: "text-center",
                sortable: true,
             },
             {
                path: "reviewUserName",
                label: "User",
                hdToolTip: "User",
                hdClassName: "text-center",
                sortable: true,
             }
        ];
    
        return <Table           
        style={{marginTop:20}}
        key="reviews"
        pk="customerId"
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