class APIMain {
    get(url) {
        return fetch(url)
        .then((res) => this.handleResponse(res));
    }

    getCustomers() {
        return fetch("/Customer.php?action=searchName")
            .then((res) => res.json());
    }

    getCurrentUser() {
        return fetch("/User.php?action=getCurrentUser")
            .then((res) => res.json());
    }

    uploadFiles(url, files, name, data = null, handleException = false) {
        let payload = new FormData();
        for (const file of files) payload.append(name, file);
        if (data) payload.append("data", JSON.stringify(data));
        if (!handleException)
            return fetch(url, {
                method: "POST",
                body: payload,
            });
        else
            return fetch(url, {
                method: "POST",
                body: payload,
            }).then((res) => this.handleResponse(res));
    }
    uploadFile(url, file, name, data = null ) {
        let payload = new FormData();
        payload.append(name, file);
        if (data) payload.append("data", JSON.stringify(data));
            return fetch(url, {
                method: "POST",
                body: payload,
            }).then((res) => this.handleResponse(res));
    }

    post(url, payload,handleException=false) {
      if (!handleException)
        return fetch(url, {
            method: "POST",
            body: JSON.stringify(payload)
        });
      else
        return fetch(url, {
          method: "POST",
          body: JSON.stringify(payload)
        }).then((res) => this.handleResponse(res));
    }

    postJson(url, payload) {
        return fetch(url, {
            method: "POST",
            body: JSON.stringify(payload)
        }).then((res) => res.json());
    }

    put(url, payload, handleException = false) {

        if (!handleException)
            return fetch(url, {
                method: "PUT",
                body: JSON.stringify(payload),
            }).then((res) => res.json());
        else
            return fetch(url, {
                method: "PUT",
                body: JSON.stringify(payload),
            }).then((res) => this.handleResponse(res));
    }

    delete(url,handleException=false) {
      if (!handleException)
        return fetch(url, {
            method: "DELETE",
        }).then((res) => res.json());
        else
        return fetch(url, {
          method: "DELETE",
      }).then((res) => this.handleResponse(res));
    }

    postFormData(url, payload) {
        return fetch(url, {
            method: "POST",
            body: payload
        });
    }

    handleResponse = async (response) => {
        const statusCode = response.status;
        const text = await response.text();
        return new Promise((res, reject) => {
            try {
                const textJson = JSON.parse(text);
                textJson.status = statusCode;
                if (response.ok) return res(textJson);
                else {
                    textJson.status = statusCode;
                    return reject(textJson);
                }
            } catch (ex) {
                return reject({state: false, error: "Data not saved successfully", responseCode: 400});
            }
        })

    }
    getCustomersByLeadStatus(leadStatusId,customerID) {
        return fetch(`Customer.php?action=searchByLeadStatus&leadStatusID=${leadStatusId}&customerID=${customerID?customerID:""}`)
            .then((res) => res.json());
    }
}

export default APIMain;