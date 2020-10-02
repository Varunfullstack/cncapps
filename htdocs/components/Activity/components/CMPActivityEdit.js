import SVGActivity from "../SVGActivity.js";
import {params, pick} from "../../utils/utils.js";
import Toggle from "../../utils/toggle.js";
import Table from "../../utils/table/table.js"
import Modal from "../../utils/modal.js";
import CKEditor from "../../utils/CKEditor.js";
import Timer from "../../utils/timer.js";
import ToolTip from "../../utils/ToolTip.js";
class CMPActivityEdit extends React.Component {
  el = React.createElement;
  api = new SVGActivity();
  activityStatus = {
    Fixed: "Fixed",
    CustomerAction: "CustomerAction",
    CncAction: "CncAction",
    Escalate: "Escalate",
  };
  constructor(props) {
    super(props);
    this.state = {
        _activityLoaded:false,
       uploadFiles: [],
      contacts: [],
      sites: [],
      priorities: [],
      data: {
        curValue: "",
        documents: [],
        reasonTemplate:'',
        reason:'',
        internalNotes:'',
        internalNotesTemplate:'',
        date: "",
        alarmDate:'',
        alarmTime:'',
        contactNotes:'',
        completeDate:'',
        techNotes:'',
        projects:[]
      },
      currentActivity: "",
      _showModal: false,
      templateOptions: [],
      templateOptionId: null,
      templateDefault: "",
      templateValue: "",
      templateType: "",
      templateTitle: "",
      contactNotes: "",
      callActTypes: [],
      users: [],
      contracts: [],
      filters: {
        showTravel: false,
        showOperationalTasks: false,
        showServerGaurdUpdates: false,
        criticalSR: false,
        monitorSR: false,
      },
    };
    this.fileUploader = new React.createRef();
  }
  componentDidMount() {
    this.loadCallActivity(params.get("callActivityID"));
    // lodaing lookups
    Promise.all([
      this.api.getCallActTypes(),
      this.api.getAllUsers(),
      this.api.getPriorities(),
      this.api.getRootCauses(),
    ]).then((result) => {
      this.setState({
        callActTypes: result[0],
        users: result[1],
        priorities: result[2],
        rootCauses: result[3],
      });
    });
  }
  //------------API
  loadCallActivity(callActivityID) {
    const { filters } = this.state;
    this.api.getCallActivityDetails(callActivityID, filters).then((res) => {
      console.log(res);
      const { filters } = this.state;
      filters.monitorSR = res.monitoringFlag == "1" ? true : false;
      filters.criticalSR = res.criticalFlag == "1" ? true : false;
      //res.date=moment(res.date).format("YYYY-MM-DD");
      res.documents=res.documents.map(d=>{
        d.createDate=moment(d.createDate).format("DD/MM/YYYY");
        return d;
      });
      res.reasonTemplate=res.reason;
      res.internalNotesTemplate=res.internalNotes;
      res.callActTypeIDOld=res.callActTypeID;
      Promise.all([
        this.api.getCustomerContacts(res.customerId, res.contactID),
        this.api.getCustomerSites(res.customerId),
        this.api
          .getCustomerContracts(
            res.customerId,
            res.contractCustomerItemId,
            res.linkedSalesOrderID > 0
          )
          .then((res) => {
            const contracts = res.reduce(function (prev, current) {
              // get group index and group by renewalType
              const index = prev
                ? prev.findIndex((g) => g.groupName === current.renewalType)
                : -1;
              if ((prev && prev.length == 0) || index === -1) {
                const obj = {
                  groupName: current.renewalType,
                  items: [current],
                };
                prev.push(obj);
              } else if (index >= 0) {
                prev[index].items.push(current);
              }
              return prev;
            }, []);
            return contracts;
          }),
      ]).then((result) => {
        // console.log('res',result);
        this.setState({
          filters,
          data: res,
          currentActivity: res.callActivityID,
          contacts: result[0],
          sites: result[1],
          contracts: result[2],
          _activityLoaded:true
        });
      });
    });
  }
  updateActivity = async () => {
    const data = {...this.state.data};
    
    data.reason=data.reasonTemplate;
    data.internalNotes=data.internalNotesTemplate;
    data.priority=this.state.priorities.filter(p=>p.name===data.priority)[0].id;
    if(this.isValid(data))
    {
      delete data.activities;
      delete data.onSiteActivities;
      delete data.documents;
      const finalData=pick(data,["callActivityID",
        "alarmDate",
        "alarmTime",
        "callActTypeID",
        "curValue",
        "contactID",
        "date",
        "siteNo",
        "startTime",
        "endTime",
        "userID",
        "contactNotes",
        "techNotes",
        "reason",
        "internalNotes",
        "nextStatus",
        "escalationReason",
      ]);
      console.log(finalData);
      this.api.updateActivity(finalData).then(response=>{
        if (response.error) alert(response.error);
        else if (response.redirectTo) document.location = response.redirectTo;
        else
          document.location = `ActivityNew.php?action=displayActivity&callActivityID=${data.callActivityID}`;
          

      }).catch(ex=>{
        alert(ex.error);

      });
      
      
    }
  };
  isValid=(data)=>{
    console.log(data);
    if(data.callActTypeID=='')
    {
      alert("Please select Activity Type");
      return false;
    }
    if(data.siteNo=='-1')
    {
      alert("Please select Customer Site");
      return false;
    }
    if(data.contactID=='')
    {
      alert("Please select Contact");
      return false;
    }
     
    const callActType=this.state.callActTypes.filter(c=>c.id==data.callActTypeID)[0];    
    data.callActType=callActType;
    if(callActType.description.indexOf("FOC")==-1&& data.siteMaxTravelHours==-1)
    {
      alert('Travel hours need entering for this site');
      return false;
    }
    if(!data.contactSupportLevel)
    {
      alert('Not a nominated support contact');
      return false;
    }
    if(data.curValueFlag=='Y'&&data.curValue==0)
    {
      alert('Please enter value');
      return false;
    }
    else{
      if(callActType.reqReasonFlag=='Y'&&!data.reason.trim()){
        alert("Please Enter Reason");
        return false;
      }
      if(data.contractCustomerItemId&&data.projectId)
      {
        alert("Project work must be logged under T&M");
        return false;
      }
      if(data.callActTypeID!=51) //CONFIG_INITIAL_ACTIVITY_TYPE_ID
      {
        const firstActivity=data.activities[0];
        const startDate =moment(data.date).format("YYYY-MM-DD")+" "+data.startTime;
        const firstActivityDate=moment(firstActivity.date).format("YYYY-MM-DD")+" "+firstActivity.startTime;
        if(moment(startDate)<moment(firstActivityDate))
        {
          alert("Date/time must be after Initial activity");
          return false;
        }
      }
      if(data.endTime)
      {        
        const duration = moment.duration(moment(data.date+" "+data.endTime).diff(moment(data.date+" "+data.startTime)));        
        const durationHours=duration.asHours();
        const durationMinutes=duration.asMinutes();

        if(data.endTime<data.startTime)
        {
          alert('End time must be after start time!');
          return false;
        }
        if([4, 8, 11, 18].indexOf(data.callActTypeID)>-1)
        {
          if(data.actUserTeamId<=4)
          {
            let usedTime = 0;
            let allocatedTime = 0;
            if(data.actUserTeamId==1)
            {

            }
          }
        }

      }
    }
    
    switch(data.nextStatus)
    {
      case this.activityStatus.CustomerAction:
        const dateMoment =moment(data.alarmDate);
        if (!dateMoment.isValid() || dateMoment.isSameOrBefore(moment(), 'minute')
        ) {
            alert('Please provide a future date and time, or just a future date');
            return false;
        }
        break;
    }
    if(data.nextStatus===this.activityStatus.Escalate)
    {
      if(["I", "F", "C"].indexOf(data.problemStatus)===-1 && !data.escalationReason)
      {
        alert("Please provide an escalate reason");
        return false;
      }
    }
    return true;
  }
  setValue = (label, value) => {
    const { data } = this.state;
    data[label] = value;
    this.setState({ data });
  };
  //-----------------Template
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
  getHeader = () => {
    const { el } = this;
    const { data } = this.state;
    return el(
      "div",
      null,
      el(
        "a",
        {
          className: data?.customerNameDisplayClass,
          href: `Customer.php?action=dispEdit&customerId=${data?.customerId}`,
          target: "_blank",
        },
        data?.customerName +
          ", " +
          data?.siteAdd1 +
          ", " +
          data?.siteAdd2 +
          ", " +
          data?.siteAdd3 +
          ", " +
          data?.siteTown +
          ", " +
          data?.sitePostcode +
          ", " +
          data?.contactName +
          "  "
      ),
      el("a", { href: `tel:${data?.sitePhone}` }, data?.sitePhone),
      el("label", null, " DDI: "),
      el("a", { href: `tel:${data?.contactPhone}` }, data?.contactPhone),
      el("label", null, " Mobile: "),
      el(
        "a",
        { href: `tel:${data?.contactMobilePhone}` },
        data?.contactMobilePhone
      ),
      el(
        "a",
        {
          href: `mailto:${data?.contactEmail}?subject=Service Request ${data?.problemID}`,
        },
        el("i", { className: "fal fa-envelope ml-5" })
      ),
      el("p", { className: "formErrorMessage mt-2" }, data?.contactNotes),
      el("p", { className: "formErrorMessage mt-2" }, data?.techNotes)
    );
  };

