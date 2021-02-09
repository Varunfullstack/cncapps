import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import {dateFormatExcludeNull, exportCSV, poundFormat} from "../../utils/utils";
import APIProjectOptions from "../services/APIProjectOptions";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import CheckBox from "../../shared/checkBox";

export class ProjectStagesComponent extends MainComponent {
    api=new APIProjectOptions();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            data:{
                id:'',
                name:'',
                displayInSR:false
            },
            items:[],
            showModal:false
        };
    }
    componentDidMount() {
        this.getData();
    }
    getData=()=>{
        this.api.getProjectStages().then(items=>{
            console.log(items);
            this.setState({items,showModal:false});
        })
    }
    getDataTableElement=()=>{
        const {items}=this.state;
        const columns=[
            {
               path: "id",
               label: "",
               hdToolTip: "ID",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-hashtag color-gray2 pointer",
               sortable: true,
               className: "text-center",               
            },
            {
                path: "name",
                label: "",
                hdToolTip: "Stage Name",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: true,
                                
             },
             {
                path: "displayInSR",
                label: "",
                hdToolTip: "Display In SR",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                className: "text-center",               
                content:(stage)=><a onClick={()=>{stage.displayInSR=!stage.displayInSR; this.setState({data:stage},()=>this.handleSave())}}>
                {stage.displayInSR?<i className="fal fa-check-square fa-2x icon pointer"></i>:<i className="fal fa-square fa-2x icon pointer"></i>}
                </a>
             },
             {
                path: "edit",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",       
                className: "text-center",
                content:(stage)=><ToolTip title="edit" >
                    <i className="fal fa-edit fa-2x pointer icon" onClick={()=>this.handleEdit(stage)}></i>
                </ToolTip>
                
             },
             {
                path: "delete",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-trash color-gray2 pointer",     
                className: "text-center",
                content:(stage)=><ToolTip title="edit" >
                <i className="fal fa-trash fa-2x pointer icon" onClick={()=>this.handleDelete(stage)}></i>
            </ToolTip>
             },
              
        ];
        return <Table    
        allowRowOrder={true}
        onOrderChange={this.handleOrderChange}    
        key="stages"
        pk="id"
        columns={columns}
        data={items||[]}
        search={true}
        >

        </Table>
    }
    handleOrderChange=async (current, next)=>{        
        const {items}=this.state;
        if(next)
        {
            current.stageOrder=next.stageOrder;
            next.stageOrder=current.stageOrder+0.001;
            await this.api.updateProjectStage(next.id,next);
        }
        if(!next)
        {        
            current.stageOrder=Math.max(...items.map(i=>i.stageOrder))+0.001;
        }        
        await this.api.updateProjectStage(current.id,current);
       
    }
    handleEdit=(stage)=>{
        console.log(stage);
        this.setState({data:stage,showModal:true});
    }
    handleDelete=async (stage)=>{
        const confirm=await this.confirm("Are you sure to delete it")
        console.log(confirm);
        if(confirm)
        this.api.deleteProjectStage(stage.id).then(result=>{
            this.getData();
        }).catch(ex=>{
            this.alert("Stage can't be delete");
        })
    }
    getModal=()=>{
        const {data,showModal}=this.state;
        if(!data)
            return null;
        return <Modal title="Project Stage Name" show={showModal} width={300}
            onClose={()=>this.setState({showModal:false})}
            content={
                <div key="content" >
                    <div className="form-group">
                        <label>Name</label>
                        <input required style={{width:200}} value={data.name} onChange={(event)=>this.setValue("name",event.target.value)} type="text"></input>                        
                    </div>
                    <div   className="form-group">
                        <label>Dislpay In SR</label>
                        <CheckBox                            
                            checked={data.displayInSR==1}
                            onChange={()=>this.setValue("displayInSR",!data.displayInSR)}>
                        </CheckBox>                       
                    </div>
                </div>
        }
        footer={<div key="footer">
            <button onClick={this.handleSave}>Save</button>
            <button   onClick={()=>this.setState({showModal:false})} >Cancel</button>
        </div>}
        >

        </Modal>
    }
    handleSave=()=>{
        const {data}=this.state;
        data.displayInSR=data.displayInSR?1:0;
        if(data.id!='')
        {
            this.api.updateProjectStage(data.id,data).then(result=>{
                console.log(result);
                this.getData();
            });
        }
        else //new 
        {
            this.api.addProjectStage(data).then(result=>{
                console.log(result);
                this.getData();
            });
        }
    }
    handleNew=()=>{
        this.setState({showModal:true,data:{id:'',name:''}});
    }
    render() {
        return <div style={{width:500}}>
            <ToolTip width={30} title="New Stage">
            <i className="fal fa-plus fa-2x m-5 pointer" onClick={this.handleNew}></i>            
            </ToolTip>
            {this.getAlert()}
            {this.getConfirm()}
            {this.getModal()}
            {this.getDataTableElement()}
        </div>
    }
}