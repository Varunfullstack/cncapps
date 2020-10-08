import APIActivity from "../../services/APIActivity.js";
import {params} from "../../utils/utils.js";
import Toggle from "../../utils/toggle.js";
import Table from "../../utils/table/table.js"
import Modal from "../../utils/modal.js";
import CKEditor from "../../utils/CKEditor.js";
import ToolTip from "../../utils/ToolTip.js";
class CMPActivityDisplay extends React.Component {    
    el=React.createElement;
    api = new APIActivity();
    constructor(props) {
        super(props);
        this.state = {  
            currentUser:{ 
                        globalExpenseApprover: 0,           
                        isExpenseApprover: 0,
                        isSdManager: false
                        },
            uploadFiles:[],
            data:null,
            currentActivity:'',
            _showModal:false,
            templateOptions:[],
            templateOptionId:null,                       
            templateDefault:'',
            templateValue:'',       
            templateType:'',
            templateTitle:'',
            selectedChangeRequestTemplateId:null,
            filters:{
                showTravel:false,
                showOperationalTasks:false,
                showServerGaurdUpdates:false,                
                criticalSR:false,
                monitorSR:false
            }            
        } 
        this.fileUploader=new React.createRef();
    }
    componentDidMount() {
        this.loadFilterSession();
        setTimeout(()=>this.loadCallActivity(params.get('callActivityID')),10);
        
       
    }
    loadCallActivity=async (callActivityID)=>{        
        const {filters}=this.state;
        const currentUser=await this.api.getCurrentUser();
        const res=await this.api.getCallActivityDetails(callActivityID,filters);        
        console.log(res);        
        filters.monitorSR=res.monitoringFlag=="1"?true:false;
        filters.criticalSR=res.criticalFlag=="1"?true:false;             
        this.setState({filters,data:res,currentActivity:res.callActivityID,currentUser});
        
    }
    getProjectsElement=()=>{
        const {data}=this.state;
        const {el}=this;
        if(data&&data.projects.length>0)
        {
            return el('div',{style:{display:"flex",flexDirection:"row",alignItems:"center",marginTop:-20} },
            el('h3',{className:"mr-5"},"Projects "),
            data.projects.map(p=>el("a",{key:p.projectID,href:p.editUrl,className:"link-round"},p.description))
            )
        }
        else return null;
    }
    getHeader=()=>{
        const {el}=this;
        const {data}=this.state;
        return el('div',{style:{display:"flex",flexDirection:"column"}},
        el('div',null,
        el('a',{ className:data?.customerNameDisplayClass,href:`Customer.php?action=dispEdit&customerId=${data?.customerId}`,target:"_blank"},
        data?.contactName+" ") ,
            el('a', {href:`tel:${data?.sitePhone}`},data?.sitePhone),
            el('label', null," DDI: "),
            el('a', {href:`tel:${data?.contactPhone}`},data?.contactPhone),
            el('label', null," Mobile: "),
            el('a', {href:`tel:${data?.contactMobilePhone}`},data?.contactMobilePhone),
            el('a', {href:`mailto:${data?.contactEmail}?subject=Service Request ${data?.problemID}`},el("i",{className:"fal fa-envelope ml-5"})),
        ),
        
        el('a',{ className:data?.customerNameDisplayClass,href:`Customer.php?action=dispEdit&customerId=${data?.customerId}`,target:"_blank"},
        data?.customerName+", "+
        data?.siteAdd1+", "+
        data?.siteAdd2+", "+
        data?.siteAdd3+", "+
        data?.siteTown+", "+
        data?.sitePostcode,
        ),        
        el('p',{className:'formErrorMessage mt-2'},data?.contactNotes),
        el('p',{className:'formErrorMessage mt-2'},data?.techNotes)
        )
    }
    
