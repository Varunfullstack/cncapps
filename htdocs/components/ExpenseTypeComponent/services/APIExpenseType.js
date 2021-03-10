import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIExpenseType extends APIMain {
  getAllTypes() {
    return this.get(`${ApiUrls.ExpenseType}types`);
  }

  addType(body) {
    return this.postJson(`${ApiUrls.ExpenseType}types`, body);
  }
  
  updateType(body) {
    return this.put(`${ApiUrls.ExpenseType}types`, body);
  }

  deleteType(id) {
    return this.delete(`${ApiUrls.ExpenseType}types&&id=${id}`);
  }
  
  getExpenseActivityTypes(id){
    return this.get(`${ApiUrls.ExpenseType}expenseActivityTypes&&id=${id}`);

  }
}
