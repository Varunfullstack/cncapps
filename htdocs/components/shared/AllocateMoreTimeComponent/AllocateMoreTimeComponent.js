import React from 'react';
import CurrentActivityService from '../../CurrentActivityReportComponent/services/CurrentActivityService';
import CNCCKEditor from '../CNCCKEditor';
import MainComponent from '../MainComponent';
import Modal from '../Modal/modal';

class AllocateMoreTimeComponent extends MainComponent {
    el = React.createElement;
    api = new CurrentActivityService();
    constructor(props){
        super(props);
        this.state={
            ...this.state,
            data:{
                allocatedTimeValue:'',
                allocatedTimeAmount:"minutes",
                status:'Approve',
                comments:"",
                 
            }
        }
    }
 
    getTimeRequestModal = () => {  
        const {data}      =this.state;
        //const isAllowed = (data.allocatedTimeValue * (data.allocatedTimeAmount === 'minutes' ? 1 : 60) + currentTimeRequest.timeSpentSoFar) < currentTimeRequest.teamManagementApprovalMinutes;
        return <Modal
            key="processRequestTime"
            show={this.props.show}
            width="700px"
            title="Allocate more time"
            onClose={this.handleCancel}
            footer={
                <div key="divFooter">                   
                    <button onClick={this.handleSave} >Save</button>
                    <button onClick={this.handleCancel}>Cancel</button>
                </div>
            }
            >
            <div key="divBody">
                <table>
                    <tbody>
                    <tr>
                        <td>Granted Minutes</td>
                        <td>
                            <input autoFocus={true}
                                   style={{marginLeft: 0}}
                                   type="number"
                                   onChange={($event) => this.setValue('allocatedTimeValue', parseInt($event.target.value))}
                                   value={data.allocatedTimeValue}
                            />
                            <select onChange={($event) => this.setValue('allocatedTimeAmount', $event.target.value)}
                                    value={data.allocatedTimeAmount}
                            >
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                            </select>
                        </td>
                    </tr>                    
                    <tr style={{verticalAlign: "top"}}>
                        <td>Comments</td>
                        <td>
                            <div id="top2"/>
                            <CNCCKEditor
                                onChange={(receivedData) => this.setValue('comments', receivedData)}
                                style={{width: 600, height: 200}}
                                type="inline"
                                sharedSpaces={true}
                                top="top2"
                                bottom="bottom2"
                            >
                            </CNCCKEditor>
                            <div id="bottom2"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </Modal>
    }
    handleSave=()=>{
        console.log('save',this.state.data);
        const {data} = this.state;
        if (!data.allocatedTimeValue) {
            this.alert("Please enter Granted Time");
            return;
        }
        if (!parseInt(data.allocatedTimeValue)) {
            this.alert("Please enter a valid time value");
            return;
        }
        data.status = "Approve";
        data.problemID=this.props.problem.problemID;
        data.queueID=this.props.problem.queueTeamId;
        console.log(this.props.problem);
       // return;
        this.api.allocateAdditionalTime(data).then(result => {
            if (result.status) {
                this.handleCancel();                 
            }
        });
    }
    handleCancel=()=>{
        this.setState({data:{
            allocatedTimeValue:'',
            allocatedTimeAmount:"minutes",
            status:'Approve',
            comments:"",             
        }})
        if(this.props.onClose)
            this.props.onClose();
    }
    render() {
        return <div>
            {this.getAlert()}
            {this.getTimeRequestModal()}
        </div>
    }
}

export default AllocateMoreTimeComponent;