    getActions=()=>{
        const {el}=this;
        const {data,currentUser}=this.state;
        return el('div',{className:"activities-contianer", style:{display:"flex",flexDirection:"row",justifyContent:"center",alignItems:"center"}},
        el(ToolTip,{title:"Follow On",content: el('i',{className:"fal fa-play fa-2x m-5 pointer icon",onClick:this.handleFollowOn})}),  
        el(ToolTip,{title:"History",content: el('a',{className:"fal fa-history fa-2x m-5 pointer icon",href:`Activity.php?action=problemHistoryPopup&problemID=${data?.problemID}&htmlFmt=popup`,target:"_blank"})}),
        el(ToolTip,{title:"Passwords",content:el('a',{className:"fal fa-unlock-alt fa-2x m-5 pointer icon",href:`Password.php?action=list&customerID=${data?.customerId}`,target:"_blank"})}),
        this. getGab(),
        data?.canEdit=='ALL_GOOD'?el(ToolTip,{title:"Edit",content: el('a',{className:"fal fa-edit fa-2x m-5 pointer icon",href:`ActivityNew.php?action=editActivity&callActivityID=${data?.callActivityID}`})}):null,  
        data?.canEdit!='ALL_GOOD'?el(ToolTip,{title:data?.canEdit,content: el('i',{className:"fal fa-edit fa-2x m-5 pointer icon-disable"})}):null,  
        data?.canDelete?el(ToolTip,{title:data?.activities.length===1?"Delete Request":"Delete Activity",content: el('i',{className:"fal fa-trash-alt fa-2x m-5 pointer icon",onClick:()=>this.handleDelete(data)})}):null,          
        !data?.canDelete?el(ToolTip,{title:"Delete Activity",content: el('i',{className:"fal fa-trash-alt fa-2x m-5 pointer  icon-disable",})}):null,  
        this. getGab(),
        data?.linkedSalesOrderID?el(ToolTip,{title:"Sales Order",content:el('a',{className:"fal fa-tag fa-2x m-5 pointer icon" ,href:`SalesOrder.php?action=displaySalesOrder&ordheadID=${data?.linkedSalesOrderID}`,target:"_blank"})}):null,
        !data?.linkedSalesOrderID?el(ToolTip,{title:"Sales Order",content:el('a',{className:"fal fa-tag fa-2x m-5 pointer icon",onClick:()=>this.handleSalesOrder(data?.callActivityID)})}):null,        
        data?.linkedSalesOrderID?el(ToolTip,{title:"Unlink Sales order",content:el('a',{className:"fal fa-unlink fa-2x m-5 pointer icon",onClick:()=>this.handleUnlink(data?.linkedSalesOrderID) })}):null,
        el(ToolTip,{title:"Renewal Information",content:el('a',{className:"fal fa-tasks fa-2x m-5 pointer icon",href:`RenewalReport.php?action=produceReport&customerID=${data?.customerId}`,target:"_blank"})}),
        // el(ToolTip,{title:"Generate Password",content: el('a',{className:"fal fa-magic fa-2x m-5 pointer icon",onClick:this.handleGeneratPassword})}),
        el(ToolTip,{title:"Contracts",content: el('a',{className:"fal fa-file-contract fa-2x m-5 pointer icon",href:`Activity.php?action=contractListPopup&customerID=${data?.customerId}`,target:"_blank"})}),

        this. getGab(),
        el(ToolTip,{title:"Contact SR History",content: el('a',{className:"fal fa-id-card fa-2x m-5 pointer icon",onClick:()=>this.handleContactSRHistory(data?.contactId)})}),    
        el(ToolTip,{title:"Third Party Contacts",content: el('a',{className:"fal fa-users fa-2x m-5 pointer icon",href:`ThirdPartyContact.php?action=list&customerID=${data?.customerId}`,target:"_blank"})}),
        this.getGab(),
        data?.hasExpenses&&(currentUser.isExpenseApprover||currentUser.globalExpenseApprover)? el(ToolTip,{title:"Expenses",content: el('a',{className:"fal fa-receipt fa-2x m-5 pointer icon",href:`Expense.php?action=view&callActivityID=${data?.callActivityID}`})}):this.getGab(),  
        el(ToolTip,{title:"Add Travel",content: el('a',{className:"fal fa-car fa-2x m-5 pointer icon",href:`Activity.php?action=createFollowOnActivity&callActivityID=${data?.callActivityID}&callActivityTypeID=22`})}),  
        currentUser.isSdManager&&data?.problemHideFromCustomerFlag=='Y'?el(ToolTip,{title:"Unhide SR",content: el('i',{className:"fal fa-eye-slash fa-2x m-5 pointer icon",onClick:()=>this.handleUnhideSR(data)})}):this.getGab(),  
        el(ToolTip,{title:"Calendar",content: el('a',{className:"fal fa-calendar-alt fa-2x m-5 pointer icon",href:`Activity.php?action=addToCalendar&callActivityID=${data?.callActivityID}`})}),      
        data?.allowSCRFlag=='Y'?el(ToolTip,{title:"Send client a visit confirmation email",content: el('i',{className:"fal fa-envelope fa-2x m-5 pointer icon",onClick:()=>this.handleConfirmEmail(data)})}):this.getGab(),      

        );
    }
    getGab=()=>{
        return this.el('span',{style:{width:35}})
    }
    handleConfirmEmail=async (data)=>{
        if(confirm('Are you sure you want to send the client a confirmation email?')) 
        {
            await this.api.sendActivityVisitEmail(data.callActivityID);
        }
    }
    handleUnhideSR=async (data)=>{
        if(data?.isSDManger&&data?.problemHideFromCustomerFlag=='Y')
        {
            if(confirm('This will unhide the SR from the customer and can\'t be undone, are you sure?'))
            await this.api.unHideSrActivity(data.callActivityID);
            data.problemHideFromCustomerFlag='N';
            this.setState({data});
        }
    }
    handleDelete=(data)=>{
        let deleteActivity=false;
        if(data.activities.length===1){            
            if(confirm('Deleting this activity will remove all traces of this Service Request from the system. Are you sure?'))
            deleteActivity=true;
        }
        else if(confirm('Delete this activity?'))
            deleteActivity=true;
        if(deleteActivity)
        this.api.deleteActivity(data.callActivityID).then(res=>this.goPrevActivity())

    }
    handleFollowOn=()=>{
        const {data}=this.state;
        const followOn=data?.problemStatus=='I'&&data?.serverGuard=='N'&&data?.hideFromCustomerFlag=='N';
        if(followOn)
        {
            if(confirm('You are about to commence work and an email will be sent to the customer?')) 
                window.location=`Activity.php?action=createFollowOnActivity&callActivityID=${data?.callActivityID}`
        }
        else 
            window.location=`Activity.php?action=createFollowOnActivity&callActivityID=${data?.callActivityID}`
    }

