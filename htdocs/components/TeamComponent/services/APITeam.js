import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APITeam extends APIMain {
  getAllTeams() {
    return this.get(`${ApiUrls.Team}teams`);
  }

  addTeam(body) {
    return this.postJson(`${ApiUrls.Team}teams`, body);
  }
  
  updateTeam(body) {
    return this.put(`${ApiUrls.Team}teams`, body);
  }

  deleteTeam(id) {
    return this.delete(`${ApiUrls.Team}teams&&id=${id}`);
  }
  
  getRoles(){
    return this.get(`${ApiUrls.Team}roles`);

  }
}
