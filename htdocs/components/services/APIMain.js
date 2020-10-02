
class APIMain {
    getCustomers()
    {
      return  fetch("/Customer.php?action=searchName")
        .then((res) => res.json());
    }
    getCurrentUser()
    {
      return  fetch("/User.php?action=getCurrentUser")
        .then((res) => res.json());
    }
    uploadFiles(url,files,name)
    {
      let payload=new FormData(); 
      for (const file of files) 
            payload.append(name,file);      
      return fetch(url, {
        method: "POST",
        body: payload        
      });
    }
    post(url,payload){
        return fetch(url, {
        method: "POST",
        body: JSON.stringify(payload)
      });
    }
    postFormData(url,payload){
      return fetch(url, {
      method: "POST",
      body: payload
    });
  }
    
}
export default APIMain;