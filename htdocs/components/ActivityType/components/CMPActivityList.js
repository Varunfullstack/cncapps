import MainComponent from "../../CMPMainComponent.js";
import APICallactType from "../../services/APICallacttype.js";
import Icon from "../../utils/Icon.js";
import Table from "../../utils/table/table.js";
import { sort } from "../../utils/utils.js";
 
class CMPActivityList extends MainComponent {
    el=React.createElement;
    apiCallactType=new APICallactType();
    constructor(props) {
        super(props);
        
        this.state = { types:[] }
    }
    componentDidMount=async ()=> {
        const types=await this.apiCallactType.getAllWithDetails();
        console.log(types);
        sort(types,"order");
        this.setState({types})
    }
    getListElement=()=>{
        const { types } = this.state;
        const {el}=this;
        const columns = [
            {
                label: "",
                path: "",
                content:(type)=>
                this.el(Icon,{title:"Move Down",name:"fal fa-sort", size:4,onClick:()=>this.handleEdit(type)})


              },
              
          {
            path: "description",
            label: "Activity Type",
            sortable:true,
            content:(item)=>el('div',{dangerouslySetInnerHTML:{ __html:item.description}
            })
          },
          {
            label: "Visible in SR",
            path: "visibleInSRFlag",     
            sortable:true,      
          },
          {
            label: "Active",
            path: "activeFlag",     
            sortable:true,       
          },
          {
            label: "Value",
            path: "curValueFlag",    
            sortable:true,        
          },
          {
            label: "Multiplier",
            path: "oohMultiplier",  
            sortable:true,          
          },
          {
            label: "Min Hours",
            path: "minHours",     
            sortable:true,       
          },
          {
            label: "Max Hours",
            path: "maxHours",            
            sortable:true,
          },
          {
            label: "Send Email",
            path: "customerEmailFlag",            
            sortable:true,
          },
          {
            label: "Portal",
            path: "portalDisplayFlag",            
            sortable:true,
          },
          {
            label: "Allow SCR Printing",
            path: "allowSCRFlag",            
            sortable:true,
          },
          {
            label: "Require Checking",
            path: "requireCheckFlag",            
            sortable:true,
          },
          {
            label: "Allow Reason",
            path: "allowReasonFlag",            
            sortable:true,
          },
          {
            label: "Allow Action",
            path: "allowActionFlag",            
            sortable:true,
          },
          {
            label: "Allow Final",
            path: "allowFinalStatusFlag",            
            sortable:true,
          },
          {
            label: "Require Reason",
            path: "reqReasonFlag",            
            sortable:true,
          },
          {
            label: "Require Action",
            path: "reqActionFlag",            
            sortable:true,
          },
          {
            label: "Require Final",
            path: "reqFinalStatusFlag",            
            sortable:true,
          },
          {
            label: "Travel",
            path: "travelFlag",            
            sortable:true,
          },
          {
            label: "Show No Charge",
            path: "showNotChargeableFlag",            
            sortable:true,
          },
          {
            label: "Engineer Over Time",
            path: "engineerOvertimeFlag",            
            sortable:true,
          },
          {
            label: "On-site",
            path: "onSiteFlag",            
            sortable:true,
          },
          {
            label: "Require CNC Next Action, CNC Action",
            path: "catRequireCNCNextActionCNCAction",            
            sortable:true,
          },
          {
            label: "Require CNC Next Action On Hold",
            path: "catRequireCNCNextActionOnHold",            
            sortable:true,
          },
          {
            label: "Require Customer Note CNC Action",
            path: "catRequireCustomerNoteCNCAction",            
            sortable:true,
          },
          {
            label: "Require Customer Note On Hold",
            path: "catRequireCustomerNoteOnHold",            
            sortable:true,
          },
          {
            label: "Min Minutes Allowed",
            path: "minMinutesAllowed",            
            sortable:true,
          },
          {
            label: "",
            path: "",
            content:(type)=>this.el(Icon,{title:"Edit",name:"fal fa-edit", size:3,onClick:()=>this.handleEdit(type)})
          },
        ];
        return this.el(
          "div",
          null,
          this.el(Table, {
            key: "activityList",
            data: types || [],
            columns: columns,
            pk: "callActTypeID",
            search: true,
            allowRowOrder:true,
            onOrderChange:this.handleOrderChange
          })
        );
    }
    handleOrderChange=(current,next)=>{
        const {types}=this.state;
        console.log(current,next);
        const last=types.filter(t=>t.order<next.order);
        const currentIndx=types.findIndex(t=>t.callActTypeID===current.callActTypeID);
        if(last.length>0)
        {
        const prevIndex=types.findIndex(t=>t.callActTypeID===last[last.length-1].callActTypeID);        
        types[currentIndx].order=  types[prevIndex].order+0.01;        
        }
        else
        {
            types[currentIndx].order=  types[0].order-0.01;            
        }
   
        this.apiCallactType.updateActivityTypeOrder(current.callActTypeID,types[currentIndx].order).then(res=>{
            console.log(res);
            sort(types,"order");
            this.setState({types});
        });
    }
    handleEdit=(type)=>{
        console.log(type);
        window.location=`ActivityType.php?action=editActivityType&callActTypeID=${type.callActTypeID}`
    }
    render() { 
        return this.getListElement();
    }
}
 
export default CMPActivityList;