import { defineStore } from "pinia";

export const userInfoStore = defineStore('userInfo',{
    state:()=>{
        return {
            username:window.localStorage.getItem('username'),
            token:window.localStorage.getItem('token'),
            charId:window.localStorage.getItem('charId'),
            uCash:window.localStorage.getItem('uCash'),
            collapsed:true
        }
    },

    actions:{
        updateUserName(username){
            this.username = username;
        },
        updateToken( token){
            this.token = token;
        },
        updateCharId(charId){
            this.charId = charId;
        },
        updateUCash(uCash){
            this.uCash = uCash;
        },
        updateCollapsed(){
            this.collapsed=!this.collapsed;
        },

        clearAll(){
            debugger
            this.username=null;
            this.token=null;
            this.charId=null;
            this.uCash=null;
            collapsed = true;
        }
    }
});