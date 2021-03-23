import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIProjectOptions extends APIMain {   
  //ProjectStages
  getProjectStages() {
    return this.get(`${ApiUrls.ProjectOptions}projectStages` );
  }
  updateProjectStage(id,body) {
    return this.put(`${ApiUrls.ProjectOptions}projectStages&id=${id}`,body );
  }
  addProjectStage(body) {
    return this.post(`${ApiUrls.ProjectOptions}projectStages`,body ).then((res) => res.json());
  }
  deleteProjectStage(id) {
    return this.delete(`${ApiUrls.ProjectOptions}projectStages&id=${id}` );
  }

  //ProjectTypes
  getProjectTypes() {
    return this.get(`${ApiUrls.ProjectOptions}projectTypes` );
  }
  updateProjectType(id,body) {
    return this.put(`${ApiUrls.ProjectOptions}projectTypes&id=${id}`,body );
  }
  addProjectType(body) {
    return this.post(`${ApiUrls.ProjectOptions}projectTypes`,body ).then((res) => res.json());
  }
  deleteProjectType(id) {
    return this.delete(`${ApiUrls.ProjectOptions}projectTypes&id=${id}` );
  }

  
}
