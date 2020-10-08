import CheckBox from "../../utils/checkBox.js";
 import Toggle from "../../utils/toggle.js";
import APICustomers from "../../services/APICutsomer.js";
import Timer from "../../utils/timer.js";
import { groupBy, SRQueues, TeamType } from "../../utils/utils.js";
import APIStandardText from "../../services/APIStandardText.js";
import StandardTextModal from "../../Modals/StandardTextModal.js";

class CMPLastStep extends React.Component {
  el = React.createElement;

  apiCustomer = new APICustomers();
  apiStandardText = new APIStandardText();
  constructor(props) {
    super(props);
    const { data } = this.props;
    this.state = {
     
      checkList: [],
      noWorkOptions: [],
      contacts: [],
      _showModal: false,
      requireAuthorize: false,
      data: {
        uploadFiles: [],
        repeatProblem: data.repeatProblem || false,
        criticalSRFlag: data.criticalSRFlag || false,
        hideFromCustomerFlag: data.hideFromCustomerFlag || false,
        monitorSRFlag: data.monitorSRFlag || false,
        priority: data.priority || -1,
        internalNotesAppend: data.internalNotesAppend || "",
        contactID: data.contactID || -1,
        startTime: data.startTime || "",
        date: data.date || "",
        queueNo: data.queueNo || "",
        notStartWorkReason: data.notStartWorkReason || "",
        notStartWorkReasonTemplate: data.notStartWorkReasonTemplate || "",
        startWork: false,
        authorisedBy: "",
      },
    };
    this.fileUploader = new React.createRef();
  }
  componentDidMount = async () => {
    const result = await Promise.all([
      this.apiStandardText.getAllTypes(),
      this.apiCustomer.getCustomerContacts(this.props.data.customerID),
      this.apiStandardText.getOptionsByType(
        "Unable to offer First Time Fix reason"
      ),
    ]);
    console.log("result", result);
    const { data } = this.state;
    const userQueue = SRQueues.filter(
      (q) => q.teamID == this.props.data.currentUser.teamID
    );
    if (userQueue.length > 0 && data.queueNo == "")
      data.queueNo = userQueue[0].id;
    data.contactID = this.props.data.customer.con_contno;
    let requireAuthorize = this.checkContactNeedAuthorize(
      data.contactID,
      result[1]
    );
    this.setState({
      requireAuthorize,
      checkList: result[0],
      contacts: result[1],
      standardTextList: result[0],
      noWorkOptions: result[2],
      data,
    });
    this.checkStartWorkNow();
  };
  getChkProblemBefore = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Have they had this problem before?")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "exitistingProblem",
          checked: this.state.data.repeatProblem,
          onChange: () => handleCheckBoxChange("repeatProblem"),
        })
      )
    );
  };
  getcriticalSRFlagBefore = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Critical SR")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "criticalSRFlag",
          checked: this.state.data.criticalSRFlag,
          onChange: () => handleCheckBoxChange("criticalSRFlag"),
        })
      )
    );
  };
  gethideFromCustomerFlag = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Hide Entire SR From Customer")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "hideFromCustomerFlag",
          checked: this.state.data.hideFromCustomerFlag,
          onChange: () => handleCheckBoxChange("hideFromCustomerFlag"),
        })
      )
    );
  };
  getmonitorSRFlag = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Monitor SR")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "monitorSRFlag",
          checked: this.state.data.monitorSRFlag,
          onChange: () => handleCheckBoxChange("monitorSRFlag"),
        })
      )
    );
  };
  handleCheckBoxChange = (prop) => {
    const { data } = this.state;
    data[prop] = !data[prop];
    this.setState({ data });
  };
  setValue = (prop, value) => {
    const { data } = this.state;
    data[prop] = value;
    this.setState({ data });
  };
  handleNext = () => {
    const { data } = this.state;
    this.checkStartWorkNow();
    data.userID = this.props.data.currentUser.id;
    //data.callActTypeID=51;
    data.completeDate = null;
    if (this.isValid()) this.props.updateSRData(data, true);
    //this.props.setActiveStep(4)
  };
  getNextButton = () => {
    const { el } = this;

    return el(
      "div",
      {
        style: {
          display: "flex",
          flexDirection: "row",
          justifyContent: "center",
          alignItems: "center",
          width: "100%",
          marginTop: 20,
        },
      },
      null,
      el(
        "button",
        { onClick: this.handleNext, className: "float-right" },
        "Save"
      ),
      !this.state.data.startWork
        ? el(
            "button",
            { onClick: this.handleStartWork, className: "ml-5 float-right" },
            "Start Work"
          )
        : null
    );
  };
  handleStartWork = () => {
    const { data } = this.state;
    data.startWork = true;
    data.notStartWorkReason = "";
    data.notStartWorkReasonTemplate = "";
    this.state({ data });
    this.handleNext();
  };
  getProblemPriority = () => {
    const { el, setValue } = this;
    const { data } = this.state;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "How serious is this issue?")),
      el(
        "td",
        null,
        el(
          "select",
          {
            value: data.priority,
            onChange: (event) => setValue("priority", event.target.value),
            style: { width: 200 },
          },
          el("option", { value: -1 }, "Select Priority"),
          el("option", { value: 1 }, "It's affecting everybody (P1)"),
          el(
            "option",
            { value: 2 },
            "It's affecting more than just one person but they can work (P2)"
          ),
          el("option", { value: 3 }, "It's only affecting me (P3)"),
          el("option", { value: 4 }, "This is a change and not a fault (P4)"),
          el("option", { value: 5 }, "This is a project work (P5)")
        )
      )
    );
  };
  handleCheckListChange = (value) => {
    const { data, checkList } = this.state;
    const index = checkList.findIndex((c) => c.id == value);
    if (index > -1) {
      data.internalNotesAppend = checkList[index].content;
    } else data.internalNotesAppend = "";
  };
  getCheckList = () => {
    const { el, setValue } = this;
    const { data, checkList } = this.state;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Is a checklist needed?")),
      el(
        "td",
        null,
        el(
          "select",
          {
            value: data.internalNotesId,
            onChange: (event) => this.handleCheckListChange(event.target.value),
            style: { width: 200 },
          },
          el("option", { key: "i_1", value: -1 }, "Select Standard Text "),
          checkList.map((s) =>
            el("option", { key: "i" + s.id, value: s.id }, s.title)
          )
        )
      )
    );
  };
  getContactsElement = () => {
    const { el } = this;
    const { data, contacts } = this.state;
    const contactsGroup = groupBy(contacts, "siteTitle");
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Contact ")),
      el(
        "td",
        null,
        el(
          "select",
          {
            value: data.contactID,
            onChange: (event) => this.handleContactSelect(event.target.value),
            style: { width: 200 },
          },
          el("option", { key: "i_1", value: -1 }, "Please Select "),
          contactsGroup.map((group, index) => {
            return el(
              "optgroup",
              { key: group.groupName, label: group.groupName },
              contactsGroup[index].items.map((item) =>
                el(
                  "option",
                  { key: "i" + item.id, value: item.id },
                  item.name + " " + (item.startMainContactStyle || "")
                )
              )
            );
          })
        )
      )
    );
  };
  handleContactSelect = (contactID) => {
    const { data, contacts } = this.state;
    data.contactID = contactID;
    let requireAuthorize = this.checkContactNeedAuthorize(contactID, contacts);
    console.log(requireAuthorize);
    this.setState({ data, requireAuthorize });
  };
  checkContactNeedAuthorize = (contactID, contacts) => {
    const { data } = this.state;
    let requireAuthorize = false;
    //contactID
    const contact = contacts.find((item) => item.id == contactID);
    console.log(contactID, contact);
    if (contact.startMainContactStyle == "- Delegate") requireAuthorize = true;
    else {
      requireAuthorize = false;
      data.authorisedBy = "";
    }
    return requireAuthorize;
  };
  getAuthorizeByElement = () => {
    const { requireAuthorize, contacts, data } = this.state;
    const { el } = this;
    if (!requireAuthorize) return null;

    const contactSupervisor = groupBy(
      contacts.filter((contact) => {
        if (
          contact.startMainContactStyle == "*" ||
          contact.startMainContactStyle == "- Supervisor"
        )
          return true;
        else return false;
      }),
      "siteTitle"
    );

    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Authorised By	")),
      el(
        "td",
        null,
        el(
          "select",
          {
            value: data.authorisedBy,
            onChange: (event) =>
              this.setValue("authorisedBy", event.target.value),
            style: { width: 200 },
          },
          el("option", { key: "i_1", value: "" }, "Please Select"),
          contactSupervisor.map((group, index) => {
            return el(
              "optgroup",
              { key: group.groupName, label: group.groupName },
              contactSupervisor[index].items.map((item) =>
                el(
                  "option",
                  { key: "i" + item.id, value: item.id },
                  item.name + " " + (item.startMainContactStyle || "")
                )
              )
            );
          })
        )
      )
    );
  };

  getQueueElement = () => {
    const { el } = this;
    const { data } = this.state;
    let queueFiltered = SRQueues;
    if (this.props.data.customer.hasPrepay === "1")
      queueFiltered = SRQueues.filter((q) => q.id != 6);
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Queue ")),
      el(
        "td",
        null,
        el(
          "select",
          {
            value: data.queueNo,
            onChange: (event) => this.setValue("queueNo", event.target.value),
            style: { width: 200 },
          },
          el("option", { key: "empty", value: -1 }, "Select Queue "),
          queueFiltered.map((s) =>
            el("option", { key: "i" + s.id, value: s.id }, s.name)
          )
        )
      )
    );
  };
  getNotStartReasonElement = () => {
    const { el } = this;
    const { _showModal, noWorkOptions, data } = this.state;
    if (!_showModal) return null;
    return el(StandardTextModal, {
      show: _showModal,
      options: noWorkOptions,
      value: data.notStartWorkReason,
      title: "Why you don't want to start working now?",
      okTitle: "Ok",
      onChange: this.handleNoWorkReason,
    });
  };
  handleNoWorkReason = (value) => {
    const { data } = this.state;
    data.notStartWorkReason = value;
    this.setState({ data });
  };
  checkStartWorkNow = () => {
    const { currentUser } = this.props.data;
    const { customer } = this.props.data;
    const { data } = this.state;
    const result = true;
    let _showModal = false;
    if (
      customer.hasServiceDesk != null &&
      currentUser.teamID == TeamType.Helpdesk &&
      data.notStartWorkReason == "" &&
      !data.startWork
    ) {
      if (!confirm("Do you want to start working on this now?")) {
        _showModal = true;
      } else data.startWork = true;
    }
    if (result) this.setState({ data, _showModal });
    return true;
  };
  isValid = () => {
    const { data, requireAuthorize } = this.state;
    if (data.contactID == -1) {
      alert("Please select contact");
      return false;
    }
    if (requireAuthorize && data.authorisedBy == "") {
      alert("Please Select Authorize By");
      return false;
    }

    if (data.priority == -1) {
      alert("Please select priority");
      return false;
    }
    if (data.queueNo == -1) {
      alert("Please select queue");
      return false;
    }
    if (data.reason == "") {
      alert("Please select queue");
      return false;
    }
    return true;
  };
  getDocumentsElement = () => {
    const { el } = this;
    return el(
      "div",
      null,
      el("h3", null, "Upload Documents"),
      el("input", {
        ref: this.fileUploader,
        name: "usefile",
        type: "file",
        style: { display: "none" },
        multiple: "multiple",
        onChange: this.handleFileSelected,
      }),
      el("i", {
        className: "fal fa-plus pointer icon icon-size-1",
        onClick: this.handleSelectFiles,
      })
    );
  };
  handleSelectFiles = () => {
    this.fileUploader.current.click();
  };
  handleFileSelected = (e) => {
    const uploadFiles = [...e.target.files];
    console.log(uploadFiles);
    const {data}=this.state;
    data.uploadFiles=uploadFiles;
    this.setState({ data });
  };
  getSelectedFilesElement = () => {
    const { uploadFiles } = this.state.data;
    const { el } = this;
    return el(
      "table",
      { className: "table table-striped",style:{maxWidth:400} },
      el(
        "tbody",
        null,
        uploadFiles.map((file) =>
          el(
            "tr",
            {key:file.name+'tr'},
            el("td", {key:file.name+'td'}, file.name),
            el("td", {key:file.name+'trash'}, el("i", { className: "fal fa-trash pointer icon float-right",title:"delete file",onClick:()=>this.deleteDocument(file) }))
          )
        )
      )
    );
  };
  deleteDocument=(file)=>{
    let { data } = this.state;
    data.uploadFiles= data.uploadFiles.filter(f=>f.name!=file.name);    
    this.setState({data});
  }
  getElements = () => {
    const {
      el,
      getChkProblemBefore,
      getProblemPriority,
      getCheckList,
      getcriticalSRFlagBefore,
      gethideFromCustomerFlag,
      getmonitorSRFlag,
    } = this;
    return el(
      "table",
      null,
      el(
        "tbody",
        null,
        getChkProblemBefore(),
        getcriticalSRFlagBefore(),
        gethideFromCustomerFlag(),
        getmonitorSRFlag(),
        this.getAuthorizeByElement(),
        this.getContactsElement(),
        getProblemPriority(),
        getCheckList(),
        this.getQueueElement()

        //  this.getDateRaisedElement(),
        //  this.getStartTimeElement(),
      )
    );
  };
  render() {
    const { el, getElements } = this;
    return el(
      "div",
      null,
      getElements(),
      this.getDocumentsElement(),
      this.getSelectedFilesElement(),
      this.getNextButton(),
      this.getNotStartReasonElement()
    );
  }
}

export default CMPLastStep;