  getActions = () => {
    const { el } = this;
    const { data } = this.state;
    return el(
      "div",
      {
        style: {
          display: "flex",
          flexDirection: "row",
          justifyContent: "center",
          alignItems: "center",
          width: 930,
        },
      },
      el(ToolTip,{title:"Renewal Information",content:  el("a", {
        className: "fal fa-tasks fa-2x m-5 pointer icon",        
        href: `RenewalReport.php?action=produceReport&customerID=${data?.customerId}`,
        target: "_blank",
      })}),
      el(ToolTip,{title:"Passwords",content: el("a", {
        className: "fal fa-unlock-alt fa-2x m-5 pointer icon",
        href: `Password.php?action=list&customerID=${data?.customerId}`,
        target: "_blank",
      })}),
      // el(ToolTip,{title:"Generate Password",content:  el("a", {
      //   className: "fal fa-magic fa-2x m-5 pointer icon",
      //   onClick: this.handleGeneratPassword,
      // })}),
      el(ToolTip,{title:"History",content: el("a", {
        className: "fal fa-history fa-2x m-5 pointer icon",
        href: `Activity.php?action=problemHistoryPopup&problemID=${data?.problemID}&htmlFmt=popup`,
        target: "_blank",
      })}),
     
      data?.linkedSalesOrderID
        ? el(ToolTip,{title:"Sales Order",content: el("a", {
            className: "fal fa-tag fa-2x m-5 pointer icon",
            href: `SalesOrder.php?action=displaySalesOrder&ordheadID=${data?.linkedSalesOrderID}`,
          })})
        : null,
        data?.linkedSalesOrderID
        ? el(ToolTip,{title:"Unlink Sales Order",content: el("a", {
            className: "fal fa-unlink fa-2x m-5 pointer icon",
            onClick: () => this.handleUnlink(data?.linkedSalesOrderID),
          })})
        : null,
      !data?.linkedSalesOrderID
        ? el(ToolTip,{title:"Sales Order",content: el("a", {
            className: "fal fa-tag fa-2x m-5 pointer icon",
            onClick: () => this.handleSalesOrder(data?.callActivityID),
          })})
        : null,
        el(ToolTip,{title:"Contracts",content: el("a", {
        className: "fal fa-file-contract fa-2x m-5 pointer icon",
        href: `Activity.php?action=contractListPopup&customerID=${data?.customerId}`,
        target: "_blank",
      })}),
      el(ToolTip,{title:"Third Party Contacts",content: el("a", {
        className: "fal fa-users fa-2x m-5 pointer icon",
        href: `ThirdPartyContact.php?action=list&customerID=${data?.customerId}`,
        target: "_blank",
      })}),
      el(ToolTip,{title:"Contact SR History",content: el("a", {
        className: "fal fa-id-card fa-2x m-5 pointer icon",
        onClick: () => this.handleContactSRHistory(data?.contactID),
      })}),
      el(ToolTip,{title:"Request more time",content: el("a", {
        className: "fal fa-hourglass-start fa-2x m-5 pointer icon",        
        onClick: () => this.handleExtraTime(data),
      })})
    );
  };
  handleExtraTime = async (data) => {
    var reason = prompt(
      "Please provide your reason to request additional time"
    );
    if (!reason) {
      return;
    }
    const result = await this.api.activityRequestAdditionalTime(
      data.callActivityID,
      reason
    );
    console.log(result);
    alert("Additional time has been requested");
  };
  getActionsButtons = () => {
    const { el } = this;
    const { data } = this.state;
    return el(
      "div",
      {
        style: {
          display: "flex",
          flexDirection: "row",
          justifyContent: "center",
          alignItems: "center",
          width: 1100,
        },
      },
      data?.callActTypeID != 59
        ? el(
            "button",
            { onClick: () => this.setNextStatus(this.activityStatus.Fixed) },
            "Fixed"
          )
        : null,
      data?.callActTypeID != 59
        ? el(
            "button",
            {
              onClick: () =>
                this.setNextStatus(this.activityStatus.CustomerAction),
            },
            "Customer Action"
          )
        : null,
      data?.callActTypeID != 59
        ? el(
            "button",
            {
              onClick: () => this.setNextStatus(this.activityStatus.CncAction),
            },
            "CNC Action"
          )
        : null,
      el("label", { className: "m-2" }, "Future Action"),
      el("input", {
        type: "date",
        value:(data?.alarmDate||""),
        onChange: (event) => this.setValue("alarmDate", event.target.value),
      }),      
      el(Timer, {value:data?.alarmTime, onChange: (value) => this.setValue("alarmTime", value) }),      
      data?.callActTypeID != 59
        ? el(
            "button",
            { onClick: () => this.setNextStatus(this.activityStatus.Escalate) },
            "Escalate"
          )
        : null,
      data?.callActTypeID != 59
        ? el(
            "button",
            { onClick: () => this.setNextStatus("Update") },
            "Update"
          )
        : null,
      data?.callActTypeID != 59
        ? el("button", { onClick: () => this.handleCancel(data) }, "Cancel")
        : null,
      el(
        "button",
        { onClick: () => this.handleTemplateDisplay("partsUsed") },
        "Parts Used"
      ),
      el(
        "button",
        { onClick: () => this.handleTemplateDisplay("salesRequest") },
        "Sales Request"
      ),
      el(
        "button",
        { onClick: () => this.handleTemplateDisplay("changeRequest") },
        "Change Request"
      )
    );
  };
  handleCancel = (data) => {
    let text = "Are you sure you want to cancel?";
    if (data?.callActTypeID == 59) {
      text = "This will delete the Change Request activity, please confirm.";
    }

    if (confirm(text)) {
      document.location = `ActivityNew.php?action=displayActivity&callActivityID=${data.callActivityID}`;
    }
  };
  setNextStatus = (status) => {
    const { data } = this.state;
    data.nextStatus = status;
    switch (status) {
      case this.activityStatus.Fixed:
        if (!confirm("Are you sure this SR is fixed?")) return;
        break;
      case this.activityStatus.Escalate:
        if (data.problemStatus == "P") {
          const escalationReason = prompt(
            "Please provide your reason for escalating this SR(Required)"
          );
          if (!escalationReason) {
            return false;
          }
          data.escalationReason = escalationReason;
        }
        break;
    }
    this.setState({ data }, () => this.updateActivity());
  };

