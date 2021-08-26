import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './ReviewListComponent.css';
import APIReviewList from "./services/APIReviewList.js";
import Table from "../shared/table/table.js";
import Modal from "../shared/Modal/modal.js";
import moment from "moment";


class ReviewListComponent extends MainComponent {
    api = new APIReviewList();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            filter: {
                limit: 100,
                page: 1,
                orderBy: 'reviewDate',
                orderDir: 'asc',
                q: '',
                discontinued: false,
                interval:3,
                from:null,
                to:null
            },
            reset: false,
            reviews: [],
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
            leadStatus: '',
            contactPhone: '',
            reviewDate: '',
           
            latestUpdate: '',
            reviewUserName: '',
            customerId: '',
        };
    }

    getData = (noSpinner = false) => {
        const {filter, reset, reviews} = this.state;
        if (!noSpinner)
            this.setState({showSpinner: true});
        let from=null,to=null;
        switch (parseInt(filter.interval)) {
          case 1: //this week
            from=moment().startOf("W").format("YYYY-MM-DD");
            to=moment().endOf("W").format("YYYY-MM-DD");
            break;
          case 2: //Next week
            from=moment().startOf("W").add(7,"d").format("YYYY-MM-DD");
            to=moment().endOf("W").add(7,"d").format("YYYY-MM-DD");
            break;
          case 3: //This month
            from=moment().startOf("M").format("YYYY-MM-DD");
            to=moment().endOf("M").format("YYYY-MM-DD");
            break;
          case 4: //Next month
            from=moment().startOf("M").add(1,"M").format("YYYY-MM-DD");
            to=moment().endOf("M").add(1,"M").format("YYYY-MM-DD");
            break;
          case 5: //This Year
            from=moment().startOf("Y").format("YYYY-MM-DD");
            to=moment().endOf("Y").format("YYYY-MM-DD");
            break;
          case 6: //All records
            from=null;
            to=null;
            break;
        }
        console.log(filter.interval,from,to);
        this.api.getReviews(filter.limit, filter.page, filter.orderBy, 
            filter.orderDir, filter.q, filter.discontinued,from,to)
            .then(res => {
                if (!reset)
                    this.setState({reviews: [...reviews, ...res.data], showSpinner: false});
                else
                    this.setState({reviews: res.data, showSpinner: false});

            },error=>{
                this.alert("Error in loading data");
                this.setState({  showSpinner: false});

            })
    }

    handleScroll = () => {
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

    getDataTable = () => {
        const columns = [
           
            {
                path: "customerName",
                label: "",
                hdToolTip: "Customer",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                hdClassName: "text-center",
                sortable: true,
                content: (review) => <a
                    href={`/Customer.php?action=dispEdit&customerID=${review.customerId}&activeTab=crm`}>{review.customerName}</a>

            },
            {
                path: "contactName",
                label: "",
                hdToolTip: "Contact",
                icon: "fal fa-2x fa-id-card-alt color-gray2 pointer",
                hdClassName: "text-center",
                sortable: true,
            },
            {
                path: "contactEmail",
                label: "",
                hdToolTip: "Email",
                icon: "fal fa-2x fa-envelope color-gray2 pointer",
                hdClassName: "text-center",
                sortable: true,
                content: (review) => <a href={`mailto:${review.contactEmail}`}>{review.contactEmail}</a>
            },
            {
                path: "contactPhone",
                label: "",
                hdToolTip: "Contact Phone",
                icon: "fal fa-2x fa-phone gray2 pointer",
                hdClassName: "text-center",
                sortable: true,
                content: (review) => <a href={`tel:${review.contactPhone}`}>{review.contactPhone}</a>
            },
            {
                path: "leadStatus",
                label: "",
                hdToolTip: "Status",
                icon: "fal fa-2x fa-thermometer-full pointer",
                hdClassName: "text-center",
                sortable: true,
                width:100
            },
            {
                path: "latestUpdate",
                label: "",
                hdToolTip: "Last Update",
                icon: "fal fa-2x fa-file-alt pointer",
                hdClassName: "text-center",
                sortable: true,
            },
            {
                path: "reviewDate",
                label: "",
                hdToolTip: "Next CRM Review",
                icon: "fal fa-2x fa-calendar pointer",
                hdClassName: "text-center",
                sortable: true,
                content: (review) => this.getCorrectDate(review.reviewDate)
            },
            
            
            {
                path: "reviewUserName",
                label: "",
                hdToolTip: "User",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                hdClassName: "text-center",
                sortable: true,
                width:100
            }
        ];

        return <Table
            style={{marginTop: 20}}
            key="reviews"
            pk="customerId"
            columns={columns}
            data={this.state.reviews || []}
            search={false}
            onSearch={this.handleSearch}
        >
        </Table>
    }
    handleSearch=(q)=>{
        const {filter}=this.state;
        filter.q=q;
        this.setState({filter,reset:true},()=>this.getData())
    }
    showEditModal = (data) => {
        this.setState({showModal: true, data, mode: 'edit'});
    }
    handleDelete = async (review) => {
        const conf = await this.confirm("Are you sure to delete this review?")
        if (conf)
            this.api.deleteReview(review.id).then(res => {
                if (res.state)
                    this.getData();
                else this.alert(res.error);
            })
    }

    handleNewReview = () => {
        this.setState({
            mode: "new", showModal: true, data: {
                id: '',
                description: '',
            }
        });
    }
    hideModal = () => {
        this.setState({showModal: false});
    }
    getModalElement = () => {
        const {mode, data} = this.state;
        return <Modal
            width={500}
            show={this.state.showModal}
            title={mode == "new" ? "Add New Review" : "Edit Review"}
            onClose={this.hideModal}
            content={
                <div key="content">

                    <div className="form-group">
                        <label>Description</label>
                        <input value={data.description} type="text" name="" id="" className="form-control required"
                               onChange={(event) => this.setValue("description", event.target.value)}/>
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
    handleSave = () => {
        const {data, mode} = this.state;
        if (data.description == "") {
            this.alert("Review name required.");
            return;
        }
        if (mode == "new") {
            this.api.addReview(data).then((result) => {
                if (result.state) {
                    this.setState({showModal: false});

                } else {
                    this.alert(result.error);
                }
                this.getData();
            });
        } else if (mode == 'edit') {
            this.api.updateReview(data).then((result) => {
                if (result.state) {
                    this.setState({showModal: false});
                } else {
                    this.alert(result.error);
                }
                this.getData();
            });
        }
    }
    handleFilterChange=(field,value)=>{
        if(this.searchTimer)
            clearTimeout(this.searchTimer);
        this.searchTimer=setTimeout(()=>{
            const {filter}=this.state;
            filter[field]=value;
            this.setState({filter,reset:true},()=>this.getData());
        },500)
        
    }
    getFilterElement=()=>{
        const {filter}=this.state;
        return <div style={{display:"flex",flexDirection:"row",alignItems:"center",maxWidth:400}}>
            <label>Search</label>
            <input className="from-control mr-3"
            
             onChange={($event)=>this.handleFilterChange("q",$event.target.value)}></input>
            <select className="form-control" value={filter.interval} 
            onChange={($event)=>this.handleFilterChange("interval",$event.target.value)}>
                <option value={1}>This week</option>
                <option value={2}>Next week</option>
                <option value={3}>This month</option>
                <option value={4}>Next month</option>
                <option value={5}>This Year</option>
                <option value={6}>All records</option>
            </select>
        </div>
    }
    render() {
        return <div>
            <Spinner show={this.state.showSpinner}/>
            {this.getFilterElement()}
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