import APICallactType from "../services/APICallacttype.js";
import APIUser from "../services/APIUser.js";
import Modal from "../utils/modal.js";
/**
 * onCancel -> call when cancel button click
 *
 */
class ActivityFollowOn extends React.Component {
  el = React.createElement;
  apiCallactType = new APICallactType();
  apiUser = new APIUser();

  constructor(props) {
    super(props);
    this.state = { types: [], callActTypeID: "" };
  }
  componentDidMount() {
    Promise.all([
      this.apiCallactType.getAll(),
      this.apiUser.getCurrentUser(),
    ]).then((result) => {
      const currentUser=result[1];    
      let types=result[0];
      console.log(currentUser,types);
      if(!currentUser.isSDManger)
      {
        types=types.filter(c=>c.visibleInSRFlag=='Y')
      }
      this.setState({ types });
    });
  }
  getModal = () => {
    const { el } = this;
    const { types } = this.state;
    return el(Modal, {
      key: "followOnModal",
      show: true,
      width: 400,
      title: "Create a Follow Activity",
      onClose:this.handleCancel,
      content: el(
        "div",
        { key: "divContainer" },
        el("label", { key: "label" }, "Activity Type"),
        el(
          "select",
          {
            required: true,
            value: this.state.callActTypeID,
            onChange: (event) =>
              this.setState({ callActTypeID: event.target.value }),
            style: { width: "100%", marginBottom:20},
            
          },
          el("option", { key: "empty", value: "" }, "Please select"),
          types?.map((t) =>
            el("option", { key: t.id, value: t.id }, t.description)
          )
        )
      ),
      footer: el(
        "div",
        {key:"divFooter"},
        el("button", {onClick: this.handleCreate }, "Create"),
        el("button", { onClick: this.handleCancel }, "Cancel"),
        
      ),
    });
  };
  handleCreate = () => {
    const { startWork,callActivityID} = this.props;
    const { callActTypeID } = this.state;
    if (callActTypeID == "") {
      alert("Please select Activity Type");
      return;
    }
    if (startWork) {
      if (
        confirm(
          "You are about to commence work and an email will be sent to the customer?"
        )
      )
        window.location = `Activity.php?action=createFollowOnActivity&callActivityID=${callActivityID}&callActivityTypeID=${callActTypeID}`;
    } else
      window.location = `Activity.php?action=createFollowOnActivity&callActivityID=${callActivityID}&callActivityTypeID=${callActTypeID}`;
  };
  handleCancel = () => {
    if (this.props.onCancel) this.props.onCancel();
  };
 
  render() {
    return this.getModal();
  }
}

export default ActivityFollowOn;
