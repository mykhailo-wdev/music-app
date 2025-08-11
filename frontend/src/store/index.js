import { createStore } from 'vuex';
import search from "./modules/search";
import auth from "./modules/auth";


export default createStore({
    modules: {
        search,
        auth
    }
})