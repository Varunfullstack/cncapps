import MainComponent from "../shared/MainComponent";
import Spinner from "../shared/Spinner/Spinner";
import './../style.css';
import './ProjectsComponent.css';
class ProjectsComponent extends MainComponent {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    const { el } = this;
    const { showSpinner } = this.state;
    return (
      <div>
        <Spinner key="spinner" show={showSpinner}></Spinner>
        <h1>Welcome</h1>
      </div>
    );
  }
}
export default ProjectsComponent;

document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactMainProjects");
  ReactDOM.render(React.createElement(ProjectsComponent), domContainer);
});
