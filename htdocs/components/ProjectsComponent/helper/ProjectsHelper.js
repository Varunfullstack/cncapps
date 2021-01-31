import MainComponent from "../../shared/MainComponent";
import React from 'react';

export default  class ProjectsHelper extends MainComponent{
    getLatestUpdate(lastUpdate) {
        if (!lastUpdate||!lastUpdate.createdBy) return "No updates";
        const createdAtDate = moment(lastUpdate.createdAt);
        const todayMinus14Days = moment().subtract(14, "d");
        let lastUpdatedClass = "";
        if (!lastUpdate.commenceDate && createdAtDate <= todayMinus14Days) {
          lastUpdatedClass = "red";
        }
        return (
          <div className={lastUpdatedClass}>
            <span className="bold">
              {this.getCorrectDate(lastUpdate.createdAt)} by {lastUpdate.createdBy}:{" "}
            </span>
            <span> {lastUpdate.comment}</span>
          </div>
        );
      }
    getRedClass(v1,v2){
        return v1 > v2 ? "red" : "";
    }
}