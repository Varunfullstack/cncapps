class APIMain {
    get(url) {
        return fetch(url)
            .then((res) => res.json());
    }

    getCustomers() {
        return fetch("/Customer.php?action=searchName")
            .then((res) => res.json());
    }

    getCurrentUser() {
        return fetch("/User.php?action=getCurrentUser")
            .then((res) => res.json());
    }

    uploadFiles(url, files, name,data=null) {
        let payload = new FormData();
        for (const file of files)
            payload.append(name, file);
        if(data)
        payload.append('data', JSON.stringify(data));

        return fetch(url, {
            method: "POST",
            body: payload
        });
    }

    post(url, payload) {
        return fetch(url, {
            method: "POST",
            body: JSON.stringify(payload)
        });
    }

    postJson(url, payload) {
        return fetch(url, {
            method: "POST",
            body: JSON.stringify(payload)
        }).then((res) => res.json());
    }

    put(url, payload) {
        return fetch(url, {
            method: "PUT",
            body: JSON.stringify(payload)
        }).then((res) => res.json());
    }
    delete(url ) {
        return fetch(url, {
            method: "DELETE",            
        }).then((res) => res.json());
    }
    postFormData(url, payload) {
        return fetch(url, {
            method: "POST",
            body: payload
        });
    }

}

export default APIMain;