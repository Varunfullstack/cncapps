import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIReports extends APIMain {
 getReportCategoriesActive(active=1){
    return this.get(`${ApiUrls.Reports}reportCategories&active=${active}`);
 }

 getCategoryReports(categoryID){
    return this.get(`${ApiUrls.Reports}categoryReports&categoryID=${categoryID}`);
 }

 getReportParameters(reportID){
   return this.get(`${ApiUrls.Reports}reportParamters&reportID=${reportID}`);
 }

}
