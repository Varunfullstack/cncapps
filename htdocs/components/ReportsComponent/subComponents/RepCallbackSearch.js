import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import Table from "../../shared/table/table";
import {equal} from "../../utils/utils";
import CurrentActivityService from "../../CurrentActivityReportComponent/services/CurrentActivityService";
import ToolTip from "../../shared/ToolTip";

export default class RepCallbackSearch extends MainComponent {
    api = new CurrentActivityService();

    constructor(props) {
        super(props);
        this.state = {
            showSpinner: false,
            projects: [],
            params: {},

        };
    }

    componentDidMount() {
        // this.getData();
    }

    componentDidUpdate(prevProps, prevState) {
        if (!equal(prevProps, this.props))
            this.getData();
    }

    getData() {        
        this.setState({showModal: true});
        const {consID,customerID,dateFrom,dateTo,callbackStatus } = this.props;
        this.api.getCallbackSearch(consID,customerID,dateFrom,dateTo,callbackStatus)
            .then(callbacks => {
                this.setState({callbacks, showModal: false, loadData: false});
            });
    }

    getDataTable = () => {
        const { callbacks} = this.state;
        const columns=[          
            {
               path: "problemID",
               label: "",
               hdToolTip: "Service Request",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-hashtag color-gray2 pointer",
               sortable: true,
               className: "text-center",         
               content:(problem)=><a href={`SRActivity.php?action=displayActivity&serviceRequestId=${problem.problemID}`} target="_blank">{problem.problemID}</a>
            },
      
            {
              path: "customerName",
              label: "",
              hdToolTip: "Customer",
              hdClassName: "text-center",
              icon: "fal fa-2x fa-building color-gray2 pointer",
              sortable: true,
              //className: "text-center",         
            },
            {
              path: "contactName",
              label: "",
              hdToolTip: "Contact",
              hdClassName: "text-center",
              icon: "fal fa-2x fa-id-card-alt color-gray2 pointer",
              sortable: true,
              //className: "text-center",         
            },
            {
              path: "DESCRIPTION",
              label: "",
              hdToolTip: "Reason for call back",
              hdClassName: "text-center",
              icon: "fal fa-2x fa-file-alt color-gray2 pointer",
              sortable: true,
              className: "text-center",         
            },
            {
              path: "callback_datetime",
              label: "",
              hdToolTip: "Call back date time",
              hdClassName: "text-center",
              icon: "fal fa-2x fa-hourglass color-gray2 pointer",
              sortable: true,
              content:(problem)=><div>{this.getCorrectDate(problem.callback_datetime,true)}</div>,
              className: "text-center",         
            },     
            {
                path: "status",
                label: "",
                hdToolTip: "Status ",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-monitor-heart-rate color-gray2 pointer",
                sortable: true,
                content:(callback)=>
                <div className="flex-row" style={{justifyContent:"center"}}>              
                         {callback.status.toUpperCase()}
                </div>,
                className: "text-center",         
              },   
                 
            {
              path: "",
              label: "",
              hdToolTip: " ",
              hdClassName: "text-center",
               
              sortable: false,
              content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>
                  {problem.timeRemain<0?
              <ToolTip title="Call back time expired" width={30}>
                  <i className="fal fa-2x fa-alarm-exclamation color-gray2 pointer"></i>               
              </ToolTip>:null}            
              </div>,
              className: "text-center",         
            },
            {
                path: "consName",
                label: "",
                hdToolTip: "Call back by this person",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
                sortable: true,
                        
              },
        ];
  
        return <div >
             
            <Table      
        key="callback"
        data={callbacks||[]}
        pk="id"
        columns={columns}
        search={false}
        >
        </Table>
            </div>;
    }

    render() {

        return <div>
            <Spinner key="spinner"
                     show={this.state.showSpinner}
            ></Spinner>
            {this.getDataTable()
            }
        </div>
    }

}

  