import { reactive } from 'vue'

export const store = reactive({
    username: window.localStorage.getItem('username') ? window.localStorage.getItem('username') : null,
    token: window.localStorage.getItem('token') ? window.localStorage.getItem('token') : null,
    charId: window.localStorage.getItem('charId') ? window.localStorage.getItem('charId') : null,
    uCash: window.localStorage.getItem('uCash') ? window.localStorage.getItem('uCash') : null,
});

export const storeClear=()=>{
    store.username=null;
    store.token=null;
    store.charId=null;
    store.uCash=null;
}