  handleGeneratPassword = () => {
    window.open(
      "Password.php?action=generate&htmlFmt=popup",
      "reason",
      "scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0"
    );
  };
  handleSalesOrder = (callActivityID) => {
    console.log("opened");
    const w = window.open(
      `Activity.php?action=editLinkedSalesOrder&htmlFmt=popup&callActivityID=${callActivityID}`,
      "reason",
      "scrollbars=yes,resizable=yes,height=150,width=250,copyhistory=no, menubar=0"
    );
    w.onbeforeunload = () => this.loadCallActivity();
  };
  handleUnlink = async (linkedSalesOrderID) => {
    const res = confirm(
      `Are you sure you want to unlink this request to Sales Order ${linkedSalesOrderID}`
    );
    if (res) {
      await this.api.unlinkSalesOrder(linkedSalesOrderID);
      this.loadCallActivity();
    }
  };
  handleContactSRHistory(contactID) {
    const w = window.open(
      `Activity.php?action=displayServiceRequestForContactPopup&contactID=${contactID}&htmlFmt=popup`,
      "reason",
      "scrollbars=yes,resizable=yes,height=400,width=1225,copyhistory=no, menubar=0"
    );
    //w.onbeforeunload =()=>this.loadCallActivity();
  }

  getElement = (key, label, text, bgcolor) => {
    const { el } = this;
    return [
      el(
        "td",
        {
          key,
          style: { marginTop: 3, backgroundcolor: bgcolor, textAlign: "right" },
        },
        label
          ? el(
              "label",
              { style: { width: 80, color: "#992211", whiteSpace: "nowrap" } },
              label
            )
          : null
      ),
      el(
        "td",
        {
          key: key + 2,
          style: { marginTop: 3, backgroundcolor: bgcolor, textAlign: "left" },
        },
        el(
          "label",
          { style: { textAlign: "left", whiteSpace: "nowrap", marginLeft: 5 } },
          text
        )
      ),
    ];
  };
  getElementControl = (key, label, content, bgcolor) => {
    const { el } = this;
    return [
      el(
        "td",
        {
          key,
          style: { marginTop: 3, backgroundcolor: bgcolor, textAlign: "right" },
        },
        label
          ? el(
              "label",
              { style: { width: 80, color: "#992211", whiteSpace: "nowrap" } },
              label
            )
          : null
      ),
      el(
        "td",
        {
          key: key + 2,
          style: { marginTop: 3, backgroundcolor: bgcolor, textAlign: "left" },
        },
        content
      ),
    ];
  };
  getDetialsElement = (data) => {
    const { el } = this;
    return el(
      "div",
      null,
      el(
        "label",
        {
          style: {
            display: "block",
            color: "#992211",
            marginTop: 10,
            marginBottom: 5,
          },
        },
        "Details"
      ),
      el("div", { dangerouslySetInnerHTML: { __html: data?.reason } })
    );
  };
   