    handleGeneratPassword=()=>{
        window.open("Password.php?action=generate&htmlFmt=popup",'reason','scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0');
    }
    handleSalesOrder=(callActivityID)=>{
        console.log('opened')
        const w=window.open(`Activity.php?action=editLinkedSalesOrder&htmlFmt=popup&callActivityID=${callActivityID}`,'reason','scrollbars=yes,resizable=yes,height=150,width=250,copyhistory=no, menubar=0');
        w.onbeforeunload =()=>this.loadCallActivity();
    }
    handleUnlink=async(linkedSalesOrderID)=>{
        const res= confirm(`Are you sure you want to unlink this request to Sales Order ${linkedSalesOrderID}`);
        if(res)
        {
            await this.api.unlinkSalesOrder(linkedSalesOrderID);
            this.loadCallActivity();
        }        
    }
    handleContactSRHistory(contactId){
        const w=window.open(`Activity.php?action=displayServiceRequestForContactPopup&contactID=${contactId}&htmlFmt=popup`, 'reason', 'scrollbars=yes,resizable=yes,height=400,width=1225,copyhistory=no, menubar=0');
        //w.onbeforeunload =()=>this.loadCallActivity();
    }
    saveFilterSession=()=>{
        const {filters}=this.state;
        sessionStorage.setItem('displayActivityFilter',JSON.stringify(filters));
    }
    loadFilterSession=()=>{
        const item=sessionStorage.getItem('displayActivityFilter');
        if(item)
        {
            const filters=JSON.parse(item);
            this.setState({filters})
        }
    }
    handleTogaleChange=async (filter)=>{
        const {filters,currentActivity}=this.state;
        filters[filter]=!filters[filter];                
        this.setState({filters});         
        console.log(filter==="criticalSR",filters[filter]);
        if(filter==="criticalSR")                    
            await this.api.setActivityCritical(currentActivity);        
        if(filter==="monitorSR")
            await  this.api.setActivityMonitoring(currentActivity);
        this.saveFilterSession();
        this.loadCallActivity(currentActivity);
    }
    getToggle=(label,filter)=>{
        const {filters}=this.state;
        const {el}=this;      
        return el('div',{className:"m-5",style:{display:"flex",alignItems: "center", justifyContent: "center"}},
                el(Toggle,{onChange:()=>this.handleTogaleChange(filter),checked: filters[filter],name:filter}),
                el("label",{className:"ml-4 nowrap"},label)
        )
    }
    
    handleActivityChange=(event)=>{
        const callActivityID=event.target.value;
        this.loadCallActivity(callActivityID);
        this.setState({currentActivity:callActivityID});
    }
    getCurrentActivityIndxElement=(data,currentActivity)=>{
        const {el}=this;
        if(!data)        
        return null;
        const indx=data.activities.findIndex(a=>a.callActivityID==currentActivity);
        return el('div',{className:"ml-5"},el('strong',null,(indx+1)),el('label',null,` of ${data.activities.length}`))
    }
    goNextActivity=()=>{
        const {data,currentActivity}=this.state;
        let index=data.activities.findIndex(a=>a.callActivityID==currentActivity);        
        if(index<(data.activities.length-1))
        {
            index++;
            this.setState({currentActivity:data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        }
       
    }
    goPrevActivity=()=>{
        const {data,currentActivity}=this.state;
        let index=data.activities.findIndex(a=>a.callActivityID==currentActivity);        
        if(index>0)
        {
            index--;
            this.setState({currentActivity:data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        }
       
    }
    goLastActivity=()=>{
        const {data,currentActivity}=this.state;
        let index=data.activities.findIndex(a=>a.callActivityID==currentActivity);        
        if(index!=(data.activities.length-1))
        {
            index=data.activities.length-1;
            this.setState({currentActivity:data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        } 
    }
    goFirstActivity=()=>{
        const {data,currentActivity}=this.state;
        let index=data.activities.findIndex(a=>a.callActivityID==currentActivity);        
        if(index!=0)
        {
            index=0;
            this.setState({currentActivity:data.activities[index].callActivityID});
            this.loadCallActivity(data.activities[index].callActivityID);
        } 
    }
    getOnsiteActivities=(onSiteActivities)=>{
        const {el}=this;
        
        if(onSiteActivities&&onSiteActivities.length>0)
        {
            let columns = [
                {
                    path: "title",
                    label: "On-site Activities Within 10 Days",
                    sortable: false,  
                    content:(activity)=>el('a',{href:`ActivityNew.php?action=displayActivity&callActivityID=${activity.callActivityID}`,target:"_blank"},activity.title)  
                },
            ]
            return el('div',{style:{width:300}},el(Table, {
                key: "onSiteActivities",
                data: onSiteActivities || [],
                columns: columns,
                pk: "callActivityID",
                search: false,
              }));
        }
        else return null;
    }
    getActivitiesElement=()=>{
        const {data,currentActivity}=this.state;
        const {el}=this;
        return el('div',{className:"activities-contianer"},
        el('div',{style:{width:"100%",display:"flex",alignItems: "center", justifyContent: "center"}},
        el(ToolTip,{title:"Next Activity",content: el('i',{className:"fal  fa-step-backward icon icon-size-1 pointer",     onClick:this.goPrevActivity})}),
        el(ToolTip,{title:"First Activity",content:  el('i',{className:"fal  fa-backward icon icon-size-1 mr-4 ml-4 pointer",style:{fontSize:21},onClick:this.goFirstActivity})}),
        el('select',{value:currentActivity,onChange:this.handleActivityChange},data?.activities.map(a=>
            el('option',{key:"cl"+a.callActivityID,value:a.callActivityID,
            style:{fontSize:10}
        },a.callActivityID+' - '+a.dateEngineer+' - '+a.contactName+(a.activityType?(' - '+a.activityType):'')))
        ),        
        el(ToolTip,{title:"Last Activity",content: el('i',{className:"fal  fa-forward icon icon-size-1 mr-4 ml-4 pointer",style:{fontSize:21}, onClick:this.goLastActivity})}),        
        el(ToolTip,{title:"Prev Activity",content: el('i',{className:"fal  fa-step-forward icon icon-size-1 pointer",      onClick:this.goNextActivity})}),
        this.getCurrentActivityIndxElement(data,currentActivity)
        ),        
        el('div',{style:{display:"flex",flexDirection:"row",alignItems: "center", justifyContent: "center"}},  
        this.getToggle("Critical SR",'criticalSR'),
        this.getToggle("Monitor SR",'monitorSR'),
        this.getToggle("Show Travel","showTravel"),
        this.getToggle("Show Operational Tasks","showOperationalTasks"),
        this.getToggle("Show ServerGuard Updates","showServerGaurdUpdates"),
        el('label',{className:"ml-5"},'Activity hours: '),
        el('label',null,data?.totalActivityDurationHours),
        el('label',{className:"ml-5"},'Chargeable hours: '),
        el('label',null,data?.chargeableActivityDurationHours)),
        this.getOnsiteActivities(data?.onSiteActivities)
        );
    }
    getHiddenSRElement=(data)=>{
        const {el}=this;
        if(data?.problemHideFromCustomerFlag==='Y')
        return this.el('div',{style:{display:"flex",justifyContent: "center",alignItems: "center"}},
        el('h1',{style:{color:"red"}},"Hidden From Customer")
        )
    }
    getElement=(label,text,bgcolor)=>{
        const {el}=this;
        return el('div',{style:{flexBasis:320,marginTop:3,backgroundcolor:bgcolor}},
        label?el('label',{style:{display: "inline-block",width:80,textAlign: "right",color: "#992211",whiteSpace: "nowrap"}},label):null,
        el('label',{style:{textAlign: "left",whiteSpace: "nowrap",marginLeft:5}},text),
        )
    }
    getDetialsElement=(data)=>{
        const {el}=this;
        return el('div',null,
        el('label',{style:{display:"block",color: "#992211",marginTop:10,marginBottom:5}},'Details'),
        el('div',{dangerouslySetInnerHTML:{ __html: data?.reason }})
        );
    }
    getNotesElement=(data)=>{
        const {el}=this;
        return el('div',null,
        el('label',{style:{display:"block",color: "#992211",marginTop:10,marginBottom:5}},'Internal Notes'),
        el('div',{dangerouslySetInnerHTML:{ __html: data?.internalNotes }})
        );
    }
    deleteDocument=async(id)=>{
        console.log(id);
        if(confirm('Are you sure you want to remove this document?'))
        {
            await this.api.deleteDocument(this.state.currentActivity,id);
            const {data}=this.state;
            data.documents=data.documents.filter(d=>d.id!=id);
            this.setState({data});
        }
    }
    getContentElement=()=>{
        const {data}=this.state;
        const {el}=this;
        return el('div',{className:"activities-contianer"},
        this.getHiddenSRElement(data),
        el('div',{className:"activity-detials-flex"},
        // this.getElement("ID",data?.problemID+'_'+data?.callActivityID),
        this.getElement(" ",data?.priority),        
        this.getElement(" ",data?.problemStatusDetials+this.getAwaitingTitle(data)),
        this.getElement("Type",data?.activityType),        
        this.getElement(" ","Authorised by "+data?.authorisedBy),        
        this.getElement("User",data?.engineerName),        
        this.getElement("Contract",data?.contractType),     
        // this.getElement("",""),    
                
        // this.getElement(" ",data?.serverGuardDetials),        
        // this.getElement("",""),    
        this.getElement("Date",data?.date),        
        // this.getElement("Project",data?.projectDescription),        
        // this.getElement("",""),    
        this.getElement("Start Time",data?.startTime),        
        // this.getElement("",""),
        // this.getElement("",""),
        this.getElement("End Time",data?.endTime), 
        this.getElement("Value",data?.curValue),       
        this.getElement("Root Cause",data?.rootCauseDescription),      
        // this.getElement("",""),  
        this.getElement("Complete Date",data?.completeDate),    
        this.getElement("",data?.currentUser,data?.currentUserBgColor), 
        this.getElement("",""),  
        this.getElement("",""),    
        this.getDetialsElement(data),
        this.getNotesElement(data),
        )
        )
    }
    getAwaitingTitle=(data)=>{
        //if(data?.awaitingCustomerResponseFlag==='Y')
        //{
            if(data?.problemStatus==='I'||data?.problemStatus==='P')
            return " - \"Awaiting CNC\" or \"On Hold\"";
            else if(data?.problemStatus==='F'||data?.problemStatus==='C')
            return "";
            else 
            return "";
        //}
        //else return "";
        
    }
    getDocumentsElement=()=>{
        const {data,uploadFiles}=this.state;
        const {el}=this;
        let columns = [
            {
                path: "Description",
                label: "Description",
                sortable: false,  
                content:(document)=>el('a',{href:`Activity.php?action=viewFile&callDocumentID=${document.id}`},document.description)  
            },
            {
                path: "File",
                label: "File",
                sortable: false,  
                content:(document)=>el('a',{href:`Activity.php?action=viewFile&callDocumentID=${document.id}`},document.filename)  
            },
            {
                path: "createDate",
                label: "Date",
                sortable: false,                  
            },
            {
                path: "delete",
                label: "",
                sortable: false,                  
                content:(document)=>el('i',{className:"fal fa-trash-alt pointer icon icon-size-1",onClick:()=>this.deleteDocument(document.id)})  
            },
        ]       
        return el('div',{className:"activities-contianer"},
        el('label',{style:{display:"block"}},"Documents"),
        data?.documents.length>0? el(Table, {
            key: "documents",
            data: data?.documents || [],
            columns: columns,
            pk: "id",
            search: false,
          }):null,
          el(ToolTip,{title:"Add document",content: el('i',{className:"fal fa-plus pointer icon icon-size-1",onClick:this.handleSelectFiles})}),                    
          el('input',{ref:this.fileUploader,name:'usefile', type:"file",style:{display:"none"},multiple:"multiple",onChange:this.handleFileSelected}),          
          this.getSelectedFilesElement(),
          uploadFiles.length>0?el('i',{className:"fal fa-upload pointer icon icon-size-1",onClick:this.handleUpload}):null,


        );
    }
    getSelectedFilesElement=()=>{
        const {uploadFiles}=this.state;
        if(uploadFiles)
        {
            let names="";
            console.log(uploadFiles);        
            for(let i=0;i<uploadFiles.length;i++){        
                names +=uploadFiles[i].name+"  ,";
            }
            names=names.substr(0,names.length-2)
            return this.el('label',{className:"ml-5"},names)
        }
        return null;
    }
    handleUpload=async()=>{
        const {uploadFiles,data,currentActivity}=this.state;                 
        await this.api.uploadFiles(`Activity.php?action=uploadFile&problemID=${data.problemID}&callActivityID=${data.callActivityID}`
        ,uploadFiles,"userfile[]");
        this.loadCallActivity(currentActivity);
    }
    handleFileSelected=(e)=>{
        const uploadFiles=[...e.target.files];
        this.setState({uploadFiles})
    }
    handleSelectFiles=()=>{
        this.fileUploader.current.click();        
    }   
    getExpensesElement=()=>{
        const {data,currentUser}=this.state;
        const {el}=this;        
        const totalExpenses=data?.expenses.map(e=>e.value).reduce((p,c)=>p+c,0);
        if(currentUser.isExpenseApprover||currentUser.globalExpenseApprover)
        {
        let columns = [
            {
                path: "expenseType",
                label: "Expense",
                sortable: false,  
                footerContent:(c)=>el('label',null,'Total')
             },
            {
                path: "mileage",
                label: "Miles",
                sortable: false,  
             },
            {
                path: "value",
                label: "Amount",
                sortable: false,  
                footerContent:(c)=>el('label',null,totalExpenses)                
            },
            {
                path: "vatFlag",
                label: "VAT included",
                sortable: false,                                  
            },
        ]       
        return el('div',{className:"activities-contianer"},
        el('label',{style:{display:"block"}},"Expenses"),
        el(Table, {
            key: "expenses",
            data: data?.expenses || [],
            columns: columns,
            pk: "id",
            search: false,
            hasFooter:true
          })
        );
        }
        else return null;
    }   
    // Parts used, change requestm and sales request
    handleTemplateChanged=(event)=>{
        console.log(event.target.value);
        const id=event.target.value;
        const {templateOptions}=this.state;
        let templateDefault='';
        let templateOptionId=null;
        let templateValue='';
        if(id>=0)
        {
            const op=templateOptions.filter(s=>s.id==id)[0];            
            templateDefault=op.template;
            templateValue=op.template;
            templateOptionId=op.id;
        }
        else 
        {
            templateDefault='';
        }
        this.setState({templateDefault,templateOptionId,templateValue});
    }
    handleTemplateValueChange=(value)=>{
        this.setState({templateValue:value})
    }
    handleTemplateSend=async(type)=>{
        const {templateValue,templateOptionId,data,currentActivity}=this.state;
        if(templateValue=='')
        {
            alert('Please enter detials');
            return ;
        }
        const payload=new FormData();
        payload.append("message",templateValue);
        payload.append("type",templateOptionId);
        switch (type) {
          case "changeRequest":
            await this.api.sendChangeRequest(data.problemID, payload);
            break;
          case "partsUsed":
            var object = {
              message: templateValue,
              callActivityID: currentActivity,
            };
            const result = await this.api.sendPartsUsed(object);
            break;
          case "salesRequest":
            await this.api.sendSalesRequest(
              data.customerId,
              data.problemID,
              payload
            );
            break;
        }
        this.loadCallActivity(currentActivity);
        this.setState({_showModal:false})
    }
    getTemplateModal=()=>{
        const {templateDefault,templateOptions,_showModal,templateTitle,templateType}=this.state;
        const {el}=this;
        return el(Modal,{width:900,key:templateType,onClose:()=>this.setState({_showModal:false}),
            title:templateTitle,show:_showModal,
            content:el('div',{key:'conatiner'},
            templateOptions.length>0?el('select',{onChange:this.handleTemplateChanged},el('option',{key:'empty',value:-1},"-- Pick an option --"),templateOptions.map(s=>el('option',{key:s.id,value:s.id},s.name))):null,
                el(CKEditor,{key:'salesRequestEditor',id:'salesRequest',value:templateDefault
                ,onChange:this.handleTemplateValueChange})
            ),
            footer:el('div',{key:"footer"},
            el('button',{onClick:()=>this.setState({_showModal:false})},"Cancel"),
            el('button',{onClick:()=>this.handleTemplateSend(templateType)},"Send"))
            }
        )
    }
    handleTemplateDisplay= async(type)=>{
        let options=[];
        let templateTitle='';
        switch (type) {
          case "salesRequest":
            options = await this.api.getSalesRequestOptions();
            templateTitle = "Sales Request";
            break;
          case "changeRequest":
            options = await this.api.getChangeRequestOptions();
            templateTitle = "Change Request";
            break;
          case "partsUsed":
            templateTitle = "Parts Used";
            break;
        }        
        this.setState({templateOptions:options,_showModal:true,templateType:type,templateTitle})        
    }
    //-------------end template
    getFooter=()=>{
        const {el}=this;
        return el('div',null,
        el('button',{className:"m-5",onClick:()=>this.handleTemplateDisplay("partsUsed")},"Parts Used"),
        el('button',{className:"m-5",onClick:()=>this.handleTemplateDisplay("salesRequest")},"Sales Request"),
        el('button',{className:"m-5",onClick:()=>this.handleTemplateDisplay("changeRequest")},"Change Request"),

        )
    }
    render() { 
        const {el}=this;
        return el('div',{},
        this.getProjectsElement(),
        this.getHeader(),
        this.getActions(),
        this.getActivitiesElement(),
        this.getContentElement(),
        this.getDocumentsElement(),
        this.getExpensesElement(),
        this.getTemplateModal(),
        this.getFooter()
        );
    }
}
 
export default CMPActivityDisplay;