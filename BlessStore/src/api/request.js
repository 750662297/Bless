import axios from 'axios'
import {getBaseUrl} from "./constant"

const service = axios.create({
    baseURL: getBaseUrl(),
    headers: {
        "Content-Type": "application/json; charset=utf-8",
        // "X-Requested-With": "XMLHttpRequest"
      }
})

//请求拦截器
service.interceptors.request.use(
    (config) =>{
        config.headers["token"] = "";
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
)

//response 拦截器 
service.interceptors.response.use(
    response => {
        //文件传输
        if(response.data &&response.data instanceof Blob) {
            if(response.headers["content-type"] == "application/json;charset=UTF-8"){
                var reader = new FileReader();
                reader.onload = e => {
                    let data =JSON.parse(e.target.result);
                    if(data && (data.code !="200")) {
                        print("error");
                    }

                    else if(data && typeof data.code == "string" && data.code != "200") {
                        print("error exception")
                    }
                }

                reader.readAsText(response.data);
            }
        }
        else{
            return response.data;
        }
    },
    error => {
        print("error exception")
        
        return Promise.reject(error);
    }
);

export default service;