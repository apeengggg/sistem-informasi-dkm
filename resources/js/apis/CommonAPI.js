import axios from 'axios'
import utils from "@/utils/CommonUtils"
import Swal from 'sweetalert2'

export default {
    async getCommon(url, params, callback) {
        const request = await axios
            .get(process.env.VUE_APP_API_URL + `/api/common${url}${params}`, {
                headers: {
                    Authorization: 'Bearer ' + localStorage.token,
                },
            })
            .then((response) => {
                if (callback) {
                    return callback(response, null)
                }
                return response
            })
            .catch((error) => {
                if (callback) {
                    return callback(error.response, error)
                }
                return error
            })
        return request
    },

    async get(url, params, callback) {
        let config = {
            headers: { 
                Authorization: 'Bearer ' + localStorage.token,
            },
        }

        let detailUrl = url
        if(params == '?'){
            detailUrl = process.env.VUE_APP_API_URL + `${url}`
        }else{
            detailUrl = process.env.VUE_APP_API_URL + `${url}`
        }
        const request = axios
            .get(detailUrl, config)
            .then((response) => {
                if (callback) {
                    return callback(response, null)
                }
                return response
            })
            .catch((error) => {
                if (callback) {
                    return callback(error.response, error)
                }
                return error
            })
        return request
    },

    async post(url, params, callback) {
        const request = await axios
            .post(process.env.VUE_APP_API_URL + `${url}`, params, {
                headers: {
                    Authorization: 'Bearer ' + localStorage.token,
                },
            })
            .then((response) => {
                if (callback) {
                    return callback(response, null)
                }
                return response
            })
            .catch((error) => {
                if (callback) {
                    return callback(error.response, error)
                }
                return error
            })
        return request
    },

    async put(url, id, params, callback) {
        const request = await axios
            .put(process.env.VUE_APP_API_URL + `${url}/edit/${id}`, params, {
                headers: {
                    Authorization: 'Bearer ' + localStorage.token,
                },
            })
            .then((response) => {
                if (callback) {
                    return callback(response, null)
                }
                return response
            })
            .catch((error) => {
                if (callback) {
                    return callback(error.response, error)
                }
                return error
            })
        return request
    },
    
    async delete(url, id, callback) {
        const request = await axios
            .delete(process.env.VUE_APP_API_URL + `${url}/delete/${id}`, {
                headers: {
                    Authorization: 'Bearer ' + localStorage.token,
                },
            })
            .then((response) => {
                if (callback) {
                    return callback(response, null)
                }
                return response
            })
            .catch((error) => {
                if (callback) {
                    return callback(error.response, error)
                }
                return error
            })
        return request
    },
    
    async cmnNodeJsonApi( uri, method, jsonBody){
        return this.execJsonApi(process.env.VUE_APP_CMN_NODE_API_URL + uri, method, jsonBody);
    },

    async jsonApi( uri, method, jsonBody){
        return this.execJsonApi(import.meta.env.VITE_APP_URL + uri, method, jsonBody);
    },

    async etrDownloadApi( uri, method, data){
        return await this.execDownloadApi( process.env.VUE_APP_API_URL + uri, method, data);
    },

    async uploadApi( uri, method, formData){
        return this.execUploadApi(import.meta.env.VITE_APP_URL + uri, method, formData);
    },

    async execJsonApi( uri, method, jsonBody){
        try{
        let payload = {
            method: method, // *GET, POST, PUT, DELETE, etc.
            headers: {
              'Content-Type': 'application/json',
              "Authorization": 'Bearer ' + localStorage.token,
            },
        }

        if(method != 'GET'){
          payload.body = jsonBody;
        }

          let response = await fetch(uri,  payload);
          let jsonResponse = await response.json();
          if(jsonResponse.status == 401){
            Swal.fire('Error!', jsonResponse.message, 'error')
            window.location.href = '/apps/login'
            return
          }
          return jsonResponse;
        }catch(e){       
          console.log("🚀 ~ execJsonApi ~ e:", e)
          if(uri.includes("rate-quality-input") && e.message == 'Failed to fetch'){
            e.message = 'Network Error'
          }

          utils.error(await utils.message('MSGCMN0001',[uri,e.message]));
        }
    },

    async execDownloadApi( uri, method, data){
        try{
          let response = await fetch( uri, {
              method: method, // *GET, POST, PUT, DELETE, etc.
              headers: {
                'Content-Type': 'application/json',
                "Authorization": 'Bearer ' + localStorage.token,
              },
              body: data,
          } );
          await response.blob().then(blob => {
            let FILE = window.URL.createObjectURL(blob);
            let docUrl = document.createElement('a');
            docUrl.href = FILE;
            let fileName = response.headers.get('content-disposition')
              .split(';')
              .find(n => n.includes('filename='))
              .replace('filename=', '')
              .trim()
              ;
            docUrl.setAttribute('download', fileName);
            document.body.appendChild(docUrl);
            docUrl.click();
          });
          return ;
        }catch(e){        
          utils.error(await utils.message('MSGCMN0001',[uri,e.message]));
        }
      },

      
    async execUploadApi( uri, method, formData){
        try{
          let response = await fetch( uri, {
              method: method, // *GET, POST, PUT, DELETE, etc.
              headers: {
                "Authorization": 'Bearer ' + localStorage.token,
              },
              body: formData
          });
          let jsonResponse = await response.json();
          return jsonResponse;
        }catch(e){        
          utils.error(await utils.message('MSGCMN0001',[uri,e.message]));
        }
    },

    async execRefreshToken(method){
        try{
            let response = await fetch(process.env.VUE_APP_SC_API_URL+'/api/authenticate', {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    "Authorization": 'Bearer ' + localStorage.token,
                },
                body: JSON.stringify({
                    userName: process.env.VUE_APP_SC_REFRESH_TOKEN_USERNAME,
                    password: process.env.VUE_APP_SC_REFRESH_TOKEN_PASSWORD
                })
            } );
            let jsonResponse = await response.json();
            console.log('jsonResponse', jsonResponse)
            if(jsonResponse.status==='401'){
                localStorage.token = '';
                localStorage.tokenId = '';
                window.location.href=process.env.VUE_APP_CONTAINER_URL;  
                return;
            }
            localStorage.setItem('token', jsonResponse.token)
            localStorage.setItem('tokenId', jsonResponse.token)
            // await callback()
            return jsonResponse;
            }catch(e){       
                utils.error(await utils.message('MSGCMN0001',[process.env.VUE_APP_SC_API_URL+'/api/authenticate',e.message]));
            }
    },
}