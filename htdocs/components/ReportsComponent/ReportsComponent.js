
import React from 'react';
import ReactDOM from 'react-dom';
import MainComponent from "../shared/MainComponent";
import { params } from '../utils/utils';
import './../style.css';
import AppReport from './AppReport';
import './ReportsComponent.css';
//categoryID=1&&hideCategories=true
class ReportsComponent extends MainComponent {  
   
  constructor(props) {
    super(props);
    
  }
  componentDidMount() {    
  }   
  render() {
    const currentCategoryID=params.get("categoryID");
    const hideCategories=params.get("hideCategories")=='true'?true:false;
    return <AppReport categoryID={currentCategoryID} hideCategories={hideCategories}></AppReport>
  }
}
export default ReportsComponent;

document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactMainReports");
  ReactDOM.render(React.createElement(ReportsComponent), domContainer);
});
