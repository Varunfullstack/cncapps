
class APIMain {
    getCustomers()
    {
      return  fetch("/Customer.php?action=searchName")
        .then((res) => res.json());
    }
    getCurrentUser()
    {
      return  fetch("/Customer.php?action=getCurrentUser")
        .then((res) => res.json());
    }
}
export default APIMain;