  deleteDocument = async (id) => {
    console.log(id);
    if (confirm("Are you sure you want to remove this document?")) {
      await this.api.deleteDocument(this.state.currentActivity, id);
      const { data } = this.state;
      data.documents = data.documents.filter((d) => d.id != id);
      this.setState({ data });
    }
  };
  getTypeElement = () => {
    const { el } = this;
    const { data, callActTypes } = this.state;
    const found = callActTypes.filter((t) => t.id == data.callActTypeIDOld);
    return this.getElementControl(
      "Type",
      "Type",
      el(
        "select",
        {
          disabled: data?.isInitalDisabled || found.length == 0,
          required: true,
          value: data?.callActTypeID,
          onChange: (event) =>
            this.setValue("callActTypeID", event.target.value),
        },
        el("option", { key: "empty", value: '' }, "Please select"),
        callActTypes?.map((t) =>
          el("option", { key: t.id, value: t.id }, t.description)
        )
      )
    );
  };
  getContactsElement = () => {
    const { el } = this;
    const { data, contacts } = this.state;
    return el(
      "select",
      {
        key: "contacts",
        required: true,
        value: data?.contactID,
        onChange: (event) => this.setValue("contactID", event.target.value),
      },
      el("option", { key: "empty", value: '' }, "Please select"),
      contacts?.map((t) =>
        el(
          "option",
          { key: t.id, value: t.id },
          (t.startMainContactStyle || "") +
            t.contactName +
            (t.endMainContactStyle || "")
        )
      )
    );
  };
  getContactPhone = () => {
    const { data } = this.state;
    const { el } = this;
    let elements = [];
    if (data?.sitePhone)
      elements.push(
        el(
          "a",
          { key: "sitePhone", href: `tel:${data.sitePhone}` },
          data.sitePhone
        )
      );
    if (data?.contactPhone) {
      elements.push(el("label", { key: "contactMobilePhonelabel" }, " DDI: "));
      elements.push(
        el(
          "a",
          { key: "contactMobilePhone", href: `tel:${data.contactPhone}` },
          data.contactPhone
        )
      );
    }
    if (data?.contactMobilePhone) {
      elements.push(
        el("label", { key: "contactMobilePhonelabel" }, " Mobile: ")
      );
      elements.push(
        el(
          "a",
          { key: "contactMobilePhone", href: `tel:${data.contactMobilePhone}` },
          data.contactMobilePhone
        )
      );
    }
    if (data?.contactEmail) {
      const subject = `Service Request ${data.problemID}`;
      elements.push(
        el(
          "a",
          {
            key: "contactEmail",
            href: `mailto:${data.contactEmail}?subject=${subject}`,
          },
          el("i", {
            key: "contactEmailicon",
            className: "fal fa-envelope icon ml-2",
          })
        )
      );
    }
    if (!data?.contactSupportLevel) {
      elements.push(
        el(
          "span",
          { key: "contactSupportLevel", className: "ml-2" },
          "Not a nominated support contact"
        )
      );
    }
    return elements;
  };
  getSites = () => {
    const { el } = this;
    const { data, sites } = this.state;
    return el(
      "select",
      {
        key: "sites",
        required: true,
        value: data?.siteNo,
        onChange: (event) => this.setValue("siteNo", event.target.value),
      },
      el("option", { key: "empty", value: '-1' }, "Please select"),
      sites?.map((t) => el("option", { key: t.id, value: t.id }, t.name))
    );
  };
  getTimeElement = () => {
    const { data } = this.state;
    const { el } = this;
    return el(
      "div",
      {
        style: {
          display: "flex",
          flexDirection: "row",
          justifyContent: "flex-start",
          alignItems: "center",
        },
      },
      el(Timer, {
        key: "startTime",
        disabled: data?.isInitalDisabled,
        value: data?.startTime,
        onChange: (value) => this.setValue("startTime", value),
      }),
      el("label", { className: "m-2" }, "to"),
      el(Timer, {
        key: "endTime",
        disabled: data?.isInitalDisabled,
        value: data?.endTime,
        onChange: (value) => this.setValue("endTime", value),
      })
    );
  };
  getPriority = () => {
    const { el } = this;
    const { data, priorities } = this.state;
    return el(
      "select",
      {
        key: "priorities",
        disabled: !data.canChangePriorityFlag,
        required: true,
        value: data?.priority,
        onChange: (event) => this.setValue("priority", event.target.value),
      },
      el("option", { key: "empty", value: null }, "Please select"),
      priorities?.map((t) => el("option", { key: t.id, value: t.name }, t.name))
    );
  };
  getUsersElement = () => {
    const { el } = this;
    const { data, users } = this.state;
    return el(
      "select",
      {
        key: "users",
        required: true,
        value: data?.userID,
        onChange: (event) => this.setValue("userID", event.target.value),
      },
      el("option", { key: "empty", value: null }, "Please select"),
      users?.map((t) => el("option", { key: t.id, value: t.id }, t.name))
    );
  };
  getContracts = () => {
    const { el } = this;
    const { data, contracts } = this.state;

    return el(
      "select",
      {
        key: "contracts",
        required: true,
        disabled: !data?.changeSRContractsFlag,
        value: data?.contractCustomerItemId || "",
        onChange: (event) => this.setValue("userID", event.target.value),
      },
      el("option", { key: "empty", value: 99 }, "Please select"),
      el("option", { key: "tandm", value: "" }, "T&M"),
      contracts?.map((t, index) =>
        el(
          "optgroup",
          { key: t.groupName, label: t.groupName },
          contracts[index].items.map((i) =>
            el(
              "option",
              {
                key: i.contractCustomerItemID,
                disabled: i.isDisabled,
                value: i.contractCustomerItemID,
              },
              i.contractDescription
            )
          )
        )
      )
    );
  };
  getRootCause = () => {
    const { el } = this;
    const { data, rootCauses } = this.state;
    return el(
      "select",
      {
        key: "rootCauses",
        disabled: !data.canChangePriorityFlag,
        style: { maxWidth: 200 },
        required: true,
        value: data?.rootCauseId,
        onChange: (event) => this.setValue("rootCauseId", event.target.value),
      },
      el("option", { key: "empty", value: null }, "Not known"),
      rootCauses?.map((t) =>
        el("option", { key: t.id, value: t.id }, t.description)
      )
    );
  };
  getContentElement = () => {
    const { data, callActTypes } = this.state;
    const { el } = this;

    return el(
      "table",
      { className: "activities-edit-contianer" },
      el(
        "tbody",
        {},
        el(
          "tr",
          null,
          this.getElement(
            "ID",
            "ID",
            data?.problemID + "_" + data?.callActivityID
          ),
          this.getElement("Authorisedby", "Authorised by ", data?.authorisedBy),
          this.getTypeElement()
        ),
        el(
          "tr",
          null,
          this.getElement("Customer", "Customer", data?.customerName),
          this.getElement("emp1"),
          this.getElementControl(
            "Value",
            "Value",
            el("input", {
              required: true,
              min: 0,
              type: "number",
              value: data?.curValue,
              onChange: (event) =>
                this.setValue("curValue", event.target.value),
            })
          )
        ),
        el(
          "tr",
          null,
          this.getElementControl("Contact", "Contact", [
            this.getContactsElement(),
            ...this.getContactPhone(),
          ]),
          this.getElementControl(
            "remaining",
            "",
            el(
              "h2",
              { style: { color: "red" } },
              `HD:${data?.hdRemainMinutes} ES:${data?.esRemainMinutes} SP:${data?.imRemainMinutes} P:${data?.projectRemainMinutes}`
            )
          ),
          this.getElementControl(
            "Date",
            "Date",
            el("input", {
              type: "date",
              disabled: data?.isInitalDisabled,
              value: data?.date,
              onChange: (event) => this.setValue("date", event.target.value),
            })
          )
        ),
        el(
          "tr",
          null,
          this.getElementControl("Site", "Site", this.getSites()),
          this.getElement("emp2"),
          this.getElementControl("Timefrom", "Time from", this.getTimeElement())
        ),
        el(
          "tr",
          null,
          this.getElementControl("Priority", "Priority", this.getPriority()),
          this.getElement("emp3"),
          this.getElementControl("User", "User", this.getUsersElement())
        ),
        el(
          "tr",
          null,
          this.getElementControl("Contract", "Contract", this.getContracts()),
          this.getElement("emp3"),
          this.getElementControl("RootCause", "Root Cause", this.getRootCause())
        ),
        el(
          "tr",
          null,
          this.getElementControl(
            "ContactNotes",
            "Contact Notes",
            el("input", {
              style: { width: "98%" },
              value: data?.contactNotes ,
              onChange: (event) =>
                this.setValue("contactNotes", event.target.value),
            })
          ),
          this.getElement("emp3"),
          this.getElementControl(
            "CompletedOn",
            "Completed On",
            el("input", {
              disabled: data?.problemStatus != "F",
              title: "Date when this request should be set to completed",
              type: "date",

              value: data?.completeDate,
              onChange: (event) =>
                this.setValue("completeDate", event.target.value),
            })
          )
        ),
        el(
          "tr",
          null,
          this.getElementControl(
            "CustomerNotes",
            "Customer Notes",
            el("input", {
              style: { width: "98%" },
              value: data?.techNotes || "",
              onChange: (event) =>
                this.setValue("techNotes", event.target.value),
            })
          ),
          this.getElement("emp3"),
          el(
            "td",
            { colSpan: 2 },
            el("label", { className: "label" }, "Hide From Customer"),
            el(Toggle, {
              disabled:
                data?.hideFromCustomerFlag == "Y" ||
                data?.problemHideFromCustomerFlag == "Y",
              value: data?.hideFromCustomerFlag == "Y" ? true : false,
              onChange: (value) =>
                this.setValue(
                  "hideFromCustomerFlag",
                  data?.hideFromCustomerFlag == "Y" ? "N" : "Y"
                ),
            }),

            el("label", { className: "label" }, "Submit as Overtime"),
            el(Toggle, {
              value: data?.submitAsOvertime,
              onChange: (value) =>
                this.setValue("submitAsOvertime", !data?.submitAsOvertime),
            })
          )
        )
      )
    );
  };
  getDocumentsElement = () => {
    const { data, uploadFiles } = this.state;
    const { el } = this;
    let columns = [
      {
        path: "Description",
        label: "Description",
        sortable: false,
        content: (document) =>
          el(
            "a",
            {
              href: `Activity.php?action=viewFile&callDocumentID=${document.id}`,
            },
            document.description
          ),
      },
      {
        path: "File",
        label: "File",
        sortable: false,
        content: (document) =>
          el(
            "a",
            {
              href: `Activity.php?action=viewFile&callDocumentID=${document.id}`,
            },
            document.filename
          ),
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
        content: (document) =>
          el("i", {
            className: "fal fa-trash-alt pointer icon icon-size-1",
            onClick: () => this.deleteDocument(document.id),
          }),
      },
    ];
    return el(
      "div",
      { className: "activities-edit-contianer" },
      el("label", { style: { display: "block" } }, "Documents"),
      data?.documents.length > 0
        ? el(Table, {
            key: "documents",
            data: data?.documents || [],
            columns: columns,
            pk: "id",
            search: false,
          })
        : null,
      el("i", {
        className: "fal fa-plus pointer icon icon-size-1",
        onClick: this.handleSelectFiles,
      }),
      el("input", {
        ref: this.fileUploader,
        name: "usefile",
        type: "file",
        style: { display: "none" },
        multiple: "multiple",
        onChange: this.handleFileSelected,
      }),
      this.getSelectedFilesElement(),
      uploadFiles.length > 0
        ? el("i", {
            className: "fal fa-upload pointer icon icon-size-1",
            onClick: this.handleUpload,
          })
        : null
    );
  };
  getSelectedFilesElement = () => {
    const { uploadFiles } = this.state;
    if (uploadFiles) {
      let names = "";
      //console.log(uploadFiles);
      for (let i = 0; i < uploadFiles.length; i++) {
        names += uploadFiles[i].name + "  ,";
      }
      names = names.substr(0, names.length - 2);
      return this.el("label", { className: "ml-5" }, names);
    }
    return null;
  };
  handleUpload = async () => {
    const { uploadFiles, data, currentActivity } = this.state;
    await this.api.uploadFiles(
      `Activity.php?action=uploadFile&problemID=${data.problemID}&callActivityID=${data.callActivityID}`,
      uploadFiles,
      "userfile[]"
    );
    this.loadCallActivity(currentActivity);
  };
  handleFileSelected = (e) => {
    const uploadFiles = [...e.target.files];
    this.setState({ uploadFiles });
  };
  handleSelectFiles = () => {
    this.fileUploader.current.click();
  };

  // Parts used, change requestm and sales request
  handleTemplateChanged = (event) => {
    //console.log(event.target.value);
    const id = event.target.value;
    const { templateOptions } = this.state;
    let templateDefault = "";
    let templateOptionId = null;
    let templateValue = "";
    if (id >= 0) {
      const op = templateOptions.filter((s) => s.id == id)[0];
      templateDefault = op.template;
      templateValue = op.template;
      templateOptionId = op.id;
    } else {
      templateDefault = "";
    }
    setTimeout(()=> this.setState({ templateDefault, templateOptionId, templateValue }),200)  
    
  };
  handleTemplateValueChange = (value) => {
    this.setState({ templateValue: value });
  };
  handleTemplateSend = async (type) => {
    const {
      templateValue,
      templateOptionId,
      data,
      currentActivity,
    } = this.state;
    if (templateValue == "") {
      alert("Please enter detials");
      return;
    }
    const payload = new FormData();
    payload.append("message", templateValue);
    payload.append("type", templateOptionId);
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
    this.setState({ _showModal: false });
  };
  getTemplateModal = () => {
    const {
      templateDefault,
      templateOptions,
      _showModal,
      templateTitle,
      templateType,
    } = this.state;
    const { el } = this;
    
    return el(Modal, {
      width: 900,
      key: templateType,
      onClose: () => this.setState({ _showModal: false }),
      title: templateTitle,
      show: _showModal,
      content: el(
        "div",
        { key: "conatiner" },
        templateOptions.length > 0
          ? el(
              "select",
              { onChange: this.handleTemplateChanged },
              el("option", { key: "empty", value: -1 }, "-- Pick an option --"),
              templateOptions.map((s) =>
                el("option", { key: s.id, value: s.id }, s.name)
              )
            )
          : null,
          this.state._activityLoaded? el(CKEditor, {
          key: "salesRequestEditor",
          id: "salesRequest",
          value: templateDefault,
          onChange: this.handleTemplateValueChange,
        }):null
      ),
      footer: el(
        "div",
        { key: "footer" },
        el(
          "button",
          { onClick: () => this.setState({ _showModal: false }) },
          "Cancel"
        ),
        el(
          "button",
          { onClick: () => this.handleTemplateSend(templateType) },
          "Send"
        )
      ),
    });
  };
  handleTemplateDisplay = async (type) => {
    let options = [];
    let templateTitle = "";
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
    this.setState({
      templateOptions: options,
      _showModal: true,
      templateType: type,
      templateTitle,
      templateDefault:''
    });
  };
  getActivityNotes()
  {
       const {el}=this;
      const {data}=this.state;
      return  el(
                "div",{ className: "activities-edit-contianer" },
                el("label",{className:"label m-5",style:{display:"block"}},"Activity Notes"),
                this.state._activityLoaded?el(CKEditor,{id:"reason",value:data?.reason,onChange:(value)=>this.setValue("reasonTemplate",value)}):null
                )
  }
  getActivityInternalNotes()
  {
       const {el}=this;
      const {data}=this.state;
      return  el(
                "div",{ className: "activities-edit-contianer" },
                el("label",{className:"label m-5",style:{display:"block"}},"Internal Notes"),
                this.state._activityLoaded?el(CKEditor,{id:"internal",value:data?.internalNotes,onChange:(value)=>this.setValue("internalNotesTemplate",value)}):null
                )   
  }
  render() {
    const { el } = this;
    return el(
      "div",null,
      this.getProjectsElement(),
      this.getHeader(),
      this.getActions(),
      this.getActionsButtons(),
      this.getContentElement(),
      this.getActivityNotes(),
      this.getActivityInternalNotes(),
      this.getDocumentsElement(),      
      this.getTemplateModal()
    );
  }
}
 
export default CMPActivityEdit;