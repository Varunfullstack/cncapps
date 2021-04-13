import MainComponent from "../shared/MainComponent.js";
import React from "react"; 
import APIItems from "../ItemsComponent/services/APIItems.js";
import AutoComplete from "./AutoComplete/autoComplete.js";
 
class ItemSearchComponent extends MainComponent {
   api=new APIItems();    
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            filter:{
                limit:100,
                page:1,
                orderBy:'description',
                orderDir:'asc',
                q:''
            },             
            items:[],            
        };
    }

    componentDidMount() {    
        this.getData();   
          }
  
      
    getData=( )=>{
        const {filter}=this.state;       
        this.api.getItems(filter.limit,filter.page,filter.orderBy,filter.orderDir,filter.q)
        .then(res=>{
           const items= res.data.filter(i=>i.description!=null&&i.description!='').map(i=>{
                return {id:i.itemID,name:i.description}
            })          
            this.setState({items}); 
        })
    }
    handleOnItemSelect=(event)=>{
        if(this.props.onSelect)
        this.props.onSelect(event);
    }
    handleOnFilter=(value)=>{
        const {filter}=this.state;
        filter.q=value;
        this.setState({filter},()=>this.getData());
    }
    render() {
        const {items}=this.state;
        return <div>
             <AutoComplete 
             errorMessage= "No Item found"
             items={items}
             displayLength= {40}
             displayColumn= "name"
             pk= "id"
             width={this.props.width||200}      
             value={this.props.value||''}      
             onSelect= {this.handleOnItemSelect}
             onFilter={this.handleOnFilter}
             ></AutoComplete>
        </div>;
    }
}

export default ItemSearchComponent;